import type { Metadata } from "next";
import Link from "next/link";
import { BarChart3, Plus } from "lucide-react";
import { createClient } from "@/lib/supabase/server";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { SurveyActiveToggle } from "./active-toggle";

export const metadata: Metadata = { title: "Admin · Surveys" };

export default async function AdminSurveysPage() {
  const supabase = await createClient();
  const { data: surveys } = await supabase
    .from("surveys")
    .select("id, title, is_active, questions, created_at")
    .order("created_at", { ascending: false });

  const all = surveys ?? [];

  // response counts
  const counts = new Map<number, number>();
  if (all.length) {
    const { data: responses } = await supabase
      .from("survey_responses")
      .select("survey_id")
      .in(
        "survey_id",
        all.map((s) => s.id)
      );
    for (const r of responses ?? [])
      counts.set(r.survey_id, (counts.get(r.survey_id) ?? 0) + 1);
  }

  return (
    <div className="mx-auto max-w-5xl space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold">Surveys</h1>
          <p className="text-muted-foreground text-sm">
            Create community surveys and review responses.
          </p>
        </div>
        <Button
          className="bg-brand-gradient border-0 text-white hover:opacity-90"
          asChild
        >
          <Link href="/admin/surveys/new">
            <Plus className="size-4" /> New survey
          </Link>
        </Button>
      </div>

      {all.length === 0 ? (
        <div className="text-muted-foreground rounded-xl border py-16 text-center text-sm">
          No surveys yet.
        </div>
      ) : (
        <ul className="space-y-2">
          {all.map((s) => {
            const qCount = Array.isArray(s.questions) ? s.questions.length : 0;
            return (
              <li key={s.id}>
                <Card className="py-0">
                  <CardContent className="flex flex-wrap items-center justify-between gap-3 py-3">
                    <div className="min-w-0">
                      <p className="truncate font-medium">{s.title}</p>
                      <p className="text-muted-foreground text-xs">
                        {qCount} question{qCount === 1 ? "" : "s"} ·{" "}
                        {counts.get(s.id) ?? 0} response
                        {(counts.get(s.id) ?? 0) === 1 ? "" : "s"}
                      </p>
                    </div>
                    <div className="flex items-center gap-3">
                      <SurveyActiveToggle id={s.id} active={s.is_active} />
                      {s.is_active ? (
                        <Badge className="bg-status-resolved/15 text-status-resolved border-status-resolved/30">
                          Active
                        </Badge>
                      ) : (
                        <Badge variant="secondary">Inactive</Badge>
                      )}
                      <Button size="sm" variant="outline" asChild>
                        <Link href={`/admin/surveys/${s.id}`}>
                          <BarChart3 className="size-4" aria-hidden /> Results
                        </Link>
                      </Button>
                    </div>
                  </CardContent>
                </Card>
              </li>
            );
          })}
        </ul>
      )}
    </div>
  );
}
