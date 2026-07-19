/**
 * Verified national emergency & help lines for Bangladesh.
 * Sources: 999.gov.bd (National Emergency Service), a2i National Helpline,
 * DGHS Shasthyo Batayon. These are toll-free public hotlines.
 * Kept as a small curated constant — never fabricate local station numbers;
 * those are resolved live from OpenStreetMap (see lib/overpass.ts).
 */

export type Hotline = {
  number: string;
  name: string;
  nameBn: string;
  description: string;
  primary?: boolean;
};

export const HOTLINES: Hotline[] = [
  {
    number: "999",
    name: "National Emergency",
    nameBn: "জাতীয় জরুরি সেবা",
    description: "Police, fire and ambulance — toll-free, 24/7.",
    primary: true,
  },
  {
    number: "333",
    name: "Government Services",
    nameBn: "সরকারি তথ্য ও সেবা",
    description: "National call centre for government information & services.",
  },
  {
    number: "109",
    name: "Violence Against Women & Children",
    nameBn: "নারী ও শিশু নির্যাতন",
    description: "National helpline to report abuse and seek support.",
  },
  {
    number: "1098",
    name: "Child Helpline",
    nameBn: "শিশু সহায়তা",
    description: "24-hour support for children in danger or distress.",
  },
  {
    number: "16263",
    name: "Health (Shasthyo Batayon)",
    nameBn: "স্বাস্থ্য বাতায়ন",
    description: "DGHS health advice and hospital information line.",
  },
  {
    number: "10921",
    name: "Women & Child Protection Cell",
    nameBn: "নারী ও শিশু সুরক্ষা সেল",
    description: "Immediate help for women facing harassment or threats.",
  },
];
