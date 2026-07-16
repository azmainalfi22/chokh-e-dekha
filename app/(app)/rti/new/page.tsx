import type { Metadata } from "next";
import { redirect } from "next/navigation";
import { createClient } from "@/lib/supabase/server";
import { RtiWizard } from "./rti-wizard";

export const metadata: Metadata = { title: "New RTI Letter" };

export default async function NewRtiPage() {
  const supabase = await createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();
  if (!user) redirect("/login");

  const { data: profile } = await supabase
    .from("profiles")
    .select("display_name, city")
    .eq("id", user.id)
    .single();

  return (
    <div className="mx-auto w-full max-w-3xl px-4 py-8 sm:px-6">
      <h1 className="text-2xl font-bold">New RTI Letter</h1>
      <p className="text-muted-foreground mt-1 text-sm">
        Four quick steps to a compliant Right to Information application.
      </p>
      <div className="mt-6">
        <RtiWizard
          defaultName={profile?.display_name ?? ""}
          defaultEmail={user.email ?? ""}
        />
      </div>
    </div>
  );
}
