import type { Metadata } from "next";
import Link from "next/link";
import { redirect } from "next/navigation";
import { Bookmark } from "lucide-react";
import { createClient } from "@/lib/supabase/server";
import { Button } from "@/components/ui/button";
import {
  ReportCard,
  type ReportCardData,
} from "@/components/reports/report-card";

export const metadata: Metadata = { title: "Bookmarks" };

export default async function BookmarksPage() {
  const supabase = await createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();
  if (!user) redirect("/login");

  const { data } = await supabase
    .from("report_bookmarks")
    .select(
      `created_at,
       reports (
         id, title, category, city_corporation, location_text, status,
         sla_due_at, created_at, endorse_count, comment_count,
         profiles!reports_user_id_fkey ( display_name ),
         report_media ( storage_path )
       )`
    )
    .eq("user_id", user.id)
    .order("created_at", { ascending: false });

  const reports: ReportCardData[] = (data ?? [])
    .map((b) => b.reports)
    .filter((r): r is NonNullable<typeof r> => r !== null)
    .map((r) => ({
      id: r.id,
      title: r.title,
      category: r.category,
      city_corporation: r.city_corporation,
      location_text: r.location_text,
      status: r.status,
      sla_due_at: r.sla_due_at,
      created_at: r.created_at,
      endorse_count: r.endorse_count,
      comment_count: r.comment_count,
      authorName: r.profiles?.display_name ?? null,
      thumbnailPath: r.report_media?.[0]?.storage_path ?? null,
    }));

  return (
    <div className="mx-auto w-full max-w-7xl space-y-6 px-4 py-8 sm:px-6">
      <div>
        <h1 className="text-2xl font-bold">Bookmarks</h1>
        <p className="text-muted-foreground mt-1 text-sm">
          Reports you&apos;re keeping an eye on. Only you can see this list.
        </p>
      </div>

      {reports.length === 0 ? (
        <div className="flex flex-col items-center gap-3 rounded-xl border py-20 text-center">
          <Bookmark className="text-muted-foreground size-12" aria-hidden />
          <h2 className="text-lg font-semibold">No bookmarks yet</h2>
          <p className="text-muted-foreground max-w-sm text-sm">
            Tap the bookmark button on any report to save it here.
          </p>
          <Button variant="outline" asChild>
            <Link href="/reports">Browse reports</Link>
          </Button>
        </div>
      ) : (
        <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
          {reports.map((r) => (
            <ReportCard key={r.id} report={r} />
          ))}
        </div>
      )}
    </div>
  );
}
