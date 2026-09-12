import { redirect } from "next/navigation";
import Link from "next/link";
import { MapPinned, Inbox } from "lucide-react";
import { createClient } from "@/lib/supabase/server";
import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { OfficerReportRow } from "./officer-report-row";

export const metadata = { title: "Ward queue" };

/**
 * What an officer sees: the reports in their own wards, and nothing else.
 *
 * The filtering is the database's. An officer's RLS policy matches reports
 * inside the territories they are assigned, including everything nested beneath
 * them, so this page asks for reports in the ordinary way and gets back exactly
 * their patch. Unapproved reports included — seeing a problem before it is
 * public is most of the job.
 */
export default async function OfficerQueuePage() {
  const supabase = await createClient();

  const {
    data: { user },
  } = await supabase.auth.getUser();
  if (!user) redirect("/login");

  const { data: profile } = await supabase
    .from("profiles")
    .select("display_name, role")
    .eq("id", user.id)
    .single();

  if (profile?.role !== "officer" && profile?.role !== "admin") {
    redirect("/dashboard");
  }

  // Fetched separately rather than as an embedded join: the generated types
  // carry no relationship metadata for these tables, and two small queries are
  // clearer than fighting that.
  const { data: assignments } = await supabase
    .from("officer_territories")
    .select("territory_id, designation")
    .eq("user_id", user.id);

  const territoryIds = (assignments ?? []).map((row) => row.territory_id);

  const { data: territories } = territoryIds.length
    ? await supabase
        .from("territories")
        .select("id, name")
        .in("id", territoryIds)
    : { data: [] };

  const territoryName = new Map(
    (territories ?? []).map((t) => [t.id, t.name] as const)
  );

  const patch = (assignments ?? []).map((row) => ({
    id: row.territory_id,
    name: territoryName.get(row.territory_id) ?? "Unknown area",
    designation: row.designation,
  }));

  // Filtered explicitly rather than leaning on RLS. The officer policy widens
  // what they may SEE, but the base policy still shows every approved report to
  // everyone, so an unfiltered query would pull in public reports from other
  // wards and make the heading above a lie. RLS remains the guard on what they
  // may CHANGE.
  let query = supabase
    .from("reports")
    .select(
      `id, title, category, status, priority, is_approved, sla_due_at,
       escalation_level, created_at, admin_note, assigned_to, territory_id,
       profiles!reports_user_id_fkey ( display_name )`
    )
    .in("status", ["pending", "in_progress"])
    .order("escalation_level", { ascending: false })
    .order("sla_due_at", { ascending: true, nullsFirst: false })
    .limit(50);

  if (territoryIds.length > 0) {
    query = query.in("territory_id", territoryIds);
  } else if (profile?.role === "officer") {
    // Assigned nothing yet: show nothing rather than the whole city.
    query = query.eq("territory_id", -1);
  }

  const { data: reports } = await query;

  const rows = (reports ?? []).map((r) => ({
    id: r.id,
    title: r.title,
    category: r.category,
    status: r.status,
    priority: r.priority,
    isApproved: r.is_approved,
    slaDueAt: r.sla_due_at,
    escalationLevel: r.escalation_level ?? 0,
    adminNote: r.admin_note,
    mine: r.assigned_to === user.id,
    ward: r.territory_id ? (territoryName.get(r.territory_id) ?? null) : null,
    reporter: r.profiles?.display_name ?? "Citizen",
  }));

  const overdue = rows.filter(
    (r) => r.slaDueAt && new Date(r.slaDueAt) < new Date()
  ).length;

  return (
    <div className="mx-auto max-w-5xl space-y-5">
      <div className="flex flex-wrap items-end justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold">Ward queue</h1>
          <p className="text-muted-foreground mt-1 text-sm">
            Open reports in your area, most urgent first. Reports outside your
            wards are not shown.
          </p>
        </div>
        {overdue > 0 ? (
          <Badge
            variant="outline"
            className="border-status-breach/40 text-status-breach"
          >
            {overdue} past deadline
          </Badge>
        ) : null}
      </div>

      {/* The patch itself, so an officer can see what they are responsible for. */}
      <Card>
        <CardContent className="flex flex-wrap items-center gap-x-4 gap-y-2 py-4 text-sm">
          <span className="text-muted-foreground flex items-center gap-1.5">
            <MapPinned className="size-4" aria-hidden />
            Your area
          </span>
          {patch.length === 0 ? (
            <span className="text-muted-foreground">
              No wards assigned yet — an administrator sets these.
            </span>
          ) : (
            patch.map((row) => (
              <span key={row.id} className="flex items-center gap-2">
                <Badge variant="outline">{row.name}</Badge>
                {row.designation ? (
                  <span className="text-muted-foreground text-xs">
                    {row.designation}
                  </span>
                ) : null}
              </span>
            ))
          )}
        </CardContent>
      </Card>

      {rows.length === 0 ? (
        <Card>
          <CardContent className="flex flex-col items-center gap-3 py-14 text-center">
            <span className="bg-muted text-muted-foreground flex size-11 items-center justify-center rounded-full">
              <Inbox className="size-5" aria-hidden />
            </span>
            <div>
              <p className="font-medium">Nothing open in your wards</p>
              <p className="text-muted-foreground mt-1 text-sm">
                New reports appear here as citizens file them.
              </p>
            </div>
            <Link
              href="/reports"
              className="text-primary text-sm font-medium hover:underline"
            >
              Browse all public reports
            </Link>
          </CardContent>
        </Card>
      ) : (
        <div className="space-y-3">
          {rows.map((row) => (
            <OfficerReportRow key={row.id} {...row} />
          ))}
        </div>
      )}
    </div>
  );
}
