import { Badge } from "@/components/ui/badge";
import { STATUS_LABELS, type Status } from "@/lib/constants";
import { cn } from "@/lib/utils";

const STYLES: Record<Status, string> = {
  pending: "bg-status-pending/15 text-status-pending border-status-pending/30",
  in_progress:
    "bg-status-progress/15 text-status-progress border-status-progress/30",
  resolved:
    "bg-status-resolved/15 text-status-resolved border-status-resolved/30",
  rejected:
    "bg-status-rejected/15 text-status-rejected border-status-rejected/30",
};

export function StatusBadge({
  status,
  className,
}: {
  status: string;
  className?: string;
}) {
  const s = (status in STYLES ? status : "pending") as Status;
  return (
    <Badge variant="outline" className={cn("font-medium", STYLES[s], className)}>
      <span
        className="size-1.5 rounded-full bg-current"
        aria-hidden
      />
      {STATUS_LABELS[s]}
    </Badge>
  );
}
