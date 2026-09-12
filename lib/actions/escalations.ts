"use server";

import { revalidatePath } from "next/cache";
import { getGrsAdapter } from "@/lib/grs/adapter";
import { createClient } from "@/lib/supabase/server";
import { getSlaState, isEscalationEligible } from "@/lib/sla";
import { authorityFromKey } from "@/lib/routing";
import {
  generate333Script,
  generateGrsComplaint,
  type EscalationChannel,
} from "@/lib/grs";

type Result = { ok: true } | { ok: false; error: string };

export type CreateEscalationResult =
  | { ok: true; id: number; body: string }
  | { ok: false; error: string };

const CHANNELS: EscalationChannel[] = ["grs", "helpline_333", "written"];
const OUTCOMES = ["filed", "acknowledged", "resolved", "no_response"] as const;

/**
 * Draft (or fetch the existing draft of) an escalation for the caller's own
 * stuck report. The complaint text is generated server-side from the report
 * record so the stored copy can't be spoofed.
 */
export async function createEscalation(
  reportId: number,
  channel: EscalationChannel,
  language: "en" | "bn"
): Promise<CreateEscalationResult> {
  if (!CHANNELS.includes(channel)) return { ok: false, error: "Bad channel" };

  const supabase = await createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();
  if (!user) return { ok: false, error: "You must be signed in" };

  const { data: report } = await supabase
    .from("reports")
    .select(
      "id, user_id, title, description, category, city_corporation, location_text, status, is_approved, sla_due_at, resolution_state, routed_authority_key, created_at"
    )
    .eq("id", reportId)
    .maybeSingle();
  if (!report || report.user_id !== user.id) {
    return { ok: false, error: "Only the reporter can escalate this report" };
  }
  if (!report.is_approved) {
    return { ok: false, error: "The report must be public first" };
  }
  if (
    !isEscalationEligible(report.status, report.sla_due_at, report.resolution_state)
  ) {
    return {
      ok: false,
      error: "Escalation opens when the deadline is breached or a fix is disputed",
    };
  }

  // Existing draft for this channel? Return it (unique per channel).
  const { data: existing } = await supabase
    .from("report_escalations")
    .select("id, complaint_body, language")
    .eq("report_id", reportId)
    .eq("user_id", user.id)
    .eq("channel", channel)
    .maybeSingle();

  const { data: profile } = await supabase
    .from("profiles")
    .select("display_name, phone")
    .eq("id", user.id)
    .single();

  const sla = getSlaState(report.sla_due_at, report.status);
  const authority = authorityFromKey(
    report.routed_authority_key,
    report.category,
    report.city_corporation
  );
  const input = {
    reportId: report.id,
    reportTitle: report.title,
    reportDescription: report.description,
    category: report.category,
    cityCorporation: report.city_corporation,
    locationText: report.location_text,
    authorityName: authority.name,
    reportCreatedAt: report.created_at,
    slaDueAt: report.sla_due_at,
    daysOverdue: sla.kind === "breached" ? sla.daysOverdue : null,
    disputed: report.resolution_state === "disputed",
    applicantName: profile?.display_name ?? "Citizen",
    applicantPhone: profile?.phone,
    language,
  };
  const body =
    channel === "helpline_333"
      ? generate333Script(input)
      : generateGrsComplaint(input);

  if (existing) {
    // Refresh the draft body if the language changed.
    if (existing.language !== language) {
      await supabase
        .from("report_escalations")
        .update({ complaint_body: body, language })
        .eq("id", existing.id);
      return { ok: true, id: existing.id, body };
    }
    return { ok: true, id: existing.id, body: existing.complaint_body };
  }

  const { data: created, error } = await supabase
    .from("report_escalations")
    .insert({
      report_id: reportId,
      user_id: user.id,
      channel,
      language,
      complaint_body: body,
    })
    .select("id")
    .single();
  if (error || !created) {
    return { ok: false, error: "Could not create the escalation" };
  }

  revalidatePath(`/reports/${reportId}`);
  return { ok: true, id: created.id, body };
}

