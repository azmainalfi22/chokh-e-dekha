"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import {
  ArrowLeft,
  ClipboardList,
  Eye,
  LayoutDashboard,
  Map,
  MessageSquarePlus,
  Users,
} from "lucide-react";
import { APP_NAME } from "@/lib/constants";
import { cn } from "@/lib/utils";

const LINKS = [
  { href: "/admin", label: "Dashboard", icon: LayoutDashboard, exact: true },
  { href: "/admin/reports", label: "Reports", icon: ClipboardList },
  { href: "/admin/map", label: "Map", icon: Map },
  { href: "/admin/surveys", label: "Surveys", icon: MessageSquarePlus },
  { href: "/admin/users", label: "Users", icon: Users },
];

export function AdminSidebar() {
  const pathname = usePathname();

  return (
    <div className="flex h-full flex-col gap-5 p-4">
      <Link href="/admin" className="flex items-center gap-2.5 px-1">
        <span className="flex size-9 items-center justify-center rounded-md bg-white/15 text-white">
          <Eye className="size-5" aria-hidden />
        </span>
        <span className="leading-none text-white">
          <span className="block text-sm font-bold">{APP_NAME}</span>
          <span className="text-white/60 block text-[11px]">
            Admin Console
          </span>
        </span>
      </Link>

      <nav aria-label="Admin" className="flex flex-1 flex-col gap-1">
        {LINKS.map(({ href, label, icon: Icon, exact }) => {
          const active = exact ? pathname === href : pathname.startsWith(href);
          return (
            <Link
              key={href}
              href={href}
              aria-current={active ? "page" : undefined}
              className={cn(
                "flex items-center gap-2.5 rounded-md px-3 py-2 text-sm font-medium transition-colors",
                active
                  ? "bg-white text-sidebar shadow-sm"
                  : "text-white/75 hover:bg-white/10 hover:text-white"
              )}
            >
              <Icon className="size-4.5" aria-hidden />
              {label}
            </Link>
          );
        })}
      </nav>

      <div className="rounded-lg bg-white/10 p-3 text-white/85">
        <p className="text-xs font-semibold text-white">Quick tip</p>
        <p className="mt-0.5 text-xs">
          Filter the Reports queue by city and status, then act in bulk.
        </p>
      </div>

      <Link
        href="/dashboard"
        className="text-white/70 flex items-center gap-2 px-3 py-1.5 text-sm transition-colors hover:text-white"
      >
        <ArrowLeft className="size-4" aria-hidden /> Back to site
      </Link>
    </div>
  );
}
