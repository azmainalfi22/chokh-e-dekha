import Link from "next/link";
import { APP_NAME, APP_NAME_BN } from "@/lib/constants";

export function SiteFooter() {
  return (
    <footer className="mt-auto border-t">
      <div className="text-muted-foreground mx-auto flex max-w-7xl flex-col items-center justify-between gap-3 px-4 py-6 text-sm sm:flex-row sm:px-6">
        <p>
          © {new Date().getFullYear()} {APP_NAME}{" "}
          <span className="font-bengali">({APP_NAME_BN})</span>. Made for civic
          good.
        </p>
        <nav aria-label="Footer" className="flex items-center gap-4">
          <Link href="/reports" className="hover:text-foreground transition-colors">
            All Reports
          </Link>
          <Link href="/rti" className="hover:text-foreground transition-colors">
            RTI Wizard
          </Link>
        </nav>
      </div>
    </footer>
  );
}
