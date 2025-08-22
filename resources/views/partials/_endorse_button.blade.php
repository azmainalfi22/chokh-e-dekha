@php
  use Illuminate\Support\Facades\Route;
  use Illuminate\Support\Facades\Schema;

  /** Required: $report */
  /** Optional: $endorsed, $endorseCount, $routeName */

  $endorsementsEnabled = Schema::hasTable('endorsements') &&
                         Schema::hasColumn('endorsements','report_id') &&
                         Schema::hasColumn('endorsements','user_id');

  // default route name
  $routeName = $routeName ?? (Route::has('reports.endorse') ? 'reports.endorse' : null);

  // counts fallback
  if (!isset($endorseCount)) {
      $endorseCount = $endorsementsEnabled
        ? ($report->endorsements_count ?? $report->endorsements()->count())
        : 0;
  }

  // endorsed-by-me fallback
  if (!isset($endorsed)) {
      $endorsed = false;
      if ($endorsementsEnabled && auth()->check()) {
          $endorsed = isset($report->endorsed_by_me)
              ? (bool) $report->endorsed_by_me
              : $report->endorsements()->where('user_id', auth()->id())->exists();
      }
  }
@endphp

@if($endorsementsEnabled && $routeName)
  @auth
    <form method="POST" action="{{ route($routeName, $report) }}"
          class="js-endorse-form" data-report-id="{{ $report->id }}">
      @csrf
      <button type="submit"
              class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg ring-1 ring-amber-900/10 transition
                     {{ $endorsed ? 'bg-amber-100 text-amber-900' : 'bg-white text-amber-900/80 hover:bg-amber-50' }}"
              data-endorsed="{{ $endorsed ? '1' : '0' }}">
        👍 <span class="js-endorse-count">{{ $endorseCount }}</span>
      </button>
    </form>
  @else
    <a href="{{ route('login') }}"
       class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg ring-1 ring-amber-900/10 bg-white text-amber-900/80 hover:bg-amber-50"
       title="Log in to endorse">
      👍 <span>{{ $endorseCount }}</span>
    </a>
  @endauth
@else
  <button type="button" disabled
          class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg ring-1 ring-amber-900/10 bg-white/70 text-amber-900/50 cursor-not-allowed">
    👍 <span>{{ $endorseCount }}</span>
  </button>
@endif
