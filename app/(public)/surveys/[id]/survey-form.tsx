"use client";

import { useState } from "react";
import { CheckCircle2, Loader2, Send, Star } from "lucide-react";
import { toast } from "sonner";
import { submitSurveyResponse } from "@/lib/actions/surveys";
import type { SurveyQuestion } from "@/lib/validations";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { cn } from "@/lib/utils";

export function SurveyForm({
  surveyId,
  questions,
}: {
  surveyId: number;
  questions: SurveyQuestion[];
}) {
  const [answers, setAnswers] = useState<Record<string, string>>({});
  const [submitting, setSubmitting] = useState(false);
  const [done, setDone] = useState(false);

  function setAnswer(id: string, value: string) {
    setAnswers((prev) => ({ ...prev, [id]: value }));
  }

  async function submit() {
    setSubmitting(true);
    const result = await submitSurveyResponse(surveyId, answers);
    setSubmitting(false);
    if (!result.ok) {
      toast.error(result.error);
      return;
    }
    setDone(true);
  }

  if (done) {
    return (
      <Card>
        <CardContent className="flex flex-col items-center gap-3 py-12 text-center">
          <span className="bg-status-resolved/15 text-status-resolved flex size-14 items-center justify-center rounded-full">
            <CheckCircle2 className="size-7" aria-hidden />
          </span>
          <h2 className="text-xl font-semibold">Thank you!</h2>
          <p className="text-muted-foreground text-sm">
            Your response has been recorded.
          </p>
        </CardContent>
      </Card>
    );
  }

  return (
    <Card>
      <CardContent className="space-y-6 pt-6">
        {questions.map((q, i) => (
          <div key={q.id} className="space-y-2">
            <Label className="text-base">
              {i + 1}. {q.label}
            </Label>

            {q.type === "text" ? (
              <Textarea
                rows={3}
                value={answers[q.id] ?? ""}
                onChange={(e) => setAnswer(q.id, e.target.value)}
                placeholder="Your answer…"
              />
            ) : null}

            {q.type === "single" && q.options ? (
              <div className="flex flex-col gap-2">
                {q.options.map((opt) => (
                  <label
                    key={opt}
                    className={cn(
                      "flex cursor-pointer items-center gap-2 rounded-lg border px-3 py-2 text-sm transition-colors",
                      answers[q.id] === opt
                        ? "border-primary bg-primary/5"
                        : "hover:bg-accent/50"
                    )}
                  >
                    <input
                      type="radio"
                      name={q.id}
                      value={opt}
                      checked={answers[q.id] === opt}
                      onChange={() => setAnswer(q.id, opt)}
                      className="accent-[var(--primary)]"
                    />
                    {opt}
                  </label>
                ))}
              </div>
            ) : null}

            {q.type === "rating" ? (
              <div className="flex items-center gap-1">
                {[1, 2, 3, 4, 5].map((n) => (
                  <button
                    key={n}
                    type="button"
                    onClick={() => setAnswer(q.id, String(n))}
                    aria-label={`${n} star${n === 1 ? "" : "s"}`}
                    className="p-1"
                  >
                    <Star
                      className={cn(
                        "size-6 transition-colors",
                        Number(answers[q.id]) >= n
                          ? "fill-status-pending text-status-pending"
                          : "text-muted-foreground/40"
                      )}
                      aria-hidden
                    />
                  </button>
                ))}
              </div>
            ) : null}
          </div>
        ))}

        <Button
          onClick={submit}
          disabled={submitting}
          className="bg-brand-gradient w-full border-0 text-white hover:opacity-90"
        >
          {submitting ? (
            <Loader2 className="size-4 animate-spin" />
          ) : (
            <Send className="size-4" />
          )}
          Submit response
        </Button>
      </CardContent>
    </Card>
  );
}
