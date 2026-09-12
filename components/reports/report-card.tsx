import Link from "next/link";
import {
  Building2,
  CalendarDays,
  ChevronRight,
  MapPin,
  MessageSquare,
  ThumbsUp,
  User,
} from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { CategoryTile } from "./category-tile";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardFooter } from "@/components/ui/card";
import { StatusBadge } from "@/components/reports/status-badge";
import { SlaBadge } from "@/components/reports/sla-badge";
import { reportMediaUrl } from "@/lib/storage";

export type ReportCardData = {
  id: number;
  title: string;
  category: string;
  city_corporation: string;
  location_text: string | null;
  status: string;
  sla_due_at: string | null;
  created_at: string;
  endorse_count: number;
  comment_count: number;
  authorName: string | null;
  thumbnailPath: string | null;
};

/** Left status rail colour — a quick-scan cue before reading anything. */
const RAIL: Record<string, string> = {
  pending: "bg-status-pending",
  in_progress: "bg-status-progress",
  resolved: "bg-status-resolved",
  rejected: "bg-status-rejected",
};

export function ReportCard({ report }: { report: ReportCardData }) {
  const date = new Date(report.created_at).toLocaleDateString("en-GB", {
    day: "numeric",
    month: "short",
    year: "numeric",
  });

  return (
    <Card className="group card-lift relative flex h-full flex-col gap-0 overflow-hidden py-0">
      <span
        className={`absolute inset-y-0 left-0 z-10 w-1 ${RAIL[report.status] ?? "bg-border"}`}
        aria-hidden
      />
      <Link
        href={`/reports/${report.id}`}
        className="relative block aspect-[16/9] overflow-hidden"
        aria-label={`View report: ${report.title}`}
      >
        {report.thumbnailPath ? (
          // eslint-disable-next-line @next/next/no-img-element
          <img
            src={reportMediaUrl(report.thumbnailPath)}
            alt=""
            loading="lazy"
            className="size-full object-cover transition-transform duration-500 group-hover:scale-[1.06]"
          />
        ) : (
          <CategoryTile category={report.category} />
        )}
        {/* readability scrim for the chips */}
        <span
          className="absolute inset-x-0 top-0 h-16 bg-gradient-to-b from-black/45 to-transparent"
          aria-hidden
        />
        <span className="absolute top-2.5 left-3">
          <Badge className="border-0 bg-white/90 text-[11px] font-semibold text-neutral-900 shadow-sm">
            {report.category}
          </Badge>
        </span>
        <span className="absolute top-2.5 right-2.5">
          <StatusBadge status={report.status} className="bg-background/90" />
        </span>
      </Link>

      <CardContent className="flex flex-1 flex-col gap-2.5 p-4 pl-5">
        <Link
          href={`/reports/${report.id}`}
          className="hover:text-primary line-clamp-2 leading-snug font-semibold transition-colors"
        >
          {report.title}
        </Link>
        <SlaBadge slaDueAt={report.sla_due_at} status={report.status} />
        <ul className="text-muted-foreground mt-auto space-y-1 text-xs">
          {report.location_text ? (
            <li className="flex items-center gap-1.5">
              <MapPin className="size-3.5 shrink-0" aria-hidden />
              <span className="truncate">{report.location_text}</span>
            </li>
          ) : null}
          <li className="flex items-center gap-1.5">
            <Building2 className="size-3.5 shrink-0" aria-hidden />
            {report.city_corporation}
          </li>
          <li className="flex items-center gap-3">
            <span className="flex items-center gap-1.5">
              <User className="size-3.5 shrink-0" aria-hidden />
              {report.authorName ?? "Citizen"}
            </span>
            <span className="flex items-center gap-1.5">
              <CalendarDays className="size-3.5 shrink-0" aria-hidden />
              {date}
            </span>
          </li>
        </ul>
      </CardContent>

      <CardFooter className="flex items-center justify-between border-t !py-3 px-4 pl-5">
        <div className="text-muted-foreground flex items-center gap-3 text-xs">
          <span className="flex items-center gap-1">
            <ThumbsUp className="size-3.5" aria-hidden />
            {report.endorse_count}
          </span>
          <span className="flex items-center gap-1">
            <MessageSquare className="size-3.5" aria-hidden />
            {report.comment_count}
          </span>
        </div>
        <Button variant="ghost" size="sm" asChild>
          <Link href={`/reports/${report.id}`}>
            View details <ChevronRight className="size-4" aria-hidden />
          </Link>
        </Button>
      </CardFooter>
    </Card>
  );
}
