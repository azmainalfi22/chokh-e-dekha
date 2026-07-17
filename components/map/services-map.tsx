"use client";

import dynamic from "next/dynamic";
import { Skeleton } from "@/components/ui/skeleton";

export const ServicesMap = dynamic(() => import("./services-map-inner"), {
  ssr: false,
  loading: () => <Skeleton className="h-80 w-full rounded-lg" />,
});
