import Link from "next/link";
import { Eye } from "lucide-react";
import { APP_NAME, APP_NAME_BN } from "@/lib/constants";
import { cn } from "@/lib/utils";

/** Official wordmark: green emblem tile + bilingual name. */
export function Logo({
  className,
  subtitle = true,
}: {
  className?: string;
  subtitle?: boolean;
}) {
  return (
    <Link
      href="/"
      className={cn("flex items-center gap-2.5", className)}
      aria-label={`${APP_NAME} home`}
    >
      <span className="bg-brand-gradient flex size-9 shrink-0 items-center justify-center rounded-md text-white shadow-sm">
        <Eye className="size-5" aria-hidden />
      </span>
      <span className="flex flex-col leading-none">
        <span className="text-[15px] font-bold tracking-tight">
          {APP_NAME}
        </span>
        {subtitle ? (
          <span className="text-muted-foreground font-bengali mt-0.5 text-[11px]">
            {APP_NAME_BN}
          </span>
        ) : null}
      </span>
    </Link>
  );
}
