"use client";

import { useRouter } from "next/navigation";
import { useState, useTransition } from "react";
import { CheckCircle2, Loader2, ThumbsDown, ThumbsUp } from "lucide-react";
import { toast } from "sonner";
import { confirmResolution, disputeResolution } from "@/lib/actions/reports";
import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { Textarea } from "@/components/ui/textarea";

/**
 * Shown to the report's owner when an admin has marked it Resolved but the
 * fix is still awaiting citizen confirmation. Confirming closes the loop;
 * disputing reopens the report and resumes the SLA clock.
 */
export function ResolutionPanel({ reportId }: { reportId: number }) {
  const router = useRouter();
  const [pending, startTransition] = useTransition();
  const [disputeOpen, setDisputeOpen] = useState(false);
  const [reason, setReason] = useState("");

  function confirm() {
    startTransition(async () => {
      const result = await confirmResolution(reportId);
      if (!result.ok) {
        toast.error(result.error);
        return;
      }
      toast.success("Thanks for confirming — marked as fixed.");
      router.refresh();
    });
  }

  function dispute() {
    startTransition(async () => {
      const result = await disputeResolution(reportId, reason);
      if (!result.ok) {
        toast.error(result.error);
        return;
      }
      setDisputeOpen(false);
      toast.success("Reopened — the authority has been put back on the clock.");
      router.refresh();
    });
  }

  return (
    <div className="border-status-pending/40 bg-status-pending/10 rounded-xl border p-4">
      <div className="flex items-start gap-3">
        <CheckCircle2 className="text-status-pending mt-0.5 size-5 shrink-0" aria-hidden />
        <div className="flex-1">
          <h3 className="font-semibold">Was this actually fixed?</h3>
          <p className="text-muted-foreground mt-0.5 text-sm">
            This report was marked resolved. Please confirm the issue is
            genuinely fixed — or reopen it if it isn&apos;t. Your confirmation
            keeps the public record honest.
          </p>
          <div className="mt-3 flex flex-wrap gap-2">
            <Button
              size="sm"
              disabled={pending}
              className="bg-brand-gradient border-0 text-white hover:opacity-95"
              onClick={confirm}
            >
              {pending ? (
                <Loader2 className="size-4 animate-spin" />
              ) : (
                <ThumbsUp className="size-4" />
              )}
              Yes, it&apos;s fixed
            </Button>
            <Button
              size="sm"
              variant="outline"
              disabled={pending}
              onClick={() => setDisputeOpen(true)}
            >
              <ThumbsDown className="size-4" /> No, reopen it
            </Button>
          </div>
        </div>
      </div>

      <Dialog open={disputeOpen} onOpenChange={setDisputeOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Reopen this report</DialogTitle>
            <DialogDescription>
              Tell us what&apos;s still wrong. The report goes back to In
              Progress and the response deadline restarts.
            </DialogDescription>
          </DialogHeader>
          <Textarea
            value={reason}
            onChange={(e) => setReason(e.target.value)}
            rows={3}
            placeholder="e.g. The pothole was only partly filled and is already breaking apart."
          />
          <DialogFooter>
            <Button variant="outline" onClick={() => setDisputeOpen(false)}>
              Cancel
            </Button>
            <Button
              variant="destructive"
              disabled={pending}
              onClick={dispute}
            >
              {pending ? (
                <Loader2 className="size-4 animate-spin" />
              ) : (
                <ThumbsDown className="size-4" />
              )}
              Reopen report
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  );
}
