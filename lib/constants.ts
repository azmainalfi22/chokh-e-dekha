/** Report categories (SPEC.md §4). */
export const CATEGORIES = [
  "Road",
  "Garbage / Waste",
  "Streetlight",
  "Drainage / Waterlogging",
  "Water Supply",
  "Sewerage",
  "Illegal Construction",
  "Traffic",
  "Public Safety",
  "Electricity",
  "Gas",
  "Parks",
  "Other",
] as const;
export type Category = (typeof CATEGORIES)[number];

/** Utility categories that behave as outages — clustered on the live board. */
export const UTILITY_CATEGORIES = [
  "Water Supply",
  "Electricity",
  "Gas",
] as const;

/** A cluster of this many recent reports is treated as a confirmed outage. */
export const OUTAGE_CONFIRM_THRESHOLD = 3;
/** How far back the live outage board looks. */
export const OUTAGE_WINDOW_HOURS = 48;

/** City corporations / cities (normalised from the original data, SPEC.md §4). */
export const CITIES = [
  "Dhaka North City Corporation",
  "Dhaka South City Corporation",
  "Chattogram City Corporation",
  "Sylhet City Corporation",
  "Rajshahi City Corporation",
  "Khulna City Corporation",
  "Barishal City Corporation",
  "Rangpur City Corporation",
  "Gazipur City Corporation",
  "Narayanganj City Corporation",
  "Cumilla City Corporation",
  "Mymensingh City Corporation",
  "Bogura",
  "Other",
] as const;
export type City = (typeof CITIES)[number];

/** Report lifecycle statuses (matches reports.status check constraint). */
export const STATUSES = [
  "pending",
  "in_progress",
  "resolved",
  "rejected",
] as const;
export type Status = (typeof STATUSES)[number];

export const STATUS_LABELS: Record<Status, string> = {
  pending: "Pending",
  in_progress: "In Progress",
  resolved: "Resolved",
  rejected: "Rejected",
};

/** Default SLA: days from approval until a report is due (SPEC.md §4). */
export const DEFAULT_SLA_DAYS = 7;

/** Public feed page size (enforced pagination, NFR-01/05). */
export const PAGE_SIZE = 12;

/** Dhaka city centre — default map view. */
export const DEFAULT_MAP_CENTER: [number, number] = [23.7808, 90.4067];
export const DEFAULT_MAP_ZOOM = 12;

export const APP_NAME = "Chokh-e-Dekha";
export const APP_NAME_BN = "চোখে দেখা";
export const APP_TAGLINE = "Your Eyes, Your Voice, Your City";
