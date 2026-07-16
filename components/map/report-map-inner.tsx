"use client";

import { MapContainer, Marker, Popup, TileLayer } from "react-leaflet";
import { defaultIcon } from "./leaflet-icon";

type Props = {
  lat: number;
  lng: number;
  label?: string;
  className?: string;
};

export default function ReportMapInner({ lat, lng, label, className }: Props) {
  return (
    <MapContainer
      center={[lat, lng]}
      zoom={16}
      className={className ?? "h-64 w-full"}
      scrollWheelZoom={false}
    >
      <TileLayer
        attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
      />
      <Marker position={[lat, lng]} icon={defaultIcon}>
        {label ? <Popup>{label}</Popup> : null}
      </Marker>
    </MapContainer>
  );
}
