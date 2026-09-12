"use client";

import { useState, useTransition } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { AlertTriangle, Loader2 } from "lucide-react";
import { toast } from "sonner";
import { officerUpdateReport } from "@/lib/actions/officer";
import { STATUS_LABELS, STATUSES, type Status } from "@/lib/constants";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { StatusBadge } from "@/components/reports/status-badge";
import { SlaBadge } from "@/components/reports/sla-badge";
import { categoryIcon } from "@/components/reports/category-tile";

type Props = {
  id: number;
  title: string;
  category: string;
  status: string;
  priority: string | null;
  isApproved: boolean;
  slaDueAt: string | null;
  escalationLevel: number;
  adminNote: string | null;
  mine: boolean;
  ward: string | null;
  reporter: string;
};

export function OfficerReportRow(props: Props) {
  const router = useRouter();
  const [pending, startTransition] = useTransition();
  const [note, setNote] = useState(props.adminNote ?? "");
  const Icon = categoryIcon(props.category);

  function save(patch: Parameters<typeof officerUpdateReport>[1]) {
    startTransition(async () => {
      const result = await officerUpdateReport(props.id, patch);
      if (!result.ok) {
        toast.error(result.error);
        return;
      }
      toast.success("Saved");
      router.refresh();
    });
  }

  return (
    <Card>
      <CardContent className="space-y-3 py-4">
        <div className="flex flex-wrap items-start justify-between gap-3">
          <div className="flex min-w-0 gap-3">
            <span className="bg-muted text-muted-foreground mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-md">
              <Icon className="size-4.5" aria-hidden />
            </span>
            <div className="min-w-0">
              <Link
                href={`/reports/${props.id}`}
                className="font-medium hover:underline"
              >
                {props.title}
              </Link>
              <p className="text-muted-foreground mt-0.5 text-xs">
                {props.category}
                {props.ward ? ` · ${props.ward}` : ""} · {props.reporter}
              </p>
            </div>
          </div>

          <div className="flex shrink-0 flex-wrap items-center gap-1.5">
            {props.escalationLevel > 0 ? (
              <Badge
                variant="outline"
                className="border-status-breach/40 text-status-breach gap-1"
              >
                <AlertTriangle className="size-3" aria-hidden />
                Escalated L{props.escalationLevel}
              </Badge>
            ) : null}
            {!props.isApproved ? (
              <Badge variant="outline">Awaiting moderation</Badge>
            ) : null}
            {props.priority ? (
              <Badge variant="outline" className="capitalize">
                {props.priority}
              </Badge>
            ) : null}
            <StatusBadge status={props.status} />
            <SlaBadge slaDueAt={props.slaDueAt} status={props.status} />
          </div>
        </div>

        <div className="flex flex-wrap items-center gap-2">
          <Select
            value={props.status}
            onValueChange={(value) => save({ status: value })}
            disabled={pending}
          >
            <SelectTrigger className="h-9 w-40 text-xs" size="sm">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              {STATUSES.filter((s) => s !== "rejected").map((s) => (
                <SelectItem key={s} value={s}>
                  {STATUS_LABELS[s as Status]}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>

          <Input
            value={note}
            onChange={(e) => setNote(e.target.value)}
            placeholder="Official note shown to the reporter"
            className="h-9 min-w-56 flex-1 text-xs"
          />

          <Button
            size="sm"
            variant="outline"
            disabled={pending || note === (props.adminNote ?? "")}
            onClick={() => save({ note })}
          >
            {pending ? <Loader2 className="size-4 animate-spin" aria-hidden /> : null}
            Save note
          </Button>

          {props.mine ? (
            <Badge variant="outline">Assigned to you</Badge>
          ) : (
            <Button
              size="sm"
              variant="ghost"
              disabled={pending}
              onClick={() => save({ takeOwnership: true })}
            >
              Take it
            </Button>
          )}
        </div>
      </CardContent>
    </Card>
  );
}
