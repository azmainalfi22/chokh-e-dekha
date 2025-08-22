<style>
/* ========================================
   DESIGN TOKENS PARTIAL
   Usage: or include in main CSS
   ======================================== */

/* ---------- CORE THEME TOKENS ---------- */
:root {
  /* System hint for native UI */
  color-scheme: light;

  /* Light theme colors */
  --surface: #ffffff;
  --surface-muted: #f8fafc;        /* slate-50 */
  --surface-elevated: #ffffff;
  --text: #0f172a;                  /* slate-900 */
  --text-secondary: #475569;        /* slate-600 */
  --muted: #64748b;                 /* slate-500 */
  --ring: #e2e8f0;                  /* slate-200 */
  --ring-focus: #f59e0b;            /* amber-500 */
  --link: #0ea5e9;                  /* sky-500 */
  --accent: #f59e0b;                /* amber-500 */
  --accent-600: #d97706;            /* amber-600 */
  --accent-700: #b45309;            /* amber-700 */

  /* RGB helpers for semi-transparent overlays */
  --accent-rgb: 245, 158, 11;       /* amber-500 */
  --link-rgb: 14, 165, 233;         /* sky-500 */
  --text-rgb: 15, 23, 42;           /* slate-900 */

  /* Elevation shadows */
  --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
  --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
  --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
  --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
  --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);

  /* Typography scale */
  --text-xs: 0.75rem;       /* 12px */
  --text-sm: 0.875rem;      /* 14px */
  --text-base: 1rem;        /* 16px */
  --text-lg: 1.125rem;      /* 18px */
  --text-xl: 1.25rem;       /* 20px */
  --text-2xl: 1.5rem;       /* 24px */
  --text-3xl: 1.875rem;     /* 30px */
  --text-4xl: 2.25rem;      /* 36px */
  --text-5xl: 3rem;         /* 48px */

  /* Spacing scale */
  --space-1: 0.25rem;       /* 4px */
  --space-2: 0.5rem;        /* 8px */
  --space-3: 0.75rem;       /* 12px */
  --space-4: 1rem;          /* 16px */
  --space-5: 1.25rem;       /* 20px */
  --space-6: 1.5rem;        /* 24px */
  --space-8: 2rem;          /* 32px */
  --space-10: 2.5rem;       /* 40px */
  --space-12: 3rem;         /* 48px */
  --space-16: 4rem;         /* 64px */

  /* Border radius scale */
  --radius-sm: 0.125rem;    /* 2px */
  --radius: 0.25rem;        /* 4px */
  --radius-md: 0.375rem;    /* 6px */
  --radius-lg: 0.5rem;      /* 8px */
  --radius-xl: 0.75rem;     /* 12px */
  --radius-2xl: 1rem;       /* 16px */
  --radius-full: 9999px;

  /* Animation durations */
  --duration-fast: 0.15s;
  --duration-normal: 0.2s;
  --duration-slow: 0.3s;
  --duration-slower: 0.4s;

  /* Animation easings */
  --ease-out: cubic-bezier(0.4, 0, 0.2, 1);
  --ease-in: cubic-bezier(0.4, 0, 1, 1);
  --ease-in-out: cubic-bezier(0.4, 0, 0.2, 1);

  /* Z-index scale */
  --z-dropdown: 1000;
  --z-sticky: 1020;
  --z-fixed: 1030;
  --z-modal-backdrop: 1040;
  --z-modal: 1050;
  --z-popover: 1060;
  --z-tooltip: 1070;
  --z-toast: 1080;
}

/* ---------- DARK THEME OVERRIDES ---------- */
.dark {
  color-scheme: dark;

  --surface: rgba(31, 41, 55, 0.95);        /* gray-800 with transparency */
  --surface-muted: #111827;                 /* gray-900 */
  --surface-elevated: rgba(55, 65, 81, 0.95); /* gray-700 with transparency */
  --text: #f9fafb;                          /* gray-50 */
  --text-secondary: #d1d5db;                /* gray-300 */
  --muted: #9ca3af;                         /* gray-400 */
  --ring: #374151;                          /* gray-700 */
  --ring-focus: #fbbf24;                    /* amber-400 */
  --link: #38bdf8;                          /* sky-400 */
  --accent: #fbbf24;                        /* amber-400 */
  --accent-600: #f59e0b;                    /* amber-500 */
  --accent-700: #f59e0b;                    /* amber-500 */

  /* Darker shadows */
  --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.3);
  --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.4), 0 1px 2px -1px rgb(0 0 0 / 0.4);
  --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.4), 0 2px 4px -2px rgb(0 0 0 / 0.4);
  --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.4), 0 4px 6px -4px rgb(0 0 0 / 0.4);
  --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.4), 0 8px 10px -6px rgb(0 0 0 / 0.4);
}

