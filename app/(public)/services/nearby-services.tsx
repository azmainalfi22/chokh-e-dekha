"use client";

import { useState } from "react";
import {
  Building2,
  Flame,
  Hospital,
  Loader2,
  MapPin,
  Navigation,
  Phone,
  Shield,
} from "lucide-react";
import { toast } from "sonner";
import {
  findNearby,
  labelFor,
  type NearbyPlace,
  type ServiceKind,
} from "@/lib/overpass";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { ServicesMap } from "@/components/map/services-map";
import { cn } from "@/lib/utils";

const KIND_META: Record<
  ServiceKind,
  { icon: typeof Shield; color: string }
> = {
  police: { icon: Shield, color: "text-status-progress" },
  hospital: { icon: Hospital, color: "text-status-breach" },
  fire: { icon: Flame, color: "text-[#E8830C]" },
  pharmacy: { icon: Building2, color: "text-status-resolved" },
};

export function NearbyServices() {
  const [loading, setLoading] = useState(false);
  const [origin, setOrigin] = useState<{ lat: number; lng: number } | null>(
    null
  );
  const [places, setPlaces] = useState<NearbyPlace[]>([]);
  const [error, setError] = useState<string | null>(null);

  async function locate() {
    if (!navigator.geolocation) {
      toast.error("Geolocation isn't supported on this device");
      return;
    }
    setLoading(true);
    setError(null);
    navigator.geolocation.getCurrentPosition(
      async (pos) => {
        const o = { lat: pos.coords.latitude, lng: pos.coords.longitude };
        setOrigin(o);
        try {
          const found = await findNearby(o, ["police", "hospital", "fire"]);
          setPlaces(found);
          if (found.length === 0) {
            setError("No mapped services found within 4 km. Try 999 directly.");
          }
        } catch {
          setError(
            "Couldn't reach the map service. Please dial 999 for emergencies."
          );
        } finally {
          setLoading(false);
        }
      },
      () => {
        setLoading(false);
        toast.error("Location permission denied — enable it to find services");
      },
      { enableHighAccuracy: true, timeout: 12000 }
    );
  }

  return (
    <div className="space-y-5">
      <div className="flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 className="text-xl font-bold">Find help near you</h2>
          <p className="text-muted-foreground text-sm">
            Live locations of the nearest police, hospitals and fire service —
            from OpenStreetMap.
          </p>
        </div>
        <Button
          onClick={locate}
          disabled={loading}
          className="bg-brand-gradient border-0 text-white hover:opacity-95"
        >
          {loading ? (
            <Loader2 className="size-4 animate-spin" />
          ) : (
            <Navigation className="size-4" />
          )}
          {origin ? "Refresh" : "Use my location"}
        </Button>
      </div>

      {error ? (
        <Card className="border-status-pending/30 bg-status-pending/5">
          <CardContent className="text-muted-foreground py-3 text-sm">
            {error}
          </CardContent>
        </Card>
      ) : null}

      {origin && places.length > 0 ? (
        <div className="grid gap-5 lg:grid-cols-2">
          <div className="overflow-hidden rounded-lg border">
            <ServicesMap origin={origin} places={places} />
          </div>
          <ul className="space-y-2">
            {places.slice(0, 8).map((p) => {
              const meta = KIND_META[p.kind];
              const Icon = meta.icon;
              return (
                <li
                  key={`${p.kind}-${p.id}`}
                  className="bg-card flex items-center gap-3 rounded-lg border p-3"
                >
                  <span
                    className={cn(
                      "bg-muted flex size-9 shrink-0 items-center justify-center rounded-md",
                      meta.color
                    )}
                  >
                    <Icon className="size-4.5" aria-hidden />
                  </span>
                  <div className="min-w-0 flex-1">
                    <p className="truncate text-sm font-medium">{p.name}</p>
                    <p className="text-muted-foreground text-xs">
                      {labelFor(p.kind)} · {p.distanceKm.toFixed(1)} km away
                      {p.address ? ` · ${p.address}` : ""}
                    </p>
                  </div>
                  <div className="flex items-center gap-1">
                    {p.phone ? (
                      <Button size="icon" variant="ghost" asChild>
                        <a href={`tel:${p.phone}`} aria-label={`Call ${p.name}`}>
                          <Phone className="size-4" />
                        </a>
                      </Button>
                    ) : null}
                    <Button size="icon" variant="ghost" asChild>
                      <a
                        href={`https://www.openstreetmap.org/directions?from=${origin.lat},${origin.lng}&to=${p.lat},${p.lng}`}
                        target="_blank"
                        rel="noreferrer"
                        aria-label={`Directions to ${p.name}`}
                      >
                        <MapPin className="size-4" />
                      </a>
                    </Button>
                  </div>
                </li>
              );
            })}
          </ul>
        </div>
      ) : null}

      {!origin && !loading ? (
        <Card className="border-dashed">
          <CardContent className="text-muted-foreground flex flex-col items-center gap-2 py-10 text-center text-sm">
            <MapPin className="size-8" aria-hidden />
            Tap &ldquo;Use my location&rdquo; to find the nearest emergency
            services. Your location is used only in your browser — never stored.
          </CardContent>
        </Card>
      ) : null}
    </div>
  );
}
