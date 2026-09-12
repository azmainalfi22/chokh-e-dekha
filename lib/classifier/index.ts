import { classifyLocally } from "./local";

/**
 * Category suggestion for an incoming report.
 *
 * The pattern worth copying from the benchmarked projects is not the model, it
 * is where the output lands: a suggestion sits in a column until a human
 * confirms it. Nothing here changes a report on its own. An admin sees what was
 * suggested, why, and how confident it was, and decides.
 *
 * That ordering matters more than accuracy. A misfiled report is a report that
 * reaches the wrong desk and misses its deadline, and a classifier that quietly
 * refiles things is one nobody can argue with.
 */

export type Suggestion = {
  category: string;
  priority: "high" | "medium" | "low";
  /** 0–1. Below the review threshold the admin sees a warning rather than a nudge. */
  confidence: number;
  /** Why, in words an admin can weigh. Never a raw score on its own. */
  rationale: string;
  /** Which provider produced this, so an old suggestion can be read in context. */
  provider: string;
};

export type ReportForClassification = {
  title: string;
  description: string;
  cityCorporation?: string | null;
};

export interface ClassifierProvider {
  readonly name: string;
  classify(report: ReportForClassification): Promise<Suggestion>;
}

/**
 * The local provider: deterministic keyword and phrase matching, in Bangla and
 * English, over the categories the app already has.
 *
 * It is not a model and does not pretend to be. It runs offline, costs nothing,
 * returns the same answer for the same text, and is good enough to drive the
 * confirmation flow that is the actual subject of this feature.
 *
 * To use a real model instead, add a provider here and set CLASSIFIER_PROVIDER.
 * The interface is the whole contract: text in, Suggestion out. Nothing
 * downstream knows or cares which one answered.
 */
const localProvider: ClassifierProvider = {
  name: "local-heuristic",
  classify: async (report) => classifyLocally(report),
};

const providers: Record<string, ClassifierProvider> = {
  "local-heuristic": localProvider,
};

export function getClassifier(): ClassifierProvider {
  const requested = process.env.CLASSIFIER_PROVIDER ?? "local-heuristic";
  return providers[requested] ?? localProvider;
}

/** Below this, the suggestion is shown as uncertain rather than as a recommendation. */
export const CONFIDENCE_REVIEW_THRESHOLD = 0.5;
