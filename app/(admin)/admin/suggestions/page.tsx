import Link from "next/link";
import { redirect } from "next/navigation";
import { Sparkles } from "lucide-react";
import { createClient } from "@/lib/supabase/server";
import { Card, CardContent } from "@/components/ui/card";
import { SuggestionCard } from "./suggestion-card";

export const metadata = { title: "Category suggestions" };

type Suggestion = {
  category?: string;
  priority?: string;
  confidence?: number;
  rationale?: string;
  provider?: string;
};

/**
 * Category suggestions waiting on a decision.
 *
 * The suggestion never touches the report on its own. It sits in a column until
 * somebody here accepts it, which is the point of the feature: a misfiled report
 * reaches the wrong desk and misses its deadline, so nothing refiles itself
 * quietly.
 */
export default async function SuggestionsPage() {
  const supabase = await createClient();

  const {
    data: { user },
  } = await supabase.auth.getUser();
  if (!user) redirect("/login");

  const { data: profile } = await supabase
    .from("profiles")
    .select("role")
    .eq("id", user.id)
    .single();
  if (profile?.role !== "admin") redirect("/dashboard");

  const { data } = await supabase
    .from("reports")
    .select(
      "id, title, description, category, priority, ai_suggestion, ai_suggested_at"
    )
    .not("ai_suggestion", "is", null)
    .is("ai_confirmed_at", null)
    .order("ai_suggested_at", { ascending: false })
    .limit(50);

  const rows = (data ?? []).map((r) => ({
    id: r.id,
    title: r.title,
    description: r.description,
    currentCategory: r.category,
    currentPriority: r.priority,
    suggestion: (r.ai_suggestion ?? {}) as Suggestion,
    suggestedAt: r.ai_suggested_at,
  }));

  return (
    <div className="mx-auto max-w-4xl space-y-5">
      <div>
        <h1 className="text-2xl font-bold">Category suggestions</h1>
        <p className="text-muted-foreground mt-1 text-sm">
          Suggestions are held here and change nothing until you accept them.
          Rejecting one is recorded too — it is the more useful signal of the two.
        </p>
      </div>

      {rows.length === 0 ? (
        <Card>
          <CardContent className="flex flex-col items-center gap-3 py-14 text-center">
            <span className="bg-muted text-muted-foreground flex size-11 items-center justify-center rounded-full">
              <Sparkles className="size-5" aria-hidden />
            </span>
            <div>
              <p className="font-medium">Nothing waiting</p>
              <p className="text-muted-foreground mt-1 text-sm">
                Every suggestion has been reviewed. New ones appear here as
                reports come in.
              </p>
            </div>
            <Link
              href="/admin/reports"
              className="text-primary text-sm font-medium hover:underline"
            >
              Go to the reports queue
            </Link>
          </CardContent>
        </Card>
      ) : (
        <div className="space-y-3">
          {rows.map((row) => (
            <SuggestionCard key={row.id} {...row} />
          ))}
        </div>
      )}
    </div>
  );
}
