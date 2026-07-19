import type { Metadata } from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import {
  ArrowLeft,
  Banknote,
  Building2,
  Clock3,
  ExternalLink,
  FileCheck2,
  Info,
  ListOrdered,
} from "lucide-react";
import { GUIDES, getGuide } from "@/lib/guides";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

type Params = Promise<{ slug: string }>;

export function generateStaticParams() {
  return GUIDES.map((g) => ({ slug: g.slug }));
}

export async function generateMetadata({
  params,
}: {
  params: Params;
}): Promise<Metadata> {
  const { slug } = await params;
  const guide = getGuide(slug);
  return {
    title: guide ? `${guide.titleEn} — Service Guide` : "Service Guide",
    description: guide?.summary,
  };
}

export default async function GuidePage({ params }: { params: Params }) {
  const { slug } = await params;
  const guide = getGuide(slug);
  if (!guide) notFound();

  return (
    <div className="mx-auto w-full max-w-4xl space-y-6 px-4 py-8 sm:px-6">
      <Button variant="ghost" size="sm" asChild>
        <Link href="/guides">
          <ArrowLeft className="size-4" aria-hidden /> All guides
        </Link>
      </Button>

      <div>
        <h1 className="text-3xl font-semibold tracking-tight sm:text-4xl">
          {guide.titleEn}
        </h1>
        <p className="text-muted-foreground font-bengali mt-1 text-lg">
          {guide.titleBn}
        </p>
        <p className="text-muted-foreground mt-3 max-w-2xl leading-relaxed">
          {guide.summary}
        </p>
      </div>

      {/* Fact strip */}
      <div className="grid gap-3 sm:grid-cols-3">
        <div className="bg-card rounded-xl border p-4">
          <p className="text-muted-foreground flex items-center gap-1.5 text-xs font-semibold tracking-wide uppercase">
            <Banknote className="size-4" aria-hidden /> Fee
          </p>
          <p className="mt-1.5 text-sm leading-snug font-medium">{guide.fee}</p>
        </div>
        <div className="bg-card rounded-xl border p-4">
          <p className="text-muted-foreground flex items-center gap-1.5 text-xs font-semibold tracking-wide uppercase">
            <Clock3 className="size-4" aria-hidden /> Time
          </p>
          <p className="mt-1.5 text-sm leading-snug font-medium">{guide.time}</p>
        </div>
        <div className="bg-card rounded-xl border p-4">
          <p className="text-muted-foreground flex items-center gap-1.5 text-xs font-semibold tracking-wide uppercase">
            <Building2 className="size-4" aria-hidden /> Authority
          </p>
          <p className="mt-1.5 text-sm leading-snug font-medium">
            {guide.agency}
          </p>
        </div>
      </div>

      <div className="border-primary/25 bg-primary/[0.04] flex flex-wrap items-center justify-between gap-3 rounded-xl border p-4">
        <p className="text-sm">
          <span className="font-semibold">Official portal:</span>{" "}
          {guide.portal.label}
        </p>
        <Button
          size="sm"
          className="bg-brand-gradient border-0 text-white hover:opacity-95"
          asChild
        >
          <a href={guide.portal.url} target="_blank" rel="noreferrer noopener">
            Open portal <ExternalLink className="size-4" aria-hidden />
          </a>
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2 text-lg">
            <FileCheck2 className="text-primary size-5" aria-hidden />
            Documents you need
          </CardTitle>
        </CardHeader>
        <CardContent>
          <ul className="space-y-2">
            {guide.documents.map((d) => (
              <li key={d} className="flex items-start gap-2.5 text-sm">
                <span
                  className="bg-primary mt-1.5 size-1.5 shrink-0 rounded-full"
                  aria-hidden
                />
                {d}
              </li>
            ))}
          </ul>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2 text-lg">
            <ListOrdered className="text-primary size-5" aria-hidden />
            Step by step
          </CardTitle>
        </CardHeader>
        <CardContent>
          <ol className="space-y-4">
            {guide.steps.map((s, i) => (
              <li key={s} className="flex items-start gap-3">
                <span className="bg-brand-gradient flex size-7 shrink-0 items-center justify-center rounded-full text-sm font-bold text-white">
                  {i + 1}
                </span>
                <p className="pt-0.5 text-sm leading-relaxed">{s}</p>
              </li>
            ))}
          </ol>
        </CardContent>
      </Card>

      {guide.notes?.length ? (
        <div className="border-status-progress/30 bg-status-progress/5 rounded-xl border p-4">
          {guide.notes.map((n) => (
            <p key={n} className="flex items-start gap-2.5 text-sm leading-relaxed">
              <Info className="text-status-progress mt-0.5 size-4 shrink-0" aria-hidden />
              {n}
            </p>
          ))}
        </div>
      ) : null}

      <p className="text-muted-foreground text-xs leading-relaxed">
        Fees and timelines are indicative and change by notification — the
        linked official portal is always authoritative. Stuck or being asked
        for extra “fees”? Call the national helpline{" "}
        <a href="tel:333" className="text-primary font-medium hover:underline">
          333
        </a>{" "}
        or file a grievance via the{" "}
        <Link href="/rights" className="text-primary font-medium hover:underline">
          escalation ladder
        </Link>
        .
      </p>
    </div>
  );
}
