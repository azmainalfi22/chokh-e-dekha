import { Check, X } from "lucide-react";
import { cn } from "@/lib/utils";

const STAGES = [
  { key: "pending", label: "Pending" },
  { key: "in_progress", label: "In Progress" },
  { key: "resolved", label: "Resolved" },
] as const;

/**
 * Visual lifecycle tracker (FR-04): Pending → In Progress → Resolved as a
 * progress bar filled to the current stage. Rejected reports show a
 * terminated track.
 */
export function StatusTracker({
  status,
  className,
}: {
  status: string;
  className?: string;
}) {
  if (status === "rejected") {
    return (
      <div
        className={cn(
          "border-status-rejected/30 bg-status-rejected/10 flex items-center gap-2 rounded-lg border px-3 py-2",
          className
        )}
        role="status"
        aria-label="Report rejected"
      >
        <span className="bg-status-rejected flex size-6 items-center justify-center rounded-full text-white">
          <X className="size-3.5" aria-hidden />
        </span>
        <span className="text-status-rejected text-sm font-medium">
          This report was rejected
        </span>
      </div>
    );
  }

  const activeIndex = Math.max(
    0,
    STAGES.findIndex((s) => s.key === status)
  );

  return (
    <ol
      className={cn("flex items-center", className)}
      aria-label={`Report status: ${STAGES[activeIndex].label}`}
    >
      {STAGES.map((stage, i) => {
        const reached = i <= activeIndex;
        const completed =
          i < activeIndex || (reached && stage.key === "resolved");
        return (
          <li
            key={stage.key}
            className={cn("flex items-center", i > 0 && "flex-1")}
          >
            {i > 0 ? (
              <span
                className={cn(
                  "mx-2 mb-5 h-1 flex-1 rounded-full transition-colors",
                  reached ? "bg-brand-gradient" : "bg-muted"
                )}
                aria-hidden
              />
            ) : null}
            <span className="flex flex-col items-center gap-1">
              <span
                className={cn(
                  "flex size-7 items-center justify-center rounded-full text-xs font-bold transition-colors",
                  reached
                    ? stage.key === "resolved"
                      ? "bg-status-resolved text-white"
                      : "bg-brand-gradient text-white"
                    : "border-muted-foreground/30 text-muted-foreground bg-background border-2"
                )}
              >
                {completed ? <Check className="size-4" aria-hidden /> : i + 1}
              </span>
              <span
                className={cn(
                  "text-[11px] font-medium whitespace-nowrap",
                  i === activeIndex ? "text-foreground" : "text-muted-foreground"
                )}
              >
                {stage.label}
              </span>
            </span>
          </li>
        );
      })}
    </ol>
  );
}
