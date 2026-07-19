"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { useCallback, useRef, useState } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import imageCompression from "browser-image-compression";
import {
  ArrowLeft,
  ArrowRight,
  BadgeCheck,
  Camera,
  CheckCircle2,
  ImagePlus,
  Landmark,
  Loader2,
  MapPin,
  Send,
  Trash2,
} from "lucide-react";
import { toast } from "sonner";
import { createClient } from "@/lib/supabase/client";
import { createReport } from "@/lib/actions/reports";
import { reportSchema, type ReportInput } from "@/lib/validations";
import { CATEGORIES, CITIES } from "@/lib/constants";
import { DEPT_LABELS, resolveAuthority } from "@/lib/routing";
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
import { LocationPicker } from "@/components/map/location-picker";
import { StatusBadge } from "@/components/reports/status-badge";
import { NearbyDuplicates } from "./nearby-duplicates";

const MAX_PHOTOS = 6;

type Photo = { file: File; preview: string };

const STEPS = [
  { title: "Issue Details", subtitle: "What and where is the problem?" },
  { title: "Location & Evidence", subtitle: "Pinpoint it and add photos" },
  { title: "Review & Submit", subtitle: "Confirm everything looks right" },
];

export function SubmitWizard() {
  const router = useRouter();
  const [step, setStep] = useState(0);
  const [photos, setPhotos] = useState<Photo[]>([]);
  const [pin, setPin] = useState<{ lat: number; lng: number } | null>(null);
  const [submitting, setSubmitting] = useState(false);
  const [submittedId, setSubmittedId] = useState<number | null>(null);
  const [autoPublished, setAutoPublished] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const form = useForm<ReportInput>({
    resolver: zodResolver(reportSchema),
    defaultValues: {
      title: "",
      description: "",
      category: undefined,
      cityCorporation: undefined,
      locationText: "",
      latitude: null,
      longitude: null,
    },
  });

  const watchedCategory = form.watch("category");
  const watchedCity = form.watch("cityCorporation");
  const routePreview =
    watchedCategory && watchedCity
      ? resolveAuthority(watchedCategory, watchedCity)
      : null;

  const addFiles = useCallback(
    async (files: FileList | File[]) => {
      const incoming = Array.from(files).filter((f) =>
        f.type.startsWith("image/")
      );
      if (incoming.length === 0) return;
      if (photos.length + incoming.length > MAX_PHOTOS) {
        toast.error(`At most ${MAX_PHOTOS} photos per report`);
        return;
      }
      for (const file of incoming) {
        try {
          const compressed = await imageCompression(file, {
            maxSizeMB: 0.8,
            maxWidthOrHeight: 1600,
            useWebWorker: true,
          });
          setPhotos((prev) => [
            ...prev,
            { file: compressed, preview: URL.createObjectURL(compressed) },
          ]);
        } catch {
          toast.error(`Could not process ${file.name}`);
        }
      }
    },
    [photos.length]
  );

  function removePhoto(index: number) {
    setPhotos((prev) => {
      URL.revokeObjectURL(prev[index].preview);
      return prev.filter((_, i) => i !== index);
    });
  }

  async function next() {
    if (step === 0) {
      const valid = await form.trigger([
        "title",
        "description",
        "category",
        "cityCorporation",
      ]);
      if (!valid) return;
    }
    if (step === 1 && !pin && !form.getValues("locationText")) {
      toast.error("Add a location — drop a pin or describe the place");
      return;
    }
    setStep((s) => Math.min(s + 1, 2));
  }

  async function onSubmit(values: ReportInput) {
    setSubmitting(true);
    try {
      const supabase = createClient();
      const {
        data: { user },
      } = await supabase.auth.getUser();
      if (!user) {
        toast.error("Session expired — please log in again");
        router.push("/login");
        return;
      }

      // 1. Upload compressed photos under the caller's uid prefix.
      const batch = crypto.randomUUID();
      const paths: string[] = [];
      for (let i = 0; i < photos.length; i++) {
        const ext = photos[i].file.type === "image/png" ? "png" : "jpg";
        const path = `${user.id}/${batch}/${i + 1}.${ext}`;
        const { error } = await supabase.storage
          .from("report-media")
          .upload(path, photos[i].file, { contentType: photos[i].file.type });
        if (error) {
          toast.error(`Photo ${i + 1} failed to upload — try again`);
          setSubmitting(false);
          return;
        }
        paths.push(path);
      }

      // 2. Create the report + media rows server-side.
      const result = await createReport(
        { ...values, latitude: pin?.lat ?? null, longitude: pin?.lng ?? null },
        paths
      );
      if (!result.ok) {
        toast.error(result.error);
        setSubmitting(false);
        return;
      }
      setAutoPublished(result.autoPublished);
      setSubmittedId(result.reportId);
    } catch {
      toast.error("Something went wrong — please try again");
      setSubmitting(false);
    }
  }

  /* ---------------------------------------------------------------- */

  if (submittedId !== null) {
    return (
      <Card className="text-center">
        <CardHeader className="items-center">
          <span className="bg-status-resolved/15 text-status-resolved mx-auto flex size-14 items-center justify-center rounded-full">
            <CheckCircle2 className="size-7" aria-hidden />
          </span>
          <CardTitle className="mt-2 text-2xl">
            {autoPublished
              ? "Report Published Immediately!"
              : "Report Submitted Successfully!"}
          </CardTitle>
          <CardDescription className="max-w-md">
            {autoPublished
              ? "As a trusted reporter your report skipped moderation — it is already live on the public feed with the response clock running."
              : "Your report is awaiting review. Once approved it appears on the public feed, and you'll be notified at every status change."}
          </CardDescription>
        </CardHeader>
        <CardContent className="flex flex-col items-center gap-3">
          {autoPublished ? (
            <span className="border-primary/30 bg-primary/5 text-primary inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold">
              <BadgeCheck className="size-4" aria-hidden />
              Trusted reporter — published without review
            </span>
          ) : (
            <StatusBadge status="pending" />
          )}
          <div className="mt-2 flex flex-wrap justify-center gap-3">
            <Button
              className="bg-brand-gradient border-0 text-white hover:opacity-90"
              asChild
            >
              {autoPublished ? (
                <Link href={`/reports/${submittedId}`}>View live report</Link>
              ) : (
                <Link href="/my-reports">Track my reports</Link>
              )}
            </Button>
            <Button
              variant="outline"
              onClick={() => {
                form.reset();
                setPhotos([]);
                setPin(null);
                setSubmittedId(null);
                setAutoPublished(false);
                setSubmitting(false);
                setStep(0);
              }}
            >
              Submit another report
            </Button>
          </div>
        </CardContent>
      </Card>
    );
  }

  const progress = ((step + 1) / 3) * 100;

  return (
    <div className="space-y-4">
      <Card className="py-4">
        <CardContent className="space-y-2">
          <div className="flex items-center justify-between text-sm">
            <span className="font-medium">Report Submission Progress</span>
            <span className="text-muted-foreground">
              {Math.round(progress)}% Complete
            </span>
          </div>
          <Progress value={progress} aria-label="Submission progress" />
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <div className="flex items-center gap-3">
            <span className="bg-brand-gradient flex size-9 shrink-0 items-center justify-center rounded-full font-bold text-white">
              {step + 1}
            </span>
            <div>
              <CardTitle>{STEPS[step].title}</CardTitle>
              <CardDescription>{STEPS[step].subtitle}</CardDescription>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <Form {...form}>
            <form onSubmit={form.handleSubmit(onSubmit)} noValidate>
              {step === 0 ? (
                <div className="space-y-4">
                  <FormField
                    control={form.control}
                    name="title"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Title</FormLabel>
                        <FormControl>
                          <Input
                            placeholder="e.g. Broken streetlight on Mirpur Road"
                            {...field}
                          />
                        </FormControl>
                        <FormMessage />
                      </FormItem>
                    )}
                  />
                  <div className="grid gap-4 sm:grid-cols-2">
                    <FormField
                      control={form.control}
                      name="category"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>Category</FormLabel>
                          <Select
                            onValueChange={field.onChange}
                            value={field.value}
                          >
                            <FormControl>
                              <SelectTrigger className="w-full">
                                <SelectValue placeholder="What kind of issue?" />
                              </SelectTrigger>
                            </FormControl>
                            <SelectContent>
                              {CATEGORIES.map((c) => (
                                <SelectItem key={c} value={c}>
                                  {c}
                                </SelectItem>
                              ))}
                            </SelectContent>
                          </Select>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                    <FormField
                      control={form.control}
                      name="cityCorporation"
                      render={({ field }) => (
                        <FormItem>
                          <FormLabel>City Corporation</FormLabel>
                          <Select
                            onValueChange={field.onChange}
                            value={field.value}
                          >
                            <FormControl>
                              <SelectTrigger className="w-full">
                                <SelectValue placeholder="Which city?" />
                              </SelectTrigger>
                            </FormControl>
                            <SelectContent>
                              {CITIES.map((c) => (
                                <SelectItem key={c} value={c}>
                                  {c}
                                </SelectItem>
                              ))}
                            </SelectContent>
                          </Select>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                  </div>
                  {routePreview ? (
                    <div className="border-primary/25 bg-primary/[0.04] flex items-center gap-3 rounded-lg border p-3">
                      <Landmark
                        className="text-primary size-4.5 shrink-0"
                        aria-hidden
                      />
                      <p className="text-sm">
                        This report will be routed to{" "}
                        <span className="font-semibold">
                          {routePreview.name}
                        </span>{" "}
                        <span className="text-muted-foreground">
                          ({DEPT_LABELS[routePreview.dept]})
                        </span>
                        .
                      </p>
                    </div>
                  ) : null}
                  <FormField
                    control={form.control}
                    name="description"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Description</FormLabel>
                        <FormControl>
                          <Textarea
                            rows={5}
                            placeholder="Describe the issue: how long it's been there, how it affects people, anything that helps authorities act."
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
                <div className="space-y-6">
                  <div>
                    <h3 className="mb-2 flex items-center gap-2 font-semibold">
                      <MapPin className="text-primary size-4.5" aria-hidden />
                      Precise Location
                    </h3>
                    <FormField
                      control={form.control}
                      name="locationText"
                      render={({ field }) => (
                        <FormItem className="mb-3">
                          <FormLabel>Address / landmark</FormLabel>
                          <FormControl>
                            <Input
                              placeholder="e.g. Nilkhet Road, opposite New Market gate 2"
                              {...field}
                            />
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                    <LocationPicker value={pin} onChange={setPin} />
                    <div className="mt-3">
                      <NearbyDuplicates
                        lat={pin?.lat ?? null}
                        lng={pin?.lng ?? null}
                        category={form.getValues("category")}
                      />
                    </div>
                  </div>

                  <div>
                    <h3 className="mb-2 flex items-center gap-2 font-semibold">
                      <Camera className="text-primary size-4.5" aria-hidden />
                      Visual Evidence
                    </h3>
                    <button
                      type="button"
                      onClick={() => fileInputRef.current?.click()}
                      onDragOver={(e) => e.preventDefault()}
                      onDrop={(e) => {
                        e.preventDefault();
                        addFiles(e.dataTransfer.files);
                      }}
                      className="border-muted-foreground/30 hover:border-primary/50 hover:bg-accent/40 flex w-full flex-col items-center gap-2 rounded-xl border-2 border-dashed px-4 py-10 transition-colors"
                      aria-label="Upload photos"
                    >
                      <span className="bg-brand-gradient flex size-11 items-center justify-center rounded-lg text-white">
                        <ImagePlus className="size-5" aria-hidden />
                      </span>
                      <span className="font-medium">
                        Drop photos here or click to browse
                      </span>
                      <span className="text-muted-foreground text-xs">
                        JPG, PNG or WebP — compressed on your device before
                        upload (max {MAX_PHOTOS})
                      </span>
                    </button>
                    <input
                      ref={fileInputRef}
                      type="file"
                      accept="image/*"
                      multiple
                      hidden
                      onChange={(e) => {
                        if (e.target.files) addFiles(e.target.files);
                        e.target.value = "";
                      }}
                    />
                    {photos.length > 0 ? (
                      <ul className="mt-3 grid grid-cols-3 gap-2 sm:grid-cols-4">
                        {photos.map((p, i) => (
                          <li key={p.preview} className="group relative">
                            {/* eslint-disable-next-line @next/next/no-img-element */}
                            <img
                              src={p.preview}
                              alt={`Evidence photo ${i + 1}`}
                              className="aspect-square w-full rounded-lg object-cover"
                            />
                            <button
                              type="button"
                              onClick={() => removePhoto(i)}
                              className="bg-destructive absolute -top-1.5 -right-1.5 flex size-6 items-center justify-center rounded-full text-white opacity-0 shadow transition-opacity group-hover:opacity-100 focus:opacity-100"
                              aria-label={`Remove photo ${i + 1}`}
                            >
                              <Trash2 className="size-3.5" aria-hidden />
                            </button>
                          </li>
                        ))}
                      </ul>
                    ) : null}
                  </div>
                </div>
              ) : null}

              {step === 2 ? (
                <ReviewStep
                  values={form.getValues()}
                  pin={pin}
                  photos={photos}
                />
              ) : null}

              <div className="mt-6 flex items-center justify-between border-t pt-4">
                <Button
                  type="button"
                  variant="ghost"
                  onClick={() => setStep((s) => Math.max(0, s - 1))}
                  disabled={step === 0 || submitting}
                >
                  <ArrowLeft className="size-4" /> Previous
                </Button>
                {step < 2 ? (
                  <Button
                    type="button"
                    onClick={next}
                    className="bg-brand-gradient border-0 text-white hover:opacity-90"
                  >
                    Next Step <ArrowRight className="size-4" />
                  </Button>
                ) : (
                  <Button
                    type="submit"
                    disabled={submitting}
                    className="bg-brand-gradient border-0 text-white hover:opacity-90"
                  >
                    {submitting ? (
                      <Loader2 className="size-4 animate-spin" />
                    ) : (
                      <Send className="size-4" />
                    )}
                    Submit Report
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

function ReviewStep({
  values,
  pin,
  photos,
}: {
  values: ReportInput;
  pin: { lat: number; lng: number } | null;
  photos: Photo[];
}) {
  const routed =
    values.category && values.cityCorporation
      ? resolveAuthority(values.category, values.cityCorporation)
      : null;

  const rows: Array<[string, React.ReactNode]> = [
    ["Title", values.title],
    ["Category", values.category],
    ["City Corporation", values.cityCorporation],
    ["Routed to", routed ? routed.name : "—"],
    ["Location", values.locationText || "—"],
    [
      "Coordinates",
      pin ? `${pin.lat.toFixed(5)}, ${pin.lng.toFixed(5)}` : "Not pinned",
    ],
    ["Photos", `${photos.length} attached`],
  ];

  return (
    <div className="space-y-4">
      <dl className="divide-y rounded-xl border">
        {rows.map(([label, value]) => (
          <div
            key={label}
            className="grid grid-cols-3 gap-2 px-4 py-2.5 text-sm"
          >
            <dt className="text-muted-foreground font-medium">{label}</dt>
            <dd className="col-span-2">{value}</dd>
          </div>
        ))}
      </dl>
      <div>
        <p className="text-muted-foreground mb-1 text-sm font-medium">
          Description
        </p>
        <p className="bg-muted/50 rounded-lg p-3 text-sm whitespace-pre-wrap">
          {values.description}
        </p>
      </div>
      {photos.length > 0 ? (
        <ul className="grid grid-cols-4 gap-2 sm:grid-cols-6">
          {photos.map((p, i) => (
            <li key={p.preview}>
              {/* eslint-disable-next-line @next/next/no-img-element */}
              <img
                src={p.preview}
                alt={`Evidence photo ${i + 1}`}
                className="aspect-square w-full rounded-lg object-cover"
              />
            </li>
          ))}
        </ul>
      ) : null}
      <p className="text-muted-foreground text-xs">
        Your report is reviewed by moderators before appearing publicly. Your
        display name is shown; your contact details are never published.
      </p>
    </div>
  );
}
