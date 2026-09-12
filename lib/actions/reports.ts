"use server";

import { revalidatePath } from "next/cache";
import { createClient, createServiceClient } from "@/lib/supabase/server";
import { reportSchema } from "@/lib/validations";
import { resolveAuthorityKey } from "@/lib/routing";
import { getClassifier } from "@/lib/classifier";

export type CreateReportResult =
  | { ok: true; reportId: number; autoPublished: boolean }
  | { ok: false; error: string };

/**
 * Creates a report plus its media rows.
 *
 * Media files are already in the report-media bucket, put there by
 * POST /api/report-media, which strips their metadata and chooses the path.
 * The prefix check below is kept as a second lock: it is cheap, and it means a
 * caller reaching this action directly still cannot attach someone else's
 * objects to their own report.
 */
export async function createReport(
  input: unknown,
  storagePaths: string[]
): Promise<CreateReportResult> {
  const parsed = reportSchema.safeParse(input);
  if (!parsed.success) {
    return { ok: false, error: parsed.error.issues[0].message };
  }

  const supabase = await createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();
  if (!user) return { ok: false, error: "You must be signed in" };

  const paths = (storagePaths ?? []).filter(
    (p): p is string => typeof p === "string"
  );
  if (paths.length > 6) {
    return { ok: false, error: "At most 6 photos per report" };
  }
  if (paths.some((p) => !p.startsWith(`${user.id}/`) || p.includes(".."))) {
    return { ok: false, error: "Invalid media path" };
  }

  const d = parsed.data;
  const { data: report, error } = await supabase
    .from("reports")
    .insert({
      user_id: user.id,
      title: d.title,
      description: d.description,
      category: d.category,
      city_corporation: d.cityCorporation,
      location_text: d.locationText || null,
      latitude: d.latitude,
      longitude: d.longitude,
      // Route to the responsible body from validated category + city. Computed
      // here (not from client input) so it can't be spoofed.
      routed_authority_key: resolveAuthorityKey(d.category, d.cityCorporation),
    })
    .select("id, auto_published, sla_due_at")
    .single();

  if (error || !report) {
    return { ok: false, error: "Could not submit the report — try again" };
  }

  if (paths.length > 0) {
    const { error: mediaError } = await supabase.from("report_media").insert(
      paths.map((p) => ({
        report_id: report.id,
        storage_path: p,
        media_type: "image" as const,
      }))
    );
    if (mediaError) {
      // Report exists; media metadata failed. Surface but don't lose the report.
      return { ok: true, reportId: report.id, autoPublished: report.auto_published };
    }
  }

  // Suggest a category. The suggestion is stored beside the report and changes
  // nothing until an admin accepts it — see supabase/migrations/…_classifier_suggestions.
  try {
    const suggestion = await getClassifier().classify({
      title: d.title,
      description: d.description,
      cityCorporation: d.cityCorporation,
    });
    await supabase.rpc("set_report_suggestion", {
      p_report_id: report.id,
      p_suggestion: suggestion,
    });
  } catch {
    // A classifier that is down must never cost a citizen their report.
  }

  // Group this with an existing report of the same problem, if there is one.
  // Runs after the insert because detection compares against rows in the table
  // and needs this one to have an id. Failing must never cost a citizen their
  // report — they have already been told it was filed — so the outcome is
  // ignored rather than surfaced.
  await supabase.rpc("link_report_duplicate", { p_report_id: report.id });

  // Trusted reporters skip moderation (DB trigger) — tell them it's live.
  if (report.auto_published) {
    const due = report.sla_due_at
      ? new Date(report.sla_due_at).toLocaleDateString("en-GB", {
          day: "numeric",
          month: "long",
          year: "numeric",
        })
      : null;
    const service = createServiceClient();
    await service.from("notifications").insert({
      user_id: user.id,
      report_id: report.id,
      type: "approved",
      title: "Published immediately — trusted reporter",
      body: `Your report "${d.title}" is already public.${due ? ` Resolution is due by ${due}.` : ""}`,
    });
    revalidatePath("/reports");
  }

  revalidatePath("/my-reports");
  revalidatePath("/dashboard");
  return { ok: true, reportId: report.id, autoPublished: report.auto_published };
}

type ActionResult = { ok: true } | { ok: false; error: string };

/** Reporter confirms their report was actually fixed. */
export async function confirmResolution(
  reportId: number
): Promise<ActionResult> {
  const supabase = await createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();
  if (!user) return { ok: false, error: "You must be signed in" };

  const { error } = await supabase.rpc("confirm_resolution", {
    p_report_id: reportId,
  });
  if (error) return { ok: false, error: "Could not confirm — try again" };

  revalidatePath(`/reports/${reportId}`);
  revalidatePath("/my-reports");
  return { ok: true };
}

/** Reporter disputes the fix → the report reopens and the SLA resumes. */
export async function disputeResolution(
  reportId: number,
  reason: string
): Promise<ActionResult> {
  const supabase = await createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();
  if (!user) return { ok: false, error: "You must be signed in" };

  const { error } = await supabase.rpc("dispute_resolution", {
    p_report_id: reportId,
    p_reason: (reason ?? "").slice(0, 500),
  });
  if (error) return { ok: false, error: "Could not reopen — try again" };

  revalidatePath(`/reports/${reportId}`);
  revalidatePath("/my-reports");
  return { ok: true };
}

/**
 * How many active reports of the same utility category exist in a city —
 * powers the "you're not alone, N active reports" alert on the submit flow.
 * Runs server-side (reliable network) via the area_outage_count RPC.
 */
export async function areaOutageCount(
  category: string,
  city: string
): Promise<number> {
  if (!category || !city) return 0;
  const supabase = await createClient();
  const { data, error } = await supabase.rpc("area_outage_count", {
    p_category: category,
    p_city: city,
  });
  if (error || typeof data !== "number") return 0;
  return data;
}

export type NearbyReport = {
  id: number;
  title: string;
  category: string;
  status: string;
  endorse_count: number;
  distance_m: number;
};

/**
 * Duplicate detection: nearby approved reports of the same category. Runs
 * server-side (reliable network) via the reports_near proximity RPC.
 */
export async function findNearbyReports(
  lat: number,
  lng: number,
  category: string | null,
  radiusM = 500
): Promise<NearbyReport[]> {
  if (!Number.isFinite(lat) || !Number.isFinite(lng)) return [];
  const supabase = await createClient();
  const { data, error } = await supabase.rpc("reports_near", {
    p_lat: lat,
    p_lng: lng,
    p_radius_m: radiusM,
    p_category: category,
  });
  if (error) return [];
  return (data ?? []).map((r) => ({
    id: r.id,
    title: r.title,
    category: r.category,
    status: r.status,
    endorse_count: r.endorse_count,
    distance_m: r.distance_m,
  }));
}
