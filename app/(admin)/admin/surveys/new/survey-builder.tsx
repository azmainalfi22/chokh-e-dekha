"use client";

import { useRouter } from "next/navigation";
import { useState } from "react";
import { GripVertical, Loader2, Plus, Save, Trash2 } from "lucide-react";
import { toast } from "sonner";
import { createSurvey } from "@/lib/actions/surveys";
import type { SurveyQuestion } from "@/lib/validations";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";

type Draft = SurveyQuestion & { optionsText: string };

function newQuestion(): Draft {
  return {
    id: crypto.randomUUID(),
    label: "",
    type: "text",
    optionsText: "",
  };
}

export function SurveyBuilder() {
  const router = useRouter();
  const [title, setTitle] = useState("");
  const [description, setDescription] = useState("");
  const [questions, setQuestions] = useState<Draft[]>([newQuestion()]);
  const [saving, setSaving] = useState(false);

  function update(id: string, patch: Partial<Draft>) {
    setQuestions((prev) =>
      prev.map((q) => (q.id === id ? { ...q, ...patch } : q))
    );
  }

  async function save() {
    const payload = {
      title,
      description,
      questions: questions.map((q) => ({
        id: q.id,
        label: q.label,
        type: q.type,
        options:
          q.type === "single"
            ? q.optionsText
                .split("\n")
                .map((o) => o.trim())
                .filter(Boolean)
            : undefined,
      })),
    };
    // client-side guard for single-choice options
    for (const q of payload.questions) {
      if (q.type === "single" && (!q.options || q.options.length < 2)) {
        toast.error("Single-choice questions need at least two options");
        return;
      }
    }

    setSaving(true);
    const result = await createSurvey(payload);
    setSaving(false);
    if (!result.ok) {
      toast.error(result.error);
      return;
    }
    toast.success("Survey published");
    router.push("/admin/surveys");
  }

  return (
    <div className="space-y-4">
      <Card>
        <CardContent className="space-y-4 pt-6">
          <div className="space-y-2">
            <Label htmlFor="survey-title">Title</Label>
            <Input
              id="survey-title"
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              placeholder="e.g. Which civic issues matter most in your area?"
            />
          </div>
          <div className="space-y-2">
            <Label htmlFor="survey-desc">Description (optional)</Label>
            <Textarea
              id="survey-desc"
              rows={2}
              value={description}
              onChange={(e) => setDescription(e.target.value)}
            />
          </div>
        </CardContent>
      </Card>

      {questions.map((q, i) => (
        <Card key={q.id}>
          <CardContent className="space-y-3 pt-6">
            <div className="flex items-center gap-2">
              <GripVertical
                className="text-muted-foreground size-4"
                aria-hidden
              />
              <span className="text-sm font-medium">Question {i + 1}</span>
              <span className="flex-1" />
              {questions.length > 1 ? (
                <Button
                  size="icon"
                  variant="ghost"
                  aria-label={`Remove question ${i + 1}`}
                  onClick={() =>
                    setQuestions((prev) => prev.filter((x) => x.id !== q.id))
                  }
                >
                  <Trash2 className="size-4" aria-hidden />
                </Button>
              ) : null}
            </div>
            <Input
              value={q.label}
              onChange={(e) => update(q.id, { label: e.target.value })}
              placeholder="Question text"
            />
            <div className="grid gap-3 sm:grid-cols-[180px_1fr]">
              <Select
                value={q.type}
                onValueChange={(v) =>
                  update(q.id, { type: v as SurveyQuestion["type"] })
                }
              >
                <SelectTrigger aria-label="Question type">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="text">Short text</SelectItem>
                  <SelectItem value="single">Single choice</SelectItem>
                  <SelectItem value="rating">Rating (1–5)</SelectItem>
                </SelectContent>
              </Select>
              {q.type === "single" ? (
                <Textarea
                  rows={3}
                  value={q.optionsText}
                  onChange={(e) => update(q.id, { optionsText: e.target.value })}
                  placeholder="One option per line"
                />
              ) : (
                <p className="text-muted-foreground self-center text-sm">
                  {q.type === "rating"
                    ? "Respondents pick 1 to 5 stars."
                    : "Respondents type a free-text answer."}
                </p>
              )}
            </div>
          </CardContent>
        </Card>
      ))}

      <div className="flex items-center justify-between">
        <Button
          variant="outline"
          onClick={() => setQuestions((prev) => [...prev, newQuestion()])}
        >
          <Plus className="size-4" aria-hidden /> Add question
        </Button>
        <Button
          onClick={save}
          disabled={saving}
          className="bg-brand-gradient border-0 text-white hover:opacity-90"
        >
          {saving ? (
            <Loader2 className="size-4 animate-spin" />
          ) : (
            <Save className="size-4" />
          )}
          Publish survey
        </Button>
      </div>
    </div>
  );
}
