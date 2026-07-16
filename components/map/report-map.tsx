"use client";

import dynamic from "next/dynamic";
import { Skeleton } from "@/components/ui/skeleton";

/** Single-report location map, client-only. */
export const ReportMap = dynamic(() => import("./report-map-inner"), {
  ssr: false,
  loading: () => <Skeleton className="h-64 w-full rounded-xl" />,
});
