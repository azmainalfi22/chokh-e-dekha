"use client";

import { useEffect, useRef, useState } from "react";
import { MapContainer, Marker, Popup, TileLayer, useMap } from "react-leaflet";
import L from "leaflet";
import "leaflet.heat";
import { Flame } from "lucide-react";
import { Button } from "@/components/ui/button";
import { DEFAULT_MAP_CENTER, DEFAULT_MAP_ZOOM } from "@/lib/constants";
import { defaultIcon } from "./leaflet-icon";

export type MapPoint = {
  id: number;
  lat: number;
  lng: number;
  title: string;
  status: string;
};

function HeatLayer({ points, on }: { points: MapPoint[]; on: boolean }) {
  const map = useMap();
  const layerRef = useRef<L.Layer | null>(null);

  useEffect(() => {
    if (layerRef.current) {
      map.removeLayer(layerRef.current);
      layerRef.current = null;
    }
    if (on && points.length) {
      // leaflet.heat attaches L.heatLayer at runtime
      const heat = (
        L as unknown as {
          heatLayer: (
            latlngs: [number, number, number][],
            opts: Record<string, unknown>
          ) => L.Layer;
        }
      ).heatLayer(
        points.map((p) => [p.lat, p.lng, 0.8]),
        { radius: 28, blur: 18, maxZoom: 15 }
      );
      heat.addTo(map);
      layerRef.current = heat;
    }
    return () => {
      if (layerRef.current) map.removeLayer(layerRef.current);
    };
  }, [map, points, on]);

  return null;
}

export default function ReportsMapInner({ points }: { points: MapPoint[] }) {
  const [heatmap, setHeatmap] = useState(false);

  return (
    <div className="relative">
      <MapContainer
        center={DEFAULT_MAP_CENTER}
        zoom={DEFAULT_MAP_ZOOM}
        className="h-[32rem] w-full"
        scrollWheelZoom
      >
        <TileLayer
          attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
          url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        />
        {!heatmap
          ? points.map((p) => (
              <Marker key={p.id} position={[p.lat, p.lng]} icon={defaultIcon}>
                <Popup>
                  <a
                    href={`/reports/${p.id}`}
                    className="font-medium underline"
                  >
                    {p.title}
                  </a>
                  <br />
                  <span className="capitalize">{p.status.replace("_", " ")}</span>
                </Popup>
              </Marker>
            ))
          : null}
        <HeatLayer points={points} on={heatmap} />
      </MapContainer>

      <div className="absolute top-3 right-3 z-[1000]">
        <Button
          type="button"
          size="sm"
          variant={heatmap ? "default" : "secondary"}
          className={heatmap ? "bg-brand-gradient border-0 text-white" : "shadow-md"}
          onClick={() => setHeatmap((v) => !v)}
          aria-pressed={heatmap}
        >
          <Flame className="size-4" aria-hidden />
          Heatmap
        </Button>
      </div>
    </div>
  );
}
