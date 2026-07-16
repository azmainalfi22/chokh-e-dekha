import type { Metadata } from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import { ArrowLeft, MessageSquare, Star } from "lucide-react";
import { createClient } from "@/lib/supabase/server";
import type { SurveyQuestion } from "@/lib/validations";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { BreakdownBars } from "@/components/admin/charts";

export const metadata: Metadata = { title: "Admin · Survey Results" };

export default async function SurveyResultsPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  const surveyId = Number(id);
  if (!Number.isInteger(surveyId)) notFound();

  const supabase = await createClient();
  const [{ data: survey }, { data: responses }] = await Promise.all([
    supabase
      .from("surveys")
      .select("id, title, questions")
      .eq("id", surveyId)
      .maybeSingle(),
    supabase
      .from("survey_responses")
      .select("answers, created_at")
      .eq("survey_id", surveyId)
      .order("created_at", { ascending: false }),
  ]);

  if (!survey) notFound();

  const questions = (
    Array.isArray(survey.questions) ? survey.questions : []
  ) as unknown as SurveyQuestion[];
  const rows = responses ?? [];

  return (
    <div className="mx-auto max-w-4xl space-y-4">
      <Button variant="ghost" size="sm" asChild>
        <Link href="/admin/surveys">
          <ArrowLeft className="size-4" aria-hidden /> Surveys
        </Link>
      </Button>
      <div>
        <h1 className="text-2xl font-bold">{survey.title}</h1>
        <p className="text-muted-foreground text-sm">
          {rows.length} response{rows.length === 1 ? "" : "s"}
        </p>
      </div>

      {rows.length === 0 ? (
        <div className="text-muted-foreground rounded-xl border py-16 text-center text-sm">
          No responses yet.
        </div>
      ) : (
        <div className="space-y-4">
          {questions.map((q, i) => {
            const values = rows
              .map((r) => (r.answers as Record<string, string>)?.[q.id])
              .filter((v): v is string => typeof v === "string" && v !== "");

            return (
              <Card key={q.id}>
                <CardHeader>
                  <CardTitle className="text-base">
                    {i + 1}. {q.label}
                  </CardTitle>
                  <p className="text-muted-foreground text-xs">
                    {values.length} answered
                  </p>
                </CardHeader>
                <CardContent>
                  {q.type === "single" ? (
                    <BreakdownBars
                      data={tally(values)}
                      height={Math.max(120, tally(values).length * 34)}
                    />
                  ) : null}

                  {q.type === "rating" ? (
                    <div className="space-y-2">
                      <p className="flex items-center gap-1.5 text-2xl font-bold">
                        <Star
                          className="fill-status-pending text-status-pending size-6"
                          aria-hidden
                        />
                        {average(values).toFixed(1)}
                        <span className="text-muted-foreground text-sm font-normal">
                          / 5 average
                        </span>
                      </p>
                      <BreakdownBars
                        data={[5, 4, 3, 2, 1].map((n) => ({
                          name: `${n} star${n === 1 ? "" : "s"}`,
                          count: values.filter((v) => Number(v) === n).length,
                        }))}
                        height={180}
                      />
                    </div>
                  ) : null}

                  {q.type === "text" ? (
                    <ul className="max-h-72 space-y-2 overflow-y-auto">
                      {values.map((v, j) => (
                        <li
                          key={j}
                          className="bg-muted/50 flex gap-2 rounded-lg p-2.5 text-sm"
                        >
                          <MessageSquare
                            className="text-muted-foreground mt-0.5 size-4 shrink-0"
                            aria-hidden
                          />
                          {v}
                        </li>
                      ))}
                    </ul>
                  ) : null}
                </CardContent>
              </Card>
            );
          })}
        </div>
      )}
    </div>
  );
}

function tally(values: string[]): { name: string; count: number }[] {
  const map = new Map<string, number>();
  for (const v of values) map.set(v, (map.get(v) ?? 0) + 1);
  return [...map.entries()]
    .map(([name, count]) => ({ name, count }))
    .sort((a, b) => b.count - a.count);
}

function average(values: string[]): number {
  const nums = values.map(Number).filter((n) => !Number.isNaN(n));
  return nums.length ? nums.reduce((a, b) => a + b, 0) / nums.length : 0;
}
