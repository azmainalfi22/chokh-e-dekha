import type { Metadata } from "next";
import Link from "next/link";
import { Globe, Landmark, Network, Phone } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import {
  AUTHORITIES,
  DEPT_LABELS,
  NATIONAL_HELPLINE,
  type Authority,
  type DeptType,
} from "@/lib/routing";

export const metadata: Metadata = {
  title: "Authority Directory",
  description:
    "Who is responsible for what across Bangladesh's cities — city corporations, WASA, development authorities, power distributors and police, with contacts. Every report you file is routed to the right desk.",
};

// Fixed display order; the "police"/"fire" groups read last.
const DEPT_ORDER: DeptType[] = [
  "municipal",
  "water",
  "planning",
  "power",
  "police",
  "fire",
];

function groupByDept(): Array<[DeptType, Authority[]]> {
  const groups = new Map<DeptType, Authority[]>();
  for (const authority of Object.values(AUTHORITIES)) {
    const list = groups.get(authority.dept) ?? [];
    list.push(authority);
    groups.set(authority.dept, list);
  }
  return DEPT_ORDER.filter((d) => groups.has(d)).map((d) => [
    d,
    groups.get(d)!.sort((a, b) => a.name.localeCompare(b.name)),
  ]);
}

export default function AuthoritiesPage() {
  const groups = groupByDept();

  return (
    <>
      <section className="civic-mesh border-b">
        <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6">
          <span className="text-primary inline-flex items-center gap-2 text-sm font-semibold">
            <Network className="size-4.5" aria-hidden />
            Who is responsible
          </span>
          <h1 className="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">
            Authority directory
          </h1>
          <p className="text-muted-foreground mt-2 max-w-2xl">
            Civic responsibility in Bangladesh is split across many bodies —
            city corporations, water and sewerage utilities (WASA), development
            authorities, power distributors and the police. Every report you
            file here is automatically routed to the right one. This is the map.
          </p>
          <div className="mt-5 flex flex-wrap gap-3">
            <Button
              className="bg-brand-gradient border-0 text-white hover:opacity-90"
              asChild
            >
              <Link href="/submit">Report an issue</Link>
            </Button>
            <Button variant="outline" asChild>
              <a href={`tel:${NATIONAL_HELPLINE}`}>
                <Phone className="size-4" aria-hidden /> National helpline{" "}
                {NATIONAL_HELPLINE}
              </a>
            </Button>
          </div>
        </div>
      </section>

      <section className="mx-auto max-w-7xl space-y-10 px-4 py-12 sm:px-6">
        {groups.map(([dept, list]) => (
          <div key={dept}>
            <h2 className="flex items-center gap-2 text-lg font-semibold">
              <Landmark className="text-primary size-5" aria-hidden />
              {DEPT_LABELS[dept]}
            </h2>
            <div className="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
              {list.map((a) => (
                <Card key={a.key} className="card-lift">
                  <CardContent className="flex h-full flex-col gap-2 pt-6">
                    <div>
                      <p className="font-semibold">{a.name}</p>
                      <p className="text-muted-foreground font-bengali text-sm">
                        {a.nameBn}
                      </p>
                    </div>
                    <p className="text-muted-foreground text-xs">
                      {a.jurisdiction}
                    </p>
                    <div className="mt-auto flex flex-wrap gap-2 pt-2">
                      <Button variant="outline" size="sm" asChild>
                        <a href={`tel:${a.hotline ?? NATIONAL_HELPLINE}`}>
                          <Phone className="size-4" aria-hidden />
                          {a.hotline ?? NATIONAL_HELPLINE}
                        </a>
                      </Button>
                      {a.website ? (
                        <Button variant="ghost" size="sm" asChild>
                          <a
                            href={a.website}
                            target="_blank"
                            rel="noreferrer noopener"
                          >
                            <Globe className="size-4" aria-hidden /> Website
                          </a>
                        </Button>
                      ) : null}
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>
          </div>
        ))}

        <p className="text-muted-foreground border-t pt-6 text-xs">
          Contact details are provided for convenience and may change. When a
          specific hotline isn&apos;t listed, the national{" "}
          <span className="font-medium">{NATIONAL_HELPLINE}</span> helpline
          routes any civic grievance. Chokh-e-Dekha is an independent civic
          platform and is not affiliated with these bodies.
        </p>
      </section>
    </>
  );
}
