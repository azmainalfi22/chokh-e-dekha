"use server";

import { revalidatePath } from "next/cache";
import { createClient } from "@/lib/supabase/server";
import { STATUSES, type Status } from "@/lib/constants";

type Result = { ok: true } | { ok: false; error: string };

/**
 * An officer moving a report through its lifecycle.
 *
 * The authorisation is the database's, not this function's. An officer's RLS
 * policy only matches reports inside the territories they are assigned, and the
 * update guard reverts every column they have no business changing — they get
 * status, note, priority, assignment and resolution state, and cannot publish a
 * report or rewrite the citizen's own words. So this can be a thin wrapper: if
 * the row is outside their patch the update simply matches nothing.
 */
export async function officerUpdateReport(
  reportId: number,
  input: { status?: string; note?: string; takeOwnership?: boolean }
): Promise<Result> {
  const supabase = await createClient();

  const {
    data: { user },
  } = await supabase.auth.getUser();
  if (!user) return { ok: false, error: "You must be signed in" };

  const patch: {
    status?: string;
    admin_note?: string | null;
    assigned_to?: string;
    assigned_at?: string;
  } = {};

  if (input.status !== undefined) {
    if (!STATUSES.includes(input.status as Status)) {
      return { ok: false, error: "Unknown status" };
    }
    patch.status = input.status;
  }

  if (input.note !== undefined) {
    patch.admin_note = input.note.trim() || null;
  }

  if (input.takeOwnership) {
    patch.assigned_to = user.id;
    patch.assigned_at = new Date().toISOString();
  }

  if (Object.keys(patch).length === 0) return { ok: true };

  const { data, error } = await supabase
    .from("reports")
    .update(patch)
    .eq("id", reportId)
    .select("id");

  if (error) return { ok: false, error: "Could not save that change" };

  // No rows matched: the report is outside this officer's patch.
  if (!data || data.length === 0) {
    return { ok: false, error: "That report is not in your area" };
  }

  revalidatePath("/officer");
  revalidatePath(`/reports/${reportId}`);
  return { ok: true };
}
