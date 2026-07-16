import type { Metadata } from "next";
import Link from "next/link";
import { AlarmClockOff, ArrowRight } from "lucide-react";
import { createClient } from "@/lib/supabase/server";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { KpiCard } from "@/components/kpi-card";
import { StatusBadge } from "@/components/reports/status-badge";
import { SlaBadge } from "@/components/reports/sla-badge";
import { TrendChart, BreakdownBars } from "@/components/admin/charts";

export const metadata: Metadata = { title: "Admin · Command Centre" };

export default async function AdminDashboardPage() {
  const supabase = await createClient();

  const [reportsRes, usersRes, breachedRes, recentRes] = await Promise.all([
    supabase
      .from("reports")
      .select("status, city_corporation, category, created_at")
      .order("created_at", { ascending: false })
      .limit(2000),
    supabase.from("profiles").select("id", { count: "exact", head: true }),
    supabase
      .from("reports")
      .select("id, title, city_corporation, status, sla_due_at")
      .lt("sla_due_at", new Date().toISOString())
      .in("status", ["pending", "in_progress"])
      .eq("is_approved", true)
      .order("sla_due_at", { ascending: true })
      .limit(8),
    supabase
      .from("reports")
      .select(
        "id, title, city_corporation, status, is_approved, created_at, profiles!reports_user_id_fkey(display_name)"
      )
      .order("created_at", { ascending: false })
      .limit(8),
  ]);

  const rows = reportsRes.data ?? [];
  const total = rows.length;
  const pending = rows.filter((r) => r.status === "pending").length;
  const inProgress = rows.filter((r) => r.status === "in_progress").length;
  const resolved = rows.filter((r) => r.status === "resolved").length;

  // Weekly trend: last 8 ISO weeks
  const weeks: { week: string; count: number }[] = [];
  for (let i = 7; i >= 0; i--) {
    const start = new Date();
    start.setDate(start.getDate() - start.getDay() - i * 7 + 1);
    start.setHours(0, 0, 0, 0);
    const end = new Date(start);
    end.setDate(end.getDate() + 7);
    weeks.push({
      week: start.toLocaleDateString("en-GB", { day: "numeric", month: "short" }),
      count: rows.filter((r) => {
        const t = new Date(r.created_at);
        return t >= start && t < end;
      }).length,
    });
  }

  const groupCount = (key: "city_corporation" | "category") => {
    const map = new Map<string, number>();
    for (const r of rows) map.set(r[key], (map.get(r[key]) ?? 0) + 1);
    return [...map.entries()]
      .map(([name, count]) => ({ name, count }))
      .sort((a, b) => b.count - a.count)
      .slice(0, 8);
  };

  return (
    <div className="mx-auto max-w-6xl space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold">Command Centre</h1>
          <p className="text-muted-foreground text-sm">
            Here&apos;s what&apos;s happening at a glance.
          </p>
        </div>
        <Button
          className="bg-brand-gradient border-0 text-white hover:opacity-90"
          asChild
        >
          <Link href="/admin/reports">
            Review reports <ArrowRight className="size-4" />
          </Link>
        </Button>
      </div>

      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <KpiCard value={total} label="Total Reports" variant="brand" />
        <KpiCard value={pending} label="Pending" variant="amber" />
        <KpiCard value={inProgress} label="In Progress" variant="blue" />
        <KpiCard value={resolved} label="Resolved" variant="green" />
        <KpiCard value={usersRes.count ?? 0} label="Users" variant="purple" />
      </div>

      <Card className="border-status-breach/30">
        <CardHeader className="flex-row items-center justify-between">
          <CardTitle className="text-status-breach flex items-center gap-2">
            <AlarmClockOff className="size-5" aria-hidden />
            SLA breaches
          </CardTitle>
          <span className="text-muted-foreground text-sm">
            {breachedRes.data?.length ?? 0} overdue
          </span>
        </CardHeader>
        <CardContent>
          {!breachedRes.data?.length ? (
            <p className="text-muted-foreground py-4 text-center text-sm">
              No overdue reports — the SLA clock is being respected. 🎉
            </p>
          ) : (
            <ul className="divide-y">
              {breachedRes.data.map((r) => (
                <li
                  key={r.id}
                  className="flex flex-wrap items-center justify-between gap-2 py-2.5"
                >
                  <div className="min-w-0">
                    <Link
                      href={`/reports/${r.id}`}
                      className="hover:text-primary block truncate text-sm font-medium transition-colors"
                    >
                      {r.title}
                    </Link>
                    <span className="text-muted-foreground text-xs">
                      {r.city_corporation}
                    </span>
                  </div>
                  <div className="flex items-center gap-2">
                    <StatusBadge status={r.status} />
                    <SlaBadge slaDueAt={r.sla_due_at} status={r.status} />
                  </div>
                </li>
              ))}
            </ul>
          )}
        </CardContent>
      </Card>

      <div className="grid gap-4 lg:grid-cols-2">
        <Card className="lg:col-span-2">
          <CardHeader>
            <CardTitle>Weekly submissions</CardTitle>
          </CardHeader>
          <CardContent>
            <TrendChart data={weeks} />
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle>Reports by city</CardTitle>
          </CardHeader>
          <CardContent>
            <BreakdownBars data={groupCount("city_corporation")} />
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle>Reports by category</CardTitle>
          </CardHeader>
          <CardContent>
            <BreakdownBars data={groupCount("category")} />
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader className="flex-row items-center justify-between">
          <CardTitle>Recent reports</CardTitle>
          <Button variant="ghost" size="sm" asChild>
            <Link href="/admin/reports">
              View all <ArrowRight className="size-4" />
            </Link>
          </Button>
        </CardHeader>
        <CardContent>
          <ul className="divide-y">
            {(recentRes.data ?? []).map((r) => (
              <li
                key={r.id}
                className="flex flex-wrap items-center justify-between gap-2 py-2.5"
              >
                <div className="min-w-0">
                  <Link
                    href={`/reports/${r.id}`}
                    className="hover:text-primary block truncate text-sm font-medium transition-colors"
                  >
                    {r.title}
                  </Link>
                  <span className="text-muted-foreground text-xs">
                    {r.profiles?.display_name ?? "Citizen"} ·{" "}
                    {r.city_corporation} ·{" "}
                    {new Date(r.created_at).toLocaleDateString("en-GB", {
                      day: "numeric",
                      month: "short",
                    })}
                  </span>
                </div>
                <div className="flex items-center gap-2">
                  {!r.is_approved && r.status !== "rejected" ? (
                    <span className="text-status-pending text-xs font-medium">
                      Needs review
                    </span>
                  ) : null}
                  <StatusBadge status={r.status} />
                </div>
              </li>
            ))}
          </ul>
        </CardContent>
      </Card>
    </div>
  );
}
