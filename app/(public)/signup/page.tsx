import type { Metadata } from "next";
import Image from "next/image";
import { SignupForm } from "./signup-form";

export const metadata: Metadata = { title: "Sign up" };

export default function SignupPage() {
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
      <SignupForm />
    </div>
  );
}
