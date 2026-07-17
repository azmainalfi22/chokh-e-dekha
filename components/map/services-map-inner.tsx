"use client";

import {
  CircleMarker,
  MapContainer,
  Marker,
  Popup,
  TileLayer,
  Tooltip,
} from "react-leaflet";
import type { NearbyPlace } from "@/lib/overpass";
import { labelFor } from "@/lib/overpass";
import { defaultIcon } from "./leaflet-icon";

const KIND_COLOR: Record<string, string> = {
  police: "#3B7DD8",
  hospital: "#E23D3D",
  fire: "#E8830C",
  pharmacy: "#2E8B57",
};

export default function ServicesMapInner({
  origin,
  places,
}: {
  origin: { lat: number; lng: number };
  places: NearbyPlace[];
}) {
  return (
    <MapContainer
      center={[origin.lat, origin.lng]}
      zoom={14}
      className="h-80 w-full"
      scrollWheelZoom
    >
      <TileLayer
        attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
      />
      <Marker position={[origin.lat, origin.lng]} icon={defaultIcon}>
        <Tooltip permanent direction="top" offset={[0, -38]}>
          You
        </Tooltip>
      </Marker>
      {places.map((p) => (
        <CircleMarker
          key={`${p.kind}-${p.id}`}
          center={[p.lat, p.lng]}
          radius={8}
          pathOptions={{
            color: "#fff",
            weight: 2,
            fillColor: KIND_COLOR[p.kind] ?? "#2E8B57",
            fillOpacity: 1,
          }}
        >
          <Popup>
            <strong>{p.name}</strong>
            <br />
            {labelFor(p.kind)} · {p.distanceKm.toFixed(1)} km
            <br />
            <a
              href={`https://www.openstreetmap.org/directions?from=${origin.lat},${origin.lng}&to=${p.lat},${p.lng}`}
              target="_blank"
              rel="noreferrer"
              className="underline"
            >
              Directions
            </a>
          </Popup>
        </CircleMarker>
      ))}
    </MapContainer>
  );
}