/* ---------- STATUS COLOR TOKENS ---------- */
:root {
  /* Status colors - light theme */
  --status-pending-bg: rgba(245, 158, 11, 0.20);
  --status-pending-text: #92400e;
  --status-pending-border: rgba(245, 158, 11, 0.40);

  --status-in-progress-bg: rgba(59, 130, 246, 0.20);
  --status-in-progress-text: #1e3a8a;
  --status-in-progress-border: rgba(59, 130, 246, 0.40);

  --status-resolved-bg: rgba(16, 185, 129, 0.20);
  --status-resolved-text: #065f46;
  --status-resolved-border: rgba(16, 185, 129, 0.40);

  --status-rejected-bg: rgba(239, 68, 68, 0.20);
  --status-rejected-text: #991b1b;
  --status-rejected-border: rgba(239, 68, 68, 0.40);
}

.dark {
  /* Status colors - dark theme (add bg + border for contrast) */
  --status-pending-bg: rgba(251, 191, 36, 0.12);
  --status-pending-text: #fbbf24;
  --status-pending-border: rgba(251, 191, 36, 0.32);

  --status-in-progress-bg: rgba(59, 130, 246, 0.12);
  --status-in-progress-text: #93c5fd;
  --status-in-progress-border: rgba(59, 130, 246, 0.32);

  --status-resolved-bg: rgba(16, 185, 129, 0.12);
  --status-resolved-text: #6ee7b7;
  --status-resolved-border: rgba(16, 185, 129, 0.32);

  --status-rejected-bg: rgba(239, 68, 68, 0.12);
  --status-rejected-text: #fca5a5;
  --status-rejected-border: rgba(239, 68, 68, 0.32);
}

/* ---------- SEMANTIC COLOR TOKENS ---------- */
:root {
  /* Success colors */
  --success-50: #f0fdf4;
  --success-100: #dcfce7;
  --success-500: #22c55e;
  --success-600: #16a34a;
  --success-700: #15803d;
  --success-rgb: 34, 197, 94;

  /* Error colors */
  --error-50: #fef2f2;
  --error-100: #fee2e2;
  --error-500: #ef4444;
  --error-600: #dc2626;
  --error-700: #b91c1c;
  --error-rgb: 239, 68, 68;

  /* Warning colors */
  --warning-50: #fffbeb;
  --warning-100: #fef3c7;
  --warning-500: #f59e0b;
  --warning-600: #d97706;
  --warning-700: #b45309;
  --warning-rgb: 245, 158, 11;

  /* Info colors */
  --info-50: #eff6ff;
  --info-100: #dbeafe;
  --info-500: #3b82f6;
  --info-600: #2563eb;
  --info-700: #1d4ed8;
  --info-rgb: 59, 130, 246;
}

/* ---------- GRAIN TEXTURE ---------- */
.grainy::before {
  content: "";
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 0;
  opacity: 0.08;
  mix-blend-mode: multiply;
  background-size: 200px 200px;
  background-repeat: repeat;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3CfeColorMatrix type='saturate' values='0'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.15'/%3E%3C/svg%3E");
}
.dark .grainy::before { 
  opacity: 0.05;
  mix-blend-mode: screen;
}

/* ---------- ACCESSIBILITY & MOTION ---------- */
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}

/* ---------- UTILITY CLASSES (minimal, non-Tailwind names) ---------- */
.surface { background: var(--surface); }
.surface-muted { background: var(--surface-muted); }
.surface-elevated { background: var(--surface-elevated); }

.text-primary { color: var(--text); }
.text-secondary { color: var(--text-secondary); }
.text-muted { color: var(--muted); }
.text-accent { color: var(--accent); }

.border-default { border-color: var(--ring); }
.border-focus { border-color: var(--ring-focus); }

/* Generic focus ring helper (opt-in) */
.u-focus-ring {
  outline: none !important;
  box-shadow: 0 0 0 4px rgba(var(--accent-rgb), .18);
  border-color: var(--ring-focus) !important;
}

/* Screen reader only utility */
.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}
</style>
