import test from "node:test";
import assert from "node:assert/strict";
import {
  addWorkingDays,
  addWorkingDaysDetailed,
  workingDaysBetween,
  appealReadiness,
  RTI_RESPONSE_WORKING_DAYS,
} from "./rti.ts";
import {
  ANNOUNCED_HOLIDAYS,
  holidaysFor,
  isWeekend,
  isNonWorkingDay,
  announcedHolidaysKnownFor,
  type Holiday,
} from "./holidays.ts";

/**
 * RTI statutory deadlines.
 *
 * The failure these guard against is specific: a deadline computed without
 * public holidays lands EARLY, and an appeal filed on an early deadline is
 * premature, so it can be thrown out and the citizen has to start over. Skipping
 * Friday and Saturday was not enough — around Eid the government closes for
 * several consecutive days.
 */

/** Runs a test with a temporary announced-holiday list, then puts it back. */
function withAnnouncedHolidays(
  year: number,
  holidays: Holiday[],
  run: () => void
): void {
  const previous = ANNOUNCED_HOLIDAYS[year];
  ANNOUNCED_HOLIDAYS[year] = holidays;
  try {
    run();
  } finally {
    if (previous === undefined) delete ANNOUNCED_HOLIDAYS[year];
    else ANNOUNCED_HOLIDAYS[year] = previous;
  }
}

test("the Bangladeshi weekend is Friday and Saturday", () => {
  // 2026-01-02 is a Friday, 2026-01-03 a Saturday, 2026-01-04 a Sunday.
  assert.equal(isWeekend(new Date("2026-01-02T00:00:00")), true, "Friday");
  assert.equal(isWeekend(new Date("2026-01-03T00:00:00")), true, "Saturday");
  assert.equal(isWeekend(new Date("2026-01-04T00:00:00")), false, "Sunday is a working day");
});

test("fixed-date national holidays are known without anyone entering them", () => {
  const dates = holidaysFor(2026).map((h) => h.date);

  assert.ok(dates.includes("2026-02-21"), "Shaheed Day");
  assert.ok(dates.includes("2026-03-26"), "Independence Day");
  assert.ok(dates.includes("2026-12-16"), "Victory Day");
});

test("a national holiday is not a working day", () => {
  assert.equal(isNonWorkingDay(new Date("2026-03-26T00:00:00")), true, "Independence Day");
});

test("a fixed holiday inside the window pushes the deadline out", () => {
  // Independence Day, 26 March 2026, is a Thursday — a day that would otherwise
  // have counted.
  const start = new Date("2026-03-22T00:00:00"); // Sunday
  const result = addWorkingDaysDetailed(start, 5);

  assert.ok(
    result.holidaysSkipped.some((h) => h.date === "2026-03-26"),
    "Independence Day should have been skipped"
  );

  // Without the holiday the fifth working day would be Thursday 26 March;
  // skipping it moves the deadline to Sunday 29 March, since Friday and
  // Saturday are the weekend.
  assert.equal(result.date.toISOString().slice(0, 10), "2026-03-29");
});

test("an Eid closure pushes the deadline later, not earlier", () => {
  // The case the benchmark flagged. Government offices close for several
  // consecutive days; without them the deadline lands early and an appeal filed
  // on it is premature.
  const eid: Holiday[] = [
    { date: "2026-03-19", nameEn: "Eid al-Fitr", nameBn: "ঈদুল ফিতর" },
    { date: "2026-03-22", nameEn: "Eid al-Fitr", nameBn: "ঈদুল ফিতর" },
    { date: "2026-03-23", nameEn: "Eid al-Fitr", nameBn: "ঈদুল ফিতর" },
  ];

  const start = new Date("2026-03-15T00:00:00");

  const withoutEid = addWorkingDaysDetailed(start, RTI_RESPONSE_WORKING_DAYS).date;

  withAnnouncedHolidays(2026, eid, () => {
    const withEid = addWorkingDaysDetailed(start, RTI_RESPONSE_WORKING_DAYS);

    assert.ok(
      withEid.date > withoutEid,
      "accounting for Eid must move the deadline later, never earlier"
    );
    assert.equal(withEid.holidaysSkipped.filter((h) => h.nameEn === "Eid al-Fitr").length, 3);
  });
});

test("a deadline in a year with no announced holidays is flagged as incomplete", () => {
  // Nothing has been entered for 2030, so Eid is unaccounted for and the date is
  // probably early. That must be visible rather than silent.
  const result = addWorkingDaysDetailed(new Date("2030-01-05T00:00:00"), 20);

  assert.equal(result.holidaysIncomplete, true);
});

test("a deadline is not flagged once the year's holidays are entered", () => {
  withAnnouncedHolidays(2026, [
    { date: "2026-03-19", nameEn: "Eid al-Fitr", nameBn: "ঈদুল ফিতর" },
  ], () => {
    const result = addWorkingDaysDetailed(new Date("2026-03-02T00:00:00"), 5);

    assert.equal(result.holidaysIncomplete, false);
  });
});

test("the completeness check spans every year the window touches", () => {
  withAnnouncedHolidays(2026, [], () => {
    // A window starting in late 2026 and ending in 2027, where 2027 is unknown.
    assert.equal(
      announcedHolidaysKnownFor(new Date("2026-12-20T00:00:00"), new Date("2027-01-20T00:00:00")),
      false
    );
    assert.equal(
      announcedHolidaysKnownFor(new Date("2026-03-01T00:00:00"), new Date("2026-04-01T00:00:00")),
      true
    );
  });
});

test("the deadline never lands on a non-working day", () => {
  // Whatever the start, the twentieth working day must itself be a working day.
  for (const startIso of [
    "2026-01-04",
    "2026-02-15",
    "2026-03-01",
    "2026-04-10",
    "2026-12-01",
  ]) {
    const deadline = addWorkingDays(new Date(`${startIso}T00:00:00`), RTI_RESPONSE_WORKING_DAYS);
    assert.equal(isNonWorkingDay(deadline), false, `deadline from ${startIso}`);
  }
});

test("counting back agrees with counting forward", () => {
  const start = new Date("2026-05-03T00:00:00");
  const deadline = addWorkingDays(start, RTI_RESPONSE_WORKING_DAYS);

  assert.equal(workingDaysBetween(start, deadline), RTI_RESPONSE_WORKING_DAYS);
});

test("working days between counts holidays as non-working", () => {
  // 26 March 2026 sits between these two dates and must not be counted.
  const from = new Date("2026-03-24T00:00:00");
  const to = new Date("2026-03-30T00:00:00");

  const days = workingDaysBetween(from, to);

  // 25 (Wed), 29 (Sun), 30 (Mon) are working; 26 is Independence Day, 27–28 the
  // weekend.
  assert.equal(days, 3);
});

test("an appeal before the deadline is premature", () => {
  const deadline = new Date("2026-05-20T00:00:00");
  const readiness = appealReadiness(deadline, new Date("2026-05-19T00:00:00"));

  assert.equal(readiness.premature, true);
  assert.equal(readiness.ready, false);
});

test("an appeal on or after the deadline is ready", () => {
  const deadline = new Date("2026-05-20T00:00:00");
  const readiness = appealReadiness(deadline, new Date("2026-05-20T00:00:00"));

  assert.equal(readiness.ready, true);
  assert.equal(readiness.premature, false);
});

test("readiness is marked uncertain when the year's holidays are unknown", () => {
  const readiness = appealReadiness(new Date("2030-05-20T00:00:00"), new Date("2030-06-01T00:00:00"));

  assert.equal(readiness.uncertain, true);
});
