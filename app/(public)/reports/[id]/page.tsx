import type { Metadata } from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import {
  ArrowLeft,
  BadgeCheck,
  Building2,
  CalendarDays,
  Globe,
  History,
  Landmark,
  MapPin,
  Phone,
  ShieldCheck,
  Tag,
  User,
} from "lucide-react";
import { createClient } from "@/lib/supabase/server";
import { reportMediaUrl } from "@/lib/storage";
import { STATUS_LABELS, type Status } from "@/lib/constants";
import {
  DEPT_LABELS,
  NATIONAL_HELPLINE,
  getAuthority,
  resolveAuthority,
} from "@/lib/routing";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { StatusBadge } from "@/components/reports/status-badge";
import { StatusTracker } from "@/components/reports/status-tracker";
import { SlaBadge } from "@/components/reports/sla-badge";
import { ReportMap } from "@/components/map/report-map";
import { EngagementBar } from "@/components/reports/engagement-bar";
import { Comments, type CommentData } from "@/components/reports/comments";
import { ResolutionPanel } from "@/components/reports/resolution-panel";

type Params = Promise<{ id: string }>;

export async function generateMetadata({
  params,
}: {
  params: Params;
}): Promise<Metadata> {
  const { id } = await params;
  const supabase = await createClient();
  const { data } = await supabase
    .from("reports")
    .select("title")
    .eq("id", Number(id))
    .maybeSingle();
  return { title: data?.title ?? "Report" };
}

