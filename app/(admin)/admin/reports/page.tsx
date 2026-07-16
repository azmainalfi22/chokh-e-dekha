import type { Metadata } from "next";
import { Suspense } from "react";
import { Download } from "lucide-react";
import { createClient } from "@/lib/supabase/server";
import { PAGE_SIZE } from "@/lib/constants";
import { Button } from "@/components/ui/button";
import { FilterBar } from "@/components/reports/filter-bar";
import { PaginationNav } from "@/components/pagination-nav";
import { QueueTable, type QueueRow } from "./queue-table";

export const metadata: Metadata = { title: "Admin · Reports" };

type SearchParams = {
  q?: string;
  status?: string;
  category?: string;
  city?: string;
  moderation?: string;
  page?: string;
};

export default async function AdminReportsPage({
  searchParams,
}: {
  searchParams: Promise<SearchParams>;
}) {
  const params = await searchParams;
  const supabase = await createClient();
  const page = Math.max(1, Number(params.page) || 1);
  const from = (page - 1) * PAGE_SIZE;

  let query = supabase
    .from("reports")
    .select(
      `id, title, category, city_corporation, status, is_approved,
       sla_due_at, created_at, admin_note, assigned_to,
       profiles!reports_user_id_fkey ( display_name ),
       assignee:profiles!reports_assigned_to_fkey ( display_name )`,
      { count: "exact" }
    )
    .order("created_at", { ascending: false })
    .range(from, from + PAGE_SIZE - 1);

  if (params.status) query = query.eq("status", params.status);
  if (params.category) query = query.eq("category", params.category);
  if (params.city) query = query.eq("city_corporation", params.city);
  if (params.moderation === "needs-review") {
    query = query.eq("is_approved", false).neq("status", "rejected");
  }
  if (params.q) {
    const q = params.q.replaceAll("%", "\\%").replaceAll(",", " ");
    query = query.or(`title.ilike.%${q}%,description.ilike.%${q}%`);
  }

  const { data, count } = await query;

  const {
    data: { user },
  } = await supabase.auth.getUser();

  const rows: QueueRow[] = (data ?? []).map((r) => ({
    id: r.id,
    title: r.title,
    category: r.category,
    city: r.city_corporation,
    status: r.status,
    isApproved: r.is_approved,
    slaDueAt: r.sla_due_at,
    createdAt: r.created_at,
    adminNote: r.admin_note,
    reporter: r.profiles?.display_name ?? "Citizen",
    assignedToMe: r.assigned_to === user?.id,
    assigneeName: r.assignee?.display_name ?? null,
  }));

  const total = count ?? 0;
  const totalPages = Math.max(1, Math.ceil(total / PAGE_SIZE));

  const exportParams = new URLSearchParams();
  for (const [k, v] of Object.entries(params)) {
    if (v && k !== "page") exportParams.set(k, v);
  }

  return (
    <div className="mx-auto max-w-6xl space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold">Reports queue</h1>
          <p className="text-muted-foreground text-sm">
            {total} report{total === 1 ? "" : "s"} match the current filters.
          </p>
        </div>
        <Button variant="outline" asChild>
          <a
            href={`/admin/reports/export?${exportParams.toString()}`}
            download
          >
            <Download className="size-4" aria-hidden /> Export CSV
          </a>
        </Button>
      </div>

      <Suspense>
        <FilterBar />
      </Suspense>

      <QueueTable rows={rows} moderationFilter={params.moderation ?? ""} />

      <PaginationNav
        page={page}
        totalPages={totalPages}
        basePath="/admin/reports"
        searchParams={params}
      />
    </div>
  );
}
