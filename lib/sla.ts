import { DEFAULT_SLA_DAYS } from "@/lib/constants";

/** Compute the SLA deadline from an approval time (SPEC.md: FR-10). */
export function computeSlaDueAt(
  approvedAt: Date,
  slaDays: number = DEFAULT_SLA_DAYS
): Date {
  const due = new Date(approvedAt);
  due.setDate(due.getDate() + slaDays);
  return due;
}

export type SlaState =
  | { kind: "none" }
  | { kind: "ok"; daysLeft: number }
  | { kind: "due-soon"; daysLeft: number }
  | { kind: "breached"; daysOverdue: number };

/**
 * SLA state for a report. Only open reports (pending / in_progress) can
 * breach — resolved and rejected reports are settled.
 */
export function getSlaState(
  slaDueAt: string | null,
  status: string,
  now: Date = new Date()
): SlaState {
  if (!slaDueAt || status === "resolved" || status === "rejected") {
    return { kind: "none" };
  }
  const due = new Date(slaDueAt);
  const msLeft = due.getTime() - now.getTime();
  const days = Math.ceil(Math.abs(msLeft) / 86_400_000);
  if (msLeft < 0) return { kind: "breached", daysOverdue: days };
  if (msLeft < 2 * 86_400_000) return { kind: "due-soon", daysLeft: days };
  return { kind: "ok", daysLeft: days };
}
