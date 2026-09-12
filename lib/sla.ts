import { DEFAULT_SLA_DAYS } from "@/lib/constants";

export type Priority = "high" | "medium" | "low";

/**
 * Days allowed for each priority.
 *
 * Must agree with sla_days_for_priority() in
 * supabase/migrations/20260912121000_sla_priority_tiers.sql. The database is the
 * source of truth — it is the only place the two publication paths meet, so it
 * is what actually sets sla_due_at. This copy exists so the interface can say
 * "high priority: 3 days" without a round trip, and so the numbers can be
 * asserted in a test.
 *
 * Wall-clock days, not working days: a blocked drain does not stop flooding a
 * street because it is Friday.
 */
export const SLA_DAYS: Record<Priority, number> = {
  high: 3,
  medium: DEFAULT_SLA_DAYS,
  low: 14,
};

/**
 * Priority for a category.
 *
 * Mirrors priority_for_category() in the same migration. Derived from the
 * category rather than accepted from the reporter, who would otherwise mark
 * their own pothole urgent.
 *
 * high   — can injure someone today: live wires, gas, no drinking water, and
 *          waterlogging, which in Dhaka means both drowning risk and disease.
 * medium — degrades daily life, and is the bulk of the queue.
 * low    — genuine but not urgent.
 */
const CATEGORY_PRIORITY: Record<string, Priority> = {
  "Public Safety": "high",
  Electricity: "high",
  Gas: "high",
  "Water Supply": "high",
  "Drainage / Waterlogging": "high",
  Road: "medium",
  Sewerage: "medium",
  Traffic: "medium",
  Streetlight: "medium",
  "Garbage / Waste": "medium",
  "Illegal Construction": "medium",
  Parks: "low",
};

export function priorityForCategory(category: string | null): Priority {
  if (!category) return "medium";
  return CATEGORY_PRIORITY[category] ?? "medium";
}

export function slaDaysForPriority(priority: string | null): number {
  if (priority === "high" || priority === "medium" || priority === "low") {
    return SLA_DAYS[priority];
  }
  return DEFAULT_SLA_DAYS;
}

/**
 * Compute an SLA deadline (SPEC.md: FR-10).
 *
 * The database sets sla_due_at on both publication paths, so this is for
 * previewing a deadline in the interface, not for writing one.
 */
export function computeSlaDueAt(
  approvedAt: Date,
  priority: string | null = "medium"
): Date {
  const due = new Date(approvedAt);
  due.setDate(due.getDate() + slaDaysForPriority(priority));
  return due;
}

export type SlaState =
  | { kind: "none" }
  | { kind: "ok"; daysLeft: number }
  | { kind: "due-soon"; daysLeft: number }
  | { kind: "breached"; daysOverdue: number };

/**
 * A report can be escalated to the official rails (GRS / 333) when the
 * authority is demonstrably failing: the response deadline has passed, or
 * a claimed fix was disputed by the reporter.
 */
export function isEscalationEligible(
  status: string,
  slaDueAt: string | null,
  resolutionState: string | null,
  now: Date = new Date()
): boolean {
  if (resolutionState === "disputed") return true;
  return getSlaState(slaDueAt, status, now).kind === "breached";
}

/**
 * SLA state for a report. Only open reports (pending / in_progress) can
 * breach — resolved and rejected reports are settled.
 */
export function getSlaState(
  slaDueAt: string | null,
  status: string,
  now: Date = new Date()
): SlaState {
  if (!slaDueAt || status === "resolved" || status === "rejected") {
    return { kind: "none" };
  }
  const due = new Date(slaDueAt);
  const msLeft = due.getTime() - now.getTime();
  const days = Math.ceil(Math.abs(msLeft) / 86_400_000);
  if (msLeft < 0) return { kind: "breached", daysOverdue: days };
  if (msLeft < 2 * 86_400_000) return { kind: "due-soon", daysLeft: days };
  return { kind: "ok", daysLeft: days };
}
