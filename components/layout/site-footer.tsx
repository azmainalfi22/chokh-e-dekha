import Link from "next/link";
import { Eye } from "lucide-react";
import { APP_NAME, APP_NAME_BN } from "@/lib/constants";

const COLUMNS = [
  {
    title: "Platform",
    links: [
      { href: "/reports", label: "All Reports" },
      { href: "/map", label: "Reports Map" },
      { href: "/outages", label: "Live Outages" },
      { href: "/submit", label: "Report an Issue" },
      { href: "/guides", label: "Service Guides" },
      { href: "/surveys", label: "Community Surveys" },
      { href: "/rti", label: "RTI Wizard" },
    ],
  },
  {
    title: "Citizens",
    links: [
      { href: "/services", label: "Emergency & Services" },
      { href: "/authorities", label: "Authority Directory" },
      { href: "/rights", label: "Know Your Rights" },
      { href: "/", label: "How it works" },
      { href: "/reports", label: "Public accountability" },
    ],
  },
];

export function SiteFooter() {
  return (
    <footer className="mt-auto border-t bg-card">
      <div className="ribbon-bd h-0.5" aria-hidden />
      <div className="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:px-6 md:grid-cols-4">
        <div className="md:col-span-2">
          <div className="flex items-center gap-2.5">
            <span className="bg-brand-gradient flex size-9 items-center justify-center rounded-md text-white">
              <Eye className="size-5" aria-hidden />
            </span>
            <div className="leading-none">
              <p className="font-display text-lg font-semibold">{APP_NAME}</p>
              <p className="text-muted-foreground font-bengali mt-0.5 text-xs">
                {APP_NAME_BN}
              </p>
            </div>
          </div>
          <p className="text-muted-foreground mt-4 max-w-sm text-sm leading-relaxed">
            A citizens&apos; platform for reporting civic issues across
            Bangladesh — with photos, precise locations, and public tracking of
            how authorities respond.
          </p>
          <p className="text-muted-foreground mt-4 max-w-sm text-xs">
            An independent civic-technology initiative. Not affiliated with, or
            endorsed by, any government body.
          </p>
        </div>

        {COLUMNS.map((col) => (
          <nav key={col.title} aria-label={col.title}>
            <h2 className="text-sm font-semibold">{col.title}</h2>
            <ul className="mt-3 space-y-2">
              {col.links.map((l) => (
                <li key={l.label}>
                  <Link
                    href={l.href}
                    className="text-muted-foreground hover:text-primary text-sm transition-colors"
                  >
                    {l.label}
                  </Link>
                </li>
              ))}
            </ul>
          </nav>
        ))}
      </div>

      <div className="border-t">
        <div className="text-muted-foreground mx-auto flex max-w-7xl flex-col items-center justify-between gap-2 px-4 py-4 text-xs sm:flex-row sm:px-6">
          <p>
            © {new Date().getFullYear()} {APP_NAME}. Built for civic
            transparency.
          </p>
          <p className="font-bengali">চোখে দেখা · জনগণের কণ্ঠস্বর</p>
        </div>
      </div>
    </footer>
  );
}
