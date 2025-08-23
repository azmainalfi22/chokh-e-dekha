@php
  use Illuminate\Support\Facades\Route;

  // Route-safe: use existing if passed in, else detect
  $commentsRouteName = $commentsRouteName
      ?? (Route::has('reports.comments.store') ? 'reports.comments.store' : null);

  // Prefer eager-loaded comments; otherwise fetch a small recent set
  $existing = isset($comments)
      ? $comments
      : (
          $report->relationLoaded('comments')
            ? $report->comments
            : (method_exists($report,'comments') ? $report->comments()->latest()->take(10)->get() : collect())
        );
@endphp

@once
  @push('styles')
  <style>
    /* ------- Scoped fixes for dark mode readability ------- */
    .cd-thread { color: var(--text, #0f172a); }
    .dark .cd-thread { color: var(--text, #e5e7eb); }

    /* If inner partials used their own classes, normalize here */
    .cd-thread .comment-bubble{
      background: var(--surface-muted, #f8fafc);
      border: 1px solid var(--ring, #e2e8f0);
      border-radius: var(--radius-2xl, 1rem);
    }
    .dark .cd-thread .comment-bubble{
      background: rgba(255,255,255,.06);         /* subtle dark bubble */
      border-color: var(--ring, #374151);
    }

    .cd-thread .comment-author{ color: var(--text, #0f172a) !important; }
    .cd-thread .comment-text{   color: var(--text-secondary, #475569) !important; }
    .dark .cd-thread .comment-author{ color: var(--text, #f9fafb) !important; }
    .dark .cd-thread .comment-text{   color: var(--text-secondary, #d1d5db) !important; }

    /* Links inside comments */
    .cd-thread a { color: var(--link, #0ea5e9); }
    .cd-thread a:hover { color: var(--accent, #f59e0b); }

    /* Textarea look in dark mode */
    .cd-thread textarea{
      background: #ffffff;
      color: #0f172a;
      border-color: var(--ring, #e2e8f0);
    }
    .dark .cd-thread textarea{
      background: rgba(255,255,255,.06);
      color: #e5e7eb;
      border-color: #374151;
    }
    .cd-thread textarea::placeholder{ color:#94a3b8; }
    .dark .cd-thread textarea::placeholder{ color:#64748b; }
  </style>
  @endpush
@endonce

<div id="thread-{{ $report->id }}" class="cd-thread mt-3 space-y-3">
  <ul class="js-thread-list space-y-2">
    @forelse($existing as $c)
      {{-- Your item partial should output .comment-bubble /.comment-author /.comment-text --}}
      @include('partials._comment', ['c' => $c])
    @empty
      {{-- No comments yet --}}
    @endforelse
  </ul>

  @auth
    @if($commentsRouteName)
      <form class="js-comment-form"
            action="{{ route($commentsRouteName, ['report' => $report], false) }}"
            method="POST" autocomplete="off">
        @csrf
        <div class="flex items-end gap-2">
          <textarea name="body" rows="2" required minlength="1" maxlength="2000"
                    placeholder="Write a comment…"
                    class="flex-1 resize-y rounded-xl border px-3 py-2 text-[13px]
                           focus:outline-none focus:ring-2 focus:ring-amber-300 dark:focus:ring-amber-400"></textarea>
          <button type="submit"
                  class="px-3 py-1.5 rounded-lg bg-amber-600 text-white hover:bg-amber-700">
            Post
          </button>
        </div>
      </form>
    @endif
  @endauth

  @guest
    <div class="mt-1 text-[13px] text-slate-600 dark:text-slate-400">
      @if (Route::has('login'))
        <a href="{{ route('login', [], false) }}" class="underline font-medium">Log in</a>
      @else
        Log in
      @endif
      to comment.
    </div>
  @endguest
</div>
