import Link from "next/link";
import {
  ArrowRight,
  Camera,
  CheckCircle2,
  FileText,
  Gauge,
  Landmark,
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
      <section className="civic-mesh relative overflow-hidden border-b">
        <div
          className="dot-grid pointer-events-none absolute inset-0 opacity-60 [mask-image:radial-gradient(60rem_40rem_at_70%_-10%,black,transparent)]"
          aria-hidden
        />
        <div className="relative mx-auto grid max-w-7xl gap-12 px-4 py-16 sm:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:items-center lg:py-24">
          <div>
            <span className="border-primary/30 bg-primary/5 text-primary inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-medium">
              <span className="relative flex size-1.5" aria-hidden>
                <span className="bg-primary absolute inline-flex size-full animate-ping rounded-full opacity-60" />
                <span className="bg-primary relative inline-flex size-1.5 rounded-full" />
              </span>
              Public civic accountability · Bangladesh
            </span>
            <h1 className="mt-6 text-[2.6rem]/[1.08] font-semibold text-balance sm:text-6xl/[1.05]">
              See it. Report it.
              <br />
              <span className="text-primary italic">
                Hold the city to account
              </span>
              <span className="text-brand-red">.</span>
            </h1>
            <p className="text-muted-foreground mt-6 max-w-xl text-lg leading-relaxed text-pretty">
              From broken roads to overflowing bins — file it with a photo and a
              map pin. Every report is public, routed to the responsible
              authority, tracked against a deadline, and only closed when the
              reporter confirms the fix.
            </p>
            <div className="mt-8 flex flex-wrap items-center gap-3">
              <Button
                size="lg"
                className="bg-brand-gradient glow-brand border-0 text-white hover:opacity-95"
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
            <ul className="text-muted-foreground mt-8 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs font-medium">
              <li className="flex items-center gap-1.5">
                <ShieldCheck className="text-primary size-4" aria-hidden />
                Publicly logged
              </li>
              <li className="flex items-center gap-1.5">
                <Landmark className="text-primary size-4" aria-hidden />
                Routed to the right authority
              </li>
              <li className="flex items-center gap-1.5">
                <Gauge className="text-primary size-4" aria-hidden />
                Deadline-tracked
              </li>
              <li className="flex items-center gap-1.5">
                <CheckCircle2 className="text-primary size-4" aria-hidden />
                Citizen-verified fixes
              </li>
            </ul>
            <p className="text-muted-foreground font-bengali mt-6 text-sm">
              {APP_NAME_BN} — আপনার চোখ, আপনার কণ্ঠস্বর, আপনার শহর
            </p>
          </div>

          {/* Stats panel — national dashboard */}
          <div className="relative">
            <div className="hero-panel shadow-elevated relative overflow-hidden rounded-2xl p-6 text-white sm:p-7">
              <div className="ribbon-bd absolute inset-x-0 top-0 h-1" aria-hidden />
              <div className="flex items-center justify-between">
                <p className="text-xs font-semibold tracking-widest text-white/70 uppercase">
                  Live platform activity
                </p>
                <span className="rounded-full bg-white/10 px-2.5 py-1 text-[10px] font-semibold tracking-wider text-white/80 uppercase">
                  Public record
                </span>
              </div>
              <dl className="mt-5 grid grid-cols-2 gap-3">
                {stats.map((s) => (
                  <div
                    key={s.label}
                    className="rounded-xl border border-white/12 bg-white/[0.07] p-4 backdrop-blur-sm"
                  >
                    <dt className="font-display text-4xl font-semibold tabular-nums">
                      <CountUp value={s.value} />
                    </dt>
                    <dd className="mt-1 text-xs font-medium text-white/75">
                      {s.label}
                    </dd>
                  </div>
                ))}
              </dl>
              <div className="mt-4 flex items-center gap-2.5 rounded-xl border border-white/12 bg-white/[0.05] p-3">
                <Gauge className="size-4 shrink-0 text-white/85" aria-hidden />
                <p className="text-xs leading-relaxed text-white/80">
                  Every approved report runs on a{" "}
                  <span className="font-semibold text-white">
                    7-day response deadline
                  </span>{" "}
                  — overdue cases are flagged publicly.
                </p>
              </div>
            </div>
            {/* floating trust-loop chip */}
            <div className="bg-card shadow-elevated absolute -bottom-5 left-6 hidden items-center gap-2 rounded-full border py-2 pr-4 pl-2 sm:flex">
              <span className="bg-status-resolved/15 text-status-resolved flex size-7 items-center justify-center rounded-full">
                <CheckCircle2 className="size-4" aria-hidden />
              </span>
              <p className="text-xs font-medium">
                Confirmed fixed by the reporter
              </p>
            </div>
          </div>
        </div>
      </section>

      {/* How it works */}
      <section className="mx-auto max-w-7xl px-4 py-20 sm:px-6">
        <div className="max-w-2xl">
          <p className="text-primary text-sm font-semibold tracking-wider uppercase">
            The process
          </p>
          <h2 className="mt-2 text-3xl font-semibold sm:text-4xl">
            How a report becomes a fix
          </h2>
          <p className="text-muted-foreground mt-3">
            Four steps from spotting a problem to a verified resolution — each
            one public, timestamped, and on the record.
          </p>
        </div>
        <div className="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          {[
            {
              n: "১",
              icon: Camera,
              title: "Capture & pin",
              body: "Add a photo, drop a map pin or use GPS, and describe the issue. Photos are compressed on your device before upload.",
            },
            {
              n: "২",
              icon: Landmark,
              title: "Routed to the right desk",
              body: "Each report is automatically routed to the responsible body — city corporation, WASA, power distributor or police.",
            },
            {
              n: "৩",
              icon: Gauge,
              title: "Tracked on a deadline",
              body: "Approved reports run Pending → In Progress → Resolved against a response deadline. Overdue cases are flagged publicly.",
            },
            {
              n: "৪",
              icon: CheckCircle2,
              title: "Verified by you",
              body: "“Resolved” only counts when the reporter confirms it. Dispute a fake fix and the report reopens with the clock running.",
            },
          ].map(({ n, icon: Icon, title, body }) => (
            <Tilt
              key={title}
              className="bg-card card-lift group relative overflow-hidden rounded-xl border p-6"
            >
              <span
                className="font-bengali text-primary/[0.07] group-hover:text-primary/[0.13] pointer-events-none absolute -top-3 right-2 text-[5.5rem] font-bold transition-colors"
                aria-hidden
              >
                {n}
              </span>
              <span className="bg-primary/10 text-primary relative inline-flex size-10 items-center justify-center rounded-md">
                <Icon className="size-5" aria-hidden />
              </span>
              <h3 className="relative mt-4 text-lg font-semibold">{title}</h3>
              <p className="text-muted-foreground relative mt-1.5 text-sm leading-relaxed">
                {body}
              </p>
            </Tilt>
          ))}
        </div>
      </section>

      {/* Features */}
      <section className="border-y bg-card">
        <div className="mx-auto max-w-7xl px-4 py-16 sm:px-6">
          <div className="grid gap-x-8 gap-y-8 sm:grid-cols-2 lg:grid-cols-4">
            {[
              {
                icon: MapPin,
                title: "Interactive map",
                body: "Every geo-tagged report on an OpenStreetMap view, with density heatmaps for administrators.",
                href: "/map",
              },
              {
                icon: MessagesSquare,
                title: "Community weight",
                body: "Endorse, comment and share — turning one report into collective, visible documentation.",
                href: "/reports",
              },
              {
                icon: Scale,
                title: "RTI wizard",
                body: "Generate a Right to Information Act 2009 application in English or Bangla, ready to print.",
                href: "/rti",
              },
              {
                icon: Landmark,
                title: "Authority directory",
                body: "Who owns which problem — city corporations, WASA, power and police, with hotlines that actually connect.",
                href: "/authorities",
              },
            ].map(({ icon: Icon, title, body, href }) => (
              <Link key={title} href={href} className="group">
                <span className="bg-primary/10 text-primary group-hover:bg-primary group-hover:text-primary-foreground inline-flex size-11 items-center justify-center rounded-lg transition-colors">
                  <Icon className="size-5.5" aria-hidden />
                </span>
                <h3 className="group-hover:text-primary mt-3 font-semibold transition-colors">
                  {title}
                </h3>
                <p className="text-muted-foreground mt-1 text-sm leading-relaxed">
                  {body}
                </p>
              </Link>
            ))}
          </div>
        </div>
      </section>

      {/* Recent reports */}
      {recent.length > 0 ? (
        <section className="mx-auto max-w-7xl px-4 py-16 sm:px-6">
          <div className="mb-8 flex flex-wrap items-end justify-between gap-3">
            <div>
              <p className="text-primary text-sm font-semibold tracking-wider uppercase">
                Live from the streets
              </p>
              <h2 className="mt-2 text-3xl font-semibold sm:text-4xl">
                Latest reports
              </h2>
              <p className="text-muted-foreground mt-2">
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
      <section className="hero-panel relative overflow-hidden border-t">
        <div className="ribbon-bd absolute inset-x-0 top-0 h-1" aria-hidden />
        <div
          className="dot-grid absolute inset-0 opacity-20 invert"
          aria-hidden
        />
        <div className="relative mx-auto max-w-4xl px-4 py-20 text-center text-white sm:px-6">
          <h2 className="text-3xl font-semibold text-balance sm:text-4xl">
            Your report is a public record.
            <br />
            <span className="italic">Make it count</span>
            <span className="text-brand-red">.</span>
          </h2>
          <p className="mx-auto mt-4 max-w-xl text-white/85">
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
