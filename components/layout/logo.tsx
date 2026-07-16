import Link from "next/link";
import { Eye } from "lucide-react";
import { APP_NAME } from "@/lib/constants";
import { cn } from "@/lib/utils";

export function Logo({ className }: { className?: string }) {
  return (
    <Link
      href="/"
      className={cn("flex items-center gap-2 font-bold", className)}
      aria-label={`${APP_NAME} home`}
    >
      <span className="bg-brand-gradient flex size-8 items-center justify-center rounded-lg text-white shadow-sm">
        <Eye className="size-4.5" aria-hidden />
      </span>
      <span className="text-brand-gradient hidden text-lg tracking-tight sm:inline">
        {APP_NAME}
      </span>
    </Link>
  );
}
