import { NextResponse, type NextRequest } from "next/server";
import { createClient } from "@/lib/supabase/server";

function csvCell(v: unknown): string {
  const s = v == null ? "" : String(v);
  return /[",\n]/.test(s) ? `"${s.replaceAll('"', '""')}"` : s;
}

/** CSV export of the filtered report set (FR-16). Admin-only. */
export async function GET(request: NextRequest) {
  const supabase = await createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();
  if (!user) return new NextResponse("Unauthorized", { status: 401 });

  const { data: profile } = await supabase
    .from("profiles")
    .select("role")
    .eq("id", user.id)
    .single();
  if (profile?.role !== "admin") {
    return new NextResponse("Forbidden", { status: 403 });
  }

  const params = request.nextUrl.searchParams;
  let query = supabase
    .from("reports")
    .select(
      `id, title, category, city_corporation, location_text, latitude,
       longitude, status, is_approved, admin_note, sla_due_at, approved_at,
       created_at, endorse_count, comment_count, view_count, share_count,
       profiles!reports_user_id_fkey ( display_name )`
    )
    .order("created_at", { ascending: false })
    .limit(5000);

  const status = params.get("status");
  const category = params.get("category");
  const city = params.get("city");
  const q = params.get("q");
  if (status) query = query.eq("status", status);
  if (category) query = query.eq("category", category);
  if (city) query = query.eq("city_corporation", city);
  if (params.get("moderation") === "needs-review") {
    query = query.eq("is_approved", false).neq("status", "rejected");
  }
  if (q) {
    const safe = q.replaceAll("%", "\\%").replaceAll(",", " ");
    query = query.or(`title.ilike.%${safe}%,description.ilike.%${safe}%`);
  }

  const { data, error } = await query;
  if (error) return new NextResponse("Export failed", { status: 500 });

  const header = [
    "id", "title", "category", "city_corporation", "location", "latitude",
    "longitude", "status", "approved", "admin_note", "sla_due_at",
    "approved_at", "created_at", "reporter", "endorsements", "comments",
    "views", "shares",
  ];
  const lines = [header.join(",")];
  for (const r of data ?? []) {
    lines.push(
      [
        r.id, r.title, r.category, r.city_corporation, r.location_text,
        r.latitude, r.longitude, r.status, r.is_approved, r.admin_note,
        r.sla_due_at, r.approved_at, r.created_at,
        r.profiles?.display_name ?? "", r.endorse_count, r.comment_count,
        r.view_count, r.share_count,
      ]
        .map(csvCell)
        .join(",")
    );
  }

  return new NextResponse(lines.join("\n"), {
    headers: {
      "Content-Type": "text/csv; charset=utf-8",
      "Content-Disposition": `attachment; filename="chokh-e-dekha-reports-${new Date().toISOString().slice(0, 10)}.csv"`,
    },
  });
}
