import type { Metadata } from "next";
import { ShieldCheck } from "lucide-react";

export const metadata: Metadata = { title: "Admin" };

/** Placeholder — replaced by the Command Centre in phase 5. */
export default function AdminHomePage() {
  return (
    <div className="mx-auto flex max-w-3xl flex-col items-center gap-3 px-4 py-24 text-center">
      <span className="bg-brand-gradient flex size-12 items-center justify-center rounded-xl text-white">
        <ShieldCheck className="size-6" aria-hidden />
      </span>
      <h1 className="text-2xl font-bold">Admin Command Centre</h1>
      <p className="text-muted-foreground text-sm">
        The full dashboard — KPIs, SLA alerts, report queue, map — arrives in
        phase 5 of the build.
      </p>
    </div>
  );
}
