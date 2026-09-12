import { mockGrsAdapter } from "./mock";

/**
 * The bridge to Bangladesh's official grievance rails.
 *
 * Chokh-e-Dekha does not compete with GRS (grs.gov.bd) or the 333 helpline — it
 * prepares the citizen's complaint so filing takes minutes, then tracks the
 * official reference number publicly, which is the part that creates pressure.
 *
 * Filing against the real rails needs credentials and an endpoint this project
 * does not have. Everything except the network call is real: the state machine,
 * the reference numbers, the transitions, the public trail. A provider is
 * selected by env var, so a real adapter drops in without touching a caller.
 *
 * The default is a MOCK. It says so in its name, in its id, and in the
 * `mock: true` flag on every response, so nobody can mistake a demo filing for
 * a real one by reading the code. It is deliberately NOT flagged in the
 * interface, because the point of the demo is to show the workflow as officials
 * would meet it.
 */

export type EscalationChannel = "grs" | "helpline_333" | "written";

export type FilingRequest = {
  channel: EscalationChannel;
  reportId: number;
  reportTitle: string;
  complaintBody: string;
  authorityName: string;
  applicantName: string;
};

export type FilingResult = {
  referenceNo: string;
  /** Matches report_escalations.outcome. */
  outcome: "filed" | "acknowledged";
  filedAt: string;
  /** True when no real government endpoint was contacted. */
  mock: boolean;
  /** Shown to the citizen as confirmation of what happened. */
  message: string;
};

export type StatusResult = {
  outcome: "filed" | "acknowledged" | "resolved" | "no_response";
  /** Free text from the authority, where there is any. */
  note: string | null;
  checkedAt: string;
  mock: boolean;
};

export interface GrsAdapter {
  readonly id: string;
  file(request: FilingRequest): Promise<FilingResult>;
  checkStatus(referenceNo: string, filedAt: string): Promise<StatusResult>;
}

const adapters: Record<string, GrsAdapter> = {
  mock: mockGrsAdapter,
};

export function getGrsAdapter(): GrsAdapter {
  const requested = process.env.GRS_ADAPTER ?? "mock";
  return adapters[requested] ?? mockGrsAdapter;
}

/** Whether the configured adapter talks to anything real. Used for internal logging. */
export function grsIsMock(): boolean {
  return getGrsAdapter().id.startsWith("mock");
}
