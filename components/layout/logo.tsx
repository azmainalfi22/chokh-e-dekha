import Image from "next/image";
import Link from "next/link";
import { APP_NAME, APP_NAME_BN } from "@/lib/constants";
import { cn } from "@/lib/utils";

/** Official wordmark: ornate emblem tile + bilingual name. */
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
      <span className="relative flex size-9 shrink-0 overflow-hidden rounded-md shadow-sm ring-1 ring-black/5">
        <Image
          src="/brand/logo-tile.png"
          alt=""
          fill
          sizes="36px"
          className="object-cover"
          priority
        />
      </span>
      <span className="flex flex-col leading-none">
        <span className="font-display text-[17px] font-semibold tracking-tight">
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
