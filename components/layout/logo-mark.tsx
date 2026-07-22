import { cn } from "@/lib/utils";

// Brand palette — deep green pin, gold bezel + compass, red echo halo.
const GOLD = "#c8922f";
const GOLD_LIGHT = "#e7c877";
const GREEN = "#0c5942";
const RED = "#e11d34";
const CREAM = "#fdf7e9";

/**
 * Chokh-e-Dekha brand mark (compact) — a deep-green map pin with a gold
 * bezel and a gold compass-rose at its heart, ringed by a red echo halo:
 * "your voice, anchored to a place, and heard" (চোখে দেখা). No eye. Sits on
 * the warm cream `.logo-tile`. See LogoEmblem for the full ornate version.
 */
export function LogoMark({ className }: { className?: string }) {
  return (
    <svg viewBox="0 0 48 48" className={cn("size-5", className)} aria-hidden>
      {/* red echo halo behind the pin */}
      <circle cx="24" cy="20" r="16" fill="none" stroke={RED} strokeWidth="3.4" />
      {/* gold bezel */}
      <path
        d="M24 5 C16.3 5 10 11 10 18.6 C10 28.5 24 43 24 43 C24 43 38 28.5 38 18.6 C38 11 31.7 5 24 5 Z"
        fill="none"
        stroke={GOLD}
        strokeWidth="3.4"
        strokeLinejoin="round"
      />
      {/* green body */}
      <path
        d="M24 7.4 C17.6 7.4 12.4 12.4 12.4 18.7 C12.4 27 24 40 24 40 C24 40 35.6 27 35.6 18.7 C35.6 12.4 30.4 7.4 24 7.4 Z"
        fill={GREEN}
      />
      {/* gold compass rose */}
      <path
        d="M24 12 L26 18 32 20 26 22 24 28 22 22 16 20 22 18 Z"
        fill={GOLD_LIGHT}
      />
      <circle cx="24" cy="20" r="2.4" fill={CREAM} stroke={GOLD} strokeWidth="1" />
    </svg>
  );
}
