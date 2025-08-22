@extends(auth()->user()?->is_admin ? 'layouts.admin' : 'layouts.app')

@section('title', 'All Reports')

@push('styles')
<style>
  /* ---------- THEME TOKENS (Light/Dark) ---------- */
  :root{
    --surface: #ffffff;
    --surface-muted: #f8fafc;        /* slate-50 */
    --text: #0f172a;                  /* slate-900 */
    --muted: #475569;                 /* slate-600/700 */
    --ring: #e2e8f0;                  /* slate-200 */
    --link: #0ea5e9;                  /* sky-500 */
    --accent: #f59e0b;                /* amber-500 */
    --accent-700:#b45309;             /* amber-700 */
  }
  .dark{
    --surface:#6653325b;              /* semi-transparent for glassy cards */
    --surface-muted:#111827;          /* gray-900 */
    --text:#e5e7eb;                   /* gray-200 */
    --muted:#9ca3af;                  /* gray-400 */
    --ring:#1f2937;                   /* gray-800 */
    --link:#38bdf8;                   /* sky-400 */
    --accent:#f59e0b;
    --accent-700:#f59e0b;
  }

  /* Soft grain overlay */
  .grainy::before{
    content:""; position:absolute; inset:0; pointer-events:none; z-index:0;
    opacity:.14; mix-blend:multiply;
    background-size: 220px 220px; background-repeat: repeat;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='180' height='180'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='3' stitchTiles='stitch'/%3E%3CfeColorMatrix type='saturate' values='0'/%3E%3C/feTurbulence%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.20'/%3E%3C/svg%3E");
  }
  .dark .grainy::before{ opacity:.10; } /* reduce glare in dark */

  /* ---------- SURFACES & TYPOGRAPHY ---------- */
  body{ color:var(--text); }
  .report-card{
    background: var(--surface);
    border: 1px solid var(--ring);
    box-shadow: 0 8px 24px rgba(100, 60, 7, 0.37);
    transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
  }
  .report-card:hover{
    transform: translateY(-3px);
    box-shadow: 0 14px 36px rgba(2,6,23,.18);
    border-color: rgba(245,158,11,.45);
  }
  .dark .report-card{
    box-shadow: 0 18px 40px rgba(0,0,0,.35);
  }

  .report-head{ display:flex; align-items:flex-start; justify-content:space-between; gap:.75rem; }
  .report-head h3{ color:var(--text); }

  .meta{ color: var(--muted); }
  .meta li{ display:flex; align-items:center; gap:.5rem; }

  a{ color: var(--link); }
  a:hover{ text-decoration: underline; }

  /* ---------- BADGES ---------- */
  .badge{ display:inline-flex; align-items:center; gap:.4rem;
          padding:.18rem .55rem; border-radius:999px; font-size:.72rem; font-weight:600; }
  .badge-category{
    background: rgba(245,158,11,.12); color: var(--accent-700);
    border: 1px solid rgba(245,158,11,.28);
  }

  /* Status pills – tuned for both themes */
  .status-pill{ display:inline-flex; align-items:center; gap:.35rem; padding:.22rem .6rem;
                border-radius:999px; font-size:.72rem; font-weight:700; line-height:1; border:1px solid transparent; white-space:nowrap;}
  .status-pending{     background: rgba(245,158,11,.16); color:#92400e; border-color: rgba(245,158,11,.35); }
  .dark .status-pending{ color:#fbbf24; }
  .status-in_progress{ background: rgba(59,130,246,.15); color:#1e3a8a;  border-color: rgba(147,197,253,.45); }
  .dark .status-in_progress{ color:#93c5fd; }
  .status-resolved{    background: rgba(16,185,129,.15); color:#065f46; border-color: rgba(110,231,183,.45); }
  .dark .status-resolved{ color:#6ee7b7; }
  .status-rejected{    background: rgba(239,68,68,.16);  color:#991b1b; border-color: rgba(252,165,165,.45); }
  .dark .status-rejected{ color:#fca5a5; }

  /* ---------- THREAD & EFFECTS ---------- */
  .cd-thread { max-height:0; overflow:hidden; transition:max-height .3s ease; }
  @keyframes fadeIn { from { opacity:0; transform: translateY(6px);} to { opacity:1; transform:none;} }
  .appear { animation: fadeIn .25s ease both; }
  @media (prefers-reduced-motion: reduce){
    .report-card, .appear { transition:none !important; animation:none !important; transform:none !important; }
  }

  /* ---------- FILTERS PANEL ---------- */
  .filters-panel{ background: var(--surface); border:1px solid var(--ring);
                  box-shadow: 0 8px 24px rgba(43, 24, 4, 0.38); }
  .filters-panel input[type="search"],
  .filters-panel select,
  .filters-panel .btn{
    background: var(--surface-muted) !important; color: var(--text) !important; border-color: var(--ring) !important;
  }
  .filters-panel .btn-apply{
    background: linear-gradient(90deg,#f59e0b,#f43f5e) !important; color:#fff !important; border-color:transparent !important;
  }
  .filters-panel .btn-ghost{
    background: var(--surface) !important; color: var(--text) !important; border-color: var(--ring) !important;
  }
</style>
@endpush


@section('content')
@php
  use Illuminate\Support\Facades\Schema;
  use Illuminate\Support\Facades\Route;
  use Illuminate\Support\Str;

  // Feature flags – resilient on fresh DBs
  $commentsEnabled     = Schema::hasTable('comments')     && Schema::hasColumn('comments','report_id');
  $endorsementsEnabled = Schema::hasTable('endorsements') && Schema::hasColumn('endorsements','report_id') && Schema::hasColumn('endorsements','user_id');

  // Route names (truthy only if registered)
  $endorseRouteName  = Route::has('reports.endorse.toggle')
                        ? 'reports.endorse.toggle'
                        : (Route::has('reports.endorse') ? 'reports.endorse' : null);
  $commentsRouteName = Route::has('reports.comments.store') ? 'reports.comments.store' : null;
@endphp

<div class="relative grainy">
  {{-- colored blobs --}}
  <div class="pointer-events-none absolute -top-20 -right-24 h-80 w-80 rounded-full blur-3xl opacity-30 bg-gradient-to-br from-amber-300 to-rose-300"></div>
  <div class="pointer-events-none absolute -bottom-24 -left-24 h-96 w-80 rounded-full blur-3xl opacity-30 bg-gradient-to-tr from-orange-300 to-pink-300"></div>

  <div class="max-w-7xl mx-auto p-4 md:p-8 relative z-[1]">
    <header class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="min-w-0">
          <h1 class="text-3xl font-extrabold text-[color:var(--text)]">All Reports</h1>
          <p class="text-sm" style="color:var(--muted)">Search and filter reports submitted across all city corporations.</p>
        </div>

        @if(Route::has('report.create') && !auth()->user()?->is_admin)
          <a href="{{ route('report.create') }}"
             class="inline-flex items-center gap-2 px-4 py-2 rounded-xl shadow hover:shadow-md bg-amber-600 text-white hover:bg-amber-700 transition self-start md:self-auto">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M11 5h2v14h-2zM5 11h14v2H5z"/></svg>
            New Report
          </a>
        @endif
      </div>
    </header>

    {{-- Flash --}}
    @if(session('success'))
      <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 shadow-sm">
        {{ session('success') }}
      </div>
    @endif

    {{-- Filters + Actions --}}
    <div class="mb-6 rounded-2xl filters-panel p-4">
      <form method="GET" action="{{ route('reports.index') }}"
            class="grid gap-3 items-center grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 xl:grid-cols-9">

        {{-- Search --}}
        <div class="flex items-center gap-2 col-span-1 sm:col-span-2 lg:col-span-3 xl:col-span-3">
          <svg class="h-5 w-5 flex-none" style="color:var(--muted)" viewBox="0 0 24 24" fill="currentColor"><path d="M10 4a6 6 0 104.47 10.03l3.75 3.75 1.41-1.41-3.75-3.75A6 6 0 0010 4zm0 2a4 4 0 110 8 4 4 0 010-8z"/></svg>
          <input type="search" name="q" value="{{ $q ?? '' }}" placeholder="Search title, description, or address…"
                 class="w-full rounded-xl border px-3 py-2 focus:ring-2" style="border-color:var(--ring)">
        </div>

        {{-- City --}}
        <select name="city_corporation"
                class="w-full rounded-xl border px-3 py-2 focus:ring-2"
                style="border-color:var(--ring)">
          <option value="">All cities</option>
          @foreach(($cities ?? collect()) as $c)
            <option value="{{ $c }}" @selected(($city ?? '') === $c)>{{ $c }}</option>
          @endforeach
        </select>

        {{-- Category --}}
        <select name="category"
                class="w-full rounded-xl border px-3 py-2 focus:ring-2"
                style="border-color:var(--ring)">
          <option value="">All categories</option>
          @foreach(($categories ?? collect()) as $categ)
            <option value="{{ $categ }}" @selected((($category ?? $cat ?? '') === $categ))>{{ $categ }}</option>
          @endforeach
        </select>

        {{-- Status --}}
        <select name="status"
                class="w-full rounded-xl border px-3 py-2 focus:ring-2"
                style="border-color:var(--ring)">
          <option value="">All statuses</option>
          @foreach(($statuses ?? ['pending','in_progress','resolved','rejected']) as $s)
            <option value="{{ $s }}" @selected(($status ?? '') === $s)>{{ Str::headline($s) }}</option>
          @endforeach
        </select>

        {{-- Near me cluster --}}
        <div class="col-span-1 sm:col-span-2 xl:col-span-3 flex flex-wrap items-center gap-2">
          <button type="button" id="nearMeBtn"
                  class="inline-flex items-center gap-2 rounded-xl px-3 py-2 bg-amber-600 text-white hover:bg-amber-700 shadow">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l1.8 4.2L18 8l-4.2 1.8L12 14l-1.8-4.2L6 8l4.2-1.8z"/></svg>
            Near me
          </button>

          <label for="radius_km" class="text-sm" style="color:var(--muted)">Radius</label>
          @php $radiusKm = (int)($radiusKm ?? request('radius_km', 0)); @endphp
          <select id="radius_km" name="radius_km"
                  class="rounded-xl border px-3 py-2 focus:ring-2" style="border-color:var(--ring)">
            <option value="0"  @selected($radiusKm===0)>Any</option>
            <option value="3"  @selected($radiusKm===3)>3 km</option>
            <option value="5"  @selected($radiusKm===5)>5 km</option>
            <option value="10" @selected($radiusKm===10)>10 km</option>
            <option value="20" @selected($radiusKm===20)>20 km</option>
          </select>

          {{-- Hidden near-me fields --}}
          <input type="hidden" id="near_lat" name="near_lat" value="{{ $nearLat ?? request('near_lat') }}">
          <input type="hidden" id="near_lng" name="near_lng" value="{{ $nearLng ?? request('near_lng') }}">
        </div>

        {{-- Actions cluster --}}
        <div class="col-span-1 sm:col-span-2 lg:col-span-3 xl:col-span-3 flex flex-wrap items-center justify-end gap-2">
          <label for="per_page" class="sr-only">Results per page</label>
          <select id="per_page" name="per_page"
                  class="w-full sm:w-auto rounded-xl border px-3 py-2 focus:ring-2 btn" style="border-color:var(--ring)">
            @foreach([12,18,24,30,36,48] as $pp)
              <option value="{{ $pp }}" @selected((int)request('per_page',12)===$pp)>Show {{ $pp }}</option>
            @endforeach
          </select>

          @php $sort = request('sort', ($nearLat ?? false) && ($nearLng ?? false) ? 'nearest' : 'newest'); @endphp
          <label for="sort" class="sr-only">Sort by</label>
          <select id="sort" name="sort"
                  class="w-full sm:w-auto rounded-xl border px-3 py-2 focus:ring-2 btn" style="border-color:var(--ring)">
            <option value="newest"   @selected($sort==='newest')>Newest first</option>
            <option value="oldest"   @selected($sort==='oldest')>Oldest first</option>
            <option value="status"   @selected($sort==='status')>Status (A→Z)</option>
            <option value="city"     @selected($sort==='city')>City (A→Z)</option>
            <option value="category" @selected($sort==='category')>Category (A→Z)</option>
            <option value="nearest"  @selected($sort==='nearest')>Nearest</option>
          </select>

          <button type="submit" name="apply" value="1"
                  class="flex-none rounded-xl font-semibold px-4 py-2 shadow hover:shadow-lg btn-apply">
            Apply
          </button>

          <a href="{{ route('reports.index') }}"
             class="flex-none rounded-xl px-3 py-2 border shadow hover:shadow-md btn-ghost">
            Clear
          </a>

          <button type="button" id="copyLinkBtn"
                  class="flex-none rounded-xl px-3 py-2 border shadow hover:shadow-md btn-ghost">
            Copy link
          </button>
        </div>

        {{-- Active chips --}}
        @if(($q ?? '') || ($city ?? '') || (($category ?? $cat ?? '') !== '') || (($status ?? '') !== '') || (($nearLat ?? request('near_lat')) && ($nearLng ?? request('near_lng'))))
          <div class="sm:col-span-2 lg:grid-cols-6 xl:grid-cols-9 mt-1 flex flex-wrap items-center gap-2 text-sm" style="color:var(--muted)">
            <span>Active:</span>
            @if($q)    <span class="px-2 py-1 rounded-lg" style="background:rgba(245,158,11,.12); color:var(--text); border:1px solid rgba(245,158,11,.28);">Search: “{{ $q }}”</span>@endif
            @if($city) <span class="px-2 py-1 rounded-lg" style="background:rgba(245,158,11,.12); color:var(--text); border:1px solid rgba(245,158,11,.28);">City: {{ $city }}</span>@endif
            @php $showCat = ($category ?? $cat ?? ''); @endphp
            @if($showCat !== '') <span class="px-2 py-1 rounded-lg" style="background:rgba(245,158,11,.12); color:var(--text); border:1px solid rgba(245,158,11,.28);">Category: {{ $showCat }}</span>@endif
            @if(($status ?? '') !== '') <span class="px-2 py-1 rounded-lg" style="background:rgba(245,158,11,.12); color:var(--text); border:1px solid rgba(245,158,11,.28);">Status: {{ Str::headline($status) }}</span>@endif
            @if(($nearLat ?? request('near_lat')) && ($nearLng ?? request('near_lng')))
              <span class="px-2 py-1 rounded-lg" style="background:rgba(245,158,11,.12); color:var(--text); border:1px solid rgba(245,158,11,.28);">Near me{{ $radiusKm ? " ({$radiusKm} km)" : '' }}</span>
            @endif
          </div>
        @endif
      </form>
    </div>

    @php
      $pill = function($status) {
        $s = $status ?? 'pending';
        $label = Str::headline($s);
        return '<span class="status-pill status-'.e($s).'">● '.$label.'</span>';
      };
    @endphp

    {{-- List --}}
    @if($reports->isEmpty())
      <div class="rounded-2xl border border-amber-300 bg-white/60 backdrop-blur px-6 py-12 text-center shadow">
        <div class="mx-auto mb-3 inline-flex h-12 w-12 items-center justify-center rounded-full ring-1 ring-amber-200 bg-amber-50">
          <svg class="h-5 w-5 text-amber-700" viewBox="0 0 24 24" fill="currentColor"><path d="M3 5h18v2H3zM3 10h18v2H3zM3 15h12v2H3z"/></svg>
        </div>
        <h3 class="text-lg font-semibold" style="color:var(--text)">No reports match your filters</h3>
        <p class="text-sm" style="color:var(--muted)">Try adjusting search terms or clearing filters.</p>
        <a href="{{ route('reports.index') }}" class="mt-4 inline-flex px-4 py-2 rounded-xl bg-amber-600 text-white hover:bg-amber-700">Reset filters</a>
      </div>
    @else
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($reports as $report)
          @php
            $commentsCount = $commentsEnabled
              ? ($report->comments_count ?? ($report->relationLoaded('comments') ? $report->comments->count() : (method_exists($report,'comments') ? $report->comments()->count() : 0)))
              : 0;

            $endorseCount  = $endorsementsEnabled
              ? ($report->endorsements_count ?? (method_exists($report,'endorsements') ? $report->endorsements()->count() : 0))
              : 0;

            $endorsed = false;
            if ($endorsementsEnabled && auth()->check() && method_exists($report,'endorsements')) {
              $endorsed = isset($report->endorsed_by_me)
                ? (bool) $report->endorsed_by_me
                : $report->endorsements()->where('user_id', auth()->id())->exists();
            }
          @endphp

          {{-- IMPORTANT: no overflow-hidden here to let the thread expand --}}
          <div class="report-card appear rounded-2xl transition">

            {{-- Static map header --}}
            @if($report->static_map_url)
              <a href="@if($report->has_coords) https://www.google.com/maps/search/?api=1&query={{ $report->latitude }},{{ $report->longitude }} @else {{ route('reports.show', $report) }} @endif"
                 target="_blank" rel="noopener"
                 class="block">
                <img src="{{ $report->static_map_url }}"
                     alt="Map preview"
                     class="w-full h-40 object-cover" loading="lazy">
              </a>
            @endif

            <div class="p-5 flex flex-col gap-3 relative group">
              <div class="report-head">
                <div class="min-w-0">
                  <h3 class="text-lg font-bold leading-snug line-clamp-2">{{ $report->title }}</h3>
                  <p class="mt-0.5">
                    <span class="badge badge-category">{{ $report->category ?? 'General' }}</span>
                  </p>
                </div>
                {!! $pill($report->status) !!}
              </div>

              <ul class="meta space-y-1">
                <li>
                  <svg class="h-4 w-4" style="color:var(--accent-700)" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8 2 5 5 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-4-3-7-7-7zm0 9.5a2.5 2.5 0 110-5 2.5 2.5 0 010 5z"/></svg>
                  <span class="font-medium" style="color:var(--text)">
                    {{ $report->short_address ?? $report->location ?? 'N/A' }}
                  </span>
                  @if($report->has_coords)
                    <a href="https://www.google.com/maps/search/?api=1&query={{ $report->latitude }},{{ $report->longitude }}"
                       target="_blank" rel="noopener"
                       class="ml-2 text-xs">Open in Maps</a>
                  @endif
                </li>
                <li>
                  <svg class="h-4 w-4" style="color:var(--accent-700)" viewBox="0 0 24 24" fill="currentColor"><path d="M5 4h14v2H5zM5 8h14v12H5z"/></svg>
                  <span>{{ $report->city_corporation ?? '—' }}</span>
                </li>
                <li>
                  <svg class="h-4 w-4" style="color:var(--accent-700)" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 100-10 5 5 0 000 10zM4 22v-2c0-2.2 3.8-3.3 6-3.3s6 1.1 6 3.3v2H4z"/></svg>
                  <span>{{ $report->user->name ?? 'Unknown' }}</span>
                </li>
                <li>
                  <svg class="h-4 w-4" style="color:var(--accent-700)" viewBox="0 0 24 24" fill="currentColor"><path d="M7 2h10v2H7zM5 6h14v14H5zM9 8h6v6H9z"/></svg>
                  <span>{{ optional($report->created_at)->format('M d, Y h:i a') }}</span>
                </li>
              </ul>

              {{-- Engagement bar --}}
              <div class="mt-2 flex items-center justify-between text-sm">
                <div class="flex items-center gap-3">
                  {{-- Endorse (like) --}}
                  @includeWhen($endorsementsEnabled && $endorseRouteName, 'partials._endorse_button', [
                    'report'        => $report,
                    'endorseCount'  => $endorseCount ?? 0,
                    'endorsed'      => $endorsed ?? false,
                    'routeName'     => $endorseRouteName
                  ])

                  {{-- Comments trigger (FB-like inline) --}}
                  <button type="button"
                          class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg border btn js-thread-toggle"
                          data-target="#thread-{{ $report->id }}"
                          aria-expanded="false">
                    💬 <span class="js-comments-count">{{ $commentsCount }}</span>
                  </button>
                </div>

                <a href="{{ route('reports.show', $report) }}"
                   class="inline-flex items-center gap-1 font-medium">
                  View details
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M10 6l6 6-6 6-1.4-1.4L12.2 12 8.6 7.4z"/></svg>
                </a>
              </div>

              {{-- Inline thread (FB-like) --}}
              @includeWhen($commentsEnabled && $commentsRouteName, 'partials._comment_thread', [
                'report' => $report,
                'commentsRouteName' => $commentsRouteName
              ])
            </div>
          </div>
        @endforeach
      </div>

      @if(method_exists($reports,'links'))
        <div class="mt-6">
          {{ $reports->links() }}
        </div>
      @endif
    @endif
  </div>
</div>

@push('scripts')
<script>
(function(){
  const getCsrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

  /* ===== tiny toast ===== */
  function toast(msg){
    let t = document.createElement('div');
    t.role = 'status';
    t.ariaLive = 'polite';
    t.textContent = msg;
    t.className = 'fixed left-1/2 -translate-x-1/2 bottom-6 z-[60] px-3 py-2 rounded-xl text-sm text-white shadow-lg';
    t.style.backgroundImage = 'linear-gradient(90deg,#d97706,#e11d48)';
    document.body.appendChild(t);
    setTimeout(()=>{ t.style.opacity='0'; t.style.transform='translate(-50%,8px)'; }, 1100);
    setTimeout(()=>t.remove(), 1500);
  }

  /* ===== Auto-appear on scroll ===== */
  const io = ('IntersectionObserver' in window) ? new IntersectionObserver((entries)=>{
    for (const e of entries){ if (e.isIntersecting) { e.target.classList.add('appear'); io.unobserve(e.target); } }
  }, { rootMargin: '0px 0px -10% 0px' }) : null;

  document.querySelectorAll('.report-card').forEach(c => io?.observe(c));

  /* ===== Filter helpers ===== */
  const form = document.querySelector('form[action="{{ route('reports.index') }}"]');
  ['per_page','sort','radius_km'].forEach(id => {
    const el = document.getElementById(id);
    if (el && form) el.addEventListener('change', () => form.requestSubmit());
  });

  // Optional: debounce search auto-apply
  const qInput = form?.querySelector('input[name="q"]');
  if (qInput){
    let t; qInput.addEventListener('input', ()=>{
      clearTimeout(t);
      t = setTimeout(()=>{ if (qInput.value.trim().length >= 2) form.requestSubmit(); }, 450);
    });
  }

  // Copy link -> toast
  const copyBtn = document.getElementById('copyLinkBtn');
  copyBtn?.addEventListener('click', async () => {
    try { await navigator.clipboard.writeText(location.href); toast('Link copied'); } catch {}
  });

  // Near me
  const nearBtn = document.getElementById('nearMeBtn');
  const nearLat = document.getElementById('near_lat');
  const nearLng = document.getElementById('near_lng');
  const sortSel = document.getElementById('sort');
  nearBtn?.addEventListener('click', () => {
    if (!navigator.geolocation) { alert('Geolocation not supported on this device/browser.'); return; }
    nearBtn.disabled = true; nearBtn.classList.add('opacity-70'); nearBtn.textContent = 'Locating…';
    navigator.geolocation.getCurrentPosition(pos => {
      nearLat.value = pos.coords.latitude.toFixed(6);
      nearLng.value = pos.coords.longitude.toFixed(6);
      if (sortSel && sortSel.value !== 'nearest') sortSel.value = 'nearest';
      form.requestSubmit();
    }, () => {
      alert('Unable to get location. Please allow location access and try again.');
      nearBtn.disabled = false; nearBtn.classList.remove('opacity-70'); nearBtn.textContent = 'Near me';
    }, { enableHighAccuracy: true, timeout: 8000, maximumAge: 60000 });
  });

  // Thread toggle (slide open like FB)
  function openThread(panel){ const h = panel.scrollHeight; panel.style.maxHeight = (h+40) + 'px'; panel.classList.add('open'); }
  function closeThread(panel){ panel.style.maxHeight = '0px'; panel.classList.remove('open'); }
  document.addEventListener('click', (e)=>{
    const btn = e.target.closest('.js-thread-toggle'); if(!btn) return;
    const panel = document.querySelector(btn.dataset.target); if(!panel) return;
    const isOpen = panel.classList.contains('open'); (isOpen ? closeThread : openThread)(panel);
    btn.setAttribute('aria-expanded', String(!isOpen));
  });

  // Endorse (like) optimistic
  document.querySelectorAll('.js-endorse-form').forEach(formEl => {
    formEl.addEventListener('submit', async (ev) => {
      ev.preventDefault();
      const btn = formEl.querySelector('button');
      const cntEl = formEl.querySelector('.js-endorse-count');
      if (!btn || !cntEl) return;
      const endorsed = btn.dataset.endorsed === '1';
      const old = parseInt(cntEl.textContent || '0', 10);

      btn.dataset.endorsed = endorsed ? '0' : '1';
      cntEl.textContent = String(old + (endorsed ? -1 : +1));
      btn.classList.toggle('bg-amber-100', !endorsed);
      btn.classList.toggle('text-amber-900', !endorsed);

      try{
        const r = await fetch(formEl.action, { method:'POST',
          headers:{'X-CSRF-TOKEN':getCsrf(),'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
          body:new FormData(formEl)
        });
        if(!r.ok) throw 0;
      }catch{
        btn.dataset.endorsed = endorsed ? '1' : '0';
        cntEl.textContent = String(old);
        btn.classList.toggle('bg-amber-100', endorsed);
        btn.classList.toggle('text-amber-900', endorsed);
        alert('Could not update like right now.');
      }
    }, {passive:false});
  });

  // Comment submit (inline + optimistic)
  document.querySelectorAll('.js-comment-form').forEach(form => {
    form.addEventListener('submit', async (ev)=>{
      ev.preventDefault();
      const ta = form.querySelector('textarea[name="body"]');
      const text = (ta?.value || '').trim();
      if(!text) return;

      const thread = form.closest('.cd-thread');
      const list   = thread?.querySelector('.js-thread-list');
      const card   = form.closest('.group');
      const cnt    = card?.querySelector('.js-comments-count');
      const youName = document.querySelector('meta[name="user-name"]')?.content || 'You';

      const li = document.createElement('li');
      li.className = 'flex items-start gap-2';
      li.innerHTML = `
        <div class="mt-0.5 h-7 w-7 flex-none rounded-full bg-amber-200/60 ring-1 ring-amber-200"></div>
        <div class="flex-1">
          <div class="inline-block rounded-2xl bg-amber-50 ring-1 ring-amber-100 px-3 py-2 text-[13px]">
            <span class="font-semibold">${youName}</span>
            <span class="ml-1">${text.replace(/</g,'&lt;')}</span>
          </div>
        </div>`;
      list?.appendChild(li);

      const old = parseInt(cnt?.textContent || '0', 10);
      if (cnt) cnt.textContent = String(old + 1);
      if (ta) ta.value = '';

      try{
        const r = await fetch(form.action, { method:'POST',
          headers:{'X-CSRF-TOKEN':getCsrf(),'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
          body:new FormData(form)
        });
        if(!r.ok) throw 0;
      }catch{
        li.remove();
        if (cnt) cnt.textContent = String(old);
        alert('Could not post comment right now.');
      }
    }, {passive:false});
  });
})();
</script>
@endpush

@endsection
