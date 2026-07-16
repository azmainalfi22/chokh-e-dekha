import type { Metadata } from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import {
  ArrowLeft,
  Building2,
  CalendarDays,
  History,
  MapPin,
  ShieldCheck,
  Tag,
  User,
} from "lucide-react";
import { createClient } from "@/lib/supabase/server";
import { reportMediaUrl } from "@/lib/storage";
import { STATUS_LABELS, type Status } from "@/lib/constants";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Separator } from "@/components/ui/separator";
import { StatusBadge } from "@/components/reports/status-badge";
import { StatusTracker } from "@/components/reports/status-tracker";
import { SlaBadge } from "@/components/reports/sla-badge";
import { ReportMap } from "@/components/map/report-map";

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
  const { data: report } = await supabase
    .from("reports")
    .select(
      `*,
       profiles!reports_user_id_fkey ( display_name ),
       report_media ( id, storage_path ),
       report_status_logs ( id, from_status, to_status, note, created_at )`
    )
    .eq("id", reportId)
    .maybeSingle();

  // RLS hides unapproved reports from non-owners — treat as not found.
  if (!report) notFound();

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

      <Separator />
      <p className="text-muted-foreground text-center text-xs">
        Endorsements, comments and sharing arrive in the next build phase.
      </p>
    </div>
  );
}
