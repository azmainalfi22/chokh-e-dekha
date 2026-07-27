import type { Metadata } from "next";
import Link from "next/link";
import {
  Droplets,
  Flame,
  Megaphone,
  RadioTower,
  Zap,
  type LucideIcon,
} from "lucide-react";
import { createClient } from "@/lib/supabase/server";
import {
  OUTAGE_CONFIRM_THRESHOLD,
  OUTAGE_WINDOW_HOURS,
} from "@/lib/constants";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";

export const metadata: Metadata = {
  title: "Live Outages",
  description:
    "Live water, electricity and gas outages reported across Bangladesh's cities — see if your area is affected and how many neighbours reported it.",
};

const UTILITY: Record<string, { icon: LucideIcon; tint: string }> = {
  "Water Supply": { icon: Droplets, tint: "text-status-progress" },
  Electricity: { icon: Zap, tint: "text-status-pending" },
  Gas: { icon: Flame, tint: "text-status-breach" },
};

function ago(iso: string): string {
  const mins = Math.floor((Date.now() - new Date(iso).getTime()) / 60000);
  if (mins < 60) return `${Math.max(mins, 1)}m ago`;
  const hrs = Math.floor(mins / 60);
  if (hrs < 24) return `${hrs}h ago`;
  return `${Math.floor(hrs / 24)}d ago`;
}

export default async function OutagesPage() {
  const supabase = await createClient();
  const { data } = await supabase.rpc("active_outages", {
    p_hours: OUTAGE_WINDOW_HOURS,
  });
  const clusters = data ?? [];
  const confirmed = clusters.filter(
    (c) => c.report_count >= OUTAGE_CONFIRM_THRESHOLD
  ).length;

  return (
    <>
      <section className="civic-mesh border-b">
        <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6">
          <span className="text-status-breach inline-flex items-center gap-2 text-sm font-semibold tracking-wider uppercase">
            <RadioTower className="size-4.5" aria-hidden />
            Live utility outages
          </span>
          <h1 className="mt-2 text-3xl font-semibold tracking-tight sm:text-4xl">
            Is your area affected?
          </h1>
          <p className="text-muted-foreground mt-3 max-w-2xl">
            Water, electricity and gas disruptions reported by citizens in the
            last {OUTAGE_WINDOW_HOURS} hours, clustered by area. When several
            neighbours report the same problem, it&apos;s a confirmed
            outage — you&apos;re not alone, and the record builds pressure.
          </p>
          <div className="mt-5 flex flex-wrap gap-3">
            <Button
              className="bg-brand-gradient border-0 text-white hover:opacity-95"
              asChild
            >
              <Link href="/submit">
                <Megaphone className="size-4" /> Report an outage
              </Link>
            </Button>
            {confirmed > 0 ? (
              <span className="border-status-breach/30 bg-status-breach/5 text-status-breach inline-flex items-center rounded-full border px-3 py-1.5 text-sm font-medium">
                {confirmed} confirmed outage{confirmed === 1 ? "" : "s"} right now
              </span>
            ) : null}
          </div>
        </div>
      </section>

      <section className="mx-auto max-w-7xl px-4 py-10 sm:px-6">
        {clusters.length === 0 ? (
          <div className="flex flex-col items-center gap-3 rounded-xl border py-20 text-center">
            <RadioTower className="text-muted-foreground size-12" aria-hidden />
            <h2 className="text-lg font-semibold">No active outages reported</h2>
            <p className="text-muted-foreground max-w-sm text-sm">
              Nothing in the last {OUTAGE_WINDOW_HOURS} hours — good news. If
              your water, power or gas is down, be the first to report it.
            </p>
          </div>
        ) : (
          <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            {clusters.map((c) => {
              const u = UTILITY[c.category] ?? {
                icon: RadioTower,
                tint: "text-muted-foreground",
              };
              const Icon = u.icon;
              const isConfirmed = c.report_count >= OUTAGE_CONFIRM_THRESHOLD;
              const href = `/reports?category=${encodeURIComponent(c.category)}&city=${encodeURIComponent(c.city_corporation)}`;
              return (
                <div
                  key={`${c.category}-${c.city_corporation}`}
                  className={`bg-card card-lift relative overflow-hidden rounded-xl border p-5 ${
                    isConfirmed ? "border-status-breach/40" : ""
                  }`}
                >
                  {isConfirmed ? (
                    <span
                      className="bg-status-breach absolute inset-x-0 top-0 h-1"
                      aria-hidden
                    />
                  ) : null}
                  <div className="flex items-start justify-between gap-2">
                    <span className={`inline-flex size-11 items-center justify-center rounded-lg bg-muted ${u.tint}`}>
                      <Icon className="size-6" aria-hidden />
                    </span>
                    {isConfirmed ? (
                      <Badge className="border-status-breach/40 bg-status-breach/10 text-status-breach border">
                        Confirmed outage
                      </Badge>
                    ) : (
                      <Badge variant="outline" className="text-muted-foreground">
                        Emerging
                      </Badge>
                    )}
                  </div>
                  <h2 className="mt-3 text-lg font-semibold">{c.category}</h2>
                  <p className="text-muted-foreground text-sm">
                    {c.city_corporation}
                  </p>
                  <div className="mt-3 flex items-baseline gap-1.5">
                    <span className="text-status-breach font-display text-3xl font-semibold tabular-nums">
                      {c.report_count}
                    </span>
                    <span className="text-muted-foreground text-sm">
                      report{c.report_count === 1 ? "" : "s"} ·{" "}
                      latest {ago(c.last_reported)}
                    </span>
                  </div>
                  <div className="mt-4 flex flex-wrap gap-2">
                    <Button variant="outline" size="sm" asChild>
                      <Link href={href}>See reports</Link>
                    </Button>
                    <Button
                      size="sm"
                      className="bg-brand-gradient border-0 text-white hover:opacity-95"
                      asChild
                    >
                      <Link href="/submit">Report too</Link>
                    </Button>
                  </div>
                </div>
              );
            })}
          </div>
        )}

        <p className="text-muted-foreground mt-8 text-xs leading-relaxed">
          A power cut affecting your whole area is usually the distributor&apos;s
          scheduled or fault maintenance — call your provider&apos;s hotline
          (see the{" "}
          <Link href="/authorities" className="text-primary font-medium hover:underline">
            authority directory
          </Link>
          ) for restoration times. Reporting here builds a public record of how
          often and how long your area loses service.
        </p>
      </section>
    </>
  );
}
