import type { Metadata } from "next";
import Image from "next/image";
import { Suspense } from "react";
import { LoginForm } from "./login-form";

export const metadata: Metadata = { title: "Log in" };

export default function LoginPage() {
  return (
    <div className="mx-auto flex w-full max-w-md flex-col px-4 py-14">
      <div className="relative mx-auto size-28 overflow-hidden rounded-3xl shadow-lg ring-1 ring-black/5 sm:size-32">
        <Image
          src="/brand/logo-tile.png"
          alt="Chokh-e-Dekha emblem"
          fill
          sizes="128px"
          priority
        />
      </div>
      <Suspense>
        <LoginForm />
      </Suspense>
    </div>
  );
}
