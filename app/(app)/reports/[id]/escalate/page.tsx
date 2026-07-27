import type { Metadata } from "next";
import Link from "next/link";
import { notFound, redirect } from "next/navigation";
import { ArrowLeft, Siren } from "lucide-react";
import { createClient } from "@/lib/supabase/server";
import { getSlaState, isEscalationEligible } from "@/lib/sla";
import { resolveAuthority } from "@/lib/routing";
import { Button } from "@/components/ui/button";
import { EscalationWizard } from "./escalation-wizard";

export const metadata: Metadata = { title: "Escalate Report" };

type Params = Promise<{ id: string }>;

export default async function EscalatePage({ params }: { params: Params }) {
  const { id } = await params;
  const reportId = Number(id);
  if (!Number.isInteger(reportId) || reportId <= 0) notFound();

  const supabase = await createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();
  if (!user) redirect("/login");

  const { data: report } = await supabase
    .from("reports")
    .select(
      "id, user_id, title, category, city_corporation, status, is_approved, sla_due_at, resolution_state"
    )
    .eq("id", reportId)
    .maybeSingle();
  if (!report) notFound();
  // Only the reporter escalates, and only once the report is stuck.
  if (report.user_id !== user.id) redirect(`/reports/${reportId}`);
  if (
    !report.is_approved ||
    !isEscalationEligible(report.status, report.sla_due_at, report.resolution_state)
  ) {
    redirect(`/reports/${reportId}`);
  }

  const { data: escalations } = await supabase
    .from("report_escalations")
    .select("id, channel, language, complaint_body, reference_no, outcome, filed_at, created_at")
    .eq("report_id", reportId)
    .eq("user_id", user.id)
    .order("created_at", { ascending: true });

  const sla = getSlaState(report.sla_due_at, report.status);
  const authority = resolveAuthority(report.category, report.city_corporation);

  return (
    <div className="mx-auto w-full max-w-4xl space-y-6 px-4 py-8 sm:px-6">
      <Button variant="ghost" size="sm" asChild>
        <Link href={`/reports/${reportId}`}>
          <ArrowLeft className="size-4" aria-hidden /> Back to report
        </Link>
      </Button>

      <div>
        <p className="text-status-breach flex items-center gap-2 text-sm font-semibold tracking-wider uppercase">
          <Siren className="size-4.5" aria-hidden />
          Official escalation
        </p>
        <h1 className="mt-2 text-3xl font-semibold">
          Take “{report.title}” up the chain
        </h1>
        <p className="text-muted-foreground mt-2 max-w-2xl">
          {report.resolution_state === "disputed"
            ? "You disputed a claimed fix — that strengthens your case."
            : sla.kind === "breached"
              ? `The response deadline passed ${sla.daysOverdue} day(s) ago.`
              : ""}{" "}
          Bangladesh runs official grievance rails for exactly this situation.
          Your complaint is drafted below — file it, then log the reference
          number so the escalation shows on the public record.
        </p>
      </div>

      <EscalationWizard
        reportId={reportId}
        authorityName={authority.name}
        existing={escalations ?? []}
      />
    </div>
  );
}
