import Link from "next/link";
import { ArrowRight, Camera, MapPin, Megaphone } from "lucide-react";
import { Button } from "@/components/ui/button";
import { APP_NAME_BN, APP_TAGLINE } from "@/lib/constants";

export default function HomePage() {
  return (
    <section className="mx-auto flex max-w-5xl flex-col items-center px-4 py-20 text-center sm:px-6 sm:py-28">
      <p className="font-bengali text-brand-gradient text-2xl font-semibold sm:text-3xl">
        {APP_NAME_BN}
      </p>
      <h1 className="mt-3 max-w-3xl text-4xl font-extrabold tracking-tight text-balance sm:text-6xl">
        See it. Report it. <span className="text-brand-gradient">Fix it.</span>
      </h1>
      <p className="text-muted-foreground mt-5 max-w-2xl text-lg text-pretty">
        {APP_TAGLINE}. Report civic issues with photos and precise locations,
        follow their progress in public, and hold city authorities accountable
        — across every city corporation in Bangladesh.
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

      <div className="mt-16 grid w-full gap-4 sm:grid-cols-3">
        {[
          {
            icon: Camera,
            title: "Photo evidence",
            body: "Attach compressed photos so authorities see exactly what you see.",
          },
          {
            icon: MapPin,
            title: "Pinned location",
            body: "Drop a pin or use GPS — every report lands on the public map.",
          },
          {
            icon: Megaphone,
            title: "Public pressure",
            body: "Reports stay visible with SLA deadlines until they are resolved.",
          },
        ].map(({ icon: Icon, title, body }) => (
          <div
            key={title}
            className="bg-card rounded-xl border p-6 text-left shadow-sm"
          >
            <span className="bg-brand-gradient inline-flex size-10 items-center justify-center rounded-lg text-white">
              <Icon className="size-5" aria-hidden />
            </span>
            <h2 className="mt-4 font-semibold">{title}</h2>
            <p className="text-muted-foreground mt-1 text-sm">{body}</p>
          </div>
        ))}
      </div>
    </section>
  );
}
