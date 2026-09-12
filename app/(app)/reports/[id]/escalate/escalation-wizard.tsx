"use client";

import { useRouter } from "next/navigation";
import { useState, useTransition } from "react";
import {
  Check,
  Copy,
  ExternalLink,
  FileText,
  Globe,
  Loader2,
  Phone,
  Printer,
} from "lucide-react";
import { toast } from "sonner";
import {
  createEscalation,
  refreshEscalationStatus,
  setEscalationOutcome,
  setEscalationReference,
  submitEscalation,
} from "@/lib/actions/escalations";
import {
  CHANNEL_LABELS,
  GRS_PORTAL_URL,
  OUTCOME_LABELS,
  type EscalationChannel,
} from "@/lib/grs";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { cn } from "@/lib/utils";

type ExistingEscalation = {
  id: number;
  channel: string;
  language: string;
  complaint_body: string;
  reference_no: string | null;
  outcome: string;
  filed_at: string | null;
  created_at: string;
};

const CHANNELS: Array<{
  key: EscalationChannel;
  icon: typeof Globe;
  blurb: string;
}> = [
  {
    key: "grs",
    icon: Globe,
    blurb: "File online at the national Grievance Redress System portal.",
  },
  {
    key: "helpline_333",
    icon: Phone,
    blurb: "Call the national helpline — works without internet.",
  },
  {
    key: "written",
    icon: FileText,
    blurb: "Print and submit to the authority's grievance officer.",
  },
];

