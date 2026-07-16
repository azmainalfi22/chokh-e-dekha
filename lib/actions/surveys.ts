"use server";

import { revalidatePath } from "next/cache";
import { createClient } from "@/lib/supabase/server";
import { surveySchema } from "@/lib/validations";

type Result = { ok: true; id?: number } | { ok: false; error: string };

async function requireAdmin() {
  const supabase = await createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();
  if (!user) return { supabase, user: null, isAdmin: false };
  const { data } = await supabase
    .from("profiles")
    .select("role")
    .eq("id", user.id)
    .single();
  return { supabase, user, isAdmin: data?.role === "admin" };
}

export async function createSurvey(input: unknown): Promise<Result> {
  const parsed = surveySchema.safeParse(input);
  if (!parsed.success) {
    return { ok: false, error: parsed.error.issues[0].message };
  }
  const { supabase, user, isAdmin } = await requireAdmin();
  if (!isAdmin || !user) return { ok: false, error: "Admin access required" };

  const { data, error } = await supabase
    .from("surveys")
    .insert({
      title: parsed.data.title,
      description: parsed.data.description || null,
      questions: parsed.data.questions,
      created_by: user.id,
      is_active: true,
    })
    .select("id")
    .single();
  if (error || !data) return { ok: false, error: "Could not create the survey" };

  revalidatePath("/admin/surveys");
  revalidatePath("/surveys");
  return { ok: true, id: data.id };
}

export async function setSurveyActive(
  id: number,
  active: boolean
): Promise<Result> {
  const { supabase, isAdmin } = await requireAdmin();
  if (!isAdmin) return { ok: false, error: "Admin access required" };
  const { error } = await supabase
    .from("surveys")
    .update({ is_active: active })
    .eq("id", id);
  if (error) return { ok: false, error: "Could not update the survey" };
  revalidatePath("/admin/surveys");
  revalidatePath("/surveys");
  return { ok: true };
}

/** Submit a response. Answers keyed by question id. */
export async function submitSurveyResponse(
  surveyId: number,
  answers: Record<string, string>
): Promise<Result> {
  const supabase = await createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();

  const clean: Record<string, string> = {};
  for (const [k, v] of Object.entries(answers)) {
    if (typeof v === "string" && v.trim()) clean[k] = v.trim().slice(0, 2000);
  }
  if (Object.keys(clean).length === 0) {
    return { ok: false, error: "Answer at least one question" };
  }

  const { error } = await supabase.from("survey_responses").insert({
    survey_id: surveyId,
    user_id: user?.id ?? null,
    answers: clean,
  });
  if (error) return { ok: false, error: "Could not submit your response" };
  revalidatePath(`/surveys/${surveyId}`);
  return { ok: true };
}
