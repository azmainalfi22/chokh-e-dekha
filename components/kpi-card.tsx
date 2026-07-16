import { cn } from "@/lib/utils";

const VARIANTS = {
  brand: "bg-brand-gradient",
  amber: "bg-gradient-to-br from-amber-500 to-orange-600",
  green: "bg-gradient-to-br from-emerald-500 to-emerald-700",
  blue: "bg-gradient-to-br from-blue-500 to-blue-700",
  purple: "bg-gradient-to-br from-violet-500 to-purple-700",
  red: "bg-gradient-to-br from-rose-500 to-red-700",
} as const;

type Props = {
  value: React.ReactNode;
  label: string;
  variant?: keyof typeof VARIANTS;
  icon?: React.ReactNode;
  className?: string;
};

/** Gradient KPI stat card (SPEC §3 — dashboard reference design). */
export function KpiCard({
  value,
  label,
  variant = "brand",
  icon,
  className,
}: Props) {
  return (
    <div
      className={cn(
        "glow-brand relative overflow-hidden rounded-xl p-5 text-white shadow-md",
        VARIANTS[variant],
        className
      )}
    >
      {icon ? (
        <span className="absolute top-4 right-4 opacity-30" aria-hidden>
          {icon}
        </span>
      ) : null}
      <p className="text-3xl font-extrabold tracking-tight sm:text-4xl">
        {value}
      </p>
      <p className="mt-1 text-xs font-semibold tracking-wider uppercase opacity-90">
        {label}
      </p>
    </div>
  );
}
