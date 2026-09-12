"use client";

import { useState, useTransition } from "react";
import Link from "next/link";
import { AlertTriangle, ArrowRight, Check, X } from "lucide-react";
import { toast } from "sonner";
import { resolveSuggestion } from "@/lib/actions/admin";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { cn } from "@/lib/utils";

/** Below this the suggestion is presented as uncertain rather than as a nudge. */
const REVIEW_THRESHOLD = 0.5;

type Props = {
  id: number;
  title: string;
  description: string;
  currentCategory: string;
  currentPriority: string | null;
  suggestion: {
    category?: string;
    priority?: string;
    confidence?: number;
    rationale?: string;
    provider?: string;
  };
  suggestedAt: string | null;
};

export function SuggestionCard(props: Props) {
  const [pending, startTransition] = useTransition();
  const [done, setDone] = useState<"accepted" | "rejected" | null>(null);

  const confidence = props.suggestion.confidence ?? 0;
  const uncertain = confidence < REVIEW_THRESHOLD;
  const changesCategory = props.suggestion.category !== props.currentCategory;

  function decide(accept: boolean) {
    startTransition(async () => {
      const result = await resolveSuggestion(props.id, accept);
      if (!result.ok) {
        toast.error(result.error);
        return;
      }
      setDone(accept ? "accepted" : "rejected");
      toast.success(accept ? "Suggestion applied" : "Suggestion dismissed");
    });
  }

  if (done) {
    return (
      <Card className="border-dashed">
        <CardContent className="text-muted-foreground flex items-center gap-2 py-4 text-sm">
          {done === "accepted" ? (
            <Check className="text-status-resolved size-4" aria-hidden />
          ) : (
            <X className="size-4" aria-hidden />
          )}
          <span>
            &ldquo;{props.title}&rdquo; — suggestion {done}.
          </span>
        </CardContent>
      </Card>
    );
  }

  return (
    <Card>
      <CardContent className="space-y-4 py-5">
        <div className="flex flex-wrap items-start justify-between gap-3">
          <div className="min-w-0">
            <Link
              href={`/reports/${props.id}`}
              className="font-medium hover:underline"
            >
              {props.title}
            </Link>
            <p className="text-muted-foreground mt-1 line-clamp-2 text-sm">
              {props.description}
            </p>
          </div>

          <Badge
            variant="outline"
            className={cn(
              "shrink-0 tabular-nums",
              uncertain
                ? "border-status-pending/40 text-status-pending"
                : "border-status-resolved/40 text-status-resolved"
            )}
          >
            {Math.round(confidence * 100)}% confident
          </Badge>
        </div>

        <div className="bg-muted/50 flex flex-wrap items-center gap-x-3 gap-y-2 rounded-md border p-3 text-sm">
          <span className="text-muted-foreground">Currently</span>
          <Badge variant="outline">{props.currentCategory}</Badge>

          <ArrowRight className="text-muted-foreground size-4" aria-hidden />

          <span className="text-muted-foreground">Suggested</span>
          <Badge variant={changesCategory ? "default" : "outline"}>
            {props.suggestion.category ?? "—"}
          </Badge>

          {props.suggestion.priority ? (
            <Badge variant="outline" className="capitalize">
              {props.suggestion.priority} priority
            </Badge>
          ) : null}
        </div>

        <p className="text-muted-foreground text-sm">
          {props.suggestion.rationale}
          {props.suggestion.provider ? (
            <span className="ml-1 opacity-70">({props.suggestion.provider})</span>
          ) : null}
        </p>

        {uncertain ? (
          <p className="text-status-pending flex items-start gap-2 text-sm">
            <AlertTriangle className="mt-0.5 size-4 shrink-0" aria-hidden />
            <span>
              Low confidence — worth reading the report before accepting this.
            </span>
          </p>
        ) : null}

        {!changesCategory ? (
          <p className="text-muted-foreground text-sm">
            This matches the category already on the report. Accepting only
            records that it was checked.
          </p>
        ) : null}

        <div className="flex flex-wrap gap-2">
          <Button size="sm" disabled={pending} onClick={() => decide(true)}>
            <Check className="size-4" aria-hidden />
            Accept
          </Button>
          <Button
            size="sm"
            variant="outline"
            disabled={pending}
            onClick={() => decide(false)}
          >
            <X className="size-4" aria-hidden />
            Dismiss
          </Button>
          <Button size="sm" variant="ghost" asChild>
            <Link href={`/reports/${props.id}`}>Open report</Link>
          </Button>
        </div>
      </CardContent>
    </Card>
  );
}
