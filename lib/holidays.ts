/**
 * Bangladeshi public holidays, for statutory deadline arithmetic.
 *
 * RTI deadlines are counted in working days, and a working day is not merely a
 * day that is not Friday or Saturday. Government offices close for national
 * holidays, and around Eid they close for several consecutive days. Ignoring
 * them makes the computed deadline **early**, and an appeal filed on an early
 * date is premature: the authority's statutory time has not actually run out,
 * so the appeal can be rejected and the citizen has to start again.
 *
 * Two kinds of holiday, treated differently on purpose:
 *
 *  - **Fixed-date.** Same Gregorian date every year. Listed below and applied
 *    automatically.
 *  - **Announced.** Eid al-Fitr, Eid al-Adha, Ashura, Durga Puja, Shab-e-Barat
 *    and the rest move with the lunar calendar, and in Bangladesh the exact days
 *    are fixed by government notification after a moon sighting. They cannot be
 *    computed, only looked up, so they must be entered per year from the
 *    gazette.
 *
 * When a deadline falls in a year whose announced holidays have not been
 * entered, the calculation says so rather than quietly returning a date that is
 * probably too early. A visible "not holiday-adjusted" caveat is worth far more
 * than a confident wrong answer, because the wrong answer is the one that gets
 * an appeal thrown out.
 */

export type Holiday = {
  /** ISO date, YYYY-MM-DD. */
  date: string;
  nameEn: string;
  nameBn: string;
};

/**
 * Fixed-date national holidays.
 *
 * Deliberately limited to dates that do not move and are not in political
 * dispute. Several other days have been added to and removed from Bangladesh's
 * national holiday list over the years; entering one that has since been revoked
 * would push deadlines later than they really are, so anything uncertain is left
 * out and belongs in ANNOUNCED_HOLIDAYS where it can be dated precisely.
 */
const FIXED_HOLIDAYS: Array<{ month: number; day: number; nameEn: string; nameBn: string }> = [
  { month: 2, day: 21, nameEn: "Shaheed Day and International Mother Language Day", nameBn: "শহীদ দিবস ও আন্তর্জাতিক মাতৃভাষা দিবস" },
  { month: 3, day: 26, nameEn: "Independence Day", nameBn: "স্বাধীনতা দিবস" },
  { month: 4, day: 14, nameEn: "Pahela Baishakh", nameBn: "পহেলা বৈশাখ" },
  { month: 5, day: 1, nameEn: "May Day", nameBn: "মে দিবস" },
  { month: 12, day: 16, nameEn: "Victory Day", nameBn: "বিজয় দিবস" },
  { month: 12, day: 25, nameEn: "Christmas Day", nameBn: "বড়দিন" },
];

/**
 * Government-announced holidays, by year.
 *
 * **This is the part that needs maintaining.** Each year the Cabinet Division
 * publishes the public holiday list; copy the dates in here. Until a year is
 * present, deadlines falling in it are flagged as not holiday-adjusted.
 *
 * Eid al-Fitr and Eid al-Adha are the ones that matter most: each closes
 * government offices for several consecutive days, which is where a 20-working-day
 * window silently loses the most time.
 *
 * Nothing is filled in below because these dates cannot be derived — they come
 * from a notification, and guessing them would trade one wrong deadline for
 * another. The shape is:
 *
 *   2026: [
 *     { date: "2026-03-20", nameEn: "Eid al-Fitr", nameBn: "ঈদুল ফিতর" },
 *     { date: "2026-03-21", nameEn: "Eid al-Fitr", nameBn: "ঈদুল ফিতর" },
 *   ],
 */
export const ANNOUNCED_HOLIDAYS: Record<number, Holiday[]> = {};

/** Years whose announced holiday list has been entered above. */
export function yearsWithAnnouncedHolidays(): number[] {
  return Object.keys(ANNOUNCED_HOLIDAYS).map(Number).sort();
}

function iso(date: Date): string {
  const year = date.getFullYear();
  const month = `${date.getMonth() + 1}`.padStart(2, "0");
  const day = `${date.getDate()}`.padStart(2, "0");
  return `${year}-${month}-${day}`;
}

/** Every known holiday in a given year, fixed and announced together. */
export function holidaysFor(year: number): Holiday[] {
  const fixed = FIXED_HOLIDAYS.map((h) => ({
    date: `${year}-${`${h.month}`.padStart(2, "0")}-${`${h.day}`.padStart(2, "0")}`,
    nameEn: h.nameEn,
    nameBn: h.nameBn,
  }));

  return [...fixed, ...(ANNOUNCED_HOLIDAYS[year] ?? [])].sort((a, b) =>
    a.date.localeCompare(b.date)
  );
}

/** The holiday falling on this date, if any. */
export function holidayOn(date: Date): Holiday | null {
  const key = iso(date);
  return holidaysFor(date.getFullYear()).find((h) => h.date === key) ?? null;
}

/**
 * Whether a date is a weekend in Bangladesh.
 *
 * The government weekend is Friday and Saturday, not Saturday and Sunday.
 */
export function isWeekend(date: Date): boolean {
  const day = date.getDay(); // 0 = Sunday … 5 = Friday, 6 = Saturday
  return day === 5 || day === 6;
}

/** Whether government offices are closed on this date, as far as we know. */
export function isNonWorkingDay(date: Date): boolean {
  return isWeekend(date) || holidayOn(date) !== null;
}

/**
 * Whether every year the span touches has its announced holidays entered.
 *
 * False means any deadline computed across that span is missing Eid and the
 * other moveable closures, and is therefore likely to be earlier than the real
 * one. Callers must surface that rather than swallow it.
 */
export function announcedHolidaysKnownFor(from: Date, to: Date): boolean {
  const known = new Set(yearsWithAnnouncedHolidays());
  const first = Math.min(from.getFullYear(), to.getFullYear());
  const last = Math.max(from.getFullYear(), to.getFullYear());

  for (let year = first; year <= last; year++) {
    if (!known.has(year)) return false;
  }

  return true;
}
