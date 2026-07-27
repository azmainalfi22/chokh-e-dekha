import type { Metadata } from "next";
import Link from "next/link";
import { notFound, redirect } from "next/navigation";
import { ArrowLeft } from "lucide-react";
import { createClient } from "@/lib/supabase/server";
import { Button } from "@/components/ui/button";
import { RtiLetterView } from "./letter-view";
import { RtiLifecycle } from "./rti-lifecycle";

export const metadata: Metadata = { title: "RTI Letter" };

export default async function RtiLetterPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  const letterId = Number(id);
  if (!Number.isInteger(letterId)) notFound();

  const supabase = await createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();
  if (!user) redirect("/login");

  const { data: letter } = await supabase
    .from("rti_letters")
    .select(
      "id, authority, subject, body, language, created_at, status, submitted_at, deadline_at, responded_at, outcome, appeal_body"
    )
    .eq("id", letterId)
    .maybeSingle();

  if (!letter) notFound();

  return (
    <div className="mx-auto w-full max-w-3xl space-y-4 px-4 py-8 sm:px-6">
      <div className="flex items-center justify-between print:hidden">
        <Button variant="ghost" size="sm" asChild>
          <Link href="/rti">
            <ArrowLeft className="size-4" aria-hidden /> RTI letters
          </Link>
        </Button>
      </div>
      <RtiLifecycle
        id={letter.id}
        status={letter.status}
        submittedAt={letter.submitted_at}
        deadlineAt={letter.deadline_at}
        respondedAt={letter.responded_at}
        outcome={letter.outcome}
        appealBody={letter.appeal_body}
        language={letter.language as "en" | "bn"}
      />
      <RtiLetterView
        id={letter.id}
        subject={letter.subject}
        body={letter.body}
        language={letter.language as "en" | "bn"}
      />
    </div>
  );
}
