"use server";

import { revalidatePath } from "next/cache";
import { createClient } from "@/lib/supabase/server";
import {
  RTI_RESPONSE_WORKING_DAYS,
  addWorkingDays,
  generateRtiAppeal,
  generateRtiLetter,
} from "@/lib/rti";
import { rtiSchema } from "@/lib/validations";

export type SaveRtiResult =
  | { ok: true; id: number }
  | { ok: false; error: string };

/** Persist a generated RTI letter for later reprint (FR-13). */
export async function saveRtiLetter(input: unknown): Promise<SaveRtiResult> {
  const parsed = rtiSchema.safeParse(input);
  if (!parsed.success) {
    return { ok: false, error: parsed.error.issues[0].message };
  }

  const supabase = await createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();
  if (!user) return { ok: false, error: "You must be signed in" };

  const d = parsed.data;
  const body = generateRtiLetter({
    authority: d.authority,
    subject: d.subject,
    informationSought: d.informationSought,
    reason: d.reason,
    deliveryMode: d.deliveryMode,
    applicantName: d.applicantName,
    applicantAddress: d.applicantAddress,
    applicantPhone: d.applicantPhone,
    applicantEmail: d.applicantEmail,
    language: d.language,
  });

  const { data, error } = await supabase
    .from("rti_letters")
    .insert({
      user_id: user.id,
      report_id: d.reportId ?? null,
      authority: d.authority,
      subject: d.subject,
      body,
      language: d.language,
    })
    .select("id")
    .single();

  if (error || !data) {
    return { ok: false, error: "Could not save the letter" };
  }

  revalidatePath("/rti");
  return { ok: true, id: data.id };
}

export async function deleteRtiLetter(id: number): Promise<void> {
  const supabase = await createClient();
  await supabase.from("rti_letters").delete().eq("id", id);
  revalidatePath("/rti");
}

type LifecycleResult = { ok: true } | { ok: false; error: string };

/**
 * Mark an RTI application as submitted and start the statutory clock: the
 * 20-working-day deadline is computed from the submission date (RLS scopes
 * the update to the owner).
 */
export async function markRtiSubmitted(
  id: number,
  submittedDate: string
): Promise<LifecycleResult> {
  const supabase = await createClient();
  const submitted = submittedDate ? new Date(submittedDate) : new Date();
  if (Number.isNaN(submitted.getTime())) {
    return { ok: false, error: "Invalid date" };
  }
  const deadline = addWorkingDays(submitted, RTI_RESPONSE_WORKING_DAYS);
  const { error } = await supabase
    .from("rti_letters")
    .update({
      status: "submitted",
      submitted_at: submitted.toISOString(),
      deadline_at: deadline.toISOString(),
      responded_at: null,
      outcome: null,
    })
    .eq("id", id);
  if (error) return { ok: false, error: "Could not update — try again" };
  revalidatePath(`/rti/${id}`);
  revalidatePath("/rti");
  return { ok: true };
}

/** Record the authority's response and its outcome. */
export async function recordRtiResponse(
  id: number,
  outcome: "received" | "partial" | "refused" | "no_response"
): Promise<LifecycleResult> {
  const valid = ["received", "partial", "refused", "no_response"] as const;
  if (!valid.includes(outcome)) return { ok: false, error: "Invalid outcome" };
  const supabase = await createClient();
  const closed = outcome === "received";
  const { error } = await supabase
    .from("rti_letters")
    .update({
      status: closed ? "closed" : "responded",
      responded_at: new Date().toISOString(),
      outcome,
    })
    .eq("id", id);
  if (error) return { ok: false, error: "Could not save — try again" };
  revalidatePath(`/rti/${id}`);
  revalidatePath("/rti");
  return { ok: true };
}

export type AppealResult =
  | { ok: true; body: string }
  | { ok: false; error: string };

/**
 * Generate the Appellate Authority appeal (RTI Act s.24) for a letter that
 * was refused, answered partially, or ignored past the deadline. The appeal
 * body is composed server-side from the stored lifecycle dates.
 */
export async function generateAppeal(id: number): Promise<AppealResult> {
  const supabase = await createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();
  if (!user) return { ok: false, error: "You must be signed in" };

  const { data: letter } = await supabase
    .from("rti_letters")
    .select(
      "id, authority, subject, submitted_at, deadline_at, outcome, status, language"
    )
    .eq("id", id)
    .maybeSingle();
  if (!letter) return { ok: false, error: "Letter not found" };
  if (!letter.submitted_at || !letter.deadline_at) {
    return { ok: false, error: "Mark the application as submitted first" };
  }

  const overdue = new Date(letter.deadline_at).getTime() < Date.now();
  const refusedOrPartial =
    letter.outcome === "refused" || letter.outcome === "partial";
  if (!overdue && !refusedOrPartial) {
    return {
      ok: false,
      error: "Appeal opens after the deadline passes or on a refusal",
    };
  }

  const { data: profile } = await supabase
    .from("profiles")
    .select("display_name, phone, city")
    .eq("id", user.id)
    .single();

  const appealOutcome =
    letter.outcome === "refused"
      ? "refused"
      : letter.outcome === "partial"
        ? "partial"
        : "no_response";

  const body = generateRtiAppeal({
    authority: letter.authority,
    subject: letter.subject,
    submittedDate: letter.submitted_at,
    deadlineDate: letter.deadline_at,
    outcome: appealOutcome,
    applicantName: profile?.display_name ?? "Citizen",
    applicantAddress: profile?.city ?? "—",
    applicantPhone: profile?.phone,
    language: (letter.language as "en" | "bn") ?? "en",
  });

  const { error } = await supabase
    .from("rti_letters")
    .update({ status: "appealed", appeal_body: body })
    .eq("id", id);
  if (error) return { ok: false, error: "Could not save the appeal" };

  revalidatePath(`/rti/${id}`);
  revalidatePath("/rti");
  return { ok: true, body };
}
