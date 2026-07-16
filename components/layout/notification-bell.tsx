"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { useEffect, useState } from "react";
import { Bell, BellOff } from "lucide-react";
import { createClient } from "@/lib/supabase/client";
import { markNotificationsRead } from "@/lib/actions/engagement";
import { Button } from "@/components/ui/button";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { cn } from "@/lib/utils";

type Notification = {
  id: number;
  report_id: number | null;
  title: string;
  body: string | null;
  is_read: boolean;
  created_at: string;
};

type Props = {
  userId: string;
  initialNotifications: Notification[];
  initialUnread: number;
};

export function NotificationBell({
  userId,
  initialNotifications,
  initialUnread,
}: Props) {
  const router = useRouter();
  const [notifications, setNotifications] = useState(initialNotifications);
  const [unread, setUnread] = useState(initialUnread);

  useEffect(() => {
    const supabase = createClient();
    const channel = supabase
      .channel(`notifications:${userId}`)
      .on(
        "postgres_changes",
        {
          event: "INSERT",
          schema: "public",
          table: "notifications",
          filter: `user_id=eq.${userId}`,
        },
        (payload) => {
          const n = payload.new as Notification;
          setNotifications((prev) => [n, ...prev].slice(0, 10));
          setUnread((u) => u + 1);
        }
      )
      .subscribe();
    return () => {
      supabase.removeChannel(channel);
    };
  }, [userId]);

  function onOpenChange(open: boolean) {
    if (open && unread > 0) {
      setUnread(0);
      markNotificationsRead().then(() => router.refresh());
    }
  }

  return (
    <DropdownMenu onOpenChange={onOpenChange}>
      <DropdownMenuTrigger asChild>
        <Button
          variant="ghost"
          size="icon"
          className="relative"
          aria-label={`Notifications${unread > 0 ? ` (${unread} unread)` : ""}`}
        >
          <Bell className="size-4.5" aria-hidden />
          {unread > 0 ? (
            <span className="bg-brand-gradient absolute top-1 right-1 flex size-4 items-center justify-center rounded-full text-[10px] font-bold text-white">
              {unread > 9 ? "9+" : unread}
            </span>
          ) : null}
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end" className="w-80 p-0">
        <p className="border-b px-4 py-2.5 text-sm font-semibold">
          Notifications
        </p>
        {notifications.length === 0 ? (
          <div className="text-muted-foreground flex flex-col items-center gap-2 py-8 text-sm">
            <BellOff className="size-6" aria-hidden />
            You&apos;re all caught up
          </div>
        ) : (
          <ul className="max-h-96 overflow-y-auto">
            {notifications.map((n) => (
              <li key={n.id} className="border-b last:border-0">
                <Link
                  href={n.report_id ? `/reports/${n.report_id}` : "/dashboard"}
                  className={cn(
                    "hover:bg-accent/60 block px-4 py-3 transition-colors",
                    !n.is_read && "bg-primary/5"
                  )}
                >
                  <p className="text-sm font-medium">{n.title}</p>
                  {n.body ? (
                    <p className="text-muted-foreground line-clamp-2 text-xs">
                      {n.body}
                    </p>
                  ) : null}
                  <p className="text-muted-foreground mt-1 text-[11px]">
                    {new Date(n.created_at).toLocaleString("en-GB", {
                      day: "numeric",
                      month: "short",
                      hour: "2-digit",
                      minute: "2-digit",
                    })}
                  </p>
                </Link>
              </li>
            ))}
          </ul>
        )}
      </DropdownMenuContent>
    </DropdownMenu>
  );
}
