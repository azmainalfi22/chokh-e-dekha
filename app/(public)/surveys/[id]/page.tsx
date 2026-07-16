import type { Metadata } from "next";
import { notFound } from "next/navigation";
import { createClient } from "@/lib/supabase/server";
import type { SurveyQuestion } from "@/lib/validations";
import { SurveyForm } from "./survey-form";

export const metadata: Metadata = { title: "Survey" };

export default async function SurveyDetailPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  const surveyId = Number(id);
  if (!Number.isInteger(surveyId)) notFound();

  const supabase = await createClient();
  const { data: survey } = await supabase
    .from("surveys")
    .select("id, title, description, questions, is_active")
    .eq("id", surveyId)
    .maybeSingle();

  if (!survey || !survey.is_active) notFound();

  const questions = (
    Array.isArray(survey.questions) ? survey.questions : []
  ) as unknown as SurveyQuestion[];

  return (
    <div className="mx-auto w-full max-w-2xl px-4 py-8 sm:px-6">
      <h1 className="text-2xl font-bold">{survey.title}</h1>
      {survey.description ? (
        <p className="text-muted-foreground mt-1 text-sm">
          {survey.description}
        </p>
      ) : null}
      <div className="mt-6">
        <SurveyForm surveyId={survey.id} questions={questions} />
      </div>
    </div>
  );
}
