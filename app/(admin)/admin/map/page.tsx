import type { Metadata } from "next";
import { createClient } from "@/lib/supabase/server";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { ReportsMap, } from "@/components/map/reports-map";
import type { MapPoint } from "@/components/map/reports-map-inner";

export const metadata: Metadata = { title: "Admin · Map" };

export default async function AdminMapPage() {
  const supabase = await createClient();
  const { data } = await supabase
    .from("reports")
    .select("id, title, status, latitude, longitude")
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
    <div className="mx-auto max-w-6xl space-y-4">
      <div>
        <h1 className="text-2xl font-bold">Reports map</h1>
        <p className="text-muted-foreground text-sm">
          {points.length} geo-tagged report{points.length === 1 ? "" : "s"}.
          Toggle the heatmap to see density.
        </p>
      </div>
      <Card className="overflow-hidden py-0">
        <CardHeader className="sr-only">
          <CardTitle>Map</CardTitle>
        </CardHeader>
        <CardContent className="p-0">
          <ReportsMap points={points} />
        </CardContent>
      </Card>
    </div>
  );
}
