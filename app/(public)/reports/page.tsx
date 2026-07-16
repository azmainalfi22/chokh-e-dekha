import type { Metadata } from "next";
import { Suspense } from "react";
import { FileSearch } from "lucide-react";
import { createClient } from "@/lib/supabase/server";
import { PAGE_SIZE } from "@/lib/constants";
import { FilterBar } from "@/components/reports/filter-bar";
import {
  ReportCard,
  type ReportCardData,
} from "@/components/reports/report-card";
import { PaginationNav } from "@/components/pagination-nav";
import { Skeleton } from "@/components/ui/skeleton";

export const metadata: Metadata = { title: "All Reports" };

type SearchParams = {
  q?: string;
  status?: string;
  category?: string;
  city?: string;
  page?: string;
};

export default async function ReportsPage({
  searchParams,
}: {
  searchParams: Promise<SearchParams>;
}) {
  const params = await searchParams;

  return (
    <div className="mx-auto w-full max-w-7xl space-y-6 px-4 py-8 sm:px-6">
      <div>
        <h1 className="text-brand-gradient text-3xl font-extrabold tracking-tight">
          All Reports
        </h1>
        <p className="text-muted-foreground mt-1 text-sm">
          Search and filter reports submitted across all city corporations.
        </p>
      </div>

      <Suspense>
        <FilterBar />
      </Suspense>

      <Suspense
        key={JSON.stringify(params)}
        fallback={
          <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            {Array.from({ length: 6 }).map((_, i) => (
              <Skeleton key={i} className="h-80 rounded-xl" />
            ))}
          </div>
        }
      >
        <ReportsGrid params={params} />
      </Suspense>
    </div>
  );
}

async function ReportsGrid({ params }: { params: SearchParams }) {
  const supabase = await createClient();
  const page = Math.max(1, Number(params.page) || 1);
  const from = (page - 1) * PAGE_SIZE;

  let query = supabase
    .from("reports")
    .select(
      `id, title, category, city_corporation, location_text, status,
       sla_due_at, created_at, endorse_count, comment_count,
       profiles!reports_user_id_fkey ( display_name ),
       report_media ( storage_path )`,
      { count: "exact" }
    )
    .eq("is_approved", true)
    .order("created_at", { ascending: false })
    .range(from, from + PAGE_SIZE - 1);

  if (params.status) query = query.eq("status", params.status);
  if (params.category) query = query.eq("category", params.category);
  if (params.city) query = query.eq("city_corporation", params.city);
  if (params.q) {
    const q = params.q.replaceAll("%", "\\%").replaceAll(",", " ");
    query = query.or(
      `title.ilike.%${q}%,description.ilike.%${q}%,location_text.ilike.%${q}%`
    );
  }

  const { data, count, error } = await query;

  if (error) {
    return (
      <p className="text-muted-foreground py-16 text-center text-sm">
        Could not load reports — please refresh.
      </p>
    );
  }

  const reports: ReportCardData[] = (data ?? []).map((r) => ({
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

  const total = count ?? 0;
  const totalPages = Math.max(1, Math.ceil(total / PAGE_SIZE));

  if (reports.length === 0) {
    return (
      <div className="flex flex-col items-center gap-3 py-20 text-center">
        <FileSearch className="text-muted-foreground size-12" aria-hidden />
        <h2 className="text-lg font-semibold">No reports found</h2>
        <p className="text-muted-foreground max-w-sm text-sm">
          {params.q || params.status || params.category || params.city
            ? "Try adjusting or clearing the filters."
            : "Approved reports will appear here — be the first to submit one."}
        </p>
      </div>
    );
  }

  return (
    <>
      <p className="text-muted-foreground text-sm" aria-live="polite">
        Showing {from + 1}–{Math.min(from + PAGE_SIZE, total)} of {total}{" "}
        report{total === 1 ? "" : "s"}
      </p>
      <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        {reports.map((r) => (
          <ReportCard key={r.id} report={r} />
        ))}
      </div>
      <PaginationNav
        page={page}
        totalPages={totalPages}
        basePath="/reports"
        searchParams={params}
      />
    </>
  );
}
