import type { Category, City } from "@/lib/constants";

/**
 * Ward + department routing (roadmap #3).
 *
 * Bangladesh's civic responsibilities are split across city corporations,
 * WASA water utilities, development authorities, power distributors and the
 * police. Citizens rarely know who owns a given problem. This module is the
 * single source of truth that maps a report (category + city) to the
 * responsible body, and holds a directory of those bodies with real contact
 * details so the app can say "this goes to the right desk."
 *
 * The resolver is a pure function so the submit wizard can preview routing
 * client-side, the createReport action can pin the authority at creation
 * time, and the report page + /authorities directory can render from the
 * same data — no drift between preview, storage and display.
 */

export type DeptType =
  | "municipal"
  | "water"
  | "planning"
  | "power"
  | "gas"
  | "police"
  | "fire";

export const DEPT_LABELS: Record<DeptType, string> = {
  municipal: "City Corporation",
  water: "Water & Sewerage (WASA)",
  planning: "Development Authority",
  power: "Power Distribution",
  gas: "Gas Distribution",
  police: "Bangladesh Police",
  fire: "Fire Service & Civil Defence",
};

export type Authority = {
  key: string;
  name: string;
  nameBn: string;
  dept: DeptType;
  /** Human-readable area served (for the directory). */
  jurisdiction: string;
  /** Own hotline, when confidently known. 333 is the universal fallback. */
  hotline?: string;
  website?: string;
};

/**
 * Authority directory. Contacts are limited to high-confidence values; the
 * national 333 helpline routes any civic grievance and is always offered as
 * a fallback, so we never publish a phone number we are unsure of.
 */