export function EscalationWizard({
  reportId,
  authorityName,
  existing,
}: {
  reportId: number;
  authorityName: string;
  existing: ExistingEscalation[];
}) {
  const router = useRouter();
  const [pending, startTransition] = useTransition();
  const [channel, setChannel] = useState<EscalationChannel | null>(null);
  const [language, setLanguage] = useState<"en" | "bn">("en");
  const [body, setBody] = useState<string>("");
  const [escalationId, setEscalationId] = useState<number | null>(null);
  const [reference, setReference] = useState("");
  const [copied, setCopied] = useState(false);

  function draft(ch: EscalationChannel, lang: "en" | "bn") {
    setChannel(ch);
    setLanguage(lang);
    startTransition(async () => {
      const result = await createEscalation(reportId, ch, lang);
      if (!result.ok) {
        toast.error(result.error);
        return;
      }
      setBody(result.body);
      setEscalationId(result.id);
      setCopied(false);
    });
  }

  async function copy() {
    await navigator.clipboard.writeText(body);
    setCopied(true);
    toast.success("Complaint copied — paste it into the portal or a document");
  }

  function markFiled() {
    if (!escalationId) return;
    startTransition(async () => {
      const result = await setEscalationReference(escalationId, reference);
      if (!result.ok) {
        toast.error(result.error);
        return;
      }
      toast.success("Logged — the escalation now shows on the public record");
      router.refresh();
    });
  }

  function submitThroughChannel() {
    if (!escalationId) return;
    startTransition(async () => {
      const result = await submitEscalation(escalationId);
      if (!result.ok) {
        toast.error(result.error);
        return;
      }
      toast.success("Submitted — the reference number is on the public record");
      router.refresh();
    });
  }

  function checkStatus(id: number) {
    startTransition(async () => {
      const result = await refreshEscalationStatus(id);
      if (!result.ok) {
        toast.error(result.error);
        return;
      }
      toast.success("Status updated");
      router.refresh();
    });
  }

  function updateOutcome(id: number, outcome: string) {
    startTransition(async () => {
      const result = await setEscalationOutcome(id, outcome);
      if (!result.ok) toast.error(result.error);
      else router.refresh();
    });
  }

  return (
    <div className="space-y-6">
      {/* Channel picker */}
      <div className="grid gap-4 sm:grid-cols-3">
        {CHANNELS.map(({ key, icon: Icon, blurb }) => (
          <button
            key={key}
            type="button"
            onClick={() => draft(key, language)}
            className={cn(
              "card-lift rounded-xl border p-5 text-left",
              channel === key
                ? "border-primary/50 bg-primary/5"
                : "bg-card hover:border-primary/30"
            )}
            aria-pressed={channel === key}
          >
            <span
              className={cn(
                "inline-flex size-10 items-center justify-center rounded-lg",
                channel === key
                  ? "bg-brand-gradient text-white"
                  : "bg-primary/10 text-primary"
              )}
            >
              <Icon className="size-5" aria-hidden />
            </span>
            <p className="mt-3 font-semibold">{CHANNEL_LABELS[key].en}</p>
            <p className="text-muted-foreground font-bengali text-sm">
              {CHANNEL_LABELS[key].bn}
            </p>
            <p className="text-muted-foreground mt-1.5 text-xs leading-relaxed">
              {blurb}
            </p>
          </button>
        ))}
      </div>

      {/* Draft preview */}
      {channel ? (
        <Card>
          <CardHeader className="flex flex-row flex-wrap items-center justify-between gap-3 space-y-0">
            <CardTitle className="text-lg">
              {channel === "helpline_333"
                ? "Your 333 call script"
                : "Your complaint, ready to file"}
            </CardTitle>
            <div className="flex items-center gap-2">
              <Button
                variant={language === "en" ? "default" : "outline"}
                size="sm"
                className={language === "en" ? "bg-brand-gradient border-0 text-white" : ""}
                onClick={() => draft(channel, "en")}
              >
                English
              </Button>
              <Button
                variant={language === "bn" ? "default" : "outline"}
                size="sm"
                className={cn(
                  "font-bengali",
                  language === "bn" && "bg-brand-gradient border-0 text-white"
                )}
                onClick={() => draft(channel, "bn")}
              >
                বাংলা
              </Button>
            </div>
          </CardHeader>
          <CardContent className="space-y-4">
            {pending && !body ? (
              <div className="flex items-center justify-center py-10">
                <Loader2 className="text-primary size-6 animate-spin" aria-hidden />
              </div>
            ) : body ? (
              <>
                <div
                  className={cn(
                    "print-area bg-muted/40 rounded-xl border p-5 text-sm leading-relaxed whitespace-pre-wrap",
                    language === "bn" && "font-bengali"
                  )}
                >
                  {body}
                </div>
                <div className="flex flex-wrap items-center gap-2">
                  <Button variant="outline" size="sm" onClick={copy}>
                    {copied ? (
                      <Check className="size-4" aria-hidden />
                    ) : (
                      <Copy className="size-4" aria-hidden />
                    )}
                    {copied ? "Copied" : "Copy text"}
                  </Button>
                  {channel !== "helpline_333" ? (
                    <Button variant="outline" size="sm" onClick={() => window.print()}>
                      <Printer className="size-4" aria-hidden /> Print
                    </Button>
                  ) : null}
                  {channel === "grs" ? (
                    <Button
                      size="sm"
                      className="bg-brand-gradient border-0 text-white hover:opacity-95"
                      asChild
                    >
                      <a href={GRS_PORTAL_URL} target="_blank" rel="noreferrer noopener">
                        Open GRS portal <ExternalLink className="size-4" aria-hidden />
                      </a>
                    </Button>
                  ) : null}
                  {channel === "helpline_333" ? (
                    <Button
                      size="sm"
                      className="bg-brand-gradient border-0 text-white hover:opacity-95"
                      asChild
                    >
                      <a href="tel:333">
                        <Phone className="size-4" aria-hidden /> Call 333
                      </a>
                    </Button>
                  ) : null}
                </div>

                <div className="border-t pt-4">
                  <p className="text-sm font-medium">Submit it now</p>
                  <p className="text-muted-foreground mt-0.5 text-xs">
                    Sends the complaint through the channel and records the
                    reference number it returns, so you do not have to copy it
                    across by hand.
                  </p>
                  <Button
                    disabled={pending}
                    onClick={submitThroughChannel}
                    className="bg-brand-gradient mt-2 border-0 text-white hover:opacity-95"
                  >
                    {pending ? (
                      <Loader2 className="size-4 animate-spin" aria-hidden />
                    ) : null}
                    Submit and get a reference
                  </Button>
                </div>

                <div className="border-t pt-4">
                  <p className="text-sm font-medium">
                    Filed it yourself? Log the reference number
                  </p>
                  <p className="text-muted-foreground mt-0.5 text-xs">
                    The GRS portal / 333 operator gives you a tracking number.
                    Logging it puts the escalation on the public record.
                  </p>
                  <div className="mt-2 flex flex-wrap gap-2">
                    <Input
                      value={reference}
                      onChange={(e) => setReference(e.target.value)}
                      placeholder="e.g. GRS-2026-104529 (optional)"
                      className="max-w-xs"
                    />
                    <Button
                      disabled={pending}
                      onClick={markFiled}
                      className="bg-brand-gradient border-0 text-white hover:opacity-95"
                    >
                      {pending ? (
                        <Loader2 className="size-4 animate-spin" aria-hidden />
                      ) : null}
                      Mark as filed
                    </Button>
                  </div>
                </div>
              </>
            ) : null}
          </CardContent>
        </Card>
      ) : (
        <p className="text-muted-foreground text-sm">
          Pick a channel above — the complaint to{" "}
          <span className="text-foreground font-medium">{authorityName}</span>{" "}
          will be drafted for you.
        </p>
      )}

      {/* Existing escalations */}
      {existing.length > 0 ? (
        <Card>
          <CardHeader>
            <CardTitle className="text-lg">Your escalations for this report</CardTitle>
          </CardHeader>
          <CardContent className="space-y-3">
            {existing.map((e) => (
              <div
                key={e.id}
                className="flex flex-wrap items-center justify-between gap-3 rounded-lg border p-3"
              >
                <div>
                  <p className="text-sm font-medium">
                    {CHANNEL_LABELS[e.channel as EscalationChannel]?.en ?? e.channel}
                    {e.reference_no ? (
                      <span className="text-muted-foreground">
                        {" "}
                        · Ref {e.reference_no}
                      </span>
                    ) : null}
                  </p>
                  <p className="text-muted-foreground text-xs">
                    {e.filed_at
                      ? `Filed ${new Date(e.filed_at).toLocaleDateString("en-GB", { day: "numeric", month: "short", year: "numeric" })}`
                      : `Drafted ${new Date(e.created_at).toLocaleDateString("en-GB", { day: "numeric", month: "short", year: "numeric" })}`}
                  </p>
                </div>
                <div className="flex items-center gap-2">
                  <Badge variant="outline">{OUTCOME_LABELS[e.outcome] ?? e.outcome}</Badge>
                  {e.reference_no ? (
                    <Button
                      size="sm"
                      variant="outline"
                      className="h-8 text-xs"
                      disabled={pending}
                      onClick={() => checkStatus(e.id)}
                    >
                      Check status
                    </Button>
                  ) : null}
                  {e.outcome !== "drafted" ? (
                    <Select
                      value={e.outcome}
                      onValueChange={(v) => updateOutcome(e.id, v)}
                    >
                      <SelectTrigger className="h-8 w-44 text-xs" size="sm">
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        {["filed", "acknowledged", "resolved", "no_response"].map(
                          (o) => (
                            <SelectItem key={o} value={o}>
                              {OUTCOME_LABELS[o]}
                            </SelectItem>
                          )
                        )}
                      </SelectContent>
                    </Select>
                  ) : null}
                </div>
              </div>
            ))}
          </CardContent>
        </Card>
      ) : null}
    </div>
  );
}
