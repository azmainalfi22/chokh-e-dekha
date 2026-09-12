import Link from "next/link";
import { Megaphone, PhoneCall } from "lucide-react";
import { createClient } from "@/lib/supabase/server";
import { Button } from "@/components/ui/button";
import { Logo } from "@/components/layout/logo";
import { NotificationBell } from "@/components/layout/notification-bell";
import { ThemeToggle } from "@/components/layout/theme-toggle";
import { UserMenu } from "@/components/layout/user-menu";
import { MobileNav } from "@/components/layout/mobile-nav";

const NAV_LINKS = [
  { href: "/reports", label: "Reports" },
  { href: "/outages", label: "Outages" },
  { href: "/guides", label: "Guides" },
  { href: "/services", label: "Emergency" },
  { href: "/rights", label: "Your Rights" },
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
  let notifications: {
    id: number;
    report_id: number | null;
    title: string;
    body: string | null;
    is_read: boolean;
    created_at: string;
  }[] = [];
  let unread = 0;

  if (user) {
    const [{ data }, { data: notifs }, { count }] = await Promise.all([
      supabase
        .from("profiles")
        .select("display_name, avatar_url, role")
        .eq("id", user.id)
        .single(),
      supabase
        .from("notifications")
        .select("id, report_id, title, body, is_read, created_at")
        .eq("user_id", user.id)
        .order("created_at", { ascending: false })
        .limit(10),
      supabase
        .from("notifications")
        .select("id", { count: "exact", head: true })
        .eq("user_id", user.id)
        .eq("is_read", false),
    ]);
    profile = data;
    notifications = notifs ?? [];
    unread = count ?? 0;
  }

  return (
    <header className="sticky top-0 z-40">
      {/* national flag ribbon */}
      <div className="ribbon-bd h-1" aria-hidden />

      {/* utility bar */}
      <div className="bg-brand-green-strong text-white/90">
        <div className="mx-auto flex h-8 max-w-7xl items-center justify-between px-4 text-xs sm:px-6">
          <p className="font-bengali hidden truncate sm:block">
            গণমুখী নাগরিক প্ল্যাটফর্ম — a citizens&apos; civic accountability
            platform
          </p>
          <div className="flex items-center gap-2">
            <a
              href="tel:999"
              className="inline-flex items-center gap-1 font-semibold text-white hover:underline"
            >
              <PhoneCall className="size-3" aria-hidden />
              Emergency 999
            </a>
            <span className="text-white/30" aria-hidden>
              |
            </span>
            <ThemeToggle />
          </div>
        </div>
      </div>

      {/* masthead */}
      <div className="glass border-b">
        <div className="mx-auto flex h-16 max-w-7xl items-center justify-between gap-3 px-4 sm:px-6">
          <div className="flex items-center gap-7">
            <Logo />
            <nav aria-label="Main" className="hidden items-center gap-5 lg:flex">
              {NAV_LINKS.map((link) => (
                <Link
                  key={link.href}
                  href={link.href}
                  className="nav-underline text-foreground/80 hover:text-primary text-sm font-medium transition-colors"
                >
                  {link.label}
                </Link>
              ))}
              {user ? (
                <Link
                  href="/dashboard"
                  className="nav-underline text-foreground/80 hover:text-primary text-sm font-medium transition-colors"
                >
                  Dashboard
                </Link>
              ) : null}
            </nav>
          </div>

          <div className="flex items-center gap-2">
            {user && profile ? (
              <>
                <NotificationBell
                  userId={user.id}
                  initialNotifications={notifications}
                  initialUnread={unread}
                />
                <Button
                  size="sm"
                  className="hidden bg-brand-gradient border-0 text-white hover:opacity-95 sm:inline-flex"
                  asChild
                >
                  <Link href="/submit">
                    <Megaphone className="size-4" />
                    Report an issue
                  </Link>
                </Button>
                <UserMenu
                  displayName={profile.display_name}
                  avatarUrl={profile.avatar_url}
                  isAdmin={profile.role === "admin"}
                  isOfficer={profile.role === "officer"}
                />
              </>
            ) : (
              <>
                <Button variant="ghost" size="sm" asChild className="hidden sm:inline-flex">
                  <Link href="/login">Log in</Link>
                </Button>
                <Button
                  size="sm"
                  className="bg-brand-gradient border-0 text-white hover:opacity-95"
                  asChild
                >
                  <Link href="/submit">
                    <Megaphone className="size-4" />
                    <span className="hidden sm:inline">Report an issue</span>
                    <span className="sm:hidden">Report</span>
                  </Link>
                </Button>
              </>
            )}
            <MobileNav
              isAuthed={!!user}
              isAdmin={profile?.role === "admin"}
              links={NAV_LINKS}
            />
          </div>
        </div>
      </div>
    </header>
  );
}
