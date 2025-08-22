@extends('layouts.admin')
@section('title', 'Dashboard')

{{-- Header slots (supported by upgraded admin layout) --}}
@section('page_title', 'Admin Dashboard')
@section('page_subtitle', "Here’s what’s happening at a glance.")
@section('page_actions')
  <a href="{{ route('admin.users.index') }}"
     class="inline-flex items-center gap-2 rounded-xl px-3 py-2 font-medium
            bg-white ring-1 ring-amber-900/10 text-amber-900/90 shadow hover:shadow-md transition">
    Manage Users
  </a>
  <a href="{{ route('admin.reports.index') }}"
     class="inline-flex items-center gap-2 rounded-xl px-3 py-2 font-semibold text-white
            bg-gradient-to-r from-amber-600 to-rose-600 shadow hover:shadow-lg transition">
    View Reports
  </a>
@endsection

@push('styles')
<style>
  /* keep filters readable in dark mode (do NOT invert) */
  .dashboard-filter {
    background:#ffffff !important;
    color:#7c2d12 !important;              /* amber-900-ish */
    border:1px solid rgba(120,53,15,.18) !important;
  }
  .dashboard-filter::placeholder { color: rgba(120,53,15,.55); }
  .dark .dashboard-filter {
    background:#ffffff !important;         /* force white in dark */
    color:#7c2d12 !important;
    border-color: rgba(120,53,15,.18) !important;
  }
  .dark .dashboard-filter::placeholder { color: rgba(120,53,15,.55); }

  /* stat cards motion */
  .stat-card{
    box-shadow: 0 18px 40px rgba(15,23,42,.18), 0 6px 16px rgba(15,23,42,.12);
    transform: translateZ(0);
    transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
  }
  .stat-card:hover{
    transform: translateY(-2px) scale(1.0125);
    box-shadow: 0 22px 48px rgba(15,23,42,.22), 0 8px 18px rgba(15,23,42,.16);
  }

  /* glass sections */
  .glass {
    background: rgba(255,255,255,.85);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    box-shadow: 0 12px 30px rgba(15,23,42,.12);
    border: 1px solid rgba(120,53,15,.10);
    border-radius: 1rem;
  }
  .dark .glass{
    background: rgba(255,255,255,.08);
    border-color: rgba(255,255,255,.10);
    box-shadow: 0 12px 30px rgba(0,0,0,.35);
  }

  /* table head contrast in dark */
  .dark .tbl-head {
    background: rgba(255,255,255,.10);
    color: #f8fafc;
  }
</style>
@endpush

