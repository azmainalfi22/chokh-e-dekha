import type { ReportForClassification, Suggestion } from "./index";
import { priorityForCategory } from "@/lib/sla";

/**
 * Deterministic keyword classifier, Bangla and English.
 *
 * Scores each category by how many of its terms appear in the report, weighting
 * the title more heavily than the body because people put the problem in the
 * title. Confidence is the winning score's share of the total, so a report that
 * matches one category strongly scores high and one that matches three
 * categories weakly scores low — which is exactly when a human should look.
 *
 * Bangla terms are matched as substrings rather than whole words. Bengali is
 * agglutinative and inflects heavily, so "রাস্তার" and "রাস্তায়" both need to
 * match "রাস্তা"; whole-word matching would miss most real reports.
 */

type CategoryTerms = { category: string; en: string[]; bn: string[] };

const TERMS: CategoryTerms[] = [
  {
    category: "Road",
    en: ["pothole", "road", "asphalt", "tarmac", "crater", "resurface", "divider", "speed breaker"],
    bn: ["রাস্তা", "সড়ক", "গর্ত", "খানাখন্দ", "পিচ", "ডিভাইডার"],
  },
  {
    category: "Garbage / Waste",
    en: ["garbage", "waste", "rubbish", "bin", "dustbin", "litter", "dump", "trash", "smell"],
    bn: ["ময়লা", "আবর্জনা", "ডাস্টবিন", "বর্জ্য", "দুর্গন্ধ"],
  },
  {
    category: "Streetlight",
    en: ["streetlight", "street light", "lamp", "lamppost", "dark", "lighting", "bulb"],
    bn: ["বাতি", "স্ট্রিটলাইট", "ল্যাম্প", "অন্ধকার", "আলো"],
  },
  {
    category: "Drainage / Waterlogging",
    en: ["drain", "drainage", "waterlogging", "waterlogged", "flood", "flooded", "sewer water", "stagnant"],
    bn: ["ড্রেন", "নর্দমা", "জলাবদ্ধতা", "পানি জমে", "বন্যা", "জমে থাকা"],
  },
  {
    category: "Water Supply",
    en: ["water supply", "wasa", "tap", "no water", "drinking water", "pipeline", "supply line"],
    bn: ["পানি সরবরাহ", "ওয়াসা", "পানির লাইন", "খাবার পানি", "পানি নেই"],
  },
  {
    category: "Sewerage",
    en: ["sewage", "sewerage", "septic", "manhole", "overflow", "foul water"],
    bn: ["পয়ঃনিষ্কাশন", "স্যুয়ারেজ", "ম্যানহোল", "সেপটিক"],
  },
  {
    category: "Illegal Construction",
    en: ["illegal construction", "encroach", "unauthorised", "unauthorized", "building extension", "occupied footpath"],
    bn: ["অবৈধ স্থাপনা", "দখল", "অননুমোদিত", "ভবন"],
  },
  {
    category: "Traffic",
    en: ["traffic", "signal", "congestion", "jam", "parking", "one way", "zebra crossing"],
    bn: ["যানজট", "ট্রাফিক", "সিগন্যাল", "পার্কিং"],
  },
  {
    category: "Public Safety",
    en: ["unsafe", "danger", "dangerous", "accident", "open manhole", "collapse", "crime", "assault", "hazard"],
    bn: ["বিপজ্জনক", "দুর্ঘটনা", "ঝুঁকি", "নিরাপত্তা", "খোলা ম্যানহোল"],
  },
  {
    category: "Electricity",
    en: ["electricity", "power cut", "load shedding", "transformer", "live wire", "cable", "dpdc", "desco"],
    bn: ["বিদ্যুৎ", "লোডশেডিং", "ট্রান্সফরমার", "তার", "কারেন্ট"],
  },
  {
    category: "Gas",
    en: ["gas", "gas line", "gas leak", "titas", "cylinder", "smell of gas"],
    bn: ["গ্যাস", "তিতাস", "গ্যাস লিক", "সিলিন্ডার"],
  },
  {
    category: "Parks",
    en: ["park", "playground", "field", "greenery", "tree", "garden"],
    bn: ["পার্ক", "খেলার মাঠ", "মাঠ", "গাছ", "বাগান"],
  },
];

/** Title carries the problem; body carries detail. Weight accordingly. */
const TITLE_WEIGHT = 3;
const BODY_WEIGHT = 1;

function countOccurrences(haystack: string, term: string): number {
  if (!term) return 0;
  let count = 0;
  let from = 0;
  for (;;) {
    const at = haystack.indexOf(term, from);
    if (at === -1) return count;
    count++;
    from = at + term.length;
  }
}

function scoreFor(terms: CategoryTerms, title: string, body: string): number {
  let score = 0;

  for (const term of [...terms.en, ...terms.bn]) {
    score += countOccurrences(title, term) * TITLE_WEIGHT;
    score += countOccurrences(body, term) * BODY_WEIGHT;
  }

  return score;
}

export function classifyLocally(report: ReportForClassification): Suggestion {
  const title = (report.title ?? "").toLowerCase();
  const body = (report.description ?? "").toLowerCase();

  const scored = TERMS.map((terms) => ({
    category: terms.category,
    score: scoreFor(terms, title, body),
  })).sort((a, b) => b.score - a.score);

  const total = scored.reduce((sum, s) => sum + s.score, 0);
  const best = scored[0];
  const runnerUp = scored[1];

  // Nothing matched at all. Say so rather than picking the first category and
  // calling it a suggestion.
  if (!best || best.score === 0) {
    return {
      category: "Other",
      priority: priorityForCategory("Other"),
      confidence: 0,
      rationale: "No recognisable keywords — needs a human to categorise.",
      provider: "local-heuristic",
    };
  }

  // Share of total, tempered by how far clear of the runner-up it is. A report
  // matching two categories equally should not look confident.
  const share = best.score / total;
  const margin = runnerUp && runnerUp.score > 0 ? 1 - runnerUp.score / best.score : 1;
  const confidence = Math.min(0.97, Math.round(share * (0.5 + 0.5 * margin) * 100) / 100);

  const contested =
    runnerUp && runnerUp.score > 0 && runnerUp.score / best.score > 0.6
      ? ` Also looks like ${runnerUp.category}.`
      : "";

  return {
    category: best.category,
    priority: priorityForCategory(best.category),
    confidence,
    rationale: `Wording matches ${best.category}.${contested}`,
    provider: "local-heuristic",
  };
}
