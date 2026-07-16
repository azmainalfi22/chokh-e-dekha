/**
 * RTI Act 2009 (Bangladesh) letter generation (FR-13).
 * Bilingual (English / Bangla) output that follows the statutory form:
 * addressed to the Designated Officer of a public authority, particulars of
 * the information sought, the manner of delivery, and the 20-working-day
 * statutory response window (RTI Act 2009, s. 9).
 */

export type AuthorityPreset = {
  id: string;
  nameEn: string;
  nameBn: string;
};

/** Common Bangladeshi public authorities citizens escalate to. */
export const AUTHORITY_PRESETS: AuthorityPreset[] = [
  {
    id: "dncc",
    nameEn: "Dhaka North City Corporation",
    nameBn: "ঢাকা উত্তর সিটি কর্পোরেশন",
  },
  {
    id: "dscc",
    nameEn: "Dhaka South City Corporation",
    nameBn: "ঢাকা দক্ষিণ সিটি কর্পোরেশন",
  },
  {
    id: "ccc",
    nameEn: "Chattogram City Corporation",
    nameBn: "চট্টগ্রাম সিটি কর্পোরেশন",
  },
  {
    id: "rajuk",
    nameEn: "Rajdhani Unnayan Kartripakkha (RAJUK)",
    nameBn: "রাজধানী উন্নয়ন কর্তৃপক্ষ (রাজউক)",
  },
  {
    id: "wasa",
    nameEn: "Dhaka WASA",
    nameBn: "ঢাকা ওয়াসা",
  },
  {
    id: "dpdc",
    nameEn: "Dhaka Power Distribution Company (DPDC)",
    nameBn: "ঢাকা পাওয়ার ডিস্ট্রিবিউশন কোম্পানি (ডিপিডিসি)",
  },
  {
    id: "lged",
    nameEn: "Local Government Engineering Department (LGED)",
    nameBn: "স্থানীয় সরকার প্রকৌশল অধিদপ্তর (এলজিইডি)",
  },
  {
    id: "rhd",
    nameEn: "Roads and Highways Department",
    nameBn: "সড়ক ও জনপথ অধিদপ্তর",
  },
  {
    id: "custom",
    nameEn: "Other (enter manually)",
    nameBn: "অন্যান্য (নিজে লিখুন)",
  },
];

export type RtiInput = {
  authority: string;
  subject: string;
  informationSought: string;
  reason?: string;
  deliveryMode: "certified_copy" | "inspection" | "email";
  applicantName: string;
  applicantAddress: string;
  applicantPhone?: string;
  applicantEmail?: string;
  language: "en" | "bn";
};

const DELIVERY_EN: Record<RtiInput["deliveryMode"], string> = {
  certified_copy: "a certified copy of the record(s)",
  inspection: "inspection of the relevant record(s)",
  email: "an electronic copy sent to my email address",
};

const DELIVERY_BN: Record<RtiInput["deliveryMode"], string> = {
  certified_copy: "সংশ্লিষ্ট রেকর্ডের সত্যায়িত অনুলিপি",
  inspection: "সংশ্লিষ্ট রেকর্ড পরিদর্শন",
  email: "আমার ইমেইল ঠিকানায় প্রেরিত ইলেকট্রনিক অনুলিপি",
};

function today(lang: "en" | "bn") {
  return new Date().toLocaleDateString(lang === "bn" ? "bn-BD" : "en-GB", {
    day: "numeric",
    month: "long",
    year: "numeric",
  });
}

/** Renders the full RTI application body in the requested language. */
export function generateRtiLetter(input: RtiInput): string {
  const authorityName = input.authority.trim();

  if (input.language === "bn") {
    return `তারিখঃ ${today("bn")}

বরাবর,
দায়িত্বপ্রাপ্ত কর্মকর্তা
${authorityName}

বিষয়ঃ তথ্য অধিকার আইন, ২০০৯ এর অধীনে তথ্য প্রাপ্তির আবেদন — ${input.subject}

জনাব,
তথ্য অধিকার আইন, ২০০৯ (২০০৯ সনের ২০ নং আইন) এর ৮ ধারা অনুযায়ী আমি নিম্নবর্ণিত তথ্য প্রাপ্তির জন্য আবেদন করছিঃ

${input.informationSought}
${input.reason ? `\nআবেদনের কারণঃ ${input.reason}\n` : ""}
আমি উপর্যুক্ত তথ্য ${DELIVERY_BN[input.deliveryMode]} আকারে পেতে ইচ্ছুক।

আইন অনুযায়ী নির্ধারিত ২০ (বিশ) কার্যদিবসের মধ্যে অনুরোধকৃত তথ্য সরবরাহের জন্য বিনীত অনুরোধ করছি। প্রযোজ্য ক্ষেত্রে নির্ধারিত ফি পরিশোধে আমি সম্মত আছি।

আবেদনকারীর তথ্যঃ
নামঃ ${input.applicantName}
ঠিকানাঃ ${input.applicantAddress}${
      input.applicantPhone ? `\nমোবাইলঃ ${input.applicantPhone}` : ""
    }${input.applicantEmail ? `\nইমেইলঃ ${input.applicantEmail}` : ""}

বিনীত নিবেদক,
${input.applicantName}`;
  }

  return `Date: ${today("en")}

To,
The Designated Officer
${authorityName}

Subject: Application for information under the Right to Information Act, 2009 — ${input.subject}

Sir/Madam,
Under Section 8 of the Right to Information Act, 2009 (Act No. 20 of 2009), I would like to request the following information:

${input.informationSought}
${input.reason ? `\nReason for the request: ${input.reason}\n` : ""}
I would like to receive the above information in the form of ${DELIVERY_EN[input.deliveryMode]}.

I request that the information be provided within the statutory period of 20 (twenty) working days as prescribed by the Act. I am willing to pay the prescribed fee, if applicable.

Applicant's details:
Name: ${input.applicantName}
Address: ${input.applicantAddress}${
    input.applicantPhone ? `\nPhone: ${input.applicantPhone}` : ""
  }${input.applicantEmail ? `\nEmail: ${input.applicantEmail}` : ""}

Yours faithfully,
${input.applicantName}`;
}
