"use client";

import { useRouter } from "next/navigation";
import { Copy, Printer, Trash2 } from "lucide-react";
import { toast } from "sonner";
import { deleteRtiLetter } from "@/lib/actions/rti";
import { APP_NAME } from "@/lib/constants";
import { Button } from "@/components/ui/button";
import { cn } from "@/lib/utils";

export function RtiLetterView({
  id,
  subject,
  body,
  language,
}: {
  id: number;
  subject: string;
  body: string;
  language: "en" | "bn";
}) {
  const router = useRouter();

  async function copy() {
    await navigator.clipboard.writeText(body);
    toast.success("Letter copied to clipboard");
  }

  async function remove() {
    await deleteRtiLetter(id);
    toast.success("Letter deleted");
    router.push("/rti");
  }

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-2 print:hidden">
        <h1 className="text-xl font-bold">{subject}</h1>
        <div className="flex items-center gap-2">
          <Button variant="outline" size="sm" onClick={copy}>
            <Copy className="size-4" aria-hidden /> Copy
          </Button>
          <Button variant="outline" size="sm" onClick={remove}>
            <Trash2 className="size-4" aria-hidden /> Delete
          </Button>
          <Button
            size="sm"
            className="bg-brand-gradient border-0 text-white hover:opacity-90"
            onClick={() => window.print()}
          >
            <Printer className="size-4" aria-hidden /> Print / Save PDF
          </Button>
        </div>
      </div>

      <p className="text-muted-foreground text-sm print:hidden">
        Use your browser&apos;s print dialog and choose “Save as PDF” to get a
        printable copy.
      </p>

      {/* The printable sheet (see @media print in globals.css) */}
      <article
        className={cn(
          "print-area mx-auto max-w-[21cm] rounded-lg border bg-white p-8 text-black shadow-sm sm:p-12",
          language === "bn" ? "font-bengali" : "font-sans"
        )}
      >
        <pre
          className={cn(
            "text-[15px] leading-relaxed whitespace-pre-wrap",
            language === "bn" ? "font-bengali" : "font-sans"
          )}
        >
          {body}
        </pre>
        <p className="mt-10 border-t pt-3 text-center text-xs text-neutral-400">
          Generated with {APP_NAME} — Right to Information Act 2009 application
        </p>
      </article>
    </div>
  );
}
