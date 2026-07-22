import { cn } from "@/lib/utils";

/**
 * Chokh-e-Dekha brand mark: an eye whose pupil is a map pin — "seen by the
 * people's eyes" (চোখে দেখা) meeting "report it, pin it." Draws in
 * currentColor so it reads white on the green brand tile and green on plain
 * surfaces. Replaces the generic lucide Eye across brand surfaces.
 */
export function LogoMark({ className }: { className?: string }) {
  return (
    <svg
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth={1.8}
      strokeLinecap="round"
      strokeLinejoin="round"
      className={cn("size-5", className)}
      aria-hidden
    >
      {/* eye */}
      <path d="M1.8 12S5.5 5.8 12 5.8 22.2 12 22.2 12 18.5 18.2 12 18.2 1.8 12 1.8 12Z" />
      {/* map-pin pupil */}
      <path d="M12 8.2a2.7 2.7 0 0 0-2.7 2.7c0 1.9 2.7 4.3 2.7 4.3s2.7-2.4 2.7-4.3A2.7 2.7 0 0 0 12 8.2Z" />
      <circle cx="12" cy="10.9" r="0.9" fill="currentColor" stroke="none" />
    </svg>
  );
}
