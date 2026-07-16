import type { Metadata } from "next";
import { SignupForm } from "./signup-form";

export const metadata: Metadata = { title: "Sign up" };

export default function SignupPage() {
  return (
    <div className="mx-auto flex w-full max-w-md flex-col px-4 py-16">
      <SignupForm />
    </div>
  );
}
