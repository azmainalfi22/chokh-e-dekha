import type { Metadata } from "next";
import Link from "next/link";
import { List } from "lucide-react";
import { createClient } from "@/lib/supabase/server";
import { Button } from "@/components/ui/button";
import { ReportsMap } from "@/components/map/reports-map";
import type { MapPoint } from "@/components/map/reports-map-inner";

export const metadata: Metadata = {
  title: "Reports Map",
  description:
    "Explore civic issue reports across Bangladesh on an interactive map.",
};

export default async function PublicMapPage() {
  const supabase = await createClient();
  const { data } = await supabase
    .from("reports")
    .select("id, title, status, latitude, longitude")
    .eq("is_approved", true)
    .not("latitude", "is", null)
    .not("longitude", "is", null)
    .order("created_at", { ascending: false })
    .limit(1000);

  const points: MapPoint[] = (data ?? [])
    .filter((r) => r.latitude != null && r.longitude != null)
    .map((r) => ({
      id: r.id,
      lat: r.latitude!,
      lng: r.longitude!,
      title: r.title,
      status: r.status,
    }));

  return (
    <div className="mx-auto w-full max-w-7xl space-y-4 px-4 py-8 sm:px-6">
      <div className="flex flex-wrap items-end justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold tracking-tight sm:text-3xl">
            Reports map
          </h1>
          <p className="text-muted-foreground mt-1 text-sm">
            {points.length} geo-tagged report{points.length === 1 ? "" : "s"}{" "}
            across Bangladesh. Tap “Near me” to jump to your area, or toggle the
            heatmap to see where problems cluster.
          </p>
        </div>
        <Button variant="outline" asChild>
          <Link href="/reports">
            <List className="size-4" /> List view
          </Link>
        </Button>
      </div>
      <div className="overflow-hidden rounded-xl border shadow-sm">
        <ReportsMap points={points} />
      </div>
    </div>
  );
}
