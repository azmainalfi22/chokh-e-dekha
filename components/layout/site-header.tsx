import Link from "next/link";
import { Plus } from "lucide-react";
import { createClient } from "@/lib/supabase/server";
import { Button } from "@/components/ui/button";
import { Logo } from "@/components/layout/logo";
import { ThemeToggle } from "@/components/layout/theme-toggle";
import { UserMenu } from "@/components/layout/user-menu";

const NAV_LINKS = [
  { href: "/reports", label: "All Reports" },
  { href: "/rti", label: "RTI Wizard" },
];

export async function SiteHeader() {
  const supabase = await createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();

  let profile: {
    display_name: string;
    avatar_url: string | null;
    role: string;
  } | null = null;

  if (user) {
    const { data } = await supabase
      .from("profiles")
      .select("display_name, avatar_url, role")
      .eq("id", user.id)
      .single();
    profile = data;
  }

  return (
    <header className="bg-background/80 sticky top-0 z-40 border-b backdrop-blur-md">
      <div className="mx-auto flex h-16 max-w-7xl items-center justify-between gap-3 px-4 sm:px-6">
        <div className="flex items-center gap-6">
          <Logo />
          <nav aria-label="Main" className="hidden items-center gap-1 md:flex">
            {NAV_LINKS.map((link) => (
              <Button key={link.href} variant="ghost" size="sm" asChild>
                <Link href={link.href}>{link.label}</Link>
              </Button>
            ))}
            {user ? (
              <Button variant="ghost" size="sm" asChild>
                <Link href="/dashboard">Dashboard</Link>
              </Button>
            ) : null}
          </nav>
        </div>

        <div className="flex items-center gap-1.5">
          <ThemeToggle />
          {user && profile ? (
            <>
              <Button size="sm" className="bg-brand-gradient border-0 text-white hover:opacity-90" asChild>
                <Link href="/submit">
                  <Plus className="size-4" />
                  <span className="hidden sm:inline">New Report</span>
                </Link>
              </Button>
              <UserMenu
                displayName={profile.display_name}
                avatarUrl={profile.avatar_url}
                isAdmin={profile.role === "admin"}
              />
            </>
          ) : (
            <>
              <Button variant="ghost" size="sm" asChild>
                <Link href="/login">Log in</Link>
              </Button>
              <Button size="sm" className="bg-brand-gradient border-0 text-white hover:opacity-90" asChild>
                <Link href="/signup">Get started</Link>
              </Button>
            </>
          )}
        </div>
      </div>
    </header>
  );
}