/** Log the official reference number once the complaint is actually filed. */
export async function setEscalationReference(
  id: number,
  referenceNo: string
): Promise<Result> {
  const ref = (referenceNo ?? "").trim().slice(0, 80);
  const supabase = await createClient();
  const { data, error } = await supabase
    .from("report_escalations")
    .update({
      reference_no: ref || null,
      outcome: "filed",
      filed_at: new Date().toISOString(),
    })
    .eq("id", id)
    .select("report_id")
    .single();
  if (error || !data) return { ok: false, error: "Could not save the reference" };
  revalidatePath(`/reports/${data.report_id}`);
  return { ok: true };
}

/** Record what the escalation led to. */
export async function setEscalationOutcome(
  id: number,
  outcome: string
): Promise<Result> {
  if (!OUTCOMES.includes(outcome as (typeof OUTCOMES)[number])) {
    return { ok: false, error: "Bad outcome" };
  }
  const supabase = await createClient();
  const { data, error } = await supabase
    .from("report_escalations")
    .update({ outcome })
    .eq("id", id)
    .select("report_id")
    .single();
  if (error || !data) return { ok: false, error: "Could not save the outcome" };
  revalidatePath(`/reports/${data.report_id}`);
  return { ok: true };
}

/**
 * File the complaint through the configured channel and record what came back.
 *
 * Replaces asking the citizen to copy a reference number out of another website
 * by hand. The adapter behind this is selected by env var; by default it is a
 * local mock that contacts nothing, so the whole workflow runs offline. See
 * lib/grs/adapter.ts.
 */
export async function submitEscalation(id: number): Promise<Result> {
  const supabase = await createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();
  if (!user) return { ok: false, error: "You must be signed in" };

  const { data: escalation } = await supabase
    .from("report_escalations")
    .select("id, report_id, user_id, channel, complaint_body, reference_no")
    .eq("id", id)
    .maybeSingle();

  if (!escalation) return { ok: false, error: "Escalation not found" };
  if (escalation.user_id !== user.id) return { ok: false, error: "Not your escalation" };
  if (escalation.reference_no) return { ok: false, error: "Already filed" };

  const { data: report } = await supabase
    .from("reports")
    .select("id, title, category, city_corporation, routed_authority_key")
    .eq("id", escalation.report_id)
    .single();

  const { data: profile } = await supabase
    .from("profiles")
    .select("display_name")
    .eq("id", user.id)
    .single();

  const result = await getGrsAdapter().file({
    channel: escalation.channel as "grs" | "helpline_333" | "written",
    reportId: escalation.report_id,
    reportTitle: report?.title ?? "",
    complaintBody: escalation.complaint_body,
    authorityName: report
      ? authorityFromKey(
          report.routed_authority_key,
          report.category,
          report.city_corporation
        ).name
      : "",
    applicantName: profile?.display_name ?? "Citizen",
  });

  const { error } = await supabase
    .from("report_escalations")
    .update({
      reference_no: result.referenceNo,
      outcome: result.outcome,
      filed_at: result.filedAt,
    })
    .eq("id", id);

  if (error) return { ok: false, error: "Could not record the filing" };

  revalidatePath(`/reports/${escalation.report_id}`);
  return { ok: true };
}

/** Ask the channel where the complaint has got to, and record the answer. */
export async function refreshEscalationStatus(id: number): Promise<Result> {
  const supabase = await createClient();

  const { data: escalation } = await supabase
    .from("report_escalations")
    .select("id, report_id, reference_no, filed_at")
    .eq("id", id)
    .maybeSingle();

  if (!escalation?.reference_no || !escalation.filed_at) {
    return { ok: false, error: "File the complaint first" };
  }

  const status = await getGrsAdapter().checkStatus(
    escalation.reference_no,
    escalation.filed_at
  );

  const { error } = await supabase
    .from("report_escalations")
    .update({ outcome: status.outcome })
    .eq("id", id);

  if (error) return { ok: false, error: "Could not save the status" };

  revalidatePath(`/reports/${escalation.report_id}`);
  return { ok: true };
}
