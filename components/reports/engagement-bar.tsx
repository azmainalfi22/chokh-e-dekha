"use client";

import { useOptimistic, useState, useTransition } from "react";
import { Bookmark, MessageSquare, Share2, ThumbsUp } from "lucide-react";
import { toast } from "sonner";
import {
  recordShare,
  toggleBookmark,
  toggleEndorsement,
} from "@/lib/actions/engagement";
import { Button } from "@/components/ui/button";
import { cn } from "@/lib/utils";

type Props = {
  reportId: number;
  title: string;
  endorseCount: number;
  commentCount: number;
  endorsed: boolean;
  bookmarked: boolean;
  isAuthed: boolean;
};

export function EngagementBar({
  reportId,
  title,
  endorseCount,
  commentCount,
  endorsed,
  bookmarked,
  isAuthed,
}: Props) {
  const [, startTransition] = useTransition();
  const [optimistic, setOptimistic] = useOptimistic(
    { endorsed, endorseCount, bookmarked },
    (
      state,
      action: { type: "endorse" } | { type: "bookmark" }
    ) => {
      if (action.type === "endorse") {
        return {
          ...state,
          endorsed: !state.endorsed,
          endorseCount: state.endorseCount + (state.endorsed ? -1 : 1),
        };
      }
      return { ...state, bookmarked: !state.bookmarked };
    }
  );
  const [shared, setShared] = useState(false);

  function requireAuth(): boolean {
    if (!isAuthed) {
      toast.error("Log in to interact with reports");
      return false;
    }
    return true;
  }

  function onEndorse() {
    if (!requireAuth()) return;
    startTransition(async () => {
      setOptimistic({ type: "endorse" });
      const result = await toggleEndorsement(reportId);
      if (!result.ok) toast.error(result.error);
    });
  }

  function onBookmark() {
    if (!requireAuth()) return;
    startTransition(async () => {
      setOptimistic({ type: "bookmark" });
      const result = await toggleBookmark(reportId);
      if (!result.ok) toast.error(result.error);
      else
        toast.success(
          result.bookmarked ? "Added to bookmarks" : "Removed from bookmarks"
        );
    });
  }

  async function onShare() {
    const url = `${window.location.origin}/reports/${reportId}`;
    try {
      if (navigator.share) {
        await navigator.share({ title, url });
      } else {
        await navigator.clipboard.writeText(url);
        toast.success("Link copied to clipboard");
      }
      if (!shared) {
        setShared(true);
        recordShare(reportId);
      }
    } catch {
      // user dismissed the share sheet — not an error
    }
  }

  return (
    <div className="bg-card flex flex-wrap items-center gap-1.5 rounded-xl border p-2 shadow-sm">
      <Button
        variant="ghost"
        size="sm"
        onClick={onEndorse}
        aria-pressed={optimistic.endorsed}
        className={cn(
          optimistic.endorsed && "text-primary bg-primary/10 hover:bg-primary/15"
        )}
      >
        <ThumbsUp
          className={cn("size-4", optimistic.endorsed && "fill-current")}
          aria-hidden
        />
        {optimistic.endorseCount}{" "}
        <span className="hidden sm:inline">
          Endorsement{optimistic.endorseCount === 1 ? "" : "s"}
        </span>
      </Button>

      <Button variant="ghost" size="sm" asChild>
        <a href="#comments">
          <MessageSquare className="size-4" aria-hidden />
          {commentCount} <span className="hidden sm:inline">Comments</span>
        </a>
      </Button>

      <span className="flex-1" />

      <Button
        variant="ghost"
        size="sm"
        onClick={onBookmark}
        aria-pressed={optimistic.bookmarked}
        className={cn(
          optimistic.bookmarked &&
            "text-primary bg-primary/10 hover:bg-primary/15"
        )}
      >
        <Bookmark
          className={cn("size-4", optimistic.bookmarked && "fill-current")}
          aria-hidden
        />
        <span className="hidden sm:inline">
          {optimistic.bookmarked ? "Bookmarked" : "Bookmark"}
        </span>
      </Button>

      <Button variant="ghost" size="sm" onClick={onShare}>
        <Share2 className="size-4" aria-hidden />
        <span className="hidden sm:inline">Share</span>
      </Button>
    </div>
  );
}
