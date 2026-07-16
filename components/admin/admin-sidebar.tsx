"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import {
  ArrowLeft,
  ClipboardList,
  LayoutDashboard,
  Map,
  Users,
} from "lucide-react";
import { Logo } from "@/components/layout/logo";
import { ThemeToggle } from "@/components/layout/theme-toggle";
import { cn } from "@/lib/utils";

const LINKS = [
  { href: "/admin", label: "Dashboard", icon: LayoutDashboard, exact: true },
  { href: "/admin/reports", label: "Reports", icon: ClipboardList },
  { href: "/admin/map", label: "Map", icon: Map },
  { href: "/admin/users", label: "Users", icon: Users },
];

export function AdminSidebar() {
  const pathname = usePathname();

  return (
    <div className="flex h-full flex-col gap-4 p-4">
      <div className="flex items-center justify-between">
        <Logo />
        <ThemeToggle />
      </div>

      <nav aria-label="Admin" className="flex flex-1 flex-col gap-1">
        {LINKS.map(({ href, label, icon: Icon, exact }) => {
          const active = exact ? pathname === href : pathname.startsWith(href);
          return (
            <Link
              key={href}
              href={href}
              aria-current={active ? "page" : undefined}
              className={cn(
                "flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium transition-colors",
                active
                  ? "bg-brand-gradient text-white shadow-sm"
                  : "text-muted-foreground hover:bg-accent hover:text-foreground"
              )}
            >
              <Icon className="size-4.5" aria-hidden />
              {label}
            </Link>
          );
        })}
      </nav>

      <div className="bg-brand-gradient rounded-xl p-3 text-white">
        <p className="text-xs font-semibold">Quick tip</p>
        <p className="mt-0.5 text-xs opacity-90">
          Use the Reports page to filter by city and status, then act in bulk.
        </p>
      </div>

      <Link
        href="/dashboard"
        className="text-muted-foreground hover:text-foreground flex items-center gap-2 px-3 py-1.5 text-sm transition-colors"
      >
        <ArrowLeft className="size-4" aria-hidden /> Back to site
      </Link>
    </div>
  );
}
