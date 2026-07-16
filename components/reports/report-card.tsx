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

export function ReportCard({ report }: { report: ReportCardData }) {
  const date = new Date(report.created_at).toLocaleDateString("en-GB", {
    day: "numeric",
    month: "short",
    year: "numeric",
  });

  return (
    <Card className="group flex h-full flex-col gap-0 overflow-hidden py-0 transition-shadow hover:shadow-lg">
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
            className="size-full object-cover transition-transform duration-300 group-hover:scale-105"
          />
        ) : (
          <span className="bg-brand-gradient flex size-full items-center justify-center opacity-80">
            <MapPin className="size-10 text-white/80" aria-hidden />
          </span>
        )}
        <span className="absolute top-2 right-2">
          <StatusBadge status={report.status} className="bg-background/85" />
        </span>
      </Link>

      <CardContent className="flex flex-1 flex-col gap-2.5 p-4">
        <div className="flex items-start justify-between gap-2">
          <Link
            href={`/reports/${report.id}`}
            className="hover:text-primary line-clamp-2 leading-snug font-semibold transition-colors"
          >
            {report.title}
          </Link>
        </div>
        <div className="flex flex-wrap items-center gap-1.5">
          <Badge variant="secondary">{report.category}</Badge>
          <SlaBadge slaDueAt={report.sla_due_at} status={report.status} />
        </div>
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
          <li className="flex items-center gap-1.5">
            <User className="size-3.5 shrink-0" aria-hidden />
            {report.authorName ?? "Citizen"}
          </li>
          <li className="flex items-center gap-1.5">
            <CalendarDays className="size-3.5 shrink-0" aria-hidden />
            {date}
          </li>
        </ul>
      </CardContent>

      <CardFooter className="flex items-center justify-between border-t !py-3 px-4">
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
