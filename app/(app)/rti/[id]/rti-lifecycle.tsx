"use client";

import { useRouter } from "next/navigation";
import { useState, useTransition } from "react";
import {
  CalendarClock,
  CheckCircle2,
  Copy,
  Gavel,
  Loader2,
  Printer,
  Send,
} from "lucide-react";
import { toast } from "sonner";
import {
  generateAppeal,
  markRtiSubmitted,
  recordRtiResponse,
} from "@/lib/actions/rti";
import {
  RTI_OUTCOME_LABELS,
  RTI_STATUS_LABELS,
  workingDaysBetween,
} from "@/lib/rti";
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

type Props = {
  id: number;
  status: string;
  submittedAt: string | null;
  deadlineAt: string | null;
  respondedAt: string | null;
  outcome: string | null;
  appealBody: string | null;
  language: "en" | "bn";
};

const STEPS = ["drafted", "submitted", "responded", "appealed"] as const;
const STEP_INDEX: Record<string, number> = {
  drafted: 0,
  submitted: 1,
  responded: 2,
  closed: 2,
  appealed: 3,
};

export function RtiLifecycle(props: Props) {
  const router = useRouter();
  const [pending, startTransition] = useTransition();
  const [date, setDate] = useState(new Date().toISOString().slice(0, 10));
  const [outcome, setOutcome] = useState<string>("");

  const deadline = props.deadlineAt ? new Date(props.deadlineAt) : null;
  const now = new Date();
  const daysLeft = deadline ? workingDaysBetween(now, deadline) : 0;
  const overdue = deadline ? deadline.getTime() < now.getTime() : false;
  const canAppeal =
    (props.status === "submitted" && overdue) ||
    props.outcome === "refused" ||
    props.outcome === "partial";
  const activeStep = STEP_INDEX[props.status] ?? 0;

  function submit() {
    startTransition(async () => {
      const r = await markRtiSubmitted(props.id, date);
      if (!r.ok) toast.error(r.error);
      else {
        toast.success("Submission logged — the 20-working-day clock is running");
        router.refresh();
      }
    });
  }

  function respond() {
    if (!outcome) {
      toast.error("Pick what the authority did");
      return;
    }
    startTransition(async () => {
      const r = await recordRtiResponse(props.id, outcome as never);
      if (!r.ok) toast.error(r.error);
      else {
        toast.success("Response recorded");
        router.refresh();
      }
    });
  }

  function appeal() {
    startTransition(async () => {
      const r = await generateAppeal(props.id);
      if (!r.ok) toast.error(r.error);
      else {
        toast.success("Appeal generated — review, print and submit it");
        router.refresh();
      }
    });
  }

  async function copyAppeal() {
    if (props.appealBody) {
      await navigator.clipboard.writeText(props.appealBody);
      toast.success("Appeal copied");
    }
  }

  return (
    <Card className="print:hidden">
      <CardHeader>
        <CardTitle className="flex items-center gap-2 text-lg">
          <CalendarClock className="text-primary size-5" aria-hidden />
          Application lifecycle
        </CardTitle>
      </CardHeader>
      <CardContent className="space-y-5">
        {/* Stepper */}
        <ol className="flex items-center">
          {STEPS.map((s, i) => {
            const done = i < activeStep;
            const current = i === activeStep;
            return (
              <li key={s} className="flex flex-1 items-center last:flex-none">
                <div className="flex flex-col items-center gap-1">
                  <span
                    className={cn(
                      "flex size-8 items-center justify-center rounded-full border text-xs font-bold",
                      done || current
                        ? "bg-brand-gradient border-0 text-white"
                        : "text-muted-foreground bg-muted"
                    )}
                  >
                    {done ? <CheckCircle2 className="size-4" aria-hidden /> : i + 1}
                  </span>
                  <span
                    className={cn(
                      "text-[11px] font-medium",
                      current ? "text-foreground" : "text-muted-foreground"
                    )}
                  >
                    {RTI_STATUS_LABELS[s]}
                  </span>
                </div>
                {i < STEPS.length - 1 ? (
                  <span
                    className={cn(
                      "mx-1 h-0.5 flex-1",
                      i < activeStep ? "bg-primary" : "bg-border"
                    )}
                    aria-hidden
                  />
                ) : null}
              </li>
            );
          })}
        </ol>

        {/* Deadline banner */}
        {props.status === "submitted" && deadline ? (
          <div
            className={cn(
              "rounded-lg border p-3 text-sm",
              overdue
                ? "border-status-breach/30 bg-status-breach/5 text-status-breach"
                : daysLeft <= 5
                  ? "border-status-pending/40 bg-status-pending/5"
                  : "border-status-progress/30 bg-status-progress/5"
            )}
          >
            {overdue ? (
              <p className="font-medium">
                Overdue — the statutory deadline (
                {deadline.toLocaleDateString("en-GB", {
                  day: "numeric",
                  month: "short",
                  year: "numeric",
                })}
                ) has passed. You can now appeal.
              </p>
            ) : (
              <p>
                <span className="font-semibold">
                  ~{daysLeft} working day{daysLeft === 1 ? "" : "s"} left
                </span>{" "}
                — response due by{" "}
                {deadline.toLocaleDateString("en-GB", {
                  day: "numeric",
                  month: "short",
                  year: "numeric",
                })}
                .
              </p>
            )}
          </div>
        ) : null}

        {props.outcome ? (
          <p className="text-sm">
            Outcome recorded:{" "}
            <Badge
              variant="outline"
              className={
                props.outcome === "received"
                  ? "border-status-resolved/40 text-status-resolved"
                  : "border-status-breach/40 text-status-breach"
              }
            >
              {RTI_OUTCOME_LABELS[props.outcome] ?? props.outcome}
            </Badge>
          </p>
        ) : null}

        {/* Actions by stage */}
        {props.status === "drafted" ? (
          <div className="border-t pt-4">
            <p className="text-sm font-medium">
              Submitted this to the authority?
            </p>
            <p className="text-muted-foreground mt-0.5 text-xs">
              Log the date you filed it and we&apos;ll track the
              20-working-day deadline for you.
            </p>
            <div className="mt-2 flex flex-wrap gap-2">
              <Input
                type="date"
                value={date}
                max={new Date().toISOString().slice(0, 10)}
                onChange={(e) => setDate(e.target.value)}
                className="max-w-44"
              />
              <Button
                disabled={pending}
                onClick={submit}
                className="bg-brand-gradient border-0 text-white hover:opacity-95"
              >
                {pending ? (
                  <Loader2 className="size-4 animate-spin" aria-hidden />
                ) : (
                  <Send className="size-4" aria-hidden />
                )}
                Mark as submitted
              </Button>
            </div>
          </div>
        ) : null}

        {props.status === "submitted" || props.status === "responded" ? (
          <div className="border-t pt-4">
            <p className="text-sm font-medium">Record the authority&apos;s response</p>
            <div className="mt-2 flex flex-wrap gap-2">
              <Select value={outcome} onValueChange={setOutcome}>
                <SelectTrigger className="w-56">
                  <SelectValue placeholder="What did they do?" />
                </SelectTrigger>
                <SelectContent>
                  {Object.entries(RTI_OUTCOME_LABELS).map(([v, label]) => (
                    <SelectItem key={v} value={v}>
                      {label}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
              <Button variant="outline" disabled={pending} onClick={respond}>
                Save outcome
              </Button>
            </div>
          </div>
        ) : null}

        {canAppeal && props.status !== "appealed" ? (
          <div className="border-status-breach/25 bg-status-breach/5 rounded-lg border p-3">
            <p className="flex items-center gap-2 text-sm font-medium">
              <Gavel className="text-status-breach size-4" aria-hidden />
              Eligible to appeal
            </p>
            <p className="text-muted-foreground mt-0.5 text-xs">
              Under Section 24 of the RTI Act you may appeal to the Appellate
              Authority. We&apos;ll draft it from your application.
            </p>
            <Button
              size="sm"
              disabled={pending}
              onClick={appeal}
              className="bg-brand-gradient mt-2 border-0 text-white hover:opacity-95"
            >
              {pending ? (
                <Loader2 className="size-4 animate-spin" aria-hidden />
              ) : (
                <Gavel className="size-4" aria-hidden />
              )}
              Generate appeal letter
            </Button>
          </div>
        ) : null}

        {props.status === "closed" ? (
          <div className="border-status-resolved/30 bg-status-resolved/5 text-status-resolved flex items-center gap-2 rounded-lg border p-3 text-sm font-medium">
            <CheckCircle2 className="size-4.5" aria-hidden />
            Information received — this request is closed. Well done holding the
            authority to account.
          </div>
        ) : null}

        {/* Generated appeal */}
        {props.appealBody ? (
          <div className="border-t pt-4">
            <div className="flex flex-wrap items-center justify-between gap-2">
              <p className="text-sm font-medium">Appeal to the Appellate Authority</p>
              <div className="flex gap-2">
                <Button variant="outline" size="sm" onClick={copyAppeal}>
                  <Copy className="size-4" aria-hidden /> Copy
                </Button>
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => window.print()}
                >
                  <Printer className="size-4" aria-hidden /> Print
                </Button>
              </div>
            </div>
            <pre
              className={cn(
                "print-area bg-muted/40 mt-2 rounded-lg border p-4 text-sm leading-relaxed whitespace-pre-wrap",
                props.language === "bn" ? "font-bengali" : "font-sans"
              )}
            >
              {props.appealBody}
            </pre>
            <p className="text-muted-foreground mt-2 text-xs">
              If the Appellate Authority also fails you, you may complain to the
              Information Commission within 30 days of their decision.
            </p>
          </div>
        ) : null}
      </CardContent>
    </Card>
  );
}
