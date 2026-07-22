import { cn } from "@/lib/utils";

/**
 * Chokh-e-Dekha full ornate emblem — a gold-bezelled deep-green map pin with
 * a red echo halo, gold voice-waves, a guilloché compass-rose medallion and a
 * faint sacred-geometry backdrop. For large, ceremonial placements (auth
 * pages, hero, print). Rendered once per view, so the gradient ids are safe.
 */
export function LogoEmblem({ className }: { className?: string }) {
  return (
    <svg
      viewBox="0 0 120 140"
      className={cn("h-auto w-40", className)}
      role="img"
      aria-label="Chokh-e-Dekha emblem"
    >
      <defs>
        <linearGradient id="ced-gold" x1="0" y1="0" x2="1" y2="1">
          <stop offset="0" stopColor="#f8ecc0" />
          <stop offset=".35" stopColor="#d8a94e" />
          <stop offset=".62" stopColor="#a9741c" />
          <stop offset="1" stopColor="#efd48a" />
        </linearGradient>
        <linearGradient id="ced-red" x1="0" y1="0" x2="1" y2="1">
          <stop offset="0" stopColor="#d31f34" />
          <stop offset=".55" stopColor="#9c1122" />
          <stop offset="1" stopColor="#6f0c18" />
        </linearGradient>
        <radialGradient id="ced-green" cx=".42" cy=".34" r=".85">
          <stop offset="0" stopColor="#16745a" />
          <stop offset=".6" stopColor="#0c5942" />
          <stop offset="1" stopColor="#053728" />
        </radialGradient>
        <radialGradient id="ced-cream" cx=".4" cy=".35" r=".8">
          <stop offset="0" stopColor="#fffdf6" />
          <stop offset="1" stopColor="#efe0bd" />
        </radialGradient>
      </defs>

      {/* faint sacred-geometry backdrop */}
      <g fill="none" stroke="#c79a4e" opacity=".28">
        <circle cx="60" cy="58" r="52" strokeWidth=".5" />
        <circle cx="60" cy="58" r="46" strokeWidth=".5" />
        <g strokeWidth=".5">
          <circle cx="60" cy="18" r="40" />
          <circle cx="94.6" cy="38" r="40" />
          <circle cx="94.6" cy="78" r="40" />
          <circle cx="60" cy="98" r="40" />
          <circle cx="25.4" cy="78" r="40" />
          <circle cx="25.4" cy="38" r="40" />
        </g>
      </g>

      {/* red halo band */}
      <path
        d="M60 12 C34 12 13 32 13 57 C13 88 60 132 60 132 C60 132 107 88 107 57 C107 32 86 12 60 12 Z"
        fill="url(#ced-red)"
      />
      <g fill="#f4d68a" opacity=".9">
        <circle cx="30" cy="44" r=".8" />
        <circle cx="26" cy="60" r=".7" />
        <circle cx="33" cy="74" r=".6" />
        <circle cx="90" cy="44" r=".8" />
        <circle cx="94" cy="60" r=".7" />
        <circle cx="87" cy="74" r=".6" />
      </g>

      {/* gold bezel + green body */}
      <path
        d="M60 20 C38 20 20 37 20 58 C20 85 60 122 60 122 C60 122 100 85 100 58 C100 37 82 20 60 20 Z"
        fill="none"
        stroke="url(#ced-gold)"
        strokeWidth="6"
        strokeLinejoin="round"
      />
      <path
        d="M60 23 C39.5 23 23 39 23 58.5 C23 83 60 118 60 118 C60 118 97 83 97 58.5 C97 39 80.5 23 60 23 Z"
        fill="url(#ced-green)"
      />

      {/* gold voice-waves */}
      <g fill="none" stroke="url(#ced-gold)" strokeLinecap="round" opacity=".92">
        <path d="M43 46 A6 6 0 0 0 43 58" strokeWidth="1.6" />
        <path d="M39 42 A9.5 9.5 0 0 0 39 62" strokeWidth="1.4" opacity=".7" />
        <path d="M77 46 A6 6 0 0 1 77 58" strokeWidth="1.6" />
        <path d="M81 42 A9.5 9.5 0 0 1 81 62" strokeWidth="1.4" opacity=".7" />
      </g>

      {/* compass-rose medallion */}
      <g transform="translate(60 52)">
        <circle r="19" fill="url(#ced-cream)" stroke="url(#ced-gold)" strokeWidth="1.6" />
        <circle r="16.5" fill="none" stroke="url(#ced-gold)" strokeWidth="2.6" strokeDasharray="1 2.7" />
        <g fill="url(#ced-gold)">
          <path d="M0 -14 L3 -3 14 0 3 3 0 14 -3 3 -14 0 -3 -3 Z" />
          <path d="M0 -9.5 L2 -2 9.5 0 2 2 0 9.5 -2 2 -9.5 0 -2 -2 Z" transform="rotate(45)" opacity=".85" />
        </g>
        <circle r="4.6" fill="url(#ced-cream)" stroke="url(#ced-gold)" strokeWidth="1.1" />
        <circle r="1.5" fill="#c0182b" />
      </g>
    </svg>
  );
}
