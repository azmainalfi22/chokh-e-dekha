@php
  use Illuminate\Support\Facades\Route;
  use Illuminate\Support\Facades\Schema;

  /** Required: $report */
  /** Optional: $endorsed, $endorseCount, $routeName */

  // Is the feature available?
  $endorsementsEnabled = Schema::hasTable('endorsements')
    && Schema::hasColumn('endorsements','report_id')
    && Schema::hasColumn('endorsements','user_id');

  // Prefer a toggle route if it exists, otherwise fallback
  $routeName = $routeName
    ?? (Route::has('reports.endorse.toggle')
          ? 'reports.endorse.toggle'
          : (Route::has('reports.endorse') ? 'reports.endorse' : null));

  // Count fallback (use eager-loaded count if available)
  if (!isset($endorseCount)) {
      $endorseCount = $endorsementsEnabled
        ? ($report->endorsements_count ?? (method_exists($report,'endorsements') ? $report->endorsements()->count() : 0))
        : 0;
  }

  // "endorsed by me" fallback
  if (!isset($endorsed)) {
      $endorsed = false;
      if ($endorsementsEnabled && auth()->check()) {
          $endorsed = isset($report->endorsed_by_me)
              ? (bool) $report->endorsed_by_me
              : (method_exists($report,'endorsements')
                    ? $report->endorsements()->where('user_id', auth()->id())->exists()
                    : false);
      }
  }

  $btnBase = 'inline-flex items-center gap-1.5 px-2 py-1 rounded-lg ring-1 ring-amber-900/10 transition';
  $btnOn   = 'bg-amber-100 text-amber-900';
  $btnOff  = 'bg-white text-amber-900/80 hover:bg-amber-50 dark:bg-white/10';
@endphp

@if($endorsementsEnabled && $routeName)
  @auth
    <form method="POST"
          action="{{ route($routeName, $report) }}"
          class="js-endorse-form"
          data-report-id="{{ $report->id }}">
      @csrf
      <button type="submit"
              class="{{ $btnBase }} {{ $endorsed ? $btnOn : $btnOff }}"
              data-endorsed="{{ $endorsed ? '1' : '0' }}"
              aria-pressed="{{ $endorsed ? 'true' : 'false' }}"
              title="{{ $endorsed ? 'Remove endorsement' : 'Endorse this report' }}">
        👍 <span class="js-endorse-count">{{ $endorseCount }}</span>
      </button>
    </form>
  @else
    <a href="{{ route('login') }}"
       class="{{ $btnBase }} {{ $btnOff }}"
       title="Log in to endorse">
      👍 <span>{{ $endorseCount }}</span>
    </a>
  @endauth
@else
  <button type="button" disabled
          class="{{ $btnBase }} bg-white/70 text-amber-900/50 cursor-not-allowed">
    👍 <span>{{ $endorseCount }}</span>
  </button>
@endif

@once
  @push('scripts')
    <script>
      (function(){
        const getCsrf = () =>
          document.querySelector('meta[name="csrf-token"]')?.content ||
          window.csrf?.() || '';

        document.addEventListener('submit', async (e) => {
          const form = e.target.closest('.js-endorse-form');
          if (!form) return;

          // Progressive enhancement: try AJAX first
          e.preventDefault();

          const btn   = form.querySelector('button[type="submit"]');
          const count = form.querySelector('.js-endorse-count');
          if (!btn || !count) { form.submit(); return; }

          const originalText = btn.textContent;
          btn.disabled = true;

          try {
            const res = await fetch(form.action, {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': getCsrf(),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
              },
              body: new FormData(form)
            });

            // If backend returns JSON { endorsed: bool, count: int }
            if (res.ok && (res.headers.get('content-type') || '').includes('application/json')) {
              const json = await res.json().catch(() => ({}));
              if (typeof json.count !== 'undefined') count.textContent = json.count;
              const endorsed = !!json.endorsed || (btn.getAttribute('data-endorsed') === '0');
              // toggle UI state
              btn.setAttribute('data-endorsed', endorsed ? '1' : '0');
              btn.setAttribute('aria-pressed', endorsed ? 'true' : 'false');
              btn.classList.toggle('bg-amber-100', endorsed);
              btn.classList.toggle('text-amber-900', endorsed);
              btn.classList.toggle('bg-white', !endorsed);
              btn.classList.toggle('hover:bg-amber-50', !endorsed);
              btn.classList.toggle('text-amber-900/80', !endorsed);
              btn.classList.toggle('dark:bg-white/10', !endorsed);
              btn.title = endorsed ? 'Remove endorsement' : 'Endorse this report';
            } else {
              // Controller might redirect for non-AJAX; fall back
              form.submit();
            }
          } catch (err) {
            console?.error?.('Endorse request failed:', err);
            // graceful fallback
            form.submit();
          } finally {
            btn.disabled = false;
            btn.textContent = originalText;
          }
        }, { capture: true });
      })();
    </script>
  @endpush
@endonce
