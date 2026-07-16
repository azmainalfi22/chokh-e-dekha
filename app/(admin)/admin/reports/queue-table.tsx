"use client";

import Link from "next/link";
import { usePathname, useRouter, useSearchParams } from "next/navigation";
import { useState, useTransition } from "react";
import {
  Check,
  Loader2,
  MessageSquarePlus,
  UserMinus,
  UserPlus,
  X,
} from "lucide-react";
import { toast } from "sonner";
import {
  approveReports,
  assignReport,
  rejectReports,
  setAdminNote,
  updateReportsStatus,
} from "@/lib/actions/admin";
import { STATUS_LABELS, STATUSES } from "@/lib/constants";
import { Button } from "@/components/ui/button";
import { Checkbox } from "@/components/ui/checkbox";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Textarea } from "@/components/ui/textarea";
import { StatusBadge } from "@/components/reports/status-badge";
import { SlaBadge } from "@/components/reports/sla-badge";
import { Badge } from "@/components/ui/badge";

export type QueueRow = {
  id: number;
  title: string;
  category: string;
  city: string;
  status: string;
  isApproved: boolean;
  slaDueAt: string | null;
  createdAt: string;
  adminNote: string | null;
  reporter: string;
  assignedToMe: boolean;
  assigneeName: string | null;
};

