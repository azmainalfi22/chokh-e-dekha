"use client";

import dynamic from "next/dynamic";
import { Skeleton } from "@/components/ui/skeleton";

/** Multi-report map with heatmap toggle, client-only. */
export const ReportsMap = dynamic(() => import("./reports-map-inner"), {
  ssr: false,
  loading: () => <Skeleton className="h-[32rem] w-full rounded-xl" />,
});
