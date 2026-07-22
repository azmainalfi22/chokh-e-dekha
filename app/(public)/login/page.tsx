import type { Metadata } from "next";
import { Suspense } from "react";
import { LogoEmblem } from "@/components/layout/logo-emblem";
import { LoginForm } from "./login-form";

export const metadata: Metadata = { title: "Log in" };

export default function LoginPage() {
  return (
    <div className="mx-auto flex w-full max-w-md flex-col px-4 py-14">
      <LogoEmblem className="mx-auto w-24 sm:w-28" />
      <Suspense>
        <LoginForm />
      </Suspense>
    </div>
  );
}
