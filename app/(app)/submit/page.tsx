import type { Metadata } from "next";
import { APP_NAME_BN, APP_TAGLINE } from "@/lib/constants";
import { SubmitWizard } from "./submit-wizard";

export const metadata: Metadata = { title: "Submit Report" };

export default function SubmitPage() {
  return (
    <div className="mx-auto w-full max-w-3xl px-4 py-10 sm:px-6">
      <div className="text-center">
        <h1 className="text-3xl font-extrabold tracking-tight">
          <span className="font-bengali text-brand-gradient">{APP_NAME_BN}</span>{" "}
          — Submit Report
        </h1>
        <p className="text-muted-foreground mt-1 text-sm">{APP_TAGLINE}</p>
        <p className="text-muted-foreground mx-auto mt-3 max-w-xl text-sm">
          Help improve your city by reporting issues directly to authorities.
          Your voice matters in building a better community.
        </p>
      </div>
      <div className="mt-8">
        <SubmitWizard />
      </div>
    </div>
  );
}
