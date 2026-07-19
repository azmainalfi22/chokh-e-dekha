/**
 * Live nearby-services lookup via the OpenStreetMap Overpass API.
 * Runs in the browser (Overpass sends CORS headers). We never store or
 * fabricate station data — results come straight from OSM, so an emergency
 * address/phone is real map data, not a guess.
 */

export type ServiceKind = "police" | "hospital" | "fire" | "pharmacy";

export type NearbyPlace = {
  id: number;
  name: string;
  kind: ServiceKind;
  lat: number;
  lng: number;
  distanceKm: number;
  phone?: string;
  address?: string;
};

const OVERPASS_ENDPOINTS = [
  "https://overpass-api.de/api/interpreter",
  "https://overpass.kumi.systems/api/interpreter",
];

const KIND_QUERY: Record<ServiceKind, string> = {
  police: 'node["amenity"="police"]',
  hospital: 'node["amenity"="hospital"]',
  fire: 'node["amenity"="fire_station"]',
  pharmacy: 'node["amenity"="pharmacy"]',
};

/** Haversine distance in km. */
export function distanceKm(
  a: { lat: number; lng: number },
  b: { lat: number; lng: number }
): number {
  const R = 6371;
  const dLat = ((b.lat - a.lat) * Math.PI) / 180;
  const dLng = ((b.lng - a.lng) * Math.PI) / 180;
  const s =
    Math.sin(dLat / 2) ** 2 +
    Math.cos((a.lat * Math.PI) / 180) *
      Math.cos((b.lat * Math.PI) / 180) *
      Math.sin(dLng / 2) ** 2;
  return R * 2 * Math.atan2(Math.sqrt(s), Math.sqrt(1 - s));
}

export async function findNearby(
  origin: { lat: number; lng: number },
  kinds: ServiceKind[],
  radiusMeters = 4000,
  limitPerKind = 6
): Promise<NearbyPlace[]> {
  const parts = kinds
    .map(
      (k) =>
        `${KIND_QUERY[k]}(around:${radiusMeters},${origin.lat},${origin.lng});`
    )
    .join("\n");
  const query = `[out:json][timeout:20];(${parts});out body ${limitPerKind * kinds.length};`;

  let data: { elements?: OverpassElement[] } | null = null;
  for (const endpoint of OVERPASS_ENDPOINTS) {
    try {
      const res = await fetch(endpoint, {
        method: "POST",
        headers: { "Content-Type": "text/plain;charset=UTF-8" },
        body: query,
      });
      if (!res.ok) continue;
      data = await res.json();
      break;
    } catch {
      // try next mirror
    }
  }
  if (!data?.elements) throw new Error("nearby lookup failed");

  const kindOf = (tags: Record<string, string> = {}): ServiceKind | null => {
    if (tags.amenity === "police") return "police";
    if (tags.amenity === "hospital") return "hospital";
    if (tags.amenity === "fire_station") return "fire";
    if (tags.amenity === "pharmacy") return "pharmacy";
    return null;
  };

  const seen = new Map<ServiceKind, NearbyPlace[]>();
  for (const el of data.elements) {
    if (el.lat == null || el.lon == null) continue;
    const kind = kindOf(el.tags);
    if (!kind) continue;
    const place: NearbyPlace = {
      id: el.id,
      name: el.tags?.name || el.tags?.["name:en"] || labelFor(kind),
      kind,
      lat: el.lat,
      lng: el.lon,
      distanceKm: distanceKm(origin, { lat: el.lat, lng: el.lon }),
      phone: el.tags?.phone || el.tags?.["contact:phone"],
      address: composeAddress(el.tags),
    };
    const list = seen.get(kind) ?? [];
    list.push(place);
    seen.set(kind, list);
  }

  return [...seen.values()]
    .flatMap((list) =>
      list.sort((a, b) => a.distanceKm - b.distanceKm).slice(0, limitPerKind)
    )
    .sort((a, b) => a.distanceKm - b.distanceKm);
}

export function labelFor(kind: ServiceKind): string {
  return {
    police: "Police station",
    hospital: "Hospital",
    fire: "Fire service",
    pharmacy: "Pharmacy",
  }[kind];
}

function composeAddress(tags: Record<string, string> = {}): string | undefined {
  const parts = [
    tags["addr:housenumber"],
    tags["addr:street"],
    tags["addr:suburb"] || tags["addr:city"],
  ].filter(Boolean);
  return parts.length ? parts.join(", ") : undefined;
}

type OverpassElement = {
  id: number;
  lat?: number;
  lon?: number;
  tags?: Record<string, string>;
};
