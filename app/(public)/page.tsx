import Link from "next/link";
import {
  ArrowRight,
  Camera,
  CheckCircle2,
  FileText,
  Gauge,
  MapPin,
  Megaphone,
  MessagesSquare,
  Scale,
  ShieldCheck,
} from "lucide-react";
import { createClient } from "@/lib/supabase/server";
import { APP_NAME_BN } from "@/lib/constants";
import { Button } from "@/components/ui/button";
import { CountUp } from "@/components/count-up";
import { Tilt } from "@/components/tilt";
import {
  ReportCard,
  type ReportCardData,
} from "@/components/reports/report-card";

export default async function HomePage() {
  const supabase = await createClient();

  const [totalRes, resolvedRes, progressRes, cityRes, recentRes] =
    await Promise.all([
      supabase
        .from("reports")
        .select("id", { count: "exact", head: true })
        .eq("is_approved", true),
      supabase
        .from("reports")
        .select("id", { count: "exact", head: true })
        .eq("is_approved", true)
        .eq("status", "resolved"),
      supabase
        .from("reports")
        .select("id", { count: "exact", head: true })
        .eq("is_approved", true)
        .eq("status", "in_progress"),
      supabase
        .from("reports")
        .select("city_corporation")
        .eq("is_approved", true)
        .limit(1000),
      supabase
        .from("reports")
        .select(
          `id, title, category, city_corporation, location_text, status,
           sla_due_at, created_at, endorse_count, comment_count,
           profiles!reports_user_id_fkey ( display_name ),
           report_media ( storage_path )`
        )
        .eq("is_approved", true)
        .order("created_at", { ascending: false })
        .limit(3),
    ]);

  const totalReports = totalRes.count ?? 0;
  const resolved = resolvedRes.count ?? 0;
  const inProgress = progressRes.count ?? 0;
  const cities = new Set((cityRes.data ?? []).map((r) => r.city_corporation))
    .size;

  const recent: ReportCardData[] = (recentRes.data ?? []).map((r) => ({
    id: r.id,
    title: r.title,
    category: r.category,
    city_corporation: r.city_corporation,
    location_text: r.location_text,
    status: r.status,
    sla_due_at: r.sla_due_at,
    created_at: r.created_at,
    endorse_count: r.endorse_count,
    comment_count: r.comment_count,
    authorName: r.profiles?.display_name ?? null,
    thumbnailPath: r.report_media?.[0]?.storage_path ?? null,
  }));

  const stats = [
    { value: totalReports, label: "Reports filed" },
    { value: inProgress, label: "In progress" },
    { value: resolved, label: "Resolved" },
    { value: cities || 0, label: "Cities covered" },
  ];

  return (
    <>
      {/* Hero */}
      <section className="border-b bg-card">
        <div className="mx-auto grid max-w-7xl gap-10 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:items-center lg:py-20">
          <div>
            <span className="border-primary/30 bg-primary/5 text-primary inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-medium">
              <span className="bg-primary size-1.5 rounded-full" aria-hidden />
              Public civic accountability · Bangladesh
            </span>
            <h1 className="mt-5 text-4xl font-bold tracking-tight text-balance sm:text-5xl">
              Report civic problems. <br className="hidden sm:block" />
              <span className="text-primary">Track the response.</span>
            </h1>
            <p className="text-muted-foreground mt-5 max-w-xl text-lg leading-relaxed text-pretty">
              From broken roads to overflowing bins, report the issues around you
              with a photo and a map pin. Every report is public, tracked against
              a response deadline, and routed to the right city corporation.
            </p>
            <div className="mt-8 flex flex-wrap items-center gap-3">
              <Button
                size="lg"
                className="bg-brand-gradient border-0 text-white hover:opacity-95"
                asChild
              >
                <Link href="/submit">
                  <Megaphone className="size-4.5" /> Report an issue
                </Link>
              </Button>
              <Button size="lg" variant="outline" asChild>
                <Link href="/reports">
                  Browse public reports <ArrowRight className="size-4.5" />
                </Link>
              </Button>
            </div>
            <p className="text-muted-foreground font-bengali mt-6 text-sm">
              {APP_NAME_BN} — আপনার চোখ, আপনার কণ্ঠস্বর, আপনার শহর
            </p>
          </div>

          {/* Stats panel */}
          <div className="bg-background rounded-xl border p-6 shadow-sm">
            <p className="text-muted-foreground text-xs font-semibold tracking-wide uppercase">
              Live platform activity
            </p>
            <dl className="mt-4 grid grid-cols-2 gap-4">
              {stats.map((s) => (
                <div key={s.label} className="rounded-lg border p-4">
                  <dt className="text-primary text-3xl font-bold tabular-nums">
                    <CountUp value={s.value} />
                  </dt>
                  <dd className="text-muted-foreground mt-1 text-xs font-medium">
                    {s.label}
                  </dd>
                </div>
              ))}
            </dl>
            <div className="mt-4 flex items-center gap-2 rounded-lg border border-dashed p-3">
              <Gauge className="text-primary size-4 shrink-0" aria-hidden />
              <p className="text-muted-foreground text-xs">
                Every approved report is tracked against a{" "}
                <span className="text-foreground font-medium">
                  7-day response SLA
                </span>{" "}
                — overdue cases are flagged publicly.
              </p>
            </div>
          </div>
        </div>
      </section>

      {/* How it works */}
      <section className="mx-auto max-w-7xl px-4 py-16 sm:px-6">
        <div className="max-w-2xl">
          <h2 className="text-2xl font-bold sm:text-3xl">How it works</h2>
          <p className="text-muted-foreground mt-2">
            Three steps from spotting a problem to public accountability —
            modelled on proven civic platforms used worldwide.
          </p>
        </div>
        <div className="mt-10 grid gap-6 md:grid-cols-3">
          {[
            {
              n: "01",
              icon: Camera,
              title: "Capture & pin",
              body: "Add a photo, drop a map pin or use GPS, and describe the issue. Photos are compressed on your device before upload.",
            },
            {
              n: "02",
              icon: ShieldCheck,
              title: "Reviewed & routed",
              body: "Moderators approve genuine reports so they appear on the public feed, mapped and tagged to the right city corporation.",
            },
            {
              n: "03",
              icon: Gauge,
              title: "Tracked to resolution",
              body: "Each report follows a Pending → In Progress → Resolved lifecycle against a response deadline, with notifications at every change.",
            },
          ].map(({ n, icon: Icon, title, body }) => (
            <Tilt key={title} className="bg-card card-lift rounded-xl border p-6">
              <div className="flex items-center justify-between">
                <span className="bg-primary/10 text-primary inline-flex size-10 items-center justify-center rounded-md">
                  <Icon className="size-5" aria-hidden />
                </span>
                <span className="text-muted-foreground/40 text-2xl font-bold">
                  {n}
                </span>
              </div>
              <h3 className="mt-4 text-lg font-semibold">{title}</h3>
              <p className="text-muted-foreground mt-1.5 text-sm leading-relaxed">
                {body}
              </p>
            </Tilt>
          ))}
        </div>
      </section>

      {/* Features */}
      <section className="border-y bg-card">
        <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6">
          <div className="grid gap-x-8 gap-y-6 sm:grid-cols-2 lg:grid-cols-4">
            {[
              {
                icon: MapPin,
                title: "Interactive map",
                body: "Every geo-tagged report on an OpenStreetMap view, with density heatmaps for administrators.",
              },
              {
                icon: MessagesSquare,
                title: "Community weight",
                body: "Endorse, comment and share — turning one report into collective, visible documentation.",
              },
              {
                icon: Scale,
                title: "RTI wizard",
                body: "Generate a Right to Information Act 2009 application in English or Bangla, ready to print.",
              },
              {
                icon: CheckCircle2,
                title: "SLA transparency",
                body: "Response deadlines are tracked and breaches flagged — so neglect can't stay invisible.",
              },
            ].map(({ icon: Icon, title, body }) => (
              <div key={title}>
                <Icon className="text-primary size-6" aria-hidden />
                <h3 className="mt-3 font-semibold">{title}</h3>
                <p className="text-muted-foreground mt-1 text-sm leading-relaxed">
                  {body}
                </p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Recent reports */}
      {recent.length > 0 ? (
        <section className="mx-auto max-w-7xl px-4 py-16 sm:px-6">
          <div className="mb-8 flex flex-wrap items-end justify-between gap-3">
            <div>
              <h2 className="text-2xl font-bold sm:text-3xl">Latest reports</h2>
              <p className="text-muted-foreground mt-1">
                Real issues raised by citizens across Bangladesh.
              </p>
            </div>
            <Button variant="outline" asChild>
              <Link href="/reports">
                View all reports <ArrowRight className="size-4" />
              </Link>
            </Button>
          </div>
          <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            {recent.map((r) => (
              <ReportCard key={r.id} report={r} />
            ))}
          </div>
        </section>
      ) : null}

      {/* CTA */}
      <section className="border-t bg-brand-gradient">
        <div className="mx-auto max-w-4xl px-4 py-16 text-center text-white sm:px-6">
          <h2 className="text-3xl font-bold text-balance">
            Your report is a public record. Make it count.
          </h2>
          <p className="mx-auto mt-3 max-w-xl text-white/85">
            Join citizens across Bangladesh documenting the issues that matter —
            and pressing for the fixes that follow.
          </p>
          <div className="mt-8 flex flex-wrap items-center justify-center gap-3">
            <Button size="lg" variant="secondary" asChild>
              <Link href="/signup">
                <FileText className="size-4.5" /> Create a free account
              </Link>
            </Button>
            <Button
              size="lg"
              variant="outline"
              className="border-white/40 bg-transparent text-white hover:bg-white/10 hover:text-white"
              asChild
            >
              <Link href="/reports">Explore reports</Link>
            </Button>
          </div>
        </div>
      </section>
    </>
  );
}
