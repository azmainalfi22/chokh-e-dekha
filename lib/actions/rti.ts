"use server";

import { revalidatePath } from "next/cache";
import { createClient } from "@/lib/supabase/server";
import { generateRtiLetter } from "@/lib/rti";
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
