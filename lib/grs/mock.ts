import type {
  FilingRequest,
  FilingResult,
  GrsAdapter,
  StatusResult,
} from "./adapter";

/**
 * MOCK adapter for the GRS / 333 bridge. Contacts nothing.
 *
 * Every part of the escalation except the network call is genuine: the
 * reference-number format, the state machine, the timings, and the public trail
 * the citizen sees. What is simulated is the authority on the other end.
 *
 * Deterministic on purpose. The reference number and every later status are
 * derived from the report id and the filing time, so a demo shows the same thing
 * twice and a refreshed page does not invent a new outcome. Randomness would
 * make the workflow look unreliable in exactly the setting where it needs to
 * look dependable.
 */

/** GRS reference numbers look like GRS-2026-000123; 333 issues a shorter ticket. */
function referenceFor(channel: FilingRequest["channel"], reportId: number, when: Date): string {
  const year = when.getFullYear();
  const serial = `${100000 + (reportId * 7919) % 899999}`;

  switch (channel) {
    case "grs":
      return `GRS-${year}-${serial}`;
    case "helpline_333":
      return `333-${year}${`${when.getMonth() + 1}`.padStart(2, "0")}-${serial.slice(0, 5)}`;
    case "written":
      return `MEMO-${year}-${serial.slice(0, 4)}`;
  }
}

/**
 * How an authority typically responds, by channel.
 *
 * Not a guess dressed as data: these are the shapes the workflow has to handle —
 * an immediate acknowledgement, a slow acknowledgement, and silence — and each
 * channel gets the one that matches how it behaves in practice. 333 answers on
 * the call; GRS sends an acknowledgement in a day or two; a written memo often
 * gets nothing at all, which is the case the escalation exists to expose.
 */
const CHANNEL_BEHAVIOUR = {
  grs: { acknowledgeAfterHours: 36, resolveAfterHours: 24 * 12, silent: false },
  helpline_333: { acknowledgeAfterHours: 0, resolveAfterHours: 24 * 7, silent: false },
  written: { acknowledgeAfterHours: 24 * 5, resolveAfterHours: 24 * 30, silent: true },
} as const;

const ACKNOWLEDGEMENT_NOTES: Record<FilingRequest["channel"], string> = {
  grs: "Complaint registered and forwarded to the responsible office for action.",
  helpline_333: "Call logged by the operator and routed to the city corporation.",
  written: "Memo received at the registry.",
};

function hoursBetween(from: Date, to: Date): number {
  return (to.getTime() - from.getTime()) / 3_600_000;
}

export const mockGrsAdapter: GrsAdapter = {
  id: "mock-grs-333",

  async file(request: FilingRequest): Promise<FilingResult> {
    const now = new Date();
    const referenceNo = referenceFor(request.channel, request.reportId, now);
    const behaviour = CHANNEL_BEHAVIOUR[request.channel];

    // The 333 helpline acknowledges on the call itself; the others take time.
    const outcome = behaviour.acknowledgeAfterHours === 0 ? "acknowledged" : "filed";

    return {
      referenceNo,
      outcome,
      filedAt: now.toISOString(),
      mock: true,
      message:
        outcome === "acknowledged"
          ? `Logged with the helpline. Quote ${referenceNo} when you follow up.`
          : `Submitted. Keep ${referenceNo} — it is how you and anyone else can track this.`,
    };
  },

  async checkStatus(referenceNo: string, filedAt: string): Promise<StatusResult> {
    const filed = new Date(filedAt);
    const now = new Date();
    const elapsed = hoursBetween(filed, now);

    const channel: FilingRequest["channel"] = referenceNo.startsWith("GRS-")
      ? "grs"
      : referenceNo.startsWith("333-")
        ? "helpline_333"
        : "written";

    const behaviour = CHANNEL_BEHAVIOUR[channel];

    // A memo that has gone unanswered long past its window is the outcome this
    // whole feature exists to make visible, so the mock reproduces it rather
    // than resolving everything happily.
    if (behaviour.silent && elapsed > behaviour.acknowledgeAfterHours) {
      return {
        outcome: elapsed > behaviour.resolveAfterHours ? "no_response" : "filed",
        note: null,
        checkedAt: now.toISOString(),
        mock: true,
      };
    }

    if (elapsed >= behaviour.resolveAfterHours) {
      return {
        outcome: "resolved",
        note: "The responsible office reports the issue as addressed.",
        checkedAt: now.toISOString(),
        mock: true,
      };
    }

    if (elapsed >= behaviour.acknowledgeAfterHours) {
      return {
        outcome: "acknowledged",
        note: ACKNOWLEDGEMENT_NOTES[channel],
        checkedAt: now.toISOString(),
        mock: true,
      };
    }

    return {
      outcome: "filed",
      note: null,
      checkedAt: now.toISOString(),
      mock: true,
    };
  },
};
