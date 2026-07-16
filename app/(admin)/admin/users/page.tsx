import type { Metadata } from "next";
import { createClient } from "@/lib/supabase/server";
import { UsersTable, type UserRow } from "./users-table";

export const metadata: Metadata = { title: "Admin · Users" };

export default async function AdminUsersPage() {
  const supabase = await createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();

  const { data: profiles } = await supabase
    .from("profiles")
    .select("id, display_name, city, role, created_at")
    .order("created_at", { ascending: false })
    .limit(500);

  const { data: reportRows } = await supabase
    .from("reports")
    .select("user_id")
    .not("user_id", "is", null)
    .limit(5000);

  const countByUser = new Map<string, number>();
  for (const r of reportRows ?? []) {
    countByUser.set(r.user_id!, (countByUser.get(r.user_id!) ?? 0) + 1);
  }

  const rows: UserRow[] = (profiles ?? []).map((p) => ({
    id: p.id,
    displayName: p.display_name,
    city: p.city,
    role: p.role,
    createdAt: p.created_at,
    reportCount: countByUser.get(p.id) ?? 0,
    isSelf: p.id === user?.id,
  }));

  return (
    <div className="mx-auto max-w-5xl space-y-4">
      <div>
        <h1 className="text-2xl font-bold">Users</h1>
        <p className="text-muted-foreground text-sm">
          {rows.length} registered user{rows.length === 1 ? "" : "s"}. Contact
          details stay private — only display names are shown.
        </p>
      </div>
      <UsersTable rows={rows} />
    </div>
  );
}
