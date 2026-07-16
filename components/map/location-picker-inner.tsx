"use client";

import { useCallback, useEffect, useRef, useState } from "react";
import { MapContainer, Marker, TileLayer, useMapEvents } from "react-leaflet";
import type { Map as LeafletMap } from "leaflet";
import { Crosshair, LocateFixed } from "lucide-react";
import { toast } from "sonner";
import { Button } from "@/components/ui/button";
import { DEFAULT_MAP_CENTER, DEFAULT_MAP_ZOOM } from "@/lib/constants";
import { defaultIcon } from "./leaflet-icon";

type Props = {
  value: { lat: number; lng: number } | null;
  onChange: (pos: { lat: number; lng: number }) => void;
};

function ClickHandler({ onChange }: Pick<Props, "onChange">) {
  useMapEvents({
    click(e) {
      onChange({ lat: e.latlng.lat, lng: e.latlng.lng });
    },
  });
  return null;
}

export default function LocationPickerInner({ value, onChange }: Props) {
  const mapRef = useRef<LeafletMap | null>(null);
  const [locating, setLocating] = useState(false);

  const flyTo = useCallback((pos: { lat: number; lng: number }) => {
    mapRef.current?.flyTo([pos.lat, pos.lng], 16, { duration: 0.8 });
  }, []);

  useEffect(() => {
    // Leaflet mis-sizes when mounted inside animated/hidden parents.
    const t = setTimeout(() => mapRef.current?.invalidateSize(), 150);
    return () => clearTimeout(t);
  }, []);

  function useMyLocation() {
    if (!navigator.geolocation) {
      toast.error("Geolocation is not supported by this browser");
      return;
    }
    setLocating(true);
    navigator.geolocation.getCurrentPosition(
      (pos) => {
        const p = { lat: pos.coords.latitude, lng: pos.coords.longitude };
        onChange(p);
        flyTo(p);
        setLocating(false);
      },
      () => {
        toast.error("Could not get your location — drop the pin manually");
        setLocating(false);
      },
      { enableHighAccuracy: true, timeout: 10000 }
    );
  }

  return (
    <div className="relative">
      <MapContainer
        center={value ? [value.lat, value.lng] : DEFAULT_MAP_CENTER}
        zoom={value ? 16 : DEFAULT_MAP_ZOOM}
        className="h-72 w-full sm:h-80"
        ref={mapRef}
        scrollWheelZoom
      >
        <TileLayer
          attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
          url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        />
        <ClickHandler onChange={onChange} />
        {value ? (
          <Marker
            position={[value.lat, value.lng]}
            icon={defaultIcon}
            draggable
            eventHandlers={{
              dragend: (e) => {
                const ll = e.target.getLatLng();
                onChange({ lat: ll.lat, lng: ll.lng });
              },
            }}
          />
        ) : null}
      </MapContainer>

      <div className="absolute top-3 left-3 z-[1000] flex flex-col gap-2">
        <Button
          type="button"
          size="sm"
          variant="secondary"
          className="shadow-md"
          onClick={useMyLocation}
          disabled={locating}
        >
          <LocateFixed
            className={locating ? "size-4 animate-spin" : "size-4"}
            aria-hidden
          />
          Use my location
        </Button>
        {value ? (
          <Button
            type="button"
            size="sm"
            variant="secondary"
            className="shadow-md"
            onClick={() => flyTo(value)}
          >
            <Crosshair className="size-4" aria-hidden />
            Center on pin
          </Button>
        ) : null}
      </div>

      <p className="text-muted-foreground mt-2 text-xs">
        {value
          ? `Pinned at ${value.lat.toFixed(5)}, ${value.lng.toFixed(5)} — drag the pin or tap the map to adjust.`
          : "Tap the map to drop a pin, or use your GPS location."}
      </p>
    </div>
  );
}
