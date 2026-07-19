import type { Metadata } from "next";
import Link from "next/link";
import { PhoneCall, ShieldAlert } from "lucide-react";
import { HOTLINES } from "@/lib/emergency";
import { Button } from "@/components/ui/button";
import { NearbyServices } from "./nearby-services";

export const metadata: Metadata = {
  title: "Emergency & Services",
  description:
    "National emergency hotlines for Bangladesh and a live finder for the nearest police, hospitals and fire service.",
};

export default function ServicesPage() {
  const primary = HOTLINES.find((h) => h.primary)!;
  const others = HOTLINES.filter((h) => !h.primary);

  return (
    <>
      {/* Emergency hero */}
      <section className="civic-mesh border-b">
        <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6">
          <span className="text-primary inline-flex items-center gap-2 text-sm font-semibold">
            <ShieldAlert className="size-4.5" aria-hidden />
            Emergency & civic services
          </span>
          <h1 className="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">
            Help, one tap away
          </h1>
          <p className="text-muted-foreground mt-2 max-w-2xl">
            In a life-threatening emergency, call the national service first.
            All numbers below are toll-free and operate 24/7.
          </p>

          <div className="mt-8 grid gap-4 lg:grid-cols-3">
            {/* Primary 999 card */}
            <a
              href={`tel:${primary.number}`}
              className="bg-brand-gradient glow-brand card-lift group flex flex-col justify-between rounded-xl p-6 text-white lg:row-span-2"
            >
              <div>
                <span className="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-medium">
                  <PhoneCall className="size-3.5" aria-hidden />
                  Tap to call
                </span>
                <p className="mt-6 text-6xl font-extrabold tracking-tight">
                  {primary.number}
                </p>
                <p className="mt-1 text-lg font-semibold">{primary.name}</p>
                <p className="font-bengali text-white/85">{primary.nameBn}</p>
              </div>
              <p className="mt-6 text-sm text-white/85">
                {primary.description}
              </p>
            </a>

            {/* Other hotlines as glass tiles */}
            {others.map((h) => (
              <a
                key={h.number}
                href={`tel:${h.number}`}
                className="glass-panel card-lift flex items-center gap-4 rounded-xl p-4"
              >
                <span className="text-primary flex size-12 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-lg font-bold">
                  {h.number.length > 3 ? (
                    <PhoneCall className="size-5" aria-hidden />
                  ) : (
                    h.number
                  )}
                </span>
                <div className="min-w-0">
                  <p className="flex items-baseline gap-2 font-semibold">
                    {h.number.length > 3 ? h.number : h.name}
                    {h.number.length > 3 ? (
                      <span className="text-muted-foreground text-xs font-normal">
                        {h.name}
                      </span>
                    ) : null}
                  </p>
                  <p className="text-muted-foreground text-xs">
                    {h.description}
                  </p>
                </div>
              </a>
            ))}
          </div>
        </div>
      </section>

      {/* Nearby services finder */}
      <section className="mx-auto max-w-7xl px-4 py-12 sm:px-6">
        <NearbyServices />
      </section>

      {/* Cross-link */}
      <section className="border-t bg-card">
        <div className="mx-auto flex max-w-7xl flex-col items-start justify-between gap-4 px-4 py-8 sm:flex-row sm:items-center sm:px-6">
          <div>
            <h2 className="text-lg font-semibold">
              Not an emergency, but still unresolved?
            </h2>
            <p className="text-muted-foreground text-sm">
              Report the issue publicly, or escalate formally with a Right to
              Information request.
            </p>
          </div>
          <div className="flex gap-2">
            <Button
              className="bg-brand-gradient border-0 text-white hover:opacity-95"
              asChild
            >
              <Link href="/submit">Report an issue</Link>
            </Button>
            <Button variant="outline" asChild>
              <Link href="/rights">Know your rights</Link>
            </Button>
          </div>
        </div>
      </section>
    </>
  );
}
