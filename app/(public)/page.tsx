import Link from "next/link";
import {
  ArrowRight,
  Camera,
  CheckCircle2,
  Eye,
  Gauge,
  MapPin,
  Megaphone,
  MessagesSquare,
  Scale,
  ShieldCheck,
} from "lucide-react";
import { createClient } from "@/lib/supabase/server";
import { APP_NAME_BN, APP_TAGLINE } from "@/lib/constants";
import { Button } from "@/components/ui/button";
import {
  ReportCard,
  type ReportCardData,
} from "@/components/reports/report-card";

export default async function HomePage() {
  const supabase = await createClient();

  const [totalRes, resolvedRes, cityRes, recentRes] = await Promise.all([
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
    { value: resolved, label: "Issues resolved" },
    { value: cities || 0, label: "Cities covered" },
  ];

  return (
    <>
      {/* Hero */}
      <section className="relative overflow-hidden">
        <div
          className="from-brand-orange/10 pointer-events-none absolute inset-0 -z-10 bg-gradient-to-b to-transparent"
          aria-hidden
        />
        <div className="mx-auto flex max-w-5xl flex-col items-center px-4 py-20 text-center sm:px-6 sm:py-28">
          <span className="bg-card mb-5 inline-flex items-center gap-2 rounded-full border px-3 py-1 text-sm shadow-sm">
            <Eye className="text-primary size-4" aria-hidden />
            <span className="font-bengali font-semibold">{APP_NAME_BN}</span>
            <span className="text-muted-foreground">· {APP_TAGLINE}</span>
          </span>
          <h1 className="max-w-3xl text-4xl font-extrabold tracking-tight text-balance sm:text-6xl">
            See it. Report it.{" "}
            <span className="text-brand-gradient">Fix it.</span>
          </h1>
          <p className="text-muted-foreground mt-5 max-w-2xl text-lg text-pretty">
            Report civic issues with photos and precise locations, follow their
            progress in public, and hold city authorities accountable — across
            every city corporation in Bangladesh.
          </p>
          <div className="mt-8 flex flex-wrap items-center justify-center gap-3">
            <Button
              size="lg"
              className="bg-brand-gradient glow-brand border-0 text-white hover:opacity-90"
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

          {/* Live stats */}
          <dl className="mt-14 grid w-full max-w-2xl grid-cols-3 gap-4">
            {stats.map((s) => (
              <div
                key={s.label}
                className="bg-card rounded-xl border p-5 shadow-sm"
              >
                <dt className="text-brand-gradient text-3xl font-extrabold sm:text-4xl">
                  {s.value.toLocaleString("en-US")}
                </dt>
                <dd className="text-muted-foreground mt-1 text-xs font-medium tracking-wide uppercase">
                  {s.label}
                </dd>
              </div>
            ))}
          </dl>
        </div>
      </section>

      {/* How it works */}
      <section className="border-t bg-muted/30">
        <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6">
          <div className="text-center">
            <h2 className="text-2xl font-bold sm:text-3xl">How it works</h2>
            <p className="text-muted-foreground mt-2">
              Three steps from spotting a problem to public accountability.
            </p>
          </div>
          <div className="mt-10 grid gap-6 md:grid-cols-3">
            {[
              {
                icon: Camera,
                title: "1. Capture & pin",
                body: "Snap a photo, drop a map pin or use GPS, and describe the issue. Photos are compressed on your device before upload.",
              },
              {
                icon: ShieldCheck,
                title: "2. We review & route",
                body: "Moderators approve genuine reports so they appear on the public feed, mapped and tagged to the right city corporation.",
              },
              {
                icon: Gauge,
                title: "3. Track to resolution",
                body: "Each report carries an SLA deadline. Follow its Pending → In Progress → Resolved journey, and get notified at every change.",
              },
            ].map(({ icon: Icon, title, body }) => (
              <div
                key={title}
                className="bg-card rounded-2xl border p-6 shadow-sm"
              >
                <span className="bg-brand-gradient inline-flex size-11 items-center justify-center rounded-xl text-white">
                  <Icon className="size-5" aria-hidden />
                </span>
                <h3 className="mt-4 text-lg font-semibold">{title}</h3>
                <p className="text-muted-foreground mt-1.5 text-sm">{body}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Features */}
      <section className="mx-auto max-w-6xl px-4 py-16 sm:px-6">
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          {[
            {
              icon: MapPin,
              title: "Interactive map",
              body: "Every geo-tagged report on an OpenStreetMap view, with density heatmaps.",
            },
            {
              icon: MessagesSquare,
              title: "Community pressure",
              body: "Endorse, comment, bookmark and share — turn one voice into collective documentation.",
            },
            {
              icon: Scale,
              title: "RTI wizard",
              body: "Generate a Right to Information Act 2009 letter in English or Bangla, ready to print.",
            },
            {
              icon: CheckCircle2,
              title: "SLA transparency",
              body: "Overdue reports are flagged automatically — neglect becomes visible.",
            },
          ].map(({ icon: Icon, title, body }) => (
            <div key={title} className="rounded-xl border p-5">
              <Icon className="text-primary size-6" aria-hidden />
              <h3 className="mt-3 font-semibold">{title}</h3>
              <p className="text-muted-foreground mt-1 text-sm">{body}</p>
            </div>
          ))}
        </div>
      </section>

      {/* Recent reports */}
      {recent.length > 0 ? (
        <section className="border-t bg-muted/30">
          <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6">
            <div className="mb-8 flex items-end justify-between">
              <div>
                <h2 className="text-2xl font-bold sm:text-3xl">
                  Latest reports
                </h2>
                <p className="text-muted-foreground mt-1">
                  Real issues raised by citizens across Bangladesh.
                </p>
              </div>
              <Button variant="ghost" asChild>
                <Link href="/reports">
                  View all <ArrowRight className="size-4" />
                </Link>
              </Button>
            </div>
            <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
              {recent.map((r) => (
                <ReportCard key={r.id} report={r} />
              ))}
            </div>
          </div>
        </section>
      ) : null}

      {/* CTA */}
      <section className="mx-auto max-w-5xl px-4 py-20 sm:px-6">
        <div className="bg-brand-gradient glow-brand relative overflow-hidden rounded-3xl px-8 py-14 text-center text-white">
          <h2 className="text-3xl font-extrabold text-balance sm:text-4xl">
            Your city is watching. Make it count.
          </h2>
          <p className="mx-auto mt-3 max-w-xl text-white/90">
            Join thousands of citizens documenting the issues that matter — and
            pushing for the fixes that follow.
          </p>
          <div className="mt-8 flex flex-wrap items-center justify-center gap-3">
            <Button size="lg" variant="secondary" asChild>
              <Link href="/signup">Create a free account</Link>
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
