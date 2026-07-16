"use client";

import Link from "next/link";
import { useEffect, useState, useTransition } from "react";
import { ExternalLink, Loader2, ThumbsUp, Users } from "lucide-react";
import { toast } from "sonner";
import { findNearbyReports } from "@/lib/actions/reports";
import { toggleEndorsement } from "@/lib/actions/engagement";
import { STATUS_LABELS, type Status } from "@/lib/constants";
import { Button } from "@/components/ui/button";

type NearbyReport = {
  id: number;
  title: string;
  category: string;
  status: string;
  endorse_count: number;
  distance_m: number;
};

/**
 * Duplicate detection: when a pin + category are set, surface nearby similar
 * reports so the citizen can endorse an existing one instead of filing a
 * duplicate (a hallmark of mature civic platforms).
 */
export function NearbyDuplicates({
  lat,
  lng,
  category,
}: {
  lat: number | null;
  lng: number | null;
  category: string | undefined;
}) {
  const [reports, setReports] = useState<NearbyReport[]>([]);
  const [loading, setLoading] = useState(false);
  const [endorsed, setEndorsed] = useState<Set<number>>(new Set());
  const [pending, startTransition] = useTransition();

  // Stable key: round coords to ~1 m so sub-metre float jitter from the map
  // doesn't restart the debounce and starve the fetch.
  const key =
    lat != null && lng != null
      ? `${lat.toFixed(5)}|${lng.toFixed(5)}|${category ?? ""}`
      : null;

  useEffect(() => {
    if (!key) {
      setReports([]);
      setLoading(false);
      return;
    }
    const [k1, k2, k3] = key.split("|");
    const qLat = Number(k1);
    const qLng = Number(k2);
    const qCat = k3 || null;

    let cancelled = false;
    setLoading(true);
    const t = setTimeout(async () => {
      try {
        const data = await findNearbyReports(qLat, qLng, qCat);
        if (!cancelled) setReports(data);
      } catch {
        if (!cancelled) setReports([]);
      } finally {
        if (!cancelled) setLoading(false);
      }
    }, 400);
    return () => {
      cancelled = true;
      clearTimeout(t);
    };
  }, [key]);

  function endorse(id: number) {
    startTransition(async () => {
      const result = await toggleEndorsement(id);
      if (!result.ok) {
        toast.error(result.error);
        return;
      }
      setEndorsed((p) => new Set(p).add(id));
      toast.success("Endorsed — thanks for adding your voice!");
    });
  }

  if (lat == null || lng == null) return null;
  if (!loading && reports.length === 0) return null;

  return (
    <div className="border-status-progress/25 bg-status-progress/5 rounded-lg border p-4">
      <div className="flex items-center gap-2">
        <Users className="text-status-progress size-4.5" aria-hidden />
        <h3 className="text-sm font-semibold">
          {loading
            ? "Checking for similar reports nearby…"
            : `${reports.length} similar report${reports.length === 1 ? "" : "s"} within 500 m`}
        </h3>
      </div>
      {loading ? (
        <div className="text-muted-foreground mt-3 flex items-center gap-2 text-sm">
          <Loader2 className="size-4 animate-spin" /> Searching…
        </div>
      ) : (
        <>
          <p className="text-muted-foreground mt-1 text-xs">
            Already reported? Endorse it instead — that carries more weight than
            a duplicate.
          </p>
          <ul className="mt-3 space-y-2">
            {reports.slice(0, 4).map((r) => (
              <li
                key={r.id}
                className="bg-card flex items-center gap-3 rounded-md border p-2.5"
              >
                <div className="min-w-0 flex-1">
                  <p className="truncate text-sm font-medium">{r.title}</p>
                  <p className="text-muted-foreground text-xs">
                    {Math.round(r.distance_m)} m away ·{" "}
                    {STATUS_LABELS[r.status as Status] ?? r.status} ·{" "}
                    {r.endorse_count + (endorsed.has(r.id) ? 1 : 0)} endorsements
                  </p>
                </div>
                <Button
                  type="button"
                  size="sm"
                  variant={endorsed.has(r.id) ? "secondary" : "outline"}
                  disabled={pending || endorsed.has(r.id)}
                  onClick={() => endorse(r.id)}
                >
                  <ThumbsUp className="size-3.5" aria-hidden />
                  {endorsed.has(r.id) ? "Endorsed" : "Endorse"}
                </Button>
                <Button type="button" size="icon" variant="ghost" asChild>
                  <Link
                    href={`/reports/${r.id}`}
                    target="_blank"
                    aria-label="Open report"
                  >
                    <ExternalLink className="size-4" aria-hidden />
                  </Link>
                </Button>
              </li>
            ))}
          </ul>
        </>
      )}
    </div>
  );
}
