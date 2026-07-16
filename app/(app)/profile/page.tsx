import type { Metadata } from "next";
import { redirect } from "next/navigation";
import { createClient } from "@/lib/supabase/server";
import { ProfileForm } from "./profile-form";

export const metadata: Metadata = { title: "Profile" };

export default async function ProfilePage() {
  const supabase = await createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();
  if (!user) redirect("/login");

  const { data: profile } = await supabase
    .from("profiles")
    .select("display_name, phone, city")
    .eq("id", user.id)
    .single();

  return (
    <div className="mx-auto w-full max-w-2xl px-4 py-8 sm:px-6">
      <h1 className="text-2xl font-bold">Your profile</h1>
      <p className="text-muted-foreground mt-1 text-sm">
        Your display name is public; your phone number is never shown to
        anyone.
      </p>
      <div className="mt-6">
        <ProfileForm
          email={user.email ?? ""}
          displayName={profile?.display_name ?? ""}
          phone={profile?.phone ?? ""}
          city={profile?.city ?? ""}
        />
      </div>
    </div>
  );
}
