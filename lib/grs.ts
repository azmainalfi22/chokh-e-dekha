/**
 * GRS / 333 escalation bridge (roadmap #4).
 *
 * Bangladesh already runs official grievance rails: the national Grievance
 * Redress System (grs.gov.bd) and the 333 helpline. Chokh-e-Dekha doesn't
 * compete with them — it prepares the citizen's complaint so filing takes
 * minutes, then tracks the official reference number publicly. Pure
 * functions, so the escalate wizard can render live previews client-side
 * and the server action can persist the exact same text.
 */

export const GRS_PORTAL_URL = "http://www.grs.gov.bd/";
export const HELPLINE_333 = "333";

export type EscalationChannel = "grs" | "helpline_333" | "written";

export const CHANNEL_LABELS: Record<
  EscalationChannel,
  { en: string; bn: string }
> = {
  grs: { en: "GRS online complaint", bn: "জিআরএস অনলাইন অভিযোগ" },
  helpline_333: { en: "333 helpline call", bn: "৩৩৩ হেল্পলাইনে কল" },
  written: { en: "Written complaint", bn: "লিখিত অভিযোগ" },
};

export const OUTCOME_LABELS: Record<string, string> = {
  drafted: "Drafted",
  filed: "Filed",
  acknowledged: "Acknowledged",
  resolved: "Resolved via escalation",
  no_response: "No response",
};

export type EscalationInput = {
  reportId: number;
  reportTitle: string;
  reportDescription: string;
  category: string;
  cityCorporation: string;
  locationText?: string | null;
  authorityName: string;
  reportCreatedAt: string;
  slaDueAt?: string | null;
  daysOverdue?: number | null;
  /** True when the authority marked it resolved but the citizen disputed. */
  disputed?: boolean;
  applicantName: string;
  applicantPhone?: string | null;
  language: "en" | "bn";
};

function fmtDate(iso: string, lang: "en" | "bn"): string {
  return new Date(iso).toLocaleDateString(lang === "bn" ? "bn-BD" : "en-GB", {
    day: "numeric",
    month: "long",
    year: "numeric",
  });
}

function publicUrl(reportId: number): string {
  return `chokhedekha.app/reports/${reportId}`;
}

/**
 * Formal complaint body for the GRS portal or a written submission.
 * References the public report record — the paper trail is the leverage.
 */