@section('content')
<div class="relative">
  {{-- background accents --}}
  <div class="pointer-events-none absolute -top-20 -right-24 h-80 w-80 rounded-full blur-3xl opacity-20 bg-gradient-to-br from-amber-300 to-rose-300"></div>
  <div class="pointer-events-none absolute -bottom-24 -left-24 h-96 w-96 rounded-full blur-3xl opacity-20 bg-gradient-to-tr from-orange-300 to-pink-300"></div>

  {{-- Stats --}}
  <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
    @php
      $cards = [
        ['label'=>'Total Reports','value'=>(int)($totalReports ?? 0),'from'=>'from-amber-500','to'=>'to-rose-600'],
        ['label'=>'Pending','value'=>(int)($pendingReports ?? 0),'from'=>'from-yellow-400','to'=>'to-orange-500'],
        ['label'=>'Resolved','value'=>(int)($resolvedReports ?? 0),'from'=>'from-emerald-500','to'=>'to-green-700'],
        ['label'=>'Users','value'=>(int)($totalUsers ?? 0),'from'=>'from-indigo-600','to'=>'to-violet-700'],
      ];
    @endphp
    @foreach($cards as $c)
      <div class="stat-card text-white p-6 rounded-2xl text-center bg-gradient-to-br {{ $c['from'] }} {{ $c['to'] }}">
        <div class="text-4xl md:text-5xl font-extrabold counter" data-target="{{ $c['value'] }}">0</div>
        <div class="text-xs mt-2 tracking-wide uppercase/90 opacity-90">{{ $c['label'] }}</div>
      </div>
    @endforeach
  </section>

  {{-- Live Map --}}
  <section class="glass overflow-hidden mb-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-amber-900/10 dark:border-white/10">
      <div class="min-w-0">
        <h2 class="text-lg font-semibold text-amber-900 dark:text-amber-100">Reports Map (Live)</h2>
        <p class="text-xs text-amber-900/60 dark:text-amber-100/60">Drag/zoom to load points in view. Toggle heatmap for density.</p>
      </div>

      {{-- Filters (forced light UI even in dark) --}}
      <form id="mapFilters" class="flex flex-wrap items-center gap-2">
        <select name="status" id="mf_status" class="dashboard-filter rounded-xl px-3 py-2 text-sm">
          <option value="">All statuses</option>
          @foreach(['pending','in_progress','resolved','rejected'] as $s)
            <option value="{{ $s }}">{{ \Illuminate\Support\Str::headline($s) }}</option>
          @endforeach
        </select>

        <select name="category" id="mf_category" class="dashboard-filter rounded-xl px-3 py-2 text-sm">
          <option value="">All categories</option>
          @foreach(($categoriesAll ?? []) as $cat)
            <option value="{{ $cat }}">{{ $cat }}</option>
          @endforeach
        </select>

        <input type="date" name="from" id="mf_from" class="dashboard-filter rounded-xl px-3 py-2 text-sm" placeholder="From">
        <input type="date" name="to"   id="mf_to"   class="dashboard-filter rounded-xl px-3 py-2 text-sm" placeholder="To">

        <label class="inline-flex items-center gap-2 px-3 py-2 rounded-xl dashboard-filter text-sm">
          <input id="mf_heat" type="checkbox" class="rounded"> Heatmap
        </label>
        <button type="button" id="mf_reset" class="rounded-xl px-3 py-2 text-sm dashboard-filter">
          Reset
        </button>
      </form>
    </div>
    <div id="adminMap" class="h-[520px] w-full"></div>
  </section>

  {{-- Reports by City --}}
  <section class="glass p-6 mb-8">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold text-amber-900 dark:text-amber-100">Reports by City</h2>
      <a href="{{ route('admin.reports.index') }}" class="text-sm text-rose-700 hover:underline dark:text-rose-300">Filter & View</a>
    </div>
    @if(!empty($reportsByCity ?? []))
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($reportsByCity as $item)
          <div class="rounded-xl border border-amber-100 bg-amber-50/70 p-4 text-amber-900 shadow-sm hover:shadow transition dark:bg-white/10 dark:border-white/10 dark:text-amber-100">
            <div class="text-sm font-semibold truncate">{{ $item->city_corporation }}</div>
            <div class="text-3xl font-extrabold mt-1">{{ number_format($item->count) }}</div>
          </div>
        @endforeach
      </div>
    @else
      <p class="text-sm text-amber-900/70 dark:text-amber-100/70">No data yet.</p>
    @endif
  </section>

  {{-- Recent Reports --}}
  <section class="glass p-6">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold text-amber-900 dark:text-amber-100">Recent Reports</h2>
      <a href="{{ route('admin.reports.index') }}" class="text-sm text-rose-700 hover:underline dark:text-rose-300">View all</a>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="tbl-head uppercase text-xs tracking-wider bg-amber-100/80 text-amber-900 dark:bg-white/10 dark:text-amber-50">
            <th class="px-4 py-2 text-left">Title</th>
            <th class="px-4 py-2 text-left">User</th>
            <th class="px-4 py-2 text-left">City</th>
            <th class="px-4 py-2 text-left">Status</th>
            <th class="px-4 py-2 text-left">Date</th>
            <th class="px-4 py-2"></th>
          </tr>
        </thead>
        <tbody class="text-amber-900/90 dark:text-amber-100/90">
          @php
            $badge = fn($s) => match($s) {
              'resolved'    => 'bg-green-200 text-green-800 dark:bg-green-500/20 dark:text-green-200',
              'in_progress' => 'bg-blue-200 text-blue-800 dark:bg-blue-500/20 dark:text-blue-200',
              'pending'     => 'bg-yellow-200 text-yellow-800 dark:bg-yellow-500/20 dark:text-yellow-100',
              'rejected'    => 'bg-rose-200 text-rose-800 dark:bg-rose-500/20 dark:text-rose-200',
              default       => 'bg-gray-200 text-gray-800 dark:bg-white/10 dark:text-gray-200',
            };
          @endphp
          @forelse(($recentReports ?? []) as $report)
            <tr class="border-b last:border-0 border-amber-900/10 dark:border-white/10 hover:bg-amber-50/60 dark:hover:bg-white/5 transition">
              <td class="px-4 py-2 font-medium max-w-[320px] truncate">
                <a href="{{ route('admin.reports.show', $report) }}" class="text-rose-700 hover:underline dark:text-rose-300">
                  {{ $report->title ?? ('Report #'.$report->id) }}
                </a>
              </td>
              <td class="px-4 py-2">{{ $report->user->name ?? 'N/A' }}</td>
              <td class="px-4 py-2">{{ $report->city_corporation ?? '—' }}</td>
              <td class="px-4 py-2">
                <span class="px-2 py-1 text-xs rounded-full font-semibold {{ $badge($report->status) }}">
                  {{ \Illuminate\Support\Str::headline((string)$report->status) }}
                </span>
              </td>
              <td class="px-4 py-2 whitespace-nowrap">{{ optional($report->created_at)->format('M d, Y h:i a') }}</td>
              <td class="px-4 py-2 text-right">
                <a href="{{ route('admin.reports.show', $report) }}"
                   class="inline-flex items-center rounded-xl px-3 py-1.5 text-xs font-medium
                          bg-white ring-1 ring-amber-900/10 text-amber-900/90 shadow hover:shadow-md dark:bg-white/10 dark:ring-white/10 dark:text-amber-50">
                  View
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-4 py-6 text-center text-amber-900/70 dark:text-amber-100/70">No recent reports.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>

  <footer class="text-center text-sm text-amber-900/60 dark:text-amber-100/60 mt-10">
    © {{ now()->year }} {{ config('app.name', 'Chokh-e-Dekha') }} Admin. All rights reserved.
  </footer>
