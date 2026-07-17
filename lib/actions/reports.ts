"use server";

import { revalidatePath } from "next/cache";
import { createClient } from "@/lib/supabase/server";
import { reportSchema } from "@/lib/validations";

export type CreateReportResult =
  | { ok: true; reportId: number }
  | { ok: false; error: string };

/**
 * Creates a report plus its media rows. Media files are already uploaded
 * to the report-media bucket by the client (authenticated upload); paths
 * must live under the caller's own uid prefix.
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
    })
    .select("id")
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
      return { ok: true, reportId: report.id };
    }
  }

  revalidatePath("/my-reports");
  revalidatePath("/dashboard");
  return { ok: true, reportId: report.id };
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