export function generateGrsComplaint(input: EscalationInput): string {
  const filed = fmtDate(input.reportCreatedAt, input.language);
  const overdueLine =
    input.daysOverdue && input.daysOverdue > 0
      ? input.language === "bn"
        ? `নির্ধারিত সময়সীমা পেরিয়ে গেছে ${input.daysOverdue} দিন আগে, অথচ সমস্যাটির সমাধান হয়নি।`
        : `The response deadline passed ${input.daysOverdue} day(s) ago, yet the problem remains unresolved.`
      : "";
  const disputedLine = input.disputed
    ? input.language === "bn"
      ? `কর্তৃপক্ষ সমস্যাটি "সমাধান হয়েছে" বলে চিহ্নিত করলেও বাস্তবে সমস্যাটি রয়ে গেছে — আমি তা যাচাই করে আপত্তি জানিয়েছি।`
      : `The authority marked this issue as "resolved", but on inspection it is not fixed — I have formally disputed that closure.`
    : "";

  if (input.language === "bn") {
    return `বিষয়ঃ নাগরিক সমস্যার প্রতিকার না পাওয়ায় অভিযোগ — ${input.reportTitle}

বরাবর,
অভিযোগ নিষ্পত্তি কর্মকর্তা (অনিক)
${input.authorityName}

জনাব,
আমি ${filed} তারিখে নিম্নবর্ণিত নাগরিক সমস্যাটি জনসমক্ষে নথিভুক্ত করি (চোখে দেখা প্ল্যাটফর্ম, রেফারেন্সঃ ${publicUrl(input.reportId)}):

সমস্যার ধরনঃ ${input.category}
এলাকাঃ ${input.cityCorporation}${input.locationText ? `\nস্থানঃ ${input.locationText}` : ""}

বিবরণঃ
${input.reportDescription}

${overdueLine}${disputedLine ? `\n${disputedLine}` : ""}

এমতাবস্থায়, সরকারি অভিযোগ প্রতিকার ব্যবস্থার (GRS) মাধ্যমে বিষয়টি তদন্ত করে দ্রুত প্রতিকারের ব্যবস্থা গ্রহণ এবং গৃহীত ব্যবস্থা সম্পর্কে আমাকে অবহিত করার জন্য বিনীত অনুরোধ করছি।

অভিযোগকারীঃ ${input.applicantName}${input.applicantPhone ? `\nমোবাইলঃ ${input.applicantPhone}` : ""}
তারিখঃ ${fmtDate(new Date().toISOString(), "bn")}`;
  }

  return `Subject: Complaint regarding an unresolved civic issue — ${input.reportTitle}

To,
The Grievance Redress Officer (Anik)
${input.authorityName}

Sir/Madam,
On ${filed} I publicly documented the following civic issue (Chokh-e-Dekha platform, reference: ${publicUrl(input.reportId)}):

Category: ${input.category}
Area: ${input.cityCorporation}${input.locationText ? `\nLocation: ${input.locationText}` : ""}

Description:
${input.reportDescription}

${overdueLine}${disputedLine ? `\n${disputedLine}` : ""}

I therefore request that this matter be investigated and redressed through the Grievance Redress System, and that I be informed of the action taken.

Complainant: ${input.applicantName}${input.applicantPhone ? `\nPhone: ${input.applicantPhone}` : ""}
Date: ${fmtDate(new Date().toISOString(), "en")}`;
}

/**
 * A short call script for the 333 helpline — built for reading aloud,
 * including the details the operator will ask for.
 */
export function generate333Script(input: EscalationInput): string {
  const filed = fmtDate(input.reportCreatedAt, input.language);

  if (input.language === "bn") {
    return `৩৩৩-এ কল করে বলুনঃ

"আসসালামু আলাইকুম। আমি ${input.applicantName}, ${input.cityCorporation} এলাকা থেকে বলছি। আমি একটি নাগরিক সমস্যার অভিযোগ জানাতে চাই।

সমস্যাঃ ${input.reportTitle} (${input.category})${input.locationText ? `\nস্থানঃ ${input.locationText}` : ""}

আমি ${filed} তারিখে সমস্যাটি নথিভুক্ত করেছিলাম, কিন্তু এখনো সমাধান হয়নি। দায়িত্বপ্রাপ্ত সংস্থাঃ ${input.authorityName}।

অনুগ্রহ করে অভিযোগটি নথিভুক্ত করে আমাকে একটি রেফারেন্স নম্বর দিন।"

মনে রাখবেনঃ
• অপারেটরের দেওয়া রেফারেন্স নম্বরটি লিখে রাখুন — পরে অগ্রগতি জানতে লাগবে।
• কলটি সকাল-সন্ধ্যা যেকোনো সময় করা যায়; ৩৩৩ টোল-ফ্রি নয়, সাধারণ কল চার্জ প্রযোজ্য।`;
  }

  return `When you call 333, say:

"Hello. My name is ${input.applicantName}, calling from ${input.cityCorporation}. I want to lodge a civic complaint.

Issue: ${input.reportTitle} (${input.category})${input.locationText ? `\nLocation: ${input.locationText}` : ""}

I documented this issue on ${filed} and it remains unresolved. The responsible body is ${input.authorityName}.

Please register the complaint and give me a reference number."

Remember:
• Write down the reference number the operator gives you — you'll need it to follow up.
• 333 answers around the clock; standard call charges apply.`;
}