export const AUTHORITIES: Record<string, Authority> = {
  // ---- City corporations (roads, waste, streetlight, drainage, parks) ----
  dncc: {
    key: "dncc",
    name: "Dhaka North City Corporation",
    nameBn: "ঢাকা উত্তর সিটি কর্পোরেশন",
    dept: "municipal",
    jurisdiction: "Dhaka North",
    hotline: "16106",
    website: "https://dncc.gov.bd",
  },
  dscc: {
    key: "dscc",
    name: "Dhaka South City Corporation",
    nameBn: "ঢাকা দক্ষিণ সিটি কর্পোরেশন",
    dept: "municipal",
    jurisdiction: "Dhaka South",
    hotline: "09617111000",
    website: "https://dscc.gov.bd",
  },
  ccc: {
    key: "ccc",
    name: "Chattogram City Corporation",
    nameBn: "চট্টগ্রাম সিটি কর্পোরেশন",
    dept: "municipal",
    jurisdiction: "Chattogram",
    website: "https://ccc.gov.bd",
  },
  scc: {
    key: "scc",
    name: "Sylhet City Corporation",
    nameBn: "সিলেট সিটি কর্পোরেশন",
    dept: "municipal",
    jurisdiction: "Sylhet",
    website: "https://sylhetcitycorporation.gov.bd",
  },
  rcc: {
    key: "rcc",
    name: "Rajshahi City Corporation",
    nameBn: "রাজশাহী সিটি কর্পোরেশন",
    dept: "municipal",
    jurisdiction: "Rajshahi",
    website: "https://rcc.gov.bd",
  },
  kcc: {
    key: "kcc",
    name: "Khulna City Corporation",
    nameBn: "খুলনা সিটি কর্পোরেশন",
    dept: "municipal",
    jurisdiction: "Khulna",
    website: "https://khulnacity.org",
  },
  bcc: {
    key: "bcc",
    name: "Barishal City Corporation",
    nameBn: "বরিশাল সিটি কর্পোরেশন",
    dept: "municipal",
    jurisdiction: "Barishal",
    website: "https://barisalcity.gov.bd",
  },
  rpcc: {
    key: "rpcc",
    name: "Rangpur City Corporation",
    nameBn: "রংপুর সিটি কর্পোরেশন",
    dept: "municipal",
    jurisdiction: "Rangpur",
    website: "https://rpcc.gov.bd",
  },
  gcc: {
    key: "gcc",
    name: "Gazipur City Corporation",
    nameBn: "গাজীপুর সিটি কর্পোরেশন",
    dept: "municipal",
    jurisdiction: "Gazipur",
    website: "https://gcc.gov.bd",
  },
  ncc: {
    key: "ncc",
    name: "Narayanganj City Corporation",
    nameBn: "নারায়ণগঞ্জ সিটি কর্পোরেশন",
    dept: "municipal",
    jurisdiction: "Narayanganj",
    website: "https://ncc.gov.bd",
  },
  cumcc: {
    key: "cumcc",
    name: "Cumilla City Corporation",
    nameBn: "কুমিল্লা সিটি কর্পোরেশন",
    dept: "municipal",
    jurisdiction: "Cumilla",
    website: "https://cumillacity.gov.bd",
  },
  mcc: {
    key: "mcc",
    name: "Mymensingh City Corporation",
    nameBn: "ময়মনসিংহ সিটি কর্পোরেশন",
    dept: "municipal",
    jurisdiction: "Mymensingh",
    website: "https://mcc.gov.bd",
  },
  bogura_poura: {
    key: "bogura_poura",
    name: "Bogura Municipality (Pourashava)",
    nameBn: "বগুড়া পৌরসভা",
    dept: "municipal",
    jurisdiction: "Bogura",
  },
  local_gov: {
    key: "local_gov",
    name: "Local Government Body",
    nameBn: "স্থানীয় সরকার কর্তৃপক্ষ",
    dept: "municipal",
    jurisdiction: "Nationwide — your local city corporation / pourashava",
  },

  // ---- WASA water & sewerage ----
  dwasa: {
    key: "dwasa",
    name: "Dhaka WASA",
    nameBn: "ঢাকা ওয়াসা",
    dept: "water",
    jurisdiction: "Dhaka (North & South)",
    hotline: "16162",
    website: "https://dwasa.org.bd",
  },
  ctgwasa: {
    key: "ctgwasa",
    name: "Chattogram WASA",
    nameBn: "চট্টগ্রাম ওয়াসা",
    dept: "water",
    jurisdiction: "Chattogram",
    website: "https://ctg-wasa.org.bd",
  },
  kwasa: {
    key: "kwasa",
    name: "Khulna WASA",
    nameBn: "খুলনা ওয়াসা",
    dept: "water",
    jurisdiction: "Khulna",
    website: "https://kwasa.org.bd",
  },
  rwasa: {
    key: "rwasa",
    name: "Rajshahi WASA",
    nameBn: "রাজশাহী ওয়াসা",
    dept: "water",
    jurisdiction: "Rajshahi",
    website: "https://rajshahiwasa.org.bd",
  },

  // ---- Development authorities (illegal construction / planning) ----
  rajuk: {
    key: "rajuk",
    name: "RAJUK (Rajdhani Unnayan Kartripakkha)",
    nameBn: "রাজধানী উন্নয়ন কর্তৃপক্ষ (রাজউক)",
    dept: "planning",
    jurisdiction: "Greater Dhaka (incl. Gazipur, Narayanganj)",
    website: "https://rajukdhaka.gov.bd",
  },
  cda: {
    key: "cda",
    name: "Chattogram Development Authority",
    nameBn: "চট্টগ্রাম উন্নয়ন কর্তৃপক্ষ",
    dept: "planning",
    jurisdiction: "Chattogram",
    website: "https://cda.gov.bd",
  },
  kda: {
    key: "kda",
    name: "Khulna Development Authority",
    nameBn: "খুলনা উন্নয়ন কর্তৃপক্ষ",
    dept: "planning",
    jurisdiction: "Khulna",
    website: "https://kda.gov.bd",
  },
  rda: {
    key: "rda",
    name: "Rajshahi Development Authority",
    nameBn: "রাজশাহী উন্নয়ন কর্তৃপক্ষ",
    dept: "planning",
    jurisdiction: "Rajshahi",
    website: "https://rda.gov.bd",
  },
  sda: {
    key: "sda",
    name: "Sylhet Development Authority",
    nameBn: "সিলেট উন্নয়ন কর্তৃপক্ষ",
    dept: "planning",
    jurisdiction: "Sylhet",
  },

  // ---- Power distribution ----
  desco: {
    key: "desco",
    name: "DESCO",
    nameBn: "ঢাকা ইলেকট্রিক সাপ্লাই কোম্পানি (ডেসকো)",
    dept: "power",
    jurisdiction: "Dhaka North (Mirpur, Gulshan, Uttara)",
    hotline: "16120",
    website: "https://desco.gov.bd",
  },
  dpdc: {
    key: "dpdc",
    name: "DPDC",
    nameBn: "ঢাকা পাওয়ার ডিস্ট্রিবিউশন কোম্পানি (ডিপিডিসি)",
    dept: "power",
    jurisdiction: "Dhaka South & Narayanganj",
    hotline: "16116",
    website: "https://dpdc.gov.bd",
  },
  nesco: {
    key: "nesco",
    name: "NESCO",
    nameBn: "নর্দার্ন ইলেকট্রিসিটি সাপ্লাই কোম্পানি (নেসকো)",
    dept: "power",
    jurisdiction: "Rajshahi & Rangpur divisions",
    hotline: "16603",
    website: "https://nesco.gov.bd",
  },
  wzpdcl: {
    key: "wzpdcl",
    name: "WZPDCL",
    nameBn: "ওয়েস্ট জোন পাওয়ার ডিস্ট্রিবিউশন কোম্পানি",
    dept: "power",
    jurisdiction: "Khulna & Barishal divisions",
    hotline: "16117",
    website: "https://wzpdcl.org.bd",
  },
  bpdb: {
    key: "bpdb",
    name: "Bangladesh Power Development Board",
    nameBn: "বাংলাদেশ বিদ্যুৎ উন্নয়ন বোর্ড",
    dept: "power",
    jurisdiction: "Nationwide (or your local distributor / palli bidyut)",
    website: "https://bpdb.gov.bd",
  },

  // ---- Gas distribution ----
  titas: {
    key: "titas",
    name: "Titas Gas Transmission & Distribution",
    nameBn: "তিতাস গ্যাস",
    dept: "gas",
    jurisdiction: "Dhaka, Gazipur, Narayanganj, Mymensingh",
    hotline: "16496",
    website: "https://titasgas.gov.bd",
  },
  karnaphuli: {
    key: "karnaphuli",
    name: "Karnaphuli Gas Distribution",
    nameBn: "কর্ণফুলী গ্যাস",
    dept: "gas",
    jurisdiction: "Chattogram",
    website: "https://kgdcl.gov.bd",
  },
  gas_distributor: {
    key: "gas_distributor",
    name: "Local Gas Distribution Company",
    nameBn: "স্থানীয় গ্যাস বিতরণ কোম্পানি",
    dept: "gas",
    jurisdiction: "Your area's distributor (Jalalabad / Bakhrabad / Pashchimanchal / Sundarban)",
  },

  // ---- Police ----
  traffic_police: {
    key: "traffic_police",
    name: "Bangladesh Police — Traffic",
    nameBn: "বাংলাদেশ পুলিশ — ট্রাফিক বিভাগ",
    dept: "police",
    jurisdiction: "Nationwide",
    hotline: "999",
    website: "https://police.gov.bd",
  },
  police: {
    key: "police",
    name: "Bangladesh Police",
    nameBn: "বাংলাদেশ পুলিশ",
    dept: "police",
    jurisdiction: "Nationwide — your local thana",
    hotline: "999",
    website: "https://police.gov.bd",
  },
};

