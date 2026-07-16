import type { Metadata } from "next";
import Link from "next/link";
import { ClipboardList } from "lucide-react";
import { createClient } from "@/lib/supabase/server";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

export const metadata: Metadata = { title: "Community Surveys" };

export default async function SurveysPage() {
  const supabase = await createClient();
  const { data: surveys } = await supabase
    .from("surveys")
    .select("id, title, description, questions, created_at")
    .eq("is_active", true)
    .order("created_at", { ascending: false });

  const all = surveys ?? [];

  return (
    <div className="mx-auto w-full max-w-4xl space-y-6 px-4 py-8 sm:px-6">
      <div>
        <h1 className="text-brand-gradient text-3xl font-extrabold tracking-tight">
          Community Surveys
        </h1>
        <p className="text-muted-foreground mt-1 text-sm">
          Share your view on civic issues — your input shapes local priorities.
        </p>
      </div>

      {all.length === 0 ? (
        <div className="flex flex-col items-center gap-3 rounded-xl border py-20 text-center">
          <ClipboardList className="text-muted-foreground size-12" aria-hidden />
          <h2 className="text-lg font-semibold">No active surveys</h2>
          <p className="text-muted-foreground max-w-sm text-sm">
            Check back soon — new community surveys appear here.
          </p>
        </div>
      ) : (
        <div className="grid gap-4 sm:grid-cols-2">
          {all.map((s) => {
            const count = Array.isArray(s.questions) ? s.questions.length : 0;
            return (
              <Card key={s.id} className="flex flex-col">
                <CardHeader>
                  <CardTitle className="text-lg">{s.title}</CardTitle>
                  {s.description ? (
                    <p className="text-muted-foreground text-sm">
                      {s.description}
                    </p>
                  ) : null}
                </CardHeader>
                <CardContent className="mt-auto flex items-center justify-between">
                  <span className="text-muted-foreground text-xs">
                    {count} question{count === 1 ? "" : "s"}
                  </span>
                  <Button
                    size="sm"
                    className="bg-brand-gradient border-0 text-white hover:opacity-90"
                    asChild
                  >
                    <Link href={`/surveys/${s.id}`}>Take survey</Link>
                  </Button>
                </CardContent>
              </Card>
            );
          })}
        </div>
      )}
    </div>
  );
}