</div>
@endsection

@push('scripts')
{{-- Google Maps + Visualization (heatmap) --}}
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=visualization&loading=async" defer></script>
<script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js" defer></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // counters
  document.querySelectorAll('.counter').forEach(el => {
    const target = Number(el.dataset.target || 0); let cur = 0;
    const step = Math.max(1, Math.ceil(target / 80));
    const tick = () => { cur = Math.min(target, cur + step); el.textContent = cur.toLocaleString(); if (cur < target) requestAnimationFrame(tick); };
    requestAnimationFrame(tick);
  });

  // wait for maps libs then init
  const t = setInterval(() => {
    if (window.google && window.markerClusterer) { clearInterval(t); initAdminMap(); }
  }, 60);
});

function initAdminMap(){
  const el = document.getElementById('adminMap'); if (!el) return;

  const map = new google.maps.Map(el, {
    center: { lat: 23.777176, lng: 90.399452 }, zoom: 11,
    streetViewControl:false, mapTypeControl:false, clickableIcons:false,
    styles:[{ featureType:"poi", stylers:[{visibility:"off"}] }]
  });

  const filters = {
    status  : document.getElementById('mf_status'),
    category: document.getElementById('mf_category'),
    from    : document.getElementById('mf_from'),
    to      : document.getElementById('mf_to'),
    heat    : document.getElementById('mf_heat'),
    reset   : document.getElementById('mf_reset'),
  };

  let clusterer = null, heatmap = null;

  const markerIcon = (status) => {
    const color = status === 'resolved' ? '#16a34a'
                : status === 'in_progress' ? '#3b82f6'
                : status === 'pending' ? '#eab308'
                : '#ef4444';
    const svg = `data:image/svg+xml;utf8,${encodeURIComponent(`
      <svg width="28" height="28" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
        <path d="M32 4c-10.5 0-19 8.5-19 19 0 13.2 19 37 19 37s19-23.8 19-37c0-10.5-8.5-19-19-19z" fill="${color}"/>
        <circle cx="32" cy="23" r="6.5" fill="#fff"/>
      </svg>`)}`
    return { url: svg, scaledSize: new google.maps.Size(28,28), anchor: new google.maps.Point(14,28) };
  };

  async function fetchPoints(){
    const b = map.getBounds(); if (!b) return { features: [] };
    const qs = new URLSearchParams({
      nelat: b.getNorthEast().lat(), nelng: b.getNorthEast().lng(),
      swlat: b.getSouthWest().lat(), swlng: b.getSouthWest().lng(),
    });
    if (filters.status.value)   qs.set('status', filters.status.value);
    if (filters.category.value) qs.set('category', filters.category.value); // using name; switch to category_id if needed
    if (filters.from.value)     qs.set('from', filters.from.value);
    if (filters.to.value)       qs.set('to',   filters.to.value);

    const url = @json(route('admin.reports.map')) + '?' + qs.toString();
    const r = await fetch(url, { headers:{ 'Accept':'application/json' }});
    return r.ok ? r.json() : { features: [] };
  }

  async function render(){
    const geo = await fetchPoints();

    const markers = (geo.features || []).map(f => {
      const [lng, lat] = f.geometry.coordinates;
      const m = new google.maps.Marker({ position:{lat, lng}, icon: markerIcon(f.properties.status) });
      const info = new google.maps.InfoWindow({
        content: `<div class="min-w-[180px]">
                    <div class="font-semibold">Report #${f.properties.id}</div>
                    <div class="text-xs text-neutral-600">${f.properties.status || ''}</div>
                    <div class="text-xs">${new Date(f.properties.created_at).toLocaleString()}</div>
                  </div>`
      });
      m.addListener('click', () => info.open({ anchor:m, map }));
      return m;
    });

    if (clusterer) clusterer.clearMarkers();
    clusterer = new markerClusterer.MarkerClusterer({ map, markers });

    const heatPts = (geo.features || []).map(f => new google.maps.LatLng(f.geometry.coordinates[1], f.geometry.coordinates[0]));
    if (!heatmap) heatmap = new google.maps.visualization.HeatmapLayer({ data: heatPts, dissipating:true, radius:26 });
    else heatmap.setData(heatPts);
    heatmap.setMap(filters.heat.checked ? map : null);
  }

  map.addListener('idle', render);
  [filters.status, filters.category, filters.from, filters.to].forEach(el => el?.addEventListener('change', render));
  filters.heat?.addEventListener('change', () => heatmap?.setMap(filters.heat.checked ? map : null));
  filters.reset?.addEventListener('click', () => {
    filters.status.value = ''; filters.category.value = ''; filters.from.value = ''; filters.to.value = ''; filters.heat.checked = false; render();
  });
}
</script>
@endpush
