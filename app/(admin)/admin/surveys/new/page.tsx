import type { Metadata } from "next";
import { SurveyBuilder } from "./survey-builder";

export const metadata: Metadata = { title: "Admin · New Survey" };

export default function NewSurveyPage() {
  return (
    <div className="mx-auto max-w-3xl">
      <h1 className="text-2xl font-bold">New survey</h1>
      <p className="text-muted-foreground mt-1 text-sm">
        Add questions, then publish. Active surveys appear on the public
        Surveys page.
      </p>
      <div className="mt-6">
        <SurveyBuilder />
      </div>
    </div>
  );
}
