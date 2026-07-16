"use server";

import { revalidatePath } from "next/cache";
import { createClient } from "@/lib/supabase/server";
import { commentSchema } from "@/lib/validations";

type Result = { ok: true } | { ok: false; error: string };

/** Toggle an endorsement (upvote). Returns the new state. */
export async function toggleEndorsement(
  reportId: number
): Promise<Result & { endorsed?: boolean }> {
  const supabase = await createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();
  if (!user) return { ok: false, error: "Log in to endorse reports" };

  const { data: existing } = await supabase
    .from("report_endorsements")
    .select("report_id")
    .eq("report_id", reportId)
    .eq("user_id", user.id)
    .maybeSingle();

  if (existing) {
    const { error } = await supabase
      .from("report_endorsements")
      .delete()
      .eq("report_id", reportId)
      .eq("user_id", user.id);
    if (error) return { ok: false, error: "Could not remove endorsement" };
    revalidatePath(`/reports/${reportId}`);
    return { ok: true, endorsed: false };
  }

  const { error } = await supabase
    .from("report_endorsements")
    .insert({ report_id: reportId, user_id: user.id });
  if (error) return { ok: false, error: "Could not endorse" };
  revalidatePath(`/reports/${reportId}`);
  return { ok: true, endorsed: true };
}

/** Toggle a private bookmark. Returns the new state. */
export async function toggleBookmark(
  reportId: number
): Promise<Result & { bookmarked?: boolean }> {
  const supabase = await createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();
  if (!user) return { ok: false, error: "Log in to bookmark reports" };

  const { data: existing } = await supabase
    .from("report_bookmarks")
    .select("report_id")
    .eq("report_id", reportId)
    .eq("user_id", user.id)
    .maybeSingle();

  if (existing) {
    const { error } = await supabase
      .from("report_bookmarks")
      .delete()
      .eq("report_id", reportId)
      .eq("user_id", user.id);
    if (error) return { ok: false, error: "Could not remove bookmark" };
    revalidatePath("/bookmarks");
    return { ok: true, bookmarked: false };
  }

  const { error } = await supabase
    .from("report_bookmarks")
    .insert({ report_id: reportId, user_id: user.id });
  if (error) return { ok: false, error: "Could not bookmark" };
  revalidatePath("/bookmarks");
  return { ok: true, bookmarked: true };
}

/** Add a comment (optionally a reply via parentId). */
export async function addComment(
  reportId: number,
  input: unknown
): Promise<Result> {
  const parsed = commentSchema.safeParse(input);
  if (!parsed.success) {
    return { ok: false, error: parsed.error.issues[0].message };
  }

  const supabase = await createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();
  if (!user) return { ok: false, error: "Log in to comment" };

  const { error } = await supabase.from("report_comments").insert({
    report_id: reportId,
    user_id: user.id,
    body: parsed.data.body,
    parent_id: parsed.data.parentId ?? null,
  });
  if (error) return { ok: false, error: "Could not post the comment" };

  revalidatePath(`/reports/${reportId}`);
  return { ok: true };
}

/** Delete own comment (admins can delete any — enforced by RLS). */
export async function deleteComment(
  commentId: number,
  reportId: number
): Promise<Result> {
  const supabase = await createClient();
  const { error } = await supabase
    .from("report_comments")
    .delete()
    .eq("id", commentId);
  if (error) return { ok: false, error: "Could not delete the comment" };
  revalidatePath(`/reports/${reportId}`);
  return { ok: true };
}

/** Record a share (Web Share / copy link). */
export async function recordShare(reportId: number): Promise<void> {
  const supabase = await createClient();
  await supabase.rpc("increment_share_count", { p_report_id: reportId });
}

/** Mark all of the user's notifications as read. */
export async function markNotificationsRead(): Promise<void> {
  const supabase = await createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();
  if (!user) return;
  await supabase
    .from("notifications")
    .update({ is_read: true })
    .eq("user_id", user.id)
    .eq("is_read", false);
}
