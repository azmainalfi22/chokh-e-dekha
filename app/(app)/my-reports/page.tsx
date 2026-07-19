import type { Metadata } from "next";
import Link from "next/link";
import { redirect } from "next/navigation";
import { FilePlus2, FileText } from "lucide-react";
import { createClient } from "@/lib/supabase/server";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { StatusBadge } from "@/components/reports/status-badge";
import { SlaBadge } from "@/components/reports/sla-badge";

export const metadata: Metadata = { title: "My Reports" };

export default async function MyReportsPage() {
  const supabase = await createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();
  if (!user) redirect("/login");

  const { data: reports } = await supabase
    .from("reports")
    .select(
      "id, title, category, city_corporation, status, is_approved, resolution_state, sla_due_at, created_at, endorse_count, comment_count"
    )
    .eq("user_id", user.id)
    .order("created_at", { ascending: false });

  const all = reports ?? [];

  return (
    <div className="mx-auto w-full max-w-5xl space-y-6 px-4 py-8 sm:px-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold">My Reports</h1>
          <p className="text-muted-foreground mt-1 text-sm">
            Every report you&apos;ve submitted, including those still in
            moderation.
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

      {all.length === 0 ? (
        <div className="flex flex-col items-center gap-3 rounded-xl border py-20 text-center">
          <FileText className="text-muted-foreground size-12" aria-hidden />
          <h2 className="text-lg font-semibold">No reports yet</h2>
          <p className="text-muted-foreground max-w-sm text-sm">
            When you submit a report it shows up here with its live status.
          </p>
          <Button
            className="bg-brand-gradient border-0 text-white hover:opacity-90"
            asChild
          >
            <Link href="/submit">Submit your first report</Link>
          </Button>
        </div>
      ) : (
        <div className="overflow-x-auto rounded-xl border">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Title</TableHead>
                <TableHead>City</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Visibility</TableHead>
                <TableHead>Submitted</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {all.map((r) => (
                <TableRow key={r.id}>
                  <TableCell className="max-w-64 font-medium">
                    <Link
                      href={`/reports/${r.id}`}
                      className="hover:text-primary block truncate transition-colors"
                    >
                      {r.title}
                    </Link>
                    <span className="text-muted-foreground text-xs">
                      {r.category}
                    </span>
                  </TableCell>
                  <TableCell className="text-sm">
                    {r.city_corporation}
                  </TableCell>
                  <TableCell>
                    <div className="flex flex-col items-start gap-1">
                      <StatusBadge status={r.status} />
                      <SlaBadge slaDueAt={r.sla_due_at} status={r.status} />
                      {r.resolution_state === "pending_confirmation" ? (
                        <Link
                          href={`/reports/${r.id}`}
                          className="text-status-pending text-xs font-medium hover:underline"
                        >
                          Action needed: confirm fix →
                        </Link>
                      ) : null}
                    </div>
                  </TableCell>
                  <TableCell>
                    {r.is_approved ? (
                      <Badge
                        variant="outline"
                        className="border-status-resolved/40 text-status-resolved"
                      >
                        Public
                      </Badge>
                    ) : (
                      <Badge variant="outline" className="text-muted-foreground">
                        In moderation
                      </Badge>
                    )}
                  </TableCell>
                  <TableCell className="text-muted-foreground text-sm whitespace-nowrap">
                    {new Date(r.created_at).toLocaleDateString("en-GB", {
                      day: "numeric",
                      month: "short",
                      year: "numeric",
                    })}
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </div>
      )}
    </div>
  );
}
