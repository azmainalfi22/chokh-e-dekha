import type { Metadata } from "next";
import { LogoEmblem } from "@/components/layout/logo-emblem";
import { SignupForm } from "./signup-form";

export const metadata: Metadata = { title: "Sign up" };

export default function SignupPage() {
  return (
    <div className="mx-auto flex w-full max-w-md flex-col px-4 py-14">
      <LogoEmblem className="mx-auto w-24 sm:w-28" />
      <SignupForm />
    </div>
  );
}
