import Link from "next/link";
import { ChevronLeft, ChevronRight } from "lucide-react";
import { Button } from "@/components/ui/button";
import { cn } from "@/lib/utils";

type Props = {
  page: number;
  totalPages: number;
  basePath: string;
  searchParams: Record<string, string | undefined>;
};

function pageHref(
  basePath: string,
  searchParams: Record<string, string | undefined>,
  page: number
) {
  const params = new URLSearchParams();
  for (const [k, v] of Object.entries(searchParams)) {
    if (v && k !== "page") params.set(k, v);
  }
  if (page > 1) params.set("page", String(page));
  const qs = params.toString();
  return qs ? `${basePath}?${qs}` : basePath;
}

export function PaginationNav({
  page,
  totalPages,
  basePath,
  searchParams,
}: Props) {
  if (totalPages <= 1) return null;

  // window of pages around the current one
  const pages = Array.from({ length: totalPages }, (_, i) => i + 1).filter(
    (p) => p === 1 || p === totalPages || Math.abs(p - page) <= 1
  );

  return (
    <nav
      aria-label="Pagination"
      className="flex items-center justify-center gap-1.5"
    >
      <Button variant="outline" size="icon" disabled={page <= 1} asChild={page > 1}>
        {page > 1 ? (
          <Link
            href={pageHref(basePath, searchParams, page - 1)}
            aria-label="Previous page"
          >
            <ChevronLeft className="size-4" />
          </Link>
        ) : (
          <ChevronLeft className="size-4" />
        )}
      </Button>

      {pages.map((p, i) => {
        const gap = i > 0 && p - pages[i - 1] > 1;
        return (
          <span key={p} className="flex items-center gap-1.5">
            {gap ? <span className="text-muted-foreground px-1">…</span> : null}
            <Button
              variant={p === page ? "default" : "outline"}
              size="icon"
              className={cn(
                p === page && "bg-brand-gradient border-0 text-white"
              )}
              asChild={p !== page}
              aria-current={p === page ? "page" : undefined}
            >
              {p === page ? (
                <span>{p}</span>
              ) : (
                <Link href={pageHref(basePath, searchParams, p)}>{p}</Link>
              )}
            </Button>
          </span>
        );
      })}

      <Button
        variant="outline"
        size="icon"
        disabled={page >= totalPages}
        asChild={page < totalPages}
      >
        {page < totalPages ? (
          <Link
            href={pageHref(basePath, searchParams, page + 1)}
            aria-label="Next page"
          >
            <ChevronRight className="size-4" />
          </Link>
        ) : (
          <ChevronRight className="size-4" />
        )}
      </Button>
    </nav>
  );
}
