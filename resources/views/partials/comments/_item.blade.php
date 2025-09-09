@php
  // allow delete if owner or admin
  $canDelete = auth()->check() && (auth()->id() === $comment->user_id || auth()->user()->is_admin);
@endphp

<div class="comment-item flex gap-3 py-3 border-b border-[var(--ring)] last:border-0" id="comment-{{ $comment->id }}">
  <div class="h-9 w-9 rounded-full bg-[var(--ring)] flex items-center justify-center shrink-0">
    <span class="text-sm font-semibold">{{ Str::upper(Str::substr($comment->user->name ?? 'U', 0, 1)) }}</span>
  </div>

  <div class="flex-1 min-w-0">
    <div class="flex items-center justify-between">
      <div class="text-sm font-semibold text-[var(--text)] truncate">
        {{ $comment->user->name ?? 'User' }}
        <span class="ml-2 text-[var(--muted)] text-xs">
          {{ optional($comment->created_at)->diffForHumans() }}
        </span>
      </div>

      @if($canDelete)
        <button
          type="button"
          class="delete-comment-btn text-[var(--muted)] hover:text-[var(--text)] text-xs"
          data-comment-id="{{ $comment->id }}"
          data-report-id="{{ $report->id }}"
        >
          Delete
        </button>
      @endif
    </div>

    <div class="mt-1 text-[var(--text)] text-sm leading-relaxed break-words">
      {{ $comment->body }}
    </div>
  </div>
</div>
