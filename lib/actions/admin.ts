"use server";

import { revalidatePath } from "next/cache";
import { createClient, createServiceClient } from "@/lib/supabase/server";
import { computeSlaDueAt } from "@/lib/sla";
import { sendStatusChangeEmail } from "@/lib/email";
import { STATUSES, type Status } from "@/lib/constants";

type Result = { ok: true; count?: number } | { ok: false; error: string };

/** Verifies the caller is an authenticated admin. */
async function requireAdmin() {
  const supabase = await createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();
  if (!user) return { supabase, user: null, isAdmin: false as const };
  const { data: profile } = await supabase
    .from("profiles")
    .select("role")
    .eq("id", user.id)
    .single();
  return { supabase, user, isAdmin: profile?.role === "admin" };
}

function revalidateAdmin() {
  revalidatePath("/admin");
  revalidatePath("/admin/reports");
  revalidatePath("/reports");
}

/** Approve reports: make public and start the SLA clock (FR-08/FR-10). */
export async function approveReports(ids: number[]): Promise<Result> {
  const { supabase, isAdmin } = await requireAdmin();
  if (!isAdmin) return { ok: false, error: "Admin access required" };
  if (!ids.length) return { ok: false, error: "No reports selected" };

  const now = new Date();
  const { data, error } = await supabase
    .from("reports")
    .update({
      is_approved: true,
      approved_at: now.toISOString(),
      sla_due_at: computeSlaDueAt(now).toISOString(),
    })
    .in("id", ids)
    .eq("is_approved", false)
    .select("id, user_id, title");

  if (error) return { ok: false, error: "Approval failed" };

  // Approval isn't a status change, so the trigger stays silent — notify here.
  const service = createServiceClient();
  const rows = (data ?? []).filter((r) => r.user_id);
  if (rows.length) {
    await service.from("notifications").insert(
      rows.map((r) => ({
        user_id: r.user_id!,
        report_id: r.id,
        type: "approved",
        title: "Report approved",
        body: `Your report "${r.title}" is approved and now public. Resolution is due by ${computeSlaDueAt(now).toLocaleDateString("en-GB", { day: "numeric", month: "long", year: "numeric" })}.`,
      }))
    );
  }

  revalidateAdmin();
  return { ok: true, count: data?.length ?? 0 };
}

/** Reject reports (kept private, marked rejected). */
export async function rejectReports(
  ids: number[],
  note?: string
): Promise<Result> {
  const { supabase, isAdmin } = await requireAdmin();
  if (!isAdmin) return { ok: false, error: "Admin access required" };
  if (!ids.length) return { ok: false, error: "No reports selected" };

  const { data, error } = await supabase
    .from("reports")
    .update({ status: "rejected", admin_note: note || null })
    .in("id", ids)
    .select("id");

  if (error) return { ok: false, error: "Rejection failed" };
  revalidateAdmin();
  return { ok: true, count: data?.length ?? 0 };
}

/** Change status (+ optional public note); notifies via trigger + email (FR-05/FR-08). */
export async function updateReportsStatus(
  ids: number[],
  status: string,
  note?: string
): Promise<Result> {
  const { supabase, isAdmin } = await requireAdmin();
  if (!isAdmin) return { ok: false, error: "Admin access required" };
  if (!ids.length) return { ok: false, error: "No reports selected" };
  if (!STATUSES.includes(status as Status)) {
    return { ok: false, error: "Invalid status" };
  }

  const patch: {
    status: string;
    admin_note?: string;
    resolution_state?: string | null;
    resolved_at?: string | null;
  } = { status };
  if (note !== undefined && note !== "") patch.admin_note = note;

  // Marking Resolved doesn't close the loop — the reporter must confirm the
  // fix. Enter pending_confirmation; any other status clears resolution state.
  if (status === "resolved") {
    patch.resolution_state = "pending_confirmation";
    patch.resolved_at = new Date().toISOString();
  } else {
    patch.resolution_state = null;
  }

  const { data, error } = await supabase
    .from("reports")
    .update(patch)
    .in("id", ids)
    .select("id, user_id, title, status, admin_note");

  if (error) return { ok: false, error: "Status update failed" };

  // Best-effort email fan-out (in-app notification handled by trigger).
  const service = createServiceClient();
  for (const r of data ?? []) {
    if (!r.user_id) continue;
    const { data: u } = await service.auth.admin.getUserById(r.user_id);
    if (u.user?.email) {
      await sendStatusChangeEmail({
        to: u.user.email,
        reportTitle: r.title,
        reportId: r.id,
        newStatus: r.status,
        note: r.admin_note,
      });
    }
  }

  revalidateAdmin();
  return { ok: true, count: data?.length ?? 0 };
}

/** Save a public official note without changing status. */
export async function setAdminNote(id: number, note: string): Promise<Result> {
  const { supabase, isAdmin } = await requireAdmin();
  if (!isAdmin) return { ok: false, error: "Admin access required" };

  const { error } = await supabase
    .from("reports")
    .update({ admin_note: note || null })
    .eq("id", id);
  if (error) return { ok: false, error: "Could not save the note" };
  revalidateAdmin();
  revalidatePath(`/reports/${id}`);
  return { ok: true };
}

/** Self-assign / unassign a report (FR-15). */
export async function assignReport(
  id: number,
  assign: boolean
): Promise<Result> {
  const { supabase, user, isAdmin } = await requireAdmin();
  if (!isAdmin || !user) return { ok: false, error: "Admin access required" };

  const { error } = await supabase
    .from("reports")
    .update(
      assign
        ? { assigned_to: user.id, assigned_at: new Date().toISOString() }
        : { assigned_to: null, assigned_at: null }
    )
    .eq("id", id);
  if (error) return { ok: false, error: "Assignment failed" };
  revalidateAdmin();
  return { ok: true };
}

/** Promote/demote a user (admins only; RLS backs this). */
export async function setUserRole(
  userId: string,
  role: "citizen" | "admin"
): Promise<Result> {
  const { supabase, user, isAdmin } = await requireAdmin();
  if (!isAdmin || !user) return { ok: false, error: "Admin access required" };
  if (userId === user.id) {
    return { ok: false, error: "You cannot change your own role" };
  }

  const { error } = await supabase
    .from("profiles")
    .update({ role })
    .eq("id", userId);
  if (error) return { ok: false, error: "Role update failed" };
  revalidatePath("/admin/users");
  return { ok: true };
}
