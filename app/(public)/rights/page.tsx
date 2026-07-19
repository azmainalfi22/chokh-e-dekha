import type { Metadata } from "next";
import Link from "next/link";
import {
  ArrowRight,
  Building2,
  FileText,
  Gavel,
  Info,
  Megaphone,
  Scale,
  ShieldCheck,
} from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

export const metadata: Metadata = {
  title: "Know Your Rights",
  description:
    "How citizens in Bangladesh can escalate civic complaints — from a public report to a Right to Information request and beyond.",
};

const LADDER = [
  {
    icon: Megaphone,
    title: "1 · Report it publicly",
    body: "File a report here with a photo and location. A public, timestamped record creates pressure and a paper trail on its own.",
    href: "/submit",
    cta: "Submit a report",
  },
  {
    icon: Building2,
    title: "2 · Your city corporation",
    body: "Every city corporation runs a grievance process and ward-level offices. Take your public report and its reference to the relevant department.",
  },
  {
    icon: FileText,
    title: "3 · Right to Information request",
    body: "Under the RTI Act 2009, any citizen can ask a public authority for information. The Designated Officer must respond within 20 working days.",
    href: "/rti",
    cta: "Open the RTI wizard",
  },
  {
    icon: Gavel,
    title: "4 · Formal & legal remedies",
    body: "If a request is refused or ignored, you can appeal to the Information Commission, and ultimately the courts have writ jurisdiction to protect fundamental rights.",
  },
];

export default function RightsPage() {
  return (
    <>
      <section className="civic-mesh border-b">
        <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6">
          <span className="text-primary inline-flex items-center gap-2 text-sm font-semibold">
            <Scale className="size-4.5" aria-hidden />
            Civic rights & escalation
          </span>
          <h1 className="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">
            Know your rights as a citizen
          </h1>
          <p className="text-muted-foreground mt-2 max-w-2xl">
            When a civic problem goes unresolved, you have more leverage than a
            single phone call. Here is the ladder — from a public report to a
            statutory information request.
          </p>
        </div>
      </section>

      <section className="mx-auto max-w-5xl px-4 py-12 sm:px-6">
        <ol className="space-y-4">
          {LADDER.map((step) => (
            <li key={step.title}>
              <Card className="card-lift">
                <CardContent className="flex flex-col gap-4 py-5 sm:flex-row sm:items-center">
                  <span className="bg-primary/10 text-primary flex size-11 shrink-0 items-center justify-center rounded-lg">
                    <step.icon className="size-5" aria-hidden />
                  </span>
                  <div className="flex-1">
                    <h2 className="font-semibold">{step.title}</h2>
                    <p className="text-muted-foreground mt-1 text-sm leading-relaxed">
                      {step.body}
                    </p>
                  </div>
                  {step.href ? (
                    <Button variant="outline" asChild className="shrink-0">
                      <Link href={step.href}>
                        {step.cta} <ArrowRight className="size-4" />
                      </Link>
                    </Button>
                  ) : null}
                </CardContent>
              </Card>
            </li>
          ))}
        </ol>

        <div className="mt-10 grid gap-4 sm:grid-cols-2">
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2 text-base">
                <FileText className="text-primary size-5" aria-hidden />
                Right to Information Act, 2009
              </CardTitle>
            </CardHeader>
            <CardContent className="text-muted-foreground space-y-2 text-sm">
              <p>
                Gives every citizen the right to request information from public
                authorities. A Designated Officer must respond within{" "}
                <span className="text-foreground font-medium">
                  20 working days
                </span>
                . Refusals can be appealed, including to the Information
                Commission.
              </p>
              <Link
                href="/rti"
                className="text-primary inline-flex items-center gap-1 font-medium hover:underline"
              >
                Generate a compliant letter <ArrowRight className="size-3.5" />
              </Link>
            </CardContent>
          </Card>
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2 text-base">
                <ShieldCheck className="text-primary size-5" aria-hidden />
                Constitutional protection
              </CardTitle>
            </CardHeader>
            <CardContent className="text-muted-foreground space-y-2 text-sm">
              <p>
                Part III of the Constitution guarantees fundamental rights, and
                the High Court Division has writ jurisdiction (Article 102) to
                direct public authorities to act lawfully. Public services carry
                a duty of accountability to citizens.
              </p>
            </CardContent>
          </Card>
        </div>

        <div className="border-status-progress/25 bg-status-progress/5 mt-6 flex items-start gap-3 rounded-lg border p-4">
          <Info className="text-status-progress mt-0.5 size-5 shrink-0" aria-hidden />
          <p className="text-muted-foreground text-sm">
            This page is general civic information, not legal advice. For a
            specific case, consult a qualified lawyer or the official text of
            the relevant law. For emergencies, see{" "}
            <Link href="/services" className="text-primary font-medium hover:underline">
              Emergency &amp; Services
            </Link>
            .
          </p>
        </div>
      </section>
    </>
  );
}
