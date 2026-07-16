"use client";

import dynamic from "next/dynamic";
import { Skeleton } from "@/components/ui/skeleton";

/** Leaflet must never render on the server (SPEC engineering rules). */
export const LocationPicker = dynamic(
  () => import("./location-picker-inner"),
  {
    ssr: false,
    loading: () => <Skeleton className="h-72 w-full rounded-xl sm:h-80" />,
  }
);