/** The universal civic helpline — always valid, routes any grievance. */
export const NATIONAL_HELPLINE = "333";

export function getAuthority(key: string | null | undefined): Authority | null {
  if (!key) return null;
  return AUTHORITIES[key] ?? null;
}

/** City → its city-corporation / municipal authority key. */
const CITY_MUNICIPAL: Record<City, string> = {
  "Dhaka North City Corporation": "dncc",
  "Dhaka South City Corporation": "dscc",
  "Chattogram City Corporation": "ccc",
  "Sylhet City Corporation": "scc",
  "Rajshahi City Corporation": "rcc",
  "Khulna City Corporation": "kcc",
  "Barishal City Corporation": "bcc",
  "Rangpur City Corporation": "rpcc",
  "Gazipur City Corporation": "gcc",
  "Narayanganj City Corporation": "ncc",
  "Cumilla City Corporation": "cumcc",
  "Mymensingh City Corporation": "mcc",
  Bogura: "bogura_poura",
  Other: "local_gov",
};

const WATER_BY_CITY: Partial<Record<City, string>> = {
  "Dhaka North City Corporation": "dwasa",
  "Dhaka South City Corporation": "dwasa",
  "Chattogram City Corporation": "ctgwasa",
  "Khulna City Corporation": "kwasa",
  "Rajshahi City Corporation": "rwasa",
};

