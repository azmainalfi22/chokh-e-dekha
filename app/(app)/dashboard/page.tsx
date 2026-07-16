import type { Metadata } from "next";
import Link from "next/link";
import { redirect } from "next/navigation";
import {
  ArrowRight,
  FilePlus2,
  FileText,
  ListChecks,
  UserPen,
} from "lucide-react";
import { createClient } from "@/lib/supabase/server";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { KpiCard } from "@/components/kpi-card";
import { StatusBadge } from "@/components/reports/status-badge";

export const metadata: Metadata = { title: "Dashboard" };

export default async function DashboardPage() {
  const supabase = await createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();
  if (!user) redirect("/login");

  const [{ data: profile }, { data: reports }, totalQ, pendingQ, resolvedQ] =
    await Promise.all([
      supabase
        .from("profiles")
        .select("display_name")
        .eq("id", user.id)
        .single(),
      supabase
        .from("reports")
        .select("id, title, city_corporation, status, created_at")
        .eq("user_id", user.id)
        .order("created_at", { ascending: false })
        .limit(6),
      supabase
        .from("reports")
        .select("id", { count: "exact", head: true })
        .eq("user_id", user.id),
      supabase
        .from("reports")
        .select("id", { count: "exact", head: true })
        .eq("user_id", user.id)
        .eq("status", "pending"),
      supabase
        .from("reports")
        .select("id", { count: "exact", head: true })
        .eq("user_id", user.id)
        .eq("status", "resolved"),
    ]);

  const all = reports ?? [];
  const total = totalQ.count ?? 0;
  const pending = pendingQ.count ?? 0;
  const resolved = resolvedQ.count ?? 0;

  const quickActions = [
    {
      href: "/submit",
      icon: FilePlus2,
      title: "Submit Report",
      body: "Let the city know",
    },
    {
      href: "/my-reports",
      icon: ListChecks,
      title: "My Reports",
      body: "Track your submissions",
    },
    {
      href: "/profile",
      icon: UserPen,
      title: "Edit Profile",
      body: "Manage your identity",
    },
  ];

  return (
    <div className="mx-auto w-full max-w-7xl space-y-6 px-4 py-8 sm:px-6">
      <Card>
        <CardHeader>
          <CardTitle className="text-2xl">
            👋 Welcome,{" "}
            <span className="text-brand-gradient">
              {profile?.display_name ?? "Citizen"}
            </span>
            !
          </CardTitle>
          <p className="text-muted-foreground text-sm">
            This is your command center for civic impact.
          </p>
        </CardHeader>
        <CardContent className="grid gap-4 sm:grid-cols-3">
          {quickActions.map(({ href, icon: Icon, title, body }) => (
            <Link
              key={href}
              href={href}
              className="group bg-card hover:border-primary/40 flex flex-col items-center gap-2 rounded-xl border p-6 text-center shadow-sm transition-all hover:shadow-md"
            >
              <span className="bg-brand-gradient flex size-11 items-center justify-center rounded-lg text-white transition-transform group-hover:scale-110">
                <Icon className="size-5" aria-hidden />
              </span>
              <span className="font-semibold">{title}</span>
              <span className="text-muted-foreground text-xs">{body}</span>
            </Link>
          ))}
        </CardContent>
      </Card>

      <div className="grid gap-4 sm:grid-cols-3">
        <KpiCard value={total} label="Total Reports" variant="brand" />
        <KpiCard value={pending} label="Pending" variant="amber" />
        <KpiCard value={resolved} label="Resolved" variant="green" />
      </div>

      <Card>
        <CardHeader className="flex-row items-center justify-between">
          <CardTitle>Recent Reports</CardTitle>
          <Button variant="ghost" size="sm" asChild>
            <Link href="/my-reports">
              View all <ArrowRight className="size-4" />
            </Link>
          </Button>
        </CardHeader>
        <CardContent>
          {all.length === 0 ? (
            <div className="flex flex-col items-center gap-3 py-10 text-center">
              <FileText className="text-muted-foreground size-10" aria-hidden />
              <p className="text-muted-foreground text-sm">
                You haven&apos;t submitted any reports yet.
              </p>
              <Button
                className="bg-brand-gradient border-0 text-white hover:opacity-90"
                asChild
              >
                <Link href="/submit">Submit your first report</Link>
              </Button>
            </div>
          ) : (
            <ul className="divide-y">
              {all.map((r) => (
                <li
                  key={r.id}
                  className="flex items-center justify-between gap-3 py-3"
                >
                  <div className="min-w-0">
                    <Link
                      href={`/reports/${r.id}`}
                      className="hover:text-primary block truncate font-medium transition-colors"
                    >
                      {r.title}
                    </Link>
                    <p className="text-muted-foreground text-xs">
                      {r.city_corporation} ·{" "}
                      {new Date(r.created_at).toLocaleDateString("en-GB", {
                        day: "numeric",
                        month: "short",
                        year: "numeric",
                      })}
                    </p>
                  </div>
                  <StatusBadge status={r.status} />
                </li>
              ))}
            </ul>
          )}
        </CardContent>
      </Card>
    </div>
  );
}
