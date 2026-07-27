/**
 * RTI Act 2009 (Bangladesh) letter generation (FR-13).
 * Bilingual (English / Bangla) output that follows the statutory form:
 * addressed to the Designated Officer of a public authority, particulars of
 * the information sought, the manner of delivery, and the 20-working-day
 * statutory response window (RTI Act 2009, s. 9).
 */

/** Statutory response window (RTI Act 2009, s.9): 20 working days. */
export const RTI_RESPONSE_WORKING_DAYS = 20;

export const RTI_STATUS_LABELS: Record<string, string> = {
  drafted: "Draft",
  submitted: "Awaiting response",
  responded: "Responded",
  appealed: "Appealed",
  closed: "Closed",
};

export const RTI_OUTCOME_LABELS: Record<string, string> = {
  received: "Information received",
  partial: "Partial / incomplete",
  refused: "Refused",
  no_response: "No response",
};
/** Window to appeal to the Appellate Authority after a miss/refusal (s.24). */
export const RTI_APPEAL_DAYS = 30;

/**
 * Add N working days to a date. Bangladesh's weekend is Friday & Saturday,
 * so those are skipped. Public holidays aren't modelled — the resulting
 * deadline is indicative, and the UI says so.
 */
export function addWorkingDays(start: Date, days: number): Date {
  const d = new Date(start);
  let added = 0;
  while (added < days) {
    d.setDate(d.getDate() + 1);
    const day = d.getDay(); // 0=Sun … 5=Fri, 6=Sat
    if (day !== 5 && day !== 6) added++;
  }
  return d;
}

/** Working days between two dates (negative if `to` is before `from`). */
export function workingDaysBetween(from: Date, to: Date): number {
  const sign = to >= from ? 1 : -1;
  const [a, b] = sign > 0 ? [from, to] : [to, from];
  const cur = new Date(a);
  let count = 0;
  while (cur < b) {
    cur.setDate(cur.getDate() + 1);
    const day = cur.getDay();
    if (day !== 5 && day !== 6) count++;
  }
  return sign * count;
}

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

export type RtiAppealInput = {
  authority: string;
  subject: string;
  /** When the original application was submitted. */
  submittedDate: string;
  /** The statutory deadline that was set. */
  deadlineDate: string;
  outcome: "refused" | "no_response" | "partial";
  applicantName: string;
  applicantAddress: string;
  applicantPhone?: string | null;
  language: "en" | "bn";
};

function fmt(iso: string, lang: "en" | "bn") {
  return new Date(iso).toLocaleDateString(lang === "bn" ? "bn-BD" : "en-GB", {
    day: "numeric",
    month: "long",
    year: "numeric",
  });
}

/**
 * Appeal to the Appellate Authority under s.24 of the RTI Act 2009, used
 * when the Designated Officer misses the 20-working-day deadline, refuses,
 * or supplies only partial information.
 */
export function generateRtiAppeal(input: RtiAppealInput): string {
  const grievanceEn =
    input.outcome === "no_response"
      ? `no response was received within the statutory period of 20 (twenty) working days, which expired on ${fmt(input.deadlineDate, "en")}`
      : input.outcome === "refused"
        ? `the request was refused without lawful justification under the Act`
        : `only partial and incomplete information was supplied`;

  const grievanceBn =
    input.outcome === "no_response"
      ? `আইন অনুযায়ী নির্ধারিত ২০ (বিশ) কার্যদিবসের মধ্যে (যা ${fmt(input.deadlineDate, "bn")} তারিখে শেষ হয়েছে) কোনো জবাব পাওয়া যায়নি`
      : input.outcome === "refused"
        ? `আইনসম্মত কোনো কারণ ছাড়াই তথ্য সরবরাহ প্রত্যাখ্যান করা হয়েছে`
        : `শুধুমাত্র আংশিক ও অসম্পূর্ণ তথ্য সরবরাহ করা হয়েছে`;

  if (input.language === "bn") {
    return `তারিখঃ ${today("bn")}

বরাবর,
আপিল কর্তৃপক্ষ
${input.authority}

বিষয়ঃ তথ্য অধিকার আইন, ২০০৯ এর ২৪ ধারার অধীনে আপিল — ${input.subject}

জনাব,
আমি ${fmt(input.submittedDate, "bn")} তারিখে উপরোক্ত বিষয়ে দায়িত্বপ্রাপ্ত কর্মকর্তার নিকট তথ্য প্রাপ্তির জন্য আবেদন করেছিলাম। কিন্তু ${grievanceBn}।

অতএব, তথ্য অধিকার আইন, ২০০৯ এর ২৪ ধারা অনুযায়ী আমি এই আপিল দাখিল করছি এবং অনুরোধকৃত তথ্য সরবরাহের জন্য প্রয়োজনীয় নির্দেশনা প্রদানের জন্য বিনীত অনুরোধ করছি।

আপিলকারীঃ
নামঃ ${input.applicantName}
ঠিকানাঃ ${input.applicantAddress}${input.applicantPhone ? `\nমোবাইলঃ ${input.applicantPhone}` : ""}

বিনীত নিবেদক,
${input.applicantName}`;
  }

  return `Date: ${today("en")}

To,
The Appellate Authority
${input.authority}

Subject: Appeal under Section 24 of the Right to Information Act, 2009 — ${input.subject}

Sir/Madam,
On ${fmt(input.submittedDate, "en")} I submitted an application for information on the above subject to the Designated Officer. However, ${grievanceEn}.

I therefore file this appeal under Section 24 of the Right to Information Act, 2009, and request that the necessary directions be issued for the requested information to be provided.

Appellant's details:
Name: ${input.applicantName}
Address: ${input.applicantAddress}${input.applicantPhone ? `\nPhone: ${input.applicantPhone}` : ""}

Yours faithfully,
${input.applicantName}`;
}
