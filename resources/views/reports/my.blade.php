@extends(auth()->user()->is_admin ? 'layouts.admin' : 'layouts.app')
@section('title', 'My Reports')

@section('content')
@php
  $q        = request('q','');
  $city     = request('city_corporation');
  $category = request('category');
  $status   = request('status');
  $statuses = ['pending'=>'Pending','in_progress'=>'In Progress','resolved'=>'Resolved','rejected'=>'Rejected'];
  $cities     = $cities     ?? [];
  $categories = $categories ?? [];

  $badge = fn($s) => match($s) {
    'resolved'    => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
    'in_progress' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
    'pending'     => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
    'rejected'    => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
    default       => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
  };

  $mapReports = collect($reports->items() ?? $reports)->map(fn($r) => [
    'id'     => $r->id,
    'title'  => $r->title,
    'status' => $r->status,
    'lat'    => $r->latitude  ? (float)$r->latitude  : null,
    'lng'    => $r->longitude ? (float)$r->longitude : null,
    'url'    => route('reports.show', $r),
  ])->filter(fn($x) => $x['lat'] && $x['lng'])->values();
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

  {{-- ── Header ──────────────────────────────────────────────────────── --}}
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
      <h1 class="text-xl font-bold text-slate-900 dark:text-white">My Reports</h1>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Your submitted issues, all in one place.</p>
    </div>
    <div class="flex items-center gap-2">
      @if($mapReports->count() > 0)
        <button id="toggleMap"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-sm font-medium border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          Map View
        </button>
      @endif
      <a href="{{ route('reports.create') }}"
         class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        New Report
      </a>
    </div>
  </div>

  {{-- ── Map Panel (collapsed by default) ───────────────────────────── --}}
  <div id="mapPanel" class="hidden mb-5">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
      <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Report Locations</h2>
        <span class="text-xs text-slate-400">{{ $mapReports->count() }} pinned</span>
      </div>
      <div id="myReportsMap" class="w-full" style="height:360px;"></div>
    </div>
  </div>

  {{-- ── Filters ────────────────────────────────────────────────────── --}}
  <form action="{{ route('reports.my') }}" method="GET"
        class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4 mb-5">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
      <input type="text" name="q" value="{{ $q }}" placeholder="Search reports…"
             class="lg:col-span-2 px-3 py-2 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
      <select name="city_corporation"
              class="px-3 py-2 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
        <option value="">All Cities</option>
        @foreach($cities as $c)
          <option value="{{ $c }}" @selected($city===$c)>{{ $c }}</option>
        @endforeach
      </select>
      <select name="status"
              class="px-3 py-2 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
        <option value="">Any Status</option>
        @foreach($statuses as $k => $label)
          <option value="{{ $k }}" @selected($status===$k)>{{ $label }}</option>
        @endforeach
      </select>
      <div class="flex gap-2">
        <button type="submit"
                class="flex-1 px-4 py-2 text-sm font-semibold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition-colors">
          Apply
        </button>
        @if($q || $city || $category || $status)
          <a href="{{ route('reports.my') }}"
             class="flex-1 px-4 py-2 text-sm font-semibold text-center rounded-lg border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            Clear
          </a>
        @endif
      </div>
    </div>
  </form>

  {{-- ── Report Cards ────────────────────────────────────────────────── --}}
  @if($reports->isEmpty())
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-dashed border-slate-300 dark:border-slate-600 p-16 text-center">
      <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center mx-auto mb-4">
        <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
      </div>
      <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">No reports yet</h3>
      <p class="text-xs text-slate-400 mb-5">Create your first one to help improve your city.</p>
      <a href="{{ route('reports.create') }}"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold bg-emerald-600 text-white hover:bg-emerald-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Submit First Report
      </a>
    </div>
  @else
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
      @foreach($reports as $report)
        <article class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm hover:border-slate-300 dark:hover:border-slate-600 hover:shadow-md transition-all overflow-hidden">

          @if($report->photo)
            <img src="{{ Storage::url($report->photo) }}" alt="{{ $report->title }}"
                 class="w-full h-36 object-cover">
          @endif

          <div class="p-5">
            <div class="flex items-start justify-between gap-3 mb-3">
              <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 leading-snug line-clamp-2">{{ $report->title }}</h3>
              <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium flex-shrink-0 {{ $badge($report->status) }}">
                {{ \Illuminate\Support\Str::headline($report->status) }}
              </span>
            </div>

            <dl class="space-y-1.5 text-xs text-slate-500 dark:text-slate-400 mb-4">
              @if($report->location)
                <dd class="flex items-center gap-1.5">
                  <svg class="w-3.5 h-3.5 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                  </svg>
                  {{ $report->location }}
                </dd>
              @endif
              @if($report->category)
                <dd class="flex items-center gap-1.5">
                  <svg class="w-3.5 h-3.5 text-violet-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                  </svg>
                  {{ ucfirst($report->category) }}
                </dd>
              @endif
              @if($report->city_corporation)
                <dd class="flex items-center gap-1.5">
                  <svg class="w-3.5 h-3.5 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                  </svg>
                  {{ $report->city_corporation }}
                </dd>
              @endif
              <dd class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ $report->created_at->format('M d, Y') }}
              </dd>
            </dl>

            <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-700">
              <div class="flex items-center gap-3 text-xs text-slate-400">
                <span>{{ $report->likes_count ?? 0 }} likes</span>
                <span>{{ $report->comments_count ?? 0 }} comments</span>
              </div>
              <a href="{{ route('reports.show', $report) }}"
                 class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 transition-colors">
                View →
              </a>
            </div>
          </div>
        </article>
      @endforeach
    </div>

    @if(method_exists($reports, 'links'))
      <div class="mt-6">
        {{ $reports->appends(request()->query())->links() }}
      </div>
    @endif
  @endif
</div>

@push('scripts')
<script>
(function(){
  const mapPanel = document.getElementById('mapPanel');
  const toggle   = document.getElementById('toggleMap');
  const reports  = @json($mapReports);
  let initialized = false;

  function colorFor(status) {
    return { resolved:'#10b981', in_progress:'#6366f1', rejected:'#ef4444' }[status] ?? '#f59e0b';
  }

  function initMap() {
    const map = L.map('myReportsMap').setView([23.777176, 90.399452], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap',
      maxZoom: 19
    }).addTo(map);

    const bounds = [];
    reports.forEach(r => {
      const circle = L.circleMarker([r.lat, r.lng], {
        radius: 10,
        fillColor: colorFor(r.status),
        color: '#fff',
        weight: 2,
        opacity: 1,
        fillOpacity: 0.85
      }).addTo(map);
      circle.bindPopup(`<div style="min-width:180px"><p style="font-weight:700;margin-bottom:4px;font-size:13px">${r.title}</p><a href="${r.url}" style="color:#059669;font-size:12px">View details →</a></div>`);
      bounds.push([r.lat, r.lng]);
    });
    if (bounds.length) map.fitBounds(bounds, { padding: [20, 20] });
  }

  toggle?.addEventListener('click', () => {
    mapPanel.classList.toggle('hidden');
    if (!initialized && !mapPanel.classList.contains('hidden')) {
      initialized = true;
      setTimeout(initMap, 50);
    }
  });
})();
</script>
@endpush
@endsection
