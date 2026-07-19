import type { Metadata } from "next";
import { BookOpenCheck } from "lucide-react";
import { GuidesList } from "./guides-list";

export const metadata: Metadata = {
  title: "Service Guides",
  description:
    "Step-by-step guides to Bangladesh's essential public services — documents, fees, timelines and official portals for birth registration, NID, passport, land mutation and more.",
};

export default function GuidesPage() {
  return (
    <>
      <section className="civic-mesh border-b">
        <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6">
          <span className="text-primary inline-flex items-center gap-2 text-sm font-semibold tracking-wider uppercase">
            <BookOpenCheck className="size-4.5" aria-hidden />
            Public service guides
          </span>
          <h1 className="mt-2 text-3xl font-semibold tracking-tight sm:text-4xl">
            Get government services done, step by step
          </h1>
          <p className="text-muted-foreground mt-3 max-w-2xl">
            The documents, fees, timelines and official portals for the
            services citizens use most — so one trip is enough. Every guide
            links to the authoritative government site.
          </p>
        </div>
      </section>
      <GuidesList />
    </>
  );
}
