import { AlarmClock, AlarmClockOff } from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { getSlaState } from "@/lib/sla";
import { cn } from "@/lib/utils";

/** SLA countdown / breach indicator (FR-10). Renders nothing when settled. */
export function SlaBadge({
  slaDueAt,
  status,
  className,
}: {
  slaDueAt: string | null;
  status: string;
  className?: string;
}) {
  const state = getSlaState(slaDueAt, status);
  if (state.kind === "none") return null;

  if (state.kind === "breached") {
    return (
      <Badge
        variant="outline"
        className={cn(
          "border-status-breach/40 bg-status-breach/15 text-status-breach animate-pulse font-semibold",
          className
        )}
      >
        <AlarmClockOff className="size-3.5" aria-hidden />
        Overdue by {state.daysOverdue}d
      </Badge>
    );
  }

  return (
    <Badge
      variant="outline"
      className={cn(
        state.kind === "due-soon"
          ? "border-status-pending/40 bg-status-pending/15 text-status-pending"
          : "text-muted-foreground",
        className
      )}
    >
      <AlarmClock className="size-3.5" aria-hidden />
      Due in {state.daysLeft}d
    </Badge>
  );
}
