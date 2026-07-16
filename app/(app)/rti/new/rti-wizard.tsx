"use client";

import { useRouter } from "next/navigation";
import { useMemo, useState } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { ArrowLeft, ArrowRight, Loader2, Save } from "lucide-react";
import { toast } from "sonner";
import { saveRtiLetter } from "@/lib/actions/rti";
import { rtiSchema, type RtiFormInput } from "@/lib/validations";
import { AUTHORITY_PRESETS, generateRtiLetter } from "@/lib/rti";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import {
  Form,
  FormControl,
  FormDescription,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import { Progress } from "@/components/ui/progress";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Textarea } from "@/components/ui/textarea";

const STEPS = ["Authority", "Information", "Your details", "Review"];

export function RtiWizard({
  defaultName,
  defaultEmail,
}: {
  defaultName: string;
  defaultEmail: string;
}) {
  const router = useRouter();
  const [step, setStep] = useState(0);
  const [presetId, setPresetId] = useState("dncc");
  const [saving, setSaving] = useState(false);

  const form = useForm<RtiFormInput>({
    resolver: zodResolver(rtiSchema),
    defaultValues: {
      authority: AUTHORITY_PRESETS[0].nameEn,
      subject: "",
      informationSought: "",
      reason: "",
      deliveryMode: "certified_copy",
      applicantName: defaultName,
      applicantAddress: "",
      applicantPhone: "",
      applicantEmail: defaultEmail,
      language: "en",
      reportId: null,
    },
  });

  const values = form.watch();
  const preview = useMemo(
    () =>
      generateRtiLetter({
        authority: values.authority || "…",
        subject: values.subject || "…",
        informationSought: values.informationSought || "…",
        reason: values.reason,
        deliveryMode: values.deliveryMode,
        applicantName: values.applicantName || "…",
        applicantAddress: values.applicantAddress || "…",
        applicantPhone: values.applicantPhone,
        applicantEmail: values.applicantEmail,
        language: values.language,
      }),
    [values]
  );

  function onPreset(id: string) {
    setPresetId(id);
    const p = AUTHORITY_PRESETS.find((a) => a.id === id);
    if (p && id !== "custom") {
      form.setValue(
        "authority",
        values.language === "bn" ? p.nameBn : p.nameEn
      );
    } else if (id === "custom") {
      form.setValue("authority", "");
    }
  }

  async function next() {
    const fields: (keyof RtiFormInput)[][] = [
      ["authority", "subject", "language"],
      ["informationSought", "deliveryMode"],
      ["applicantName", "applicantAddress", "applicantEmail"],
    ];
    if (step < 3) {
      const valid = await form.trigger(fields[step]);
      if (!valid) return;
    }
    setStep((s) => Math.min(s + 1, 3));
  }

  async function onSubmit(v: RtiFormInput) {
    setSaving(true);
    const result = await saveRtiLetter(v);
    setSaving(false);
    if (!result.ok) {
      toast.error(result.error);
      return;
    }
    toast.success("RTI letter saved");
    router.push(`/rti/${result.id}`);
  }

  const progress = ((step + 1) / STEPS.length) * 100;

  return (
    <div className="space-y-4">
      <div className="space-y-2">
        <div className="flex items-center justify-between text-sm">
          <span className="font-medium">
            Step {step + 1} of {STEPS.length}: {STEPS[step]}
          </span>
          <span className="text-muted-foreground">
            {Math.round(progress)}%
          </span>
        </div>
        <Progress value={progress} />
      </div>

      <Card>
        <CardHeader>
          <CardTitle>{STEPS[step]}</CardTitle>
          <CardDescription>
            {
              [
                "Who are you requesting information from?",
                "What information do you need, and how?",
                "How should the authority reach you?",
                "Review the generated letter before saving.",
              ][step]
            }
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Form {...form}>
            <form onSubmit={form.handleSubmit(onSubmit)} noValidate>
              {step === 0 ? (
                <div className="space-y-4">
                  <FormField
                    control={form.control}
                    name="language"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Letter language</FormLabel>
                        <Select
                          value={field.value}
                          onValueChange={(v) => {
                            field.onChange(v);
                            const p = AUTHORITY_PRESETS.find(
                              (a) => a.id === presetId
                            );
                            if (p && presetId !== "custom") {
                              form.setValue(
                                "authority",
                                v === "bn" ? p.nameBn : p.nameEn
                              );
                            }
                          }}
                        >
                          <FormControl>
                            <SelectTrigger className="w-full">
                              <SelectValue />
                            </SelectTrigger>
                          </FormControl>
                          <SelectContent>
                            <SelectItem value="en">English</SelectItem>
                            <SelectItem value="bn">বাংলা (Bangla)</SelectItem>
                          </SelectContent>
                        </Select>
                      </FormItem>
                    )}
                  />

                  <FormItem>
                    <FormLabel>Public authority</FormLabel>
                    <Select value={presetId} onValueChange={onPreset}>
                      <SelectTrigger className="w-full">
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        {AUTHORITY_PRESETS.map((a) => (
                          <SelectItem key={a.id} value={a.id}>
                            {values.language === "bn" ? a.nameBn : a.nameEn}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  </FormItem>

                  <FormField
                    control={form.control}
                    name="authority"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Authority name</FormLabel>
                        <FormControl>
                          <Input
                            {...field}
                            className={
                              values.language === "bn" ? "font-bengali" : ""
                            }
                          />
                        </FormControl>
                        <FormDescription>
                          Edit if you need a specific department or office.
                        </FormDescription>
                        <FormMessage />
                      </FormItem>
                    )}
                  />

                  <FormField
                    control={form.control}
                    name="subject"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Subject</FormLabel>
                        <FormControl>
                          <Input
                            placeholder="e.g. Repair status of Mirpur Road streetlights"
                            {...field}
                          />
                        </FormControl>
                        <FormMessage />
                      </FormItem>
                    )}
                  />
                </div>
              ) : null}

              {step === 1 ? (
                <div className="space-y-4">
                  <FormField
                    control={form.control}
                    name="informationSought"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Information requested</FormLabel>
                        <FormControl>
                          <Textarea
                            rows={5}
                            placeholder="Be specific: dates, locations, documents, decisions, budgets, timelines…"
                            className={
                              values.language === "bn" ? "font-bengali" : ""
                            }
                            {...field}
                          />
                        </FormControl>
                        <FormMessage />
                      </FormItem>
                    )}
                  />
                  <FormField
                    control={form.control}
                    name="reason"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Reason (optional)</FormLabel>
                        <FormControl>
                          <Textarea
                            rows={2}
                            placeholder="Why you need this — not required by the Act, but can help."
                            {...field}
                          />
                        </FormControl>
                        <FormMessage />
                      </FormItem>
                    )}
                  />
                  <FormField
                    control={form.control}
                    name="deliveryMode"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>How do you want the information?</FormLabel>
                        <Select
                          value={field.value}
                          onValueChange={field.onChange}
                        >
                          <FormControl>
                            <SelectTrigger className="w-full">
                              <SelectValue />
                            </SelectTrigger>
                          </FormControl>
                          <SelectContent>
                            <SelectItem value="certified_copy">
                              Certified copy of records
                            </SelectItem>
                            <SelectItem value="inspection">
                              Inspection of records
                            </SelectItem>
                            <SelectItem value="email">
                              Electronic copy by email
                            </SelectItem>
                          </SelectContent>
                        </Select>
                        <FormMessage />
                      </FormItem>
                    )}
                  />
                </div>
              ) : null}

              {step === 2 ? (
                <div className="space-y-4">
                  <FormField
                    control={form.control}
                    name="applicantName"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Full name</FormLabel>
                        <FormControl>
                          <Input {...field} />
                        </FormControl>
                        <FormMessage />
                      </FormItem>
                    )}
                  />
                  <FormField
                    control={form.control}
                    name="applicantAddress"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Address</FormLabel>
                        <FormControl>
                          <Textarea rows={2} {...field} />
                        </FormControl>
                        <FormMessage />
                      </FormItem>
                    )}
                  />
                  <div className="grid gap-4 sm:grid-cols-2">
                    <FormField
                      control={form.control}
                      name="applicantPhone"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Phone (optional)</FormLabel>
                          <FormControl>
                            <Input type="tel" {...field} />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                    <FormField
                      control={form.control}
                      name="applicantEmail"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Email (optional)</FormLabel>
                          <FormControl>
                            <Input type="email" {...field} />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                  </div>
                </div>
              ) : null}

              {step === 3 ? (
                <div className="space-y-3">
                  <p className="text-muted-foreground text-sm">
                    This is your letter. Save it, then print to PDF from the
                    next screen.
                  </p>
                  <pre
                    className={`bg-muted/50 max-h-96 overflow-y-auto rounded-lg border p-4 text-sm whitespace-pre-wrap ${
                      values.language === "bn" ? "font-bengali" : "font-sans"
                    }`}
                  >
                    {preview}
                  </pre>
                </div>
              ) : null}

              <div className="mt-6 flex items-center justify-between border-t pt-4">
                <Button
                  type="button"
                  variant="ghost"
                  onClick={() => setStep((s) => Math.max(0, s - 1))}
                  disabled={step === 0 || saving}
                >
                  <ArrowLeft className="size-4" /> Back
                </Button>
                {step < 3 ? (
                  <Button
                    type="button"
                    onClick={next}
                    className="bg-brand-gradient border-0 text-white hover:opacity-90"
                  >
                    Next <ArrowRight className="size-4" />
                  </Button>
                ) : (
                  <Button
                    type="submit"
                    disabled={saving}
                    className="bg-brand-gradient border-0 text-white hover:opacity-90"
                  >
                    {saving ? (
                      <Loader2 className="size-4 animate-spin" />
                    ) : (
                      <Save className="size-4" />
                    )}
                    Save letter
                  </Button>
                )}
              </div>
            </form>
          </Form>
        </CardContent>
      </Card>
    </div>
  );
}
