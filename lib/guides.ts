/**
 * Public-service how-to guides (roadmap #9) — the "how do I get X done"
 * layer that makes the platform useful between complaints. Content is
 * grounded in the official portals linked from each guide; fees and
 * timelines are indicative and every guide links to the authoritative
 * source so citizens can verify current figures.
 */

export type Guide = {
  slug: string;
  titleEn: string;
  titleBn: string;
  summary: string;
  agency: string;
  portal: { label: string; url: string };
  /** Indicative fee text — always verify on the portal. */
  fee: string;
  /** Typical processing time. */
  time: string;
  documents: string[];
  steps: string[];
  notes?: string[];
};

export const GUIDES: Guide[] = [
  {
    slug: "birth-registration",
    titleEn: "Birth Registration",
    titleBn: "জন্ম নিবন্ধন",
    summary:
      "The foundational civil record — needed for school admission, NID, passport and almost every other government service.",
    agency: "Office of the Registrar General, Birth & Death Registration (Local Government Division)",
    portal: { label: "bdris.gov.bd", url: "https://bdris.gov.bd" },
    fee: "Free within 45 days of birth; small late fees apply afterwards (higher after 5 years)",
    time: "Typically a few working days after the ward/union office verifies",
    documents: [
      "Hospital birth record / EPI (vaccination) card, if any",
      "Parents' birth registration numbers or NID copies",
      "Proof of address (utility bill / holding tax receipt)",
      "Passport-size photo (for late registration of older children/adults)",
    ],
    steps: [
      "Apply online at bdris.gov.bd — select your registrar office (city corporation ward / union parishad).",
      "Fill the child's details in Bangla and English exactly as they should appear on the certificate.",
      "Upload the supporting documents and submit; note the application ID.",
      "Take the printed application and originals to the ward/union registrar office named on the receipt.",
      "Pay the fee (if applicable) and collect the certificate when notified, or track with the application ID online.",
    ],
    notes: [
      "Both Bangla and English versions matter — English is required for passports; check the spelling carefully before submitting.",
    ],
  },
  {
    slug: "nid-correction",
    titleEn: "NID Correction & Services",
    titleBn: "জাতীয় পরিচয়পত্র সংশোধন",
    summary:
      "Fix a wrong name, date of birth or address on your National ID, get a reissue for a lost card, or download the online copy.",
    agency: "National Identity Registration Wing, Election Commission",
    portal: { label: "services.nidw.gov.bd", url: "https://services.nidw.gov.bd" },
    fee: "Correction ৳230–৳345 per category (approx.); reissue ৳345+ depending on urgency — pay by bKash/Nagad/Rocket",
    time: "Simple corrections in days–weeks; category B/C corrections need committee approval and take longer",
    documents: [
      "Existing NID (copy) or NID number",
      "Evidence supporting the correction — SSC certificate (for name/DOB), birth registration, passport, marriage certificate as relevant",
      "For address change: utility bill or holding tax receipt of the new address",
    ],
    steps: [
      "Register/log in at services.nidw.gov.bd with your NID number and a face-verified account.",
      "Choose the correction type (name, DOB, address, photo…) and upload the supporting evidence.",
      "Pay the correction fee through mobile banking (the portal shows the exact amount).",
      "Track the application status online; you may be called to the upazila/thana election office for verification.",
      "Download the corrected online copy or collect the smart card when it's ready.",
    ],
    notes: [
      "Date-of-birth changes are the strictest category — align your SSC certificate, birth registration and NID before applying.",
    ],
  },
  {
    slug: "e-passport",
    titleEn: "e-Passport (New / Renewal)",
    titleBn: "ই-পাসপোর্ট",
    summary:
      "Apply online, pay the bank/mobile fee, then give biometrics at your regional passport office by appointment.",
    agency: "Department of Immigration & Passports",
    portal: { label: "epassport.gov.bd", url: "https://www.epassport.gov.bd" },
    fee: "48-page 5-year: ৳4,025 (regular) to ৳8,625 (super express); 10-year and 64-page cost more — VAT-inclusive figures on the portal",
    time: "Regular ~15–21 working days; express ~7; super express ~2 after biometrics",
    documents: [
      "NID (adults) or online birth registration (minors) — details must match exactly",
      "Previous passport (for renewal)",
      "Payment proof (A-Challan / offline bank slip if not paid online)",
      "Profession proof (e.g. student ID, job letter) — sometimes requested at the counter",
    ],
    steps: [
      "Check the instructions at epassport.gov.bd and fill the online application (no photo needed — taken live).",
      "Choose delivery speed and pay online (A-Challan) or at the designated banks.",
      "Book the appointment slot offered at the end of the application.",
      "Attend the regional passport office with printed application + documents for photo, fingerprints and iris scan.",
      "Track status online / by SMS and collect the passport with your delivery slip.",
    ],
    notes: [
      "Everything is matched against your NID/birth record — fix those first if they contain errors (see the NID correction guide).",
    ],
  },
  {
    slug: "trade-licence",
    titleEn: "Trade Licence (City Corporation)",
    titleBn: "ট্রেড লাইসেন্স",
    summary:
      "The city-corporation licence every business needs — now issued and renewed online in Dhaka North/South.",
    agency: "Your City Corporation (DNCC / DSCC / others) — Revenue Department",
    portal: { label: "etradelicense.gov.bd", url: "https://etradelicense.gov.bd" },
    fee: "Varies by business category (schedule fee) + signboard tax + forms — from a few hundred to several thousand taka/year",
    time: "Online issue typically within days once documents verify",
    documents: [
      "NID of the proprietor / managing partner",
      "Holding tax receipt or rent agreement of the business premises",
      "Passport-size photo",
      "For companies: incorporation certificate, MoA; for factories: environment/fire clearances as applicable",
    ],
    steps: [
      "Create an account on the e-trade-licence portal (or your city corporation's e-services site).",
      "Select the business category carefully — the annual fee schedule depends on it.",
      "Upload documents, submit, and pay online (bKash/Nagad/cards accepted).",
      "The ward inspector may verify the premises; respond to any query in the portal.",
      "Download the e-licence; renew annually from the same account.",
    ],
  },
  {
    slug: "holding-tax",
    titleEn: "Holding Tax Payment",
    titleBn: "হোল্ডিং ট্যাক্স",
    summary:
      "Pay your property (holding) tax to the city corporation online instead of queuing at the revenue office.",
    agency: "Your City Corporation — Revenue Department",
    portal: { label: "Search your city corporation e-revenue portal", url: "https://dncc.gov.bd" },
    fee: "Quarterly/annual amount set by your holding's assessment; rebate windows for early payment are common",
    time: "Instant e-receipt on online payment",
    documents: [
      "Holding number (on previous tax receipts)",
      "Owner NID / phone number registered with the corporation",
    ],
    steps: [
      "Find your corporation's e-revenue system (DNCC and DSCC both run online holding-tax portals).",
      "Look up your holding by number and verify the assessed amount.",
      "Pay with bKash/Nagad/card and save the e-receipt.",
      "Keep receipts — they double as address proof for many other services.",
      "If the assessment looks wrong, apply for reassessment at the regional revenue office.",
    ],
  },
  {
    slug: "e-namjari",
    titleEn: "Land Mutation (e-Namjari)",
    titleBn: "ই-নামজারি",
    summary:
      "Transfer the land record into your name after buying or inheriting land — fully online application and hearing tracking.",
    agency: "Ministry of Land — Assistant Commissioner (Land) offices",
    portal: { label: "mutation.land.gov.bd", url: "https://mutation.land.gov.bd" },
    fee: "Government fee ~৳1,170 total (court fee + notice + record correction + khatiyan), paid online",
    time: "Statutory target ~28 working days for standard cases",
    documents: [
      "Registered deed (dolil) of purchase / inheritance documents (warishan certificate)",
      "Up-to-date khatiyan / porcha of the land",
      "Latest land development tax (khajna) receipt",
      "NID and photo of the applicant",
    ],
    steps: [
      "Apply at mutation.land.gov.bd selecting your division → district → upazila land office.",
      "Upload the deed, khatiyan, tax receipt and NID; pay the fee online.",
      "Track the case number — notices and the hearing date appear in the portal/SMS.",
      "Attend the AC (Land) hearing with originals if summoned.",
      "On approval, download the new khatiyan; pay land development tax annually at ldtax.gov.bd.",
    ],
    notes: [
      "Mutation protects you from double-selling fraud — do it immediately after registration, and keep paying khajna in your name.",
    ],
  },
  {
    slug: "driving-licence",
    titleEn: "Driving Licence",
    titleBn: "ড্রাইভিং লাইসেন্স",
    summary:
      "From learner's permit to smart-card licence — applications, fees and exams all run through BRTA's service portal.",
    agency: "Bangladesh Road Transport Authority (BRTA)",
    portal: { label: "bsp.brta.gov.bd", url: "https://bsp.brta.gov.bd" },
    fee: "Learner ~৳345–৳518; smart licence ~৳2,772 (non-professional, 10 yr) / ~৳1,677 (professional, 5 yr) — check current schedule",
    time: "Learner instantly online; smart card after passing the test + biometrics (weeks)",
    documents: [
      "NID and online birth-registration-verified details",
      "Medical certificate (prescribed form, registered doctor)",
      "Utility bill as address proof",
      "Educational certificate (minimum class 8 for professional)",
    ],
    steps: [
      "Create a BSP (BRTA Service Portal) account with your NID and phone.",
      "Apply for the learner licence, upload the medical certificate, pay online — practise for 2–3 months.",
      "Book and take the driving test (written, oral and practical) at your BRTA circle.",
      "After passing, pay the smart-licence fee and give biometrics when called.",
      "Track delivery in the portal; drive with the e-copy where accepted until the card arrives.",
    ],
  },
  {
    slug: "etin",
    titleEn: "e-TIN & Income Tax Return",
    titleBn: "ই-টিআইএন ও আয়কর রিটার্ন",
    summary:
      "Get your taxpayer identification number in minutes and file the now-mandatory online return — needed for bank loans, land registration, trade licences and more.",
    agency: "National Board of Revenue (NBR)",
    portal: { label: "etaxnbr.gov.bd", url: "https://etaxnbr.gov.bd" },
    fee: "e-TIN registration is free; tax depends on your income slab (returns can be zero-tax)",
    time: "e-TIN immediate; e-Return filing ~30 minutes with documents ready",
    documents: [
      "NID and a mobile number registered in your name (biometric-verified SIM)",
      "Salary certificate / income statements for the tax year",
      "Bank statements, investment (DPS, sanchaypatra) documents for rebates",
    ],
    steps: [
      "Register at the NBR e-tax portal — your e-TIN certificate generates instantly from NID data.",
      "Each tax year, log in to eReturn and let it pre-fill what it can.",
      "Enter income, expenses and investments; the system computes tax and rebates.",
      "Pay any tax due online (challan integrates with the portal) and submit.",
      "Download the acknowledgement — banks and registries accept the e-copy as proof of return submission.",
    ],
    notes: [
      "Many services (credit cards, land registration, some licences) now legally require proof of return submission, not just a TIN.",
    ],
  },
];

export function getGuide(slug: string): Guide | null {
  return GUIDES.find((g) => g.slug === slug) ?? null;
}