const PLANNING_BY_CITY: Partial<Record<City, string>> = {
  "Dhaka North City Corporation": "rajuk",
  "Dhaka South City Corporation": "rajuk",
  "Gazipur City Corporation": "rajuk",
  "Narayanganj City Corporation": "rajuk",
  "Chattogram City Corporation": "cda",
  "Khulna City Corporation": "kda",
  "Rajshahi City Corporation": "rda",
  "Sylhet City Corporation": "sda",
};

const GAS_BY_CITY: Partial<Record<City, string>> = {
  "Dhaka North City Corporation": "titas",
  "Dhaka South City Corporation": "titas",
  "Gazipur City Corporation": "titas",
  "Narayanganj City Corporation": "titas",
  "Mymensingh City Corporation": "titas",
  "Chattogram City Corporation": "karnaphuli",
};

const POWER_BY_CITY: Partial<Record<City, string>> = {
  "Dhaka North City Corporation": "desco",
  "Dhaka South City Corporation": "dpdc",
  "Narayanganj City Corporation": "dpdc",
  "Rajshahi City Corporation": "nesco",
  "Rangpur City Corporation": "nesco",
  Bogura: "nesco",
  "Khulna City Corporation": "wzpdcl",
  "Barishal City Corporation": "wzpdcl",
};

/**
 * Deterministically map a report to the authority that owns it. Never throws:
 * unknown inputs fall back to the local government body so a report always
 * has a home.
 */
export function resolveAuthorityKey(
  category: Category | string,
  city: City | string
): string {
  const c = city as City;
  const municipal = CITY_MUNICIPAL[c] ?? "local_gov";

  switch (category) {
    case "Water Supply":
    case "Sewerage":
      return WATER_BY_CITY[c] ?? municipal;
    case "Illegal Construction":
      return PLANNING_BY_CITY[c] ?? municipal;
    case "Electricity":
      return POWER_BY_CITY[c] ?? "bpdb";
    case "Gas":
      return GAS_BY_CITY[c] ?? "gas_distributor";
    case "Traffic":
      return "traffic_police";
    case "Public Safety":
      return "police";
    // Road, Garbage / Waste, Streetlight, Drainage / Waterlogging, Parks, Other
    default:
      return municipal;
  }
}

/** Convenience: resolve straight to the Authority record. */
export function resolveAuthority(
  category: Category | string,
  city: City | string
): Authority {
  return (
    AUTHORITIES[resolveAuthorityKey(category, city)] ?? AUTHORITIES.local_gov
  );
}
