@php
  /** Accept either $comment or $c */
  /** @var \App\Models\Comment $comment */
  $comment = $comment ?? $c ?? null;

  $name = trim($comment?->user?->name ?? 'User');
  $initial = mb_strtoupper(mb_substr($name, 0, 1));
@endphp

@once
  @push('styles')
  <style>
    /* Comment bubble readability in both themes (uses your theme tokens) */
    .cd-comment-item .bubble{
      background: var(--surface-muted, #f8fafc);
      border: 1px solid var(--ring, #e2e8f0);
      border-radius: 1rem;
      padding: .6rem .8rem;
      min-width: 0;
    }
    .dark .cd-comment-item .bubble{
      background: rgba(255,255,255,.06);
      border-color: #374151;
    }
    .cd-comment-item .author{
      color: var(--text, #0f172a);
      font-weight: 700;
      font-size: .875rem;
    }
    .dark .cd-comment-item .author{ color:#f9fafb; }
    .cd-comment-item .body{
      color: var(--text-secondary, #475569);
      font-size: .875rem;
      line-height: 1.4;
      white-space: pre-line;        /* preserve newlines */
      overflow-wrap: anywhere;      /* avoid overflow on long words/urls */
    }
    .dark .cd-comment-item .body{ color:#d1d5db; }
  </style>
  @endpush
@endonce

<li id="comment-{{ $comment->id }}"
    data-comment-id="{{ $comment->id }}"
    class="cd-comment-item flex items-start gap-2">

  {{-- avatar --}}
  <div class="mt-0.5 h-7 w-7 flex-none rounded-full ring-1
              bg-amber-200/60 ring-amber-200 text-amber-900
              dark:bg-white/10 dark:ring-white/10 dark:text-amber-200
              grid place-items-center text-[11px] font-semibold">
    {{ $initial }}
  </div>

  <div class="flex-1 min-w-0">
    <div class="bubble inline-block">
      <span class="author">{{ $name }}</span>
      <span class="ml-1 body">{!! nl2br(e($comment->body)) !!}</span>
    </div>

    <div class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400"
         title="{{ optional($comment->created_at)->toDayDateTimeString() }}">
      {{ optional($comment->created_at)->diffForHumans() }}
    </div>
  </div>
</li>
