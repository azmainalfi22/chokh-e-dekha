import { cn } from "@/lib/utils";

const ACCENT: Record<string, string> = {
  brand: "var(--brand-green)",
  amber: "var(--status-pending)",
  green: "var(--status-resolved)",
  blue: "var(--status-progress)",
  purple: "var(--chart-5)",
  red: "var(--status-breach)",
};

type Props = {
  value: React.ReactNode;
  label: string;
  variant?: keyof typeof ACCENT | string;
  icon?: React.ReactNode;
  className?: string;
};

/**
 * Sober stat card: white surface, a colored left accent + icon, ink number.
 * Reads as an official data dashboard rather than a consumer app.
 */
export function KpiCard({
  value,
  label,
  variant = "brand",
  icon,
  className,
}: Props) {
  const accent = ACCENT[variant] ?? ACCENT.brand;
  return (
    <div
      className={cn(
        "bg-card relative overflow-hidden rounded-lg border p-5 shadow-sm",
        className
      )}
      style={{ borderLeft: `3px solid ${accent}` }}
    >
      <div className="flex items-start justify-between gap-2">
        <p className="text-3xl font-bold tracking-tight tabular-nums sm:text-4xl">
          {value}
        </p>
        {icon ? (
          <span
            className="flex size-9 items-center justify-center rounded-md"
            style={{
              color: accent,
              backgroundColor: `color-mix(in oklch, ${accent} 14%, transparent)`,
            }}
            aria-hidden
          >
            {icon}
          </span>
        ) : null}
      </div>
      <p className="text-muted-foreground mt-1 text-xs font-semibold tracking-wide uppercase">
        {label}
      </p>
    </div>
  );
}
