import type { Metadata } from "next";
import Link from "next/link";
import { redirect } from "next/navigation";
import {
  ArrowRight,
  BadgeCheck,
  BookOpenCheck,
  Building2 as BuildingIcon,
  CheckCircle2,
  Eye,
  FilePlus2,
  FileText,
  Landmark,
  ListChecks,
  Map as MapIcon,
  PhoneCall,
  Scale,
  Siren,
  ThumbsUp,
} from "lucide-react";
import { createClient } from "@/lib/supabase/server";
import { isEscalationEligible } from "@/lib/sla";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { KpiCard } from "@/components/kpi-card";
import { StatusBadge } from "@/components/reports/status-badge";
import { Progress } from "@/components/ui/progress";

export const metadata: Metadata = { title: "Civic Desk" };

const TRUST_THRESHOLD = 3;

export default async function DashboardPage() {
  const supabase = await createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();
  if (!user) redirect("/login");

  const [
    { data: profile },
    { data: reports },
    { data: trusted },
    { data: escalations },
  ] = await Promise.all([
    supabase.from("profiles").select("display_name").eq("id", user.id).single(),
    supabase
      .from("reports")
      .select(
        "id, title, city_corporation, status, is_approved, sla_due_at, resolution_state, endorse_count, corroboration_count, created_at"
      )
      .eq("user_id", user.id)
      .order("created_at", { ascending: false }),
    supabase.rpc("is_trusted_reporter", { p_user_id: user.id }),
    supabase
      .from("report_escalations")
      .select("id, report_id, channel, outcome, reference_no")
      .eq("user_id", user.id),
  ]);

  const all = reports ?? [];
  const total = all.length;
  const approved = all.filter((r) => r.is_approved).length;
  const pending = all.filter((r) => r.status === "pending").length;
  const resolved = all.filter((r) => r.status === "resolved").length;
  const endorsements = all.reduce((s, r) => s + r.endorse_count, 0);
  const corroborations = all.reduce((s, r) => s + r.corroboration_count, 0);
  const isTrusted = trusted === true;

  // ---- Action needed: the citizen's civic to-do list ----
  const confirmNeeded = all.filter(
    (r) => r.resolution_state === "pending_confirmation"
  );
  const escalatedReportIds = new Set((escalations ?? []).map((e) => e.report_id));
  const escalatable = all.filter(
    (r) =>
      r.is_approved &&
      isEscalationEligible(r.status, r.sla_due_at, r.resolution_state) &&
      !escalatedReportIds.has(r.id)
  );
  const draftedEscalations = (escalations ?? []).filter(
    (e) => e.outcome === "drafted"
  );
  const actionCount =
    confirmNeeded.length + escalatable.length + draftedEscalations.length;

  const services = [
    {
      href: "/guides",
      icon: BookOpenCheck,
      title: "Service Guides",
      body: "Passport, NID, land, tax — step by step",
    },
    {
      href: "/rti",
      icon: Scale,
      title: "RTI Wizard",
      body: "Demand information by law",
    },
    {
      href: "/authorities",
      icon: Landmark,
      title: "Authority Directory",
      body: "Who owns which problem",
    },
    {
      href: "/services",
      icon: PhoneCall,
      title: "Emergency & Nearby",
      body: "Hotlines + services around you",
    },
    {
      href: "/map",
      icon: MapIcon,
      title: "City Map",
      body: "Every public report, mapped",
    },
    {
      href: "/rights",
      icon: BuildingIcon,
      title: "Know Your Rights",
      body: "The escalation ladder",
    },
  ];

  return (
    <div className="mx-auto w-full max-w-7xl space-y-6 px-4 py-8 sm:px-6">
      {/* Welcome + trust standing */}
      <div className="grid gap-4 lg:grid-cols-[1.4fr_1fr]">
        <Card>
          <CardHeader>
            <CardTitle className="text-2xl">
              Welcome,{" "}
              <span className="text-brand-gradient">
                {profile?.display_name ?? "Citizen"}
              </span>
            </CardTitle>
            <p className="text-muted-foreground text-sm">
              Your civic desk — reports, services and follow-ups in one place.
            </p>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
              <KpiCard value={total} label="Reports" variant="brand" />
              <KpiCard value={pending} label="Pending" variant="amber" />
              <KpiCard value={resolved} label="Resolved" variant="green" />
              <div className="bg-card rounded-xl border p-4">
                <p className="text-primary flex items-center gap-3 text-2xl font-bold tabular-nums">
                  <span className="flex items-center gap-1">
                    <ThumbsUp className="size-4.5" aria-hidden />
                    {endorsements}
                  </span>
                  <span className="flex items-center gap-1">
                    <Eye className="size-4.5" aria-hidden />
                    {corroborations}
                  </span>
                </p>
                <p className="text-muted-foreground mt-1 text-xs font-medium">
                  Community backing
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card className={isTrusted ? "border-primary/30 bg-primary/[0.03]" : ""}>
          <CardHeader>
            <CardTitle className="flex items-center gap-2 text-lg">
              <BadgeCheck
                className={isTrusted ? "text-primary size-5" : "text-muted-foreground size-5"}
                aria-hidden
              />
              Reporter standing
            </CardTitle>
          </CardHeader>
          <CardContent>
            {isTrusted ? (
              <>
                <p className="text-primary font-semibold">Trusted reporter</p>
                <p className="text-muted-foreground mt-1 text-sm leading-relaxed">
                  Your reports publish instantly — no moderation queue — with
                  the response clock started. Keep the record honest to keep
                  the badge.
                </p>
              </>
            ) : (
              <>
                <p className="font-semibold">
                  {approved} of {TRUST_THRESHOLD} approved reports
                </p>
                <Progress
                  value={Math.min(100, (approved / TRUST_THRESHOLD) * 100)}
                  className="mt-2"
                  aria-label="Progress toward trusted reporter"
                />
                <p className="text-muted-foreground mt-2 text-sm leading-relaxed">
                  Reach {TRUST_THRESHOLD} approved reports (with no rejections)
                  and new reports skip moderation entirely.
                </p>
              </>
            )}
          </CardContent>
        </Card>
      </div>

      {/* Action needed */}
      {actionCount > 0 ? (
        <Card className="border-status-pending/40">
          <CardHeader>
            <CardTitle className="flex items-center gap-2 text-lg">
              <ListChecks className="text-status-pending size-5" aria-hidden />
              Action needed ({actionCount})
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-2.5">
            {confirmNeeded.map((r) => (
              <Link
                key={`c${r.id}`}
                href={`/reports/${r.id}`}
                className="border-status-resolved/30 bg-status-resolved/5 hover:bg-status-resolved/10 flex items-center justify-between gap-3 rounded-lg border p-3 transition-colors"
              >
                <span className="flex min-w-0 items-center gap-2.5 text-sm">
                  <CheckCircle2 className="text-status-resolved size-4.5 shrink-0" aria-hidden />
                  <span className="truncate">
                    <span className="font-medium">Confirm the fix:</span>{" "}
                    {r.title}
                  </span>
                </span>
                <ArrowRight className="text-muted-foreground size-4 shrink-0" aria-hidden />
              </Link>
            ))}
            {escalatable.map((r) => (
              <Link
                key={`e${r.id}`}
                href={`/reports/${r.id}/escalate`}
                className="border-status-breach/30 bg-status-breach/5 hover:bg-status-breach/10 flex items-center justify-between gap-3 rounded-lg border p-3 transition-colors"
              >
                <span className="flex min-w-0 items-center gap-2.5 text-sm">
                  <Siren className="text-status-breach size-4.5 shrink-0" aria-hidden />
                  <span className="truncate">
                    <span className="font-medium">Overdue — escalate:</span>{" "}
                    {r.title}
                  </span>
                </span>
                <ArrowRight className="text-muted-foreground size-4 shrink-0" aria-hidden />
              </Link>
            ))}
            {draftedEscalations.map((e) => (
              <Link
                key={`d${e.id}`}
                href={`/reports/${e.report_id}/escalate`}
                className="border-status-pending/40 bg-status-pending/5 hover:bg-status-pending/10 flex items-center justify-between gap-3 rounded-lg border p-3 transition-colors"
              >
                <span className="flex min-w-0 items-center gap-2.5 text-sm">
                  <FileText className="text-status-pending size-4.5 shrink-0" aria-hidden />
                  <span className="truncate">
                    <span className="font-medium">
                      Escalation drafted, not filed
                    </span>{" "}
                    — finish filing it and log the reference number
                  </span>
                </span>
                <ArrowRight className="text-muted-foreground size-4 shrink-0" aria-hidden />
              </Link>
            ))}
          </CardContent>
        </Card>
      ) : null}

      {/* Services launcher */}
      <div>
        <div className="mb-4 flex flex-wrap items-end justify-between gap-3">
          <div>
            <h2 className="text-xl font-semibold">Civic services</h2>
            <p className="text-muted-foreground text-sm">
              More than reporting — the everyday government toolkit.
            </p>
          </div>
          <Button
            className="bg-brand-gradient border-0 text-white hover:opacity-90"
            asChild
          >
            <Link href="/submit">
              <FilePlus2 className="size-4" /> New Report
            </Link>
          </Button>
        </div>
        <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
          {services.map(({ href, icon: Icon, title, body }) => (
            <Link
              key={href}
              href={href}
              className="group bg-card card-lift flex flex-col items-center gap-2 rounded-xl border p-5 text-center"
            >
              <span className="bg-primary/10 text-primary group-hover:bg-primary group-hover:text-primary-foreground flex size-11 items-center justify-center rounded-lg transition-colors">
                <Icon className="size-5" aria-hidden />
              </span>
              <span className="text-sm font-semibold">{title}</span>
              <span className="text-muted-foreground text-xs leading-snug">
                {body}
              </span>
            </Link>
          ))}
        </div>
      </div>

      {/* Recent reports */}
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
              {all.slice(0, 6).map((r) => (
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
