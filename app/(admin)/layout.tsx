import { redirect } from "next/navigation";
import { createClient } from "@/lib/supabase/server";
import { AdminSidebar } from "@/components/admin/admin-sidebar";

export default async function AdminLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  const supabase = await createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();
  if (!user) redirect("/login");

  const { data: profile } = await supabase
    .from("profiles")
    .select("role")
    .eq("id", user.id)
    .single();

  // Non-admins are sent back to the citizen dashboard. RLS is the real
  // enforcement; this is the UX gate.
  if (profile?.role !== "admin") redirect("/dashboard");

  return (
    <div className="flex min-h-dvh">
      <aside className="bg-sidebar sticky top-0 hidden h-dvh w-60 shrink-0 border-r lg:block">
        <AdminSidebar />
      </aside>
      <div className="min-w-0 flex-1">
        <div className="bg-sidebar sticky top-0 z-40 border-b p-3 lg:hidden">
          <AdminSidebar />
        </div>
        <main className="p-4 sm:p-6">{children}</main>
      </div>
    </div>
  );
}
