@php
  use Illuminate\Support\Facades\Route;

  // Default comments store route (route-safe)
  $commentsRouteName = $commentsRouteName
      ?? (Route::has('reports.comments.store') ? 'reports.comments.store' : null);

  // Existing list (prefer eager relation, else fallback query)
  $existing = isset($comments)
      ? $comments
      : ( $report->relationLoaded('comments')
            ? $report->comments
            : (method_exists($report,'comments') ? $report->comments()->latest()->take(10)->get() : collect()) );
@endphp

<div id="thread-{{ $report->id }}" class="cd-thread mt-3 space-y-3">
  <ul class="js-thread-list space-y-2">
    @forelse($existing as $c)
      @include('partials._comment', ['c' => $c])
    @empty
      {{-- no comments yet --}}
    @endforelse
  </ul>

  @auth
    @if($commentsRouteName)
      <form class="js-comment-form"
            action="{{ route($commentsRouteName, $report, false) }}" {{-- ← relative path --}}
            method="POST" autocomplete="off">
        @csrf
        <div class="flex items-end gap-2">
          <textarea name="body" rows="1" required minlength="1" maxlength="2000"
                    placeholder="Write a comment…"
                    class="flex-1 resize-none rounded-xl border px-3 py-2 text-[13px]
                           bg-white dark:bg-white/5
                           text-slate-900 dark:text-slate-100
                           placeholder:text-slate-400 dark:placeholder:text-slate-500
                           border-amber-200 dark:border-white/10
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