export default async function ReportDetailPage({
  params,
}: {
  params: Params;
}) {
  const { id } = await params;
  const reportId = Number(id);
  if (!Number.isInteger(reportId) || reportId <= 0) notFound();

  const supabase = await createClient();
  const [{ data: report }, userRes] = await Promise.all([
    supabase
      .from("reports")
      .select(
        `*,
         profiles!reports_user_id_fkey ( display_name ),
         report_media ( id, storage_path ),
         report_status_logs ( id, from_status, to_status, note, created_at )`
      )
      .eq("id", reportId)
      .maybeSingle(),
    supabase.auth.getUser(),
  ]);

  // RLS hides unapproved reports from non-owners — treat as not found.
  if (!report) notFound();

  const user = userRes.data.user;

  const [
    commentsRes,
    endorsedRes,
    corroboratedRes,
    bookmarkedRes,
    viewerProfileRes,
    trustedRes,
  ] = await Promise.all([
      supabase
        .from("report_comments")
        .select(
          "id, body, parent_id, created_at, user_id, profiles ( display_name )"
        )
        .eq("report_id", reportId)
        .order("created_at", { ascending: true }),
      user
        ? supabase
            .from("report_endorsements")
            .select("report_id")
            .eq("report_id", reportId)
            .eq("user_id", user.id)
            .maybeSingle()
        : Promise.resolve({ data: null }),
      user
        ? supabase
            .from("report_corroborations")
            .select("report_id")
            .eq("report_id", reportId)
            .eq("user_id", user.id)
            .maybeSingle()
        : Promise.resolve({ data: null }),
      user
        ? supabase
            .from("report_bookmarks")
            .select("report_id")
            .eq("report_id", reportId)
            .eq("user_id", user.id)
            .maybeSingle()
        : Promise.resolve({ data: null }),
      user
        ? supabase
            .from("profiles")
            .select("role")
            .eq("id", user.id)
            .single()
        : Promise.resolve({ data: null }),
      report.user_id
        ? supabase.rpc("is_trusted_reporter", { p_user_id: report.user_id })
        : Promise.resolve({ data: false }),
    ]);

  const comments: CommentData[] = (commentsRes.data ?? []).map((c) => ({
    id: c.id,
    body: c.body,
    parent_id: c.parent_id,
    created_at: c.created_at,
    user_id: c.user_id,
    authorName: c.profiles?.display_name ?? "Citizen",
  }));

  // Count the view (best-effort, approved reports only).
  if (report.is_approved) {
    supabase
      .rpc("increment_view_count", { p_report_id: reportId })
      .then(() => {});
  }

  const created = new Date(report.created_at).toLocaleString("en-GB", {
    day: "numeric",
    month: "long",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });

  const logs = [...(report.report_status_logs ?? [])].sort(
    (a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
  );

  // Responsible authority: prefer the key pinned at creation; fall back to a
  // fresh resolve so older reports (before routing) still show a destination.
  const authority =
    getAuthority(report.routed_authority_key) ??
    resolveAuthority(report.category, report.city_corporation);

  return (
    <div className="mx-auto w-full max-w-4xl space-y-6 px-4 py-8 sm:px-6">
      <Button variant="ghost" size="sm" asChild>
        <Link href="/reports">
          <ArrowLeft className="size-4" aria-hidden /> All reports
        </Link>
      </Button>

      <div className="space-y-3">
        <div className="flex flex-wrap items-center gap-2">
          <StatusBadge status={report.status} />
          <SlaBadge slaDueAt={report.sla_due_at} status={report.status} />
          {report.status === "resolved" &&
          report.resolution_state === "pending_confirmation" ? (
            <Badge
              variant="outline"
              className="border-status-pending/40 text-status-pending"
            >
              Awaiting citizen confirmation
            </Badge>
          ) : null}
          {report.resolution_state === "confirmed" ? (
            <Badge
              variant="outline"
              className="border-status-resolved/40 text-status-resolved"
            >
              ✓ Confirmed fixed by reporter
            </Badge>
          ) : null}
          {report.resolution_state === "disputed" ? (
            <Badge
              variant="outline"
              className="border-status-breach/40 text-status-breach"
            >
              Reopened — citizen disputed the fix
            </Badge>
          ) : null}
          {!report.is_approved ? (
            <Badge variant="outline" className="text-muted-foreground">
              Awaiting moderation — only you can see this
            </Badge>
          ) : null}
        </div>
        <h1 className="text-3xl font-extrabold tracking-tight text-balance">
          {report.title}
        </h1>
        <ul className="text-muted-foreground flex flex-wrap items-center gap-x-4 gap-y-1 text-sm">
          <li className="flex items-center gap-1.5">
            <Tag className="size-4" aria-hidden />
            {report.category}
          </li>
          <li className="flex items-center gap-1.5">
            <Building2 className="size-4" aria-hidden />
            {report.city_corporation}
          </li>
          <li className="flex items-center gap-1.5">
            <User className="size-4" aria-hidden />
            {report.profiles?.display_name ?? "Citizen"}
            {trustedRes.data === true ? (
              <span
                className="text-primary inline-flex items-center gap-1 text-xs font-semibold"
                title="At least 3 approved reports and no rejections — new reports publish without moderation"
              >
                <BadgeCheck className="size-4" aria-hidden />
                Trusted reporter
              </span>
            ) : null}
          </li>
          <li className="flex items-center gap-1.5">
            <CalendarDays className="size-4" aria-hidden />
            {created}
          </li>
        </ul>
      </div>

      <Card>
        <CardContent className="pt-6">
          <StatusTracker status={report.status} className="mx-auto max-w-md" />
        </CardContent>
      </Card>

      <Card className="border-primary/25 bg-primary/[0.04]">
        <CardContent className="flex flex-col gap-3 pt-6 sm:flex-row sm:items-center sm:justify-between">
          <div className="flex items-start gap-3">
            <span className="bg-brand-gradient flex size-10 shrink-0 items-center justify-center rounded-lg text-white">
              <Landmark className="size-5" aria-hidden />
            </span>
            <div>
              <p className="text-muted-foreground text-xs font-medium tracking-wide uppercase">
                Routed to responsible authority
              </p>
              <p className="font-semibold">{authority.name}</p>
              <p className="text-muted-foreground font-bengali text-sm">
                {authority.nameBn}
              </p>
              <p className="text-muted-foreground mt-0.5 text-xs">
                {DEPT_LABELS[authority.dept]} · {authority.jurisdiction}
              </p>
            </div>
          </div>
          <div className="flex flex-wrap gap-2 sm:flex-col sm:items-stretch">
            {authority.hotline ? (
              <Button variant="outline" size="sm" asChild>
                <a href={`tel:${authority.hotline}`}>
                  <Phone className="size-4" aria-hidden /> {authority.hotline}
                </a>
              </Button>
            ) : (
              <Button variant="outline" size="sm" asChild>
                <a href={`tel:${NATIONAL_HELPLINE}`}>
                  <Phone className="size-4" aria-hidden /> Helpline{" "}
                  {NATIONAL_HELPLINE}
                </a>
              </Button>
            )}
            {authority.website ? (
              <Button variant="ghost" size="sm" asChild>
                <a
                  href={authority.website}
                  target="_blank"
                  rel="noreferrer noopener"
                >
                  <Globe className="size-4" aria-hidden /> Website
                </a>
              </Button>
            ) : null}
          </div>
        </CardContent>
      </Card>

      {user?.id === report.user_id &&
      report.resolution_state === "pending_confirmation" ? (
        <ResolutionPanel reportId={report.id} />
      ) : null}

      {report.resolution_state === "disputed" && report.dispute_reason ? (
        <div className="border-status-breach/25 bg-status-breach/5 rounded-lg border p-4">
          <p className="text-status-breach text-sm font-medium">
            Reporter disputed the fix
          </p>
          <p className="text-muted-foreground mt-1 text-sm">
            “{report.dispute_reason}”
          </p>
        </div>
      ) : null}

      {report.is_approved ? (
        <EngagementBar
          reportId={report.id}
          title={report.title}
          endorseCount={report.endorse_count}
          corroborationCount={report.corroboration_count}
          commentCount={comments.length}
          endorsed={!!endorsedRes.data}
          corroborated={!!corroboratedRes.data}
          bookmarked={!!bookmarkedRes.data}
          isAuthed={!!user}
          isOwner={user?.id === report.user_id}
        />
      ) : null}

      {report.report_media.length > 0 ? (
        <div
          className={
            report.report_media.length === 1
              ? "grid grid-cols-1"
              : "grid grid-cols-2 gap-3 sm:grid-cols-3"
          }
        >
          {report.report_media.map((m, i) => (
            <a
              key={m.id}
              href={reportMediaUrl(m.storage_path)}
              target="_blank"
              rel="noreferrer"
              className="group overflow-hidden rounded-xl border"
            >
              {/* eslint-disable-next-line @next/next/no-img-element */}
              <img
                src={reportMediaUrl(m.storage_path)}
                alt={`Evidence photo ${i + 1} for ${report.title}`}
                loading={i > 0 ? "lazy" : undefined}
                className="aspect-[4/3] w-full object-cover transition-transform duration-300 group-hover:scale-105"
              />
            </a>
          ))}
        </div>
      ) : null}

      <Card>
        <CardHeader>
          <CardTitle className="text-lg">Description</CardTitle>
        </CardHeader>
        <CardContent>
          <p className="text-sm leading-relaxed whitespace-pre-wrap">
            {report.description}
          </p>
        </CardContent>
      </Card>

      {report.admin_note ? (
        <Card className="border-status-progress/30 bg-status-progress/5">
          <CardHeader>
            <CardTitle className="flex items-center gap-2 text-lg">
              <ShieldCheck className="text-status-progress size-5" aria-hidden />
              Official note
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-sm leading-relaxed whitespace-pre-wrap">
              {report.admin_note}
            </p>
          </CardContent>
        </Card>
      ) : null}

      {report.latitude != null && report.longitude != null ? (
        <Card className="overflow-hidden py-0">
          <CardHeader className="pt-6">
            <CardTitle className="flex items-center gap-2 text-lg">
              <MapPin className="text-primary size-5" aria-hidden />
              Location
            </CardTitle>
            {report.location_text ? (
              <p className="text-muted-foreground text-sm">
                {report.location_text}
              </p>
            ) : null}
          </CardHeader>
          <CardContent className="px-0 pb-0">
            <ReportMap
              lat={report.latitude}
              lng={report.longitude}
              label={report.title}
              className="h-72 w-full !rounded-none"
            />
          </CardContent>
        </Card>
      ) : report.location_text ? (
        <Card>
          <CardContent className="text-muted-foreground flex items-center gap-2 pt-6 text-sm">
            <MapPin className="size-4" aria-hidden />
            {report.location_text}
          </CardContent>
        </Card>
      ) : null}

      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2 text-lg">
            <History className="size-5" aria-hidden />
            Status timeline
          </CardTitle>
        </CardHeader>
        <CardContent>
          <ol className="relative space-y-5 border-l pl-5">
            {logs.map((log) => (
              <li key={log.id} className="relative">
                <span
                  className="bg-brand-gradient absolute -left-[26.5px] mt-1 size-3 rounded-full"
                  aria-hidden
                />
                <p className="text-sm font-medium">
                  {log.from_status
                    ? `${STATUS_LABELS[log.from_status as Status] ?? log.from_status} → `
                    : ""}
                  {STATUS_LABELS[log.to_status as Status] ?? log.to_status}
                </p>
                {log.note ? (
                  <p className="text-muted-foreground text-sm">{log.note}</p>
                ) : null}
                <p className="text-muted-foreground mt-0.5 text-xs">
                  {new Date(log.created_at).toLocaleString("en-GB", {
                    day: "numeric",
                    month: "short",
                    year: "numeric",
                    hour: "2-digit",
                    minute: "2-digit",
                  })}
                </p>
              </li>
            ))}
            <li className="relative">
              <span
                className="bg-muted-foreground/40 absolute -left-[26.5px] mt-1 size-3 rounded-full"
                aria-hidden
              />
              <p className="text-sm font-medium">Report submitted</p>
              <p className="text-muted-foreground mt-0.5 text-xs">{created}</p>
            </li>
          </ol>
        </CardContent>
      </Card>

      {report.is_approved ? (
        <Card>
          <CardContent className="pt-6">
            <Comments
              reportId={report.id}
              comments={comments}
              currentUserId={user?.id ?? null}
              isAdmin={viewerProfileRes.data?.role === "admin"}
            />
          </CardContent>
        </Card>
      ) : null}
    </div>
  );
}
