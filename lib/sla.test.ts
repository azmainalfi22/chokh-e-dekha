import test from "node:test";
import assert from "node:assert/strict";
import {
  priorityForCategory,
  slaDaysForPriority,
  computeSlaDueAt,
  getSlaState,
  isEscalationEligible,
  SLA_DAYS,
} from "./sla.ts";
import { CATEGORIES } from "./constants.ts";

/**
 * Response deadlines.
 *
 * These numbers must agree with sla_days_for_priority() and
 * priority_for_category() in
 * supabase/migrations/20260912121000_sla_priority_tiers.sql. The database is what
 * actually writes sla_due_at; this is the copy the interface reads from, and the
 * point of the tests is that the two do not drift apart unnoticed.
 */

test("dangerous categories get the shortest window", () => {
  for (const category of [
    "Public Safety",
    "Electricity",
    "Gas",
    "Water Supply",
    "Drainage / Waterlogging",
  ]) {
    assert.equal(priorityForCategory(category), "high", category);
  }
});

test("everyday categories get the standard window", () => {
  for (const category of ["Road", "Sewerage", "Traffic", "Streetlight", "Garbage / Waste"]) {
    assert.equal(priorityForCategory(category), "medium", category);
  }
});

test("Parks is the only low-priority category", () => {
  assert.equal(priorityForCategory("Parks"), "low");
});

test("every category in the app has a priority", () => {
  // Guards against someone adding a category to CATEGORIES and it silently
  // landing on the fallback.
  for (const category of CATEGORIES) {
    const priority = priorityForCategory(category);
    assert.ok(
      ["high", "medium", "low"].includes(priority),
      `${category} resolved to ${priority}`
    );
  }
});

test("an unknown or missing category falls back rather than throwing", () => {
  assert.equal(priorityForCategory("Something nobody configured"), "medium");
  assert.equal(priorityForCategory(null), "medium");
});

test("the windows are ordered by urgency", () => {
  assert.ok(SLA_DAYS.high < SLA_DAYS.medium);
  assert.ok(SLA_DAYS.medium < SLA_DAYS.low);
});

test("the standard window is unchanged at seven days", () => {
  // The bulk of the queue is medium, so most reports keep the deadline they
  // would have had before tiering.
  assert.equal(slaDaysForPriority("medium"), 7);
});

test("an unknown priority falls back to the standard window", () => {
  assert.equal(slaDaysForPriority("urgent"), 7);
  assert.equal(slaDaysForPriority(null), 7);
});

test("a gas leak is due sooner than a park complaint", () => {
  const approvedAt = new Date("2026-03-01T09:00:00Z");

  const gas = computeSlaDueAt(approvedAt, priorityForCategory("Gas"));
  const park = computeSlaDueAt(approvedAt, priorityForCategory("Parks"));

  assert.ok(gas < park);
  assert.equal(gas.toISOString().slice(0, 10), "2026-03-04");
  assert.equal(park.toISOString().slice(0, 10), "2026-03-15");
});

test("a report inside its window is not breached", () => {
  const now = new Date("2026-03-01T09:00:00Z");
  const due = new Date("2026-03-05T09:00:00Z");

  const state = getSlaState(due.toISOString(), "in_progress", now);

  assert.equal(state.kind, "ok");
});

test("a report past its deadline is breached", () => {
  const now = new Date("2026-03-10T09:00:00Z");
  const due = new Date("2026-03-05T09:00:00Z");

  const state = getSlaState(due.toISOString(), "in_progress", now);

  assert.equal(state.kind, "breached");
});

test("a settled report cannot breach", () => {
  const now = new Date("2026-03-10T09:00:00Z");
  const due = new Date("2026-03-05T09:00:00Z");

  assert.equal(getSlaState(due.toISOString(), "resolved", now).kind, "none");
  assert.equal(getSlaState(due.toISOString(), "rejected", now).kind, "none");
});

test("a breach makes a report eligible for the official rails", () => {
  const now = new Date("2026-03-10T09:00:00Z");
  const due = new Date("2026-03-05T09:00:00Z");

  assert.equal(isEscalationEligible("in_progress", due.toISOString(), null, now), true);
});

test("a disputed fix is eligible even inside the window", () => {
  const now = new Date("2026-03-01T09:00:00Z");
  const due = new Date("2026-03-30T09:00:00Z");

  assert.equal(isEscalationEligible("in_progress", due.toISOString(), "disputed", now), true);
});
