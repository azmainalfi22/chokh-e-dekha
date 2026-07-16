"use client";

import { useRouter } from "next/navigation";
import { useState } from "react";
import { Loader2, MessageSquare, Reply, Send, Trash2 } from "lucide-react";
import { toast } from "sonner";
import { addComment, deleteComment } from "@/lib/actions/engagement";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import { Button } from "@/components/ui/button";
import { Textarea } from "@/components/ui/textarea";
import { cn } from "@/lib/utils";

export type CommentData = {
  id: number;
  body: string;
  parent_id: number | null;
  created_at: string;
  user_id: string;
  authorName: string;
};

type Props = {
  reportId: number;
  comments: CommentData[];
  currentUserId: string | null;
  isAdmin: boolean;
};

function initials(name: string) {
  return name
    .split(/\s+/)
    .map((w) => w[0])
    .slice(0, 2)
    .join("")
    .toUpperCase();
}

function timeAgo(iso: string) {
  const s = (Date.now() - new Date(iso).getTime()) / 1000;
  if (s < 60) return "just now";
  if (s < 3600) return `${Math.floor(s / 60)}m ago`;
  if (s < 86400) return `${Math.floor(s / 3600)}h ago`;
  return new Date(iso).toLocaleDateString("en-GB", {
    day: "numeric",
    month: "short",
    year: "numeric",
  });
}

export function Comments({
  reportId,
  comments,
  currentUserId,
  isAdmin,
}: Props) {
  const roots = comments.filter((c) => c.parent_id === null);
  const repliesOf = (id: number) =>
    comments.filter((c) => c.parent_id === id);

  return (
    <section id="comments" aria-label="Comments" className="space-y-4">
      <h2 className="flex items-center gap-2 text-lg font-semibold">
        <MessageSquare className="size-5" aria-hidden />
        Comments ({comments.length})
      </h2>

      {currentUserId ? (
        <CommentForm reportId={reportId} />
      ) : (
        <p className="text-muted-foreground bg-muted/50 rounded-lg p-3 text-sm">
          Log in to join the discussion.
        </p>
      )}

      {roots.length === 0 ? (
        <p className="text-muted-foreground py-6 text-center text-sm">
          No comments yet — start the conversation.
        </p>
      ) : (
        <ul className="space-y-4">
          {roots.map((c) => (
            <CommentItem
              key={c.id}
              comment={c}
              replies={repliesOf(c.id)}
              reportId={reportId}
              currentUserId={currentUserId}
              isAdmin={isAdmin}
            />
          ))}
        </ul>
      )}
    </section>
  );
}

function CommentItem({
  comment,
  replies,
  reportId,
  currentUserId,
  isAdmin,
}: {
  comment: CommentData;
  replies: CommentData[];
  reportId: number;
  currentUserId: string | null;
  isAdmin: boolean;
}) {
  const router = useRouter();
  const [replying, setReplying] = useState(false);
  const canDelete = isAdmin || comment.user_id === currentUserId;

  async function onDelete() {
    const result = await deleteComment(comment.id, reportId);
    if (!result.ok) toast.error(result.error);
    else router.refresh();
  }

  return (
    <li className="space-y-3">
      <div className="flex gap-3">
        <Avatar className="size-8 shrink-0">
          <AvatarFallback className="bg-brand-gradient text-[10px] font-semibold text-white">
            {initials(comment.authorName)}
          </AvatarFallback>
        </Avatar>
        <div className="min-w-0 flex-1">
          <div className="bg-muted/50 rounded-lg px-3 py-2">
            <p className="text-sm font-medium">{comment.authorName}</p>
            <p className="text-sm whitespace-pre-wrap">{comment.body}</p>
          </div>
          <div className="text-muted-foreground mt-1 flex items-center gap-3 text-xs">
            <span>{timeAgo(comment.created_at)}</span>
            {currentUserId && comment.parent_id === null ? (
              <button
                type="button"
                onClick={() => setReplying((v) => !v)}
                className="hover:text-foreground flex items-center gap-1 font-medium transition-colors"
              >
                <Reply className="size-3" aria-hidden /> Reply
              </button>
            ) : null}
            {canDelete ? (
              <button
                type="button"
                onClick={onDelete}
                className="text-destructive/80 hover:text-destructive flex items-center gap-1 font-medium transition-colors"
              >
                <Trash2 className="size-3" aria-hidden /> Delete
              </button>
            ) : null}
          </div>
          {replying ? (
            <div className="mt-2">
              <CommentForm
                reportId={reportId}
                parentId={comment.id}
                onDone={() => setReplying(false)}
                compact
              />
            </div>
          ) : null}
        </div>
      </div>

      {replies.length > 0 ? (
        <ul className="border-muted ml-11 space-y-3 border-l-2 pl-4">
          {replies.map((r) => (
            <CommentItem
              key={r.id}
              comment={r}
              replies={[]}
              reportId={reportId}
              currentUserId={currentUserId}
              isAdmin={isAdmin}
            />
          ))}
        </ul>
      ) : null}
    </li>
  );
}

function CommentForm({
  reportId,
  parentId = null,
  onDone,
  compact,
}: {
  reportId: number;
  parentId?: number | null;
  onDone?: () => void;
  compact?: boolean;
}) {
  const router = useRouter();
  const [body, setBody] = useState("");
  const [posting, setPosting] = useState(false);

  async function submit() {
    if (!body.trim()) return;
    setPosting(true);
    const result = await addComment(reportId, { body, parentId });
    setPosting(false);
    if (!result.ok) {
      toast.error(result.error);
      return;
    }
    setBody("");
    onDone?.();
    router.refresh();
  }

  return (
    <div className="flex gap-2">
      <Textarea
        value={body}
        onChange={(e) => setBody(e.target.value)}
        rows={compact ? 2 : 3}
        placeholder={parentId ? "Write a reply…" : "Share your perspective…"}
        aria-label={parentId ? "Reply" : "Comment"}
        className={cn(compact && "min-h-0")}
      />
      <Button
        type="button"
        onClick={submit}
        disabled={posting || !body.trim()}
        size={compact ? "sm" : "default"}
        className="bg-brand-gradient self-end border-0 text-white hover:opacity-90"
        aria-label="Post comment"
      >
        {posting ? (
          <Loader2 className="size-4 animate-spin" />
        ) : (
          <Send className="size-4" />
        )}
      </Button>
    </div>
  );
}
