import type { Metadata } from "next";
import Link from "next/link";
import { redirect } from "next/navigation";
import { CalendarClock, FileText, Plus, Printer, Scale } from "lucide-react";
import { createClient } from "@/lib/supabase/server";
import { RTI_STATUS_LABELS, workingDaysBetween } from "@/lib/rti";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent } from "@/components/ui/card";

export const metadata: Metadata = { title: "RTI Wizard" };

export default async function RtiPage() {
  const supabase = await createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();
  if (!user) redirect("/login");

  const { data: letters } = await supabase
    .from("rti_letters")
    .select(
      "id, authority, subject, language, created_at, status, deadline_at, outcome"
    )
    .eq("user_id", user.id)
    .order("created_at", { ascending: false });

  const all = letters ?? [];

  function deadlineHint(status: string, deadlineAt: string | null): string | null {
    if (status !== "submitted" || !deadlineAt) return null;
    const due = new Date(deadlineAt);
    if (due.getTime() < Date.now()) return "Overdue — you can appeal";
    return `~${workingDaysBetween(new Date(), due)} working days left`;
  }

  return (
    <div className="mx-auto w-full max-w-4xl space-y-6 px-4 py-8 sm:px-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="flex items-center gap-3">
          <span className="bg-brand-gradient flex size-11 items-center justify-center rounded-xl text-white">
            <Scale className="size-5" aria-hidden />
          </span>
          <div>
            <h1 className="text-2xl font-bold">RTI Letter Wizard</h1>
            <p className="text-muted-foreground text-sm">
              Generate a Right to Information Act 2009-compliant application in
              English or Bangla.
            </p>
          </div>
        </div>
        <Button
          className="bg-brand-gradient border-0 text-white hover:opacity-90"
          asChild
        >
          <Link href="/rti/new">
            <Plus className="size-4" /> New RTI letter
          </Link>
        </Button>
      </div>

      <Card className="border-status-progress/30 bg-status-progress/5">
        <CardContent className="text-muted-foreground pt-6 text-sm">
          The Right to Information Act 2009 gives every citizen the right to
          request information from a public authority. The designated officer
          must respond within <strong>20 working days</strong>. This wizard
          drafts a compliant letter — review it, then print it to PDF and
          submit it to the authority.
        </CardContent>
      </Card>

      <div>
        <h2 className="mb-3 font-semibold">Your saved letters</h2>
        {all.length === 0 ? (
          <div className="flex flex-col items-center gap-3 rounded-xl border py-16 text-center">
            <FileText className="text-muted-foreground size-10" aria-hidden />
            <p className="text-muted-foreground text-sm">
              You haven&apos;t created any RTI letters yet.
            </p>
            <Button
              className="bg-brand-gradient border-0 text-white hover:opacity-90"
              asChild
            >
              <Link href="/rti/new">Create your first letter</Link>
            </Button>
          </div>
        ) : (
          <ul className="space-y-2">
            {all.map((l) => (
              <li key={l.id}>
                <Card className="py-0">
                  <CardContent className="flex flex-wrap items-center justify-between gap-3 py-3">
                    <div className="min-w-0">
                      <p className="truncate font-medium">{l.subject}</p>
                      <p className="text-muted-foreground truncate text-xs">
                        {l.authority} ·{" "}
                        {new Date(l.created_at).toLocaleDateString("en-GB", {
                          day: "numeric",
                          month: "short",
                          year: "numeric",
                        })}
                      </p>
                      {(() => {
                        const hint = deadlineHint(l.status, l.deadline_at);
                        return hint ? (
                          <p
                            className={`mt-1 flex items-center gap-1 text-xs font-medium ${
                              hint.startsWith("Overdue")
                                ? "text-status-breach"
                                : "text-muted-foreground"
                            }`}
                          >
                            <CalendarClock className="size-3.5" aria-hidden />
                            {hint}
                          </p>
                        ) : null;
                      })()}
                    </div>
                    <div className="flex items-center gap-2">
                      <Badge
                        variant="outline"
                        className={
                          l.status === "closed"
                            ? "border-status-resolved/40 text-status-resolved"
                            : l.status === "appealed"
                              ? "border-status-breach/40 text-status-breach"
                              : l.status === "drafted"
                                ? "text-muted-foreground"
                                : "border-primary/40 text-primary"
                        }
                      >
                        {RTI_STATUS_LABELS[l.status] ?? l.status}
                      </Badge>
                      <Button size="sm" variant="outline" asChild>
                        <Link href={`/rti/${l.id}`}>
                          <Printer className="size-4" aria-hidden /> View / Print
                        </Link>
                      </Button>
                    </div>
                  </CardContent>
                </Card>
              </li>
            ))}
          </ul>
        )}
      </div>
    </div>
  );
}
