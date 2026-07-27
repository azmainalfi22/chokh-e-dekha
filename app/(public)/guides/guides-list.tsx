"use client";

import Link from "next/link";
import { useMemo, useState } from "react";
import {
  Baby,
  Calculator,
  Car,
  Clock3,
  IdCard,
  LandPlot,
  Plane,
  Receipt,
  Search,
  Store,
  Wallet,
  type LucideIcon,
} from "lucide-react";
import { GUIDES } from "@/lib/guides";
import { Input } from "@/components/ui/input";

const ICONS: Record<string, LucideIcon> = {
  "birth-registration": Baby,
  "nid-correction": IdCard,
  "e-passport": Plane,
  "trade-licence": Store,
  "holding-tax": Receipt,
  "e-namjari": LandPlot,
  "driving-licence": Car,
  etin: Calculator,
};

export function GuidesList() {
  const [q, setQ] = useState("");

  const filtered = useMemo(() => {
    const needle = q.trim().toLowerCase();
    if (!needle) return GUIDES;
    return GUIDES.filter((g) =>
      [g.titleEn, g.titleBn, g.summary, g.agency]
        .join(" ")
        .toLowerCase()
        .includes(needle)
    );
  }, [q]);

  return (
    <section className="mx-auto max-w-7xl px-4 py-10 sm:px-6">
      <div className="relative max-w-md">
        <Search
          className="text-muted-foreground absolute top-1/2 left-3 size-4 -translate-y-1/2"
          aria-hidden
        />
        <Input
          value={q}
          onChange={(e) => setQ(e.target.value)}
          placeholder="Search: passport, জন্ম নিবন্ধন, land, tax…"
          className="pl-9"
          aria-label="Search guides"
        />
      </div>

      <div className="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        {filtered.map((g) => {
          const Icon = ICONS[g.slug] ?? Wallet;
          return (
            <Link
              key={g.slug}
              href={`/guides/${g.slug}`}
              className="bg-card card-lift group flex flex-col rounded-xl border p-5"
            >
              <span className="bg-primary/10 text-primary group-hover:bg-primary group-hover:text-primary-foreground inline-flex size-11 items-center justify-center rounded-lg transition-colors">
                <Icon className="size-5.5" aria-hidden />
              </span>
              <h2 className="group-hover:text-primary mt-3 font-semibold transition-colors">
                {g.titleEn}
              </h2>
              <p className="text-muted-foreground font-bengali text-sm">
                {g.titleBn}
              </p>
              <p className="text-muted-foreground mt-2 line-clamp-3 flex-1 text-sm leading-relaxed">
                {g.summary}
              </p>
              <p className="text-muted-foreground mt-3 flex items-center gap-1.5 text-xs">
                <Clock3 className="size-3.5 shrink-0" aria-hidden />
                <span className="truncate">{g.time}</span>
              </p>
            </Link>
          );
        })}
      </div>

      {filtered.length === 0 ? (
        <p className="text-muted-foreground mt-10 text-center text-sm">
          No guide matches “{q}” yet — try another term, or call{" "}
          <a href="tel:333" className="text-primary font-medium hover:underline">
            333
          </a>{" "}
          for any government-service question.
        </p>
      ) : null}
    </section>
  );
}