export function QueueTable({
  rows,
  moderationFilter,
}: {
  rows: QueueRow[];
  moderationFilter: string;
}) {
  const router = useRouter();
  const pathname = usePathname();
  const searchParams = useSearchParams();
  const [pending, startTransition] = useTransition();
  const [selected, setSelected] = useState<Set<number>>(new Set());
  const [bulkStatus, setBulkStatus] = useState("");
  const [noteFor, setNoteFor] = useState<QueueRow | null>(null);
  const [noteDraft, setNoteDraft] = useState("");

  const ids = [...selected];
  const allSelected = rows.length > 0 && selected.size === rows.length;

  function toggleAll() {
    setSelected(allSelected ? new Set() : new Set(rows.map((r) => r.id)));
  }
  function toggleOne(id: number) {
    setSelected((prev) => {
      const next = new Set(prev);
      if (next.has(id)) next.delete(id);
      else next.add(id);
      return next;
    });
  }

  function run(fn: () => Promise<{ ok: boolean; error?: string; count?: number }>, verb: string) {
    startTransition(async () => {
      const result = await fn();
      if (!result.ok) {
        toast.error(result.error ?? `${verb} failed`);
        return;
      }
      toast.success(`${verb}${result.count !== undefined ? ` — ${result.count} report${result.count === 1 ? "" : "s"}` : ""}`);
      setSelected(new Set());
      router.refresh();
    });
  }

  function setModeration(value: string) {
    const params = new URLSearchParams(searchParams.toString());
    if (value === "all") params.delete("moderation");
    else params.set("moderation", value);
    params.delete("page");
    router.push(`${pathname}?${params.toString()}`, { scroll: false });
  }

  return (
    <div className="space-y-3">
      <div className="flex flex-wrap items-center gap-2">
        <Select
          value={moderationFilter || "all"}
          onValueChange={setModeration}
        >
          <SelectTrigger className="w-44" aria-label="Moderation filter">
            <SelectValue />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All reports</SelectItem>
            <SelectItem value="needs-review">Needs review</SelectItem>
          </SelectContent>
        </Select>

        {selected.size > 0 ? (
          <div className="bg-accent/60 flex flex-wrap items-center gap-2 rounded-lg border px-3 py-1.5">
            <span className="text-sm font-medium">{selected.size} selected</span>
            <Button
              size="sm"
              variant="outline"
              disabled={pending}
              onClick={() => run(() => approveReports(ids), "Approved")}
            >
              <Check className="text-status-resolved size-4" aria-hidden />
              Approve
            </Button>
            <Button
              size="sm"
              variant="outline"
              disabled={pending}
              onClick={() => run(() => rejectReports(ids), "Rejected")}
            >
              <X className="text-status-rejected size-4" aria-hidden />
              Reject
            </Button>
            <div className="flex items-center gap-1.5">
              <Select value={bulkStatus} onValueChange={setBulkStatus}>
                <SelectTrigger className="h-8 w-36" aria-label="Bulk status">
                  <SelectValue placeholder="Set status…" />
                </SelectTrigger>
                <SelectContent>
                  {STATUSES.filter((s) => s !== "rejected").map((s) => (
                    <SelectItem key={s} value={s}>
                      {STATUS_LABELS[s]}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
              <Button
                size="sm"
                disabled={pending || !bulkStatus}
                className="bg-brand-gradient border-0 text-white hover:opacity-90"
                onClick={() =>
                  run(
                    () => updateReportsStatus(ids, bulkStatus),
                    "Status updated"
                  )
                }
              >
                {pending ? (
                  <Loader2 className="size-4 animate-spin" />
                ) : (
                  "Apply"
                )}
              </Button>
            </div>
          </div>
        ) : null}
      </div>

      <div className="overflow-x-auto rounded-xl border">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead className="w-10">
                <Checkbox
                  checked={allSelected}
                  onCheckedChange={toggleAll}
                  aria-label="Select all reports"
                />
              </TableHead>
              <TableHead>Report</TableHead>
              <TableHead>City</TableHead>
              <TableHead>Status</TableHead>
              <TableHead>Visibility</TableHead>
              <TableHead>Assignee</TableHead>
              <TableHead className="text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {rows.length === 0 ? (
              <TableRow>
                <TableCell
                  colSpan={7}
                  className="text-muted-foreground py-10 text-center"
                >
                  No reports match the current filters.
                </TableCell>
              </TableRow>
            ) : (
              rows.map((r) => (
                <TableRow key={r.id} data-state={selected.has(r.id) ? "selected" : undefined}>
                  <TableCell>
                    <Checkbox
                      checked={selected.has(r.id)}
                      onCheckedChange={() => toggleOne(r.id)}
                      aria-label={`Select ${r.title}`}
                    />
                  </TableCell>
                  <TableCell className="max-w-64">
                    <Link
                      href={`/reports/${r.id}`}
                      className="hover:text-primary block truncate text-sm font-medium transition-colors"
                    >
                      {r.title}
                    </Link>
                    <span className="text-muted-foreground text-xs">
                      {r.category} · {r.reporter} ·{" "}
                      {new Date(r.createdAt).toLocaleDateString("en-GB", {
                        day: "numeric",
                        month: "short",
                      })}
                    </span>
                  </TableCell>
                  <TableCell className="text-sm">{r.city}</TableCell>
                  <TableCell>
                    <div className="flex flex-col items-start gap-1">
                      <StatusBadge status={r.status} />
                      <SlaBadge slaDueAt={r.slaDueAt} status={r.status} />
                    </div>
                  </TableCell>
                  <TableCell>
                    {r.isApproved ? (
                      <Badge
                        variant="outline"
                        className="border-status-resolved/40 text-status-resolved"
                      >
                        Public
                      </Badge>
                    ) : (
                      <Badge variant="outline" className="text-status-pending border-status-pending/40">
                        Needs review
                      </Badge>
                    )}
                  </TableCell>
                  <TableCell className="text-sm">
                    {r.assigneeName ?? (
                      <span className="text-muted-foreground">—</span>
                    )}
                  </TableCell>
                  <TableCell>
                    <div className="flex items-center justify-end gap-1">
                      <Button
                        size="icon"
                        variant="ghost"
                        aria-label={`Add note to ${r.title}`}
                        onClick={() => {
                          setNoteFor(r);
                          setNoteDraft(r.adminNote ?? "");
                        }}
                      >
                        <MessageSquarePlus className="size-4" aria-hidden />
                      </Button>
                      <Button
                        size="icon"
                        variant="ghost"
                        aria-label={
                          r.assignedToMe
                            ? `Unassign ${r.title}`
                            : `Assign ${r.title} to me`
                        }
                        disabled={pending}
                        onClick={() =>
                          run(
                            () => assignReport(r.id, !r.assignedToMe),
                            r.assignedToMe ? "Unassigned" : "Assigned to you"
                          )
                        }
                      >
                        {r.assignedToMe ? (
                          <UserMinus className="size-4" aria-hidden />
                        ) : (
                          <UserPlus className="size-4" aria-hidden />
                        )}
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              ))
            )}
          </TableBody>
        </Table>
      </div>

      <Dialog open={noteFor !== null} onOpenChange={(o) => !o && setNoteFor(null)}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Official public note</DialogTitle>
            <DialogDescription>
              Shown on the public report page and included in status-change
              notifications for “{noteFor?.title}”.
            </DialogDescription>
          </DialogHeader>
          <Textarea
            value={noteDraft}
            onChange={(e) => setNoteDraft(e.target.value)}
            rows={4}
            placeholder="e.g. Crew dispatched — repair scheduled for Thursday."
          />
          <DialogFooter>
            <Button variant="outline" onClick={() => setNoteFor(null)}>
              Cancel
            </Button>
            <Button
              className="bg-brand-gradient border-0 text-white hover:opacity-90"
              disabled={pending}
              onClick={() => {
                if (!noteFor) return;
                run(() => setAdminNote(noteFor.id, noteDraft), "Note saved");
                setNoteFor(null);
              }}
            >
              Save note
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  );
}
