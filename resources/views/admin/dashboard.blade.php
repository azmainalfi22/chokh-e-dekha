@extends('layouts.admin')

@section('page_title', 'Dashboard')
@section('page_subtitle', "Overview of reports and platform activity.")

@section('page_actions')
  <a href="{{ route('admin.reports.index') }}"
     class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-semibold
            bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm transition">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
    </svg>
    All Reports
  </a>
@endsection

@push('styles')
<style>
  .stat-card { transition: transform .15s ease, box-shadow .15s ease; }
  .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.10); }
  #adminMap { border-radius: 0 0 .75rem .75rem; }
  .leaflet-popup-content-wrapper { border-radius: .5rem !important; box-shadow: 0 4px 16px rgba(0,0,0,.15) !important; }
</style>
@endpush

@section('content')

{{-- ── Stat Cards ─────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">

  {{-- Total Reports --}}
  <div class="stat-card bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 flex items-center gap-4 shadow-sm">
    <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
      <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
      </svg>
    </div>
    <div>
      <div class="text-2xl font-bold text-slate-800 dark:text-slate-100 counter" data-target="{{ (int)($totalReports ?? 0) }}">0</div>
      <div class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide mt-0.5">Total Reports</div>
    </div>
  </div>

  {{-- Pending --}}
  <div class="stat-card bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 flex items-center gap-4 shadow-sm">
    <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center">
      <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
    </div>
    <div>
      <div class="text-2xl font-bold text-slate-800 dark:text-slate-100 counter" data-target="{{ (int)($pendingReports ?? 0) }}">0</div>
      <div class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide mt-0.5">Pending</div>
    </div>
  </div>

  {{-- Resolved --}}
  <div class="stat-card bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 flex items-center gap-4 shadow-sm">
    <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center">
      <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
    </div>
    <div>
      <div class="text-2xl font-bold text-slate-800 dark:text-slate-100 counter" data-target="{{ (int)($resolvedReports ?? 0) }}">0</div>
      <div class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide mt-0.5">Resolved</div>
    </div>
  </div>

  {{-- Users --}}
  <div class="stat-card bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 flex items-center gap-4 shadow-sm">
    <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-violet-50 dark:bg-violet-900/30 flex items-center justify-center">
      <svg class="w-6 h-6 text-violet-600 dark:text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
      </svg>
    </div>
    <div>
      <div class="text-2xl font-bold text-slate-800 dark:text-slate-100 counter" data-target="{{ (int)($totalUsers ?? 0) }}">0</div>
      <div class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide mt-0.5">Registered Users</div>
    </div>
  </div>

</div>

{{-- ── Live Reports Map ─────────────────────────────────────────────── --}}
<div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm mb-8 overflow-hidden">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-5 py-4 border-b border-slate-200 dark:border-slate-700">
    <div>
      <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Reports Map</h2>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Live map of all geo-tagged reports. Pan & zoom to explore.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <select id="mf_status"
              class="text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700
                     text-slate-700 dark:text-slate-200 px-3 py-1.5 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
        <option value="">All Statuses</option>
        <option value="pending">Pending</option>
        <option value="in_progress">In Progress</option>
        <option value="resolved">Resolved</option>
        <option value="rejected">Rejected</option>
      </select>
      <input id="mf_from" type="date"
             class="text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700
                    text-slate-700 dark:text-slate-200 px-3 py-1.5 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
      <input id="mf_to" type="date"
             class="text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700
                    text-slate-700 dark:text-slate-200 px-3 py-1.5 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
      <button id="mf_reset"
              class="text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700
                     text-slate-600 dark:text-slate-300 px-3 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-600 transition">
        Reset
      </button>
    </div>
  </div>

  {{-- Map legend --}}
  <div class="flex items-center gap-4 px-5 py-2 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Legend:</span>
    @foreach(['pending'=>['#eab308','Pending'],'in_progress'=>['#3b82f6','In Progress'],'resolved'=>['#16a34a','Resolved'],'rejected'=>['#ef4444','Rejected']] as $k=>$v)
    <span class="flex items-center gap-1 text-xs text-slate-600 dark:text-slate-300">
      <span class="w-2.5 h-2.5 rounded-full inline-block" style="background:{{ $v[0] }}"></span>
      {{ $v[1] }}
    </span>
    @endforeach
  </div>

  <div id="adminMap" class="h-[480px] w-full"></div>
</div>

{{-- ── Reports by City + Recent Reports (side by side on xl) ───────── --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">

  {{-- Reports by City (1/3 width on xl) --}}
  <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200 dark:border-slate-700">
      <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Reports by City</h2>
    </div>
    <div class="p-4 space-y-2">
      @forelse(($reportsByCity ?? []) as $item)
        @php $max = $reportsByCity->max('count'); $pct = $max > 0 ? ($item->count / $max * 100) : 0; @endphp
        <div class="flex items-center gap-3">
          <div class="w-28 shrink-0 text-xs font-medium text-slate-700 dark:text-slate-200 truncate">
            {{ $item->city_corporation ?: 'Unknown' }}
          </div>
          <div class="flex-1 bg-slate-100 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
            <div class="bg-emerald-500 h-full rounded-full transition-all" style="width:{{ $pct }}%"></div>
          </div>
          <div class="w-8 text-right text-xs font-bold text-slate-700 dark:text-slate-200">{{ $item->count }}</div>
        </div>
      @empty
        <p class="text-xs text-slate-500 dark:text-slate-400 py-4 text-center">No data yet.</p>
      @endforelse
    </div>
  </div>

  {{-- Recent Reports (2/3 width on xl) --}}
  <div class="xl:col-span-2 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200 dark:border-slate-700">
      <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Recent Reports</h2>
      <a href="{{ route('admin.reports.index') }}"
         class="text-xs font-medium text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300">
        View all →
      </a>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700">
            <th class="px-5 py-3 text-left font-medium">Title</th>
            <th class="px-5 py-3 text-left font-medium">Reported by</th>
            <th class="px-5 py-3 text-left font-medium">City</th>
            <th class="px-5 py-3 text-left font-medium">Status</th>
            <th class="px-5 py-3 text-left font-medium">Date</th>
            <th class="px-5 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
          @php
            $badge = fn($s) => match($s) {
              'resolved'    => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
              'in_progress' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
              'pending'     => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
              'rejected'    => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
              default       => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
            };
          @endphp
          @forelse(($recentReports ?? []) as $report)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors">
              <td class="px-5 py-3 max-w-[200px]">
                <a href="{{ route('admin.reports.show', $report) }}"
                   class="font-medium text-slate-800 dark:text-slate-100 hover:text-emerald-600 dark:hover:text-emerald-400 truncate block">
                  {{ $report->title ?? ('Report #'.$report->id) }}
                </a>
              </td>
              <td class="px-5 py-3 text-slate-600 dark:text-slate-300 whitespace-nowrap">
                {{ $report->user->name ?? 'N/A' }}
              </td>
              <td class="px-5 py-3 text-slate-600 dark:text-slate-300 whitespace-nowrap">
                {{ $report->city_corporation ?? '—' }}
              </td>
              <td class="px-5 py-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badge($report->status) }}">
                  {{ \Illuminate\Support\Str::headline((string)$report->status) }}
                </span>
              </td>
              <td class="px-5 py-3 text-slate-500 dark:text-slate-400 whitespace-nowrap text-xs">
                {{ optional($report->created_at)->format('M d, Y') }}
              </td>
              <td class="px-5 py-3 text-right">
                <a href="{{ route('admin.reports.show', $report) }}"
                   class="inline-flex items-center rounded-lg px-3 py-1 text-xs font-medium
                          bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200
                          hover:bg-emerald-50 hover:text-emerald-700 dark:hover:bg-emerald-900/30 dark:hover:text-emerald-300 transition-colors">
                  View
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-5 py-10 text-center text-slate-400 dark:text-slate-500 text-sm">
                No reports yet.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

  // ── Animated counters ────────────────────────────────────────────────
  document.querySelectorAll('.counter').forEach(el => {
    const target = Number(el.dataset.target || 0);
    if (target === 0) return;
    let cur = 0;
    const step = Math.max(1, Math.ceil(target / 60));
    const tick = () => {
      cur = Math.min(target, cur + step);
      el.textContent = cur.toLocaleString();
      if (cur < target) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
  });

  // ── Leaflet Map ──────────────────────────────────────────────────────
  const mapEl = document.getElementById('adminMap');
  if (!mapEl || typeof L === 'undefined') return;

  const map = L.map('adminMap', {
    center: [23.777176, 90.399452],
    zoom: 11,
    zoomControl: true,
  });

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 18,
  }).addTo(map);

  const statusColors = {
    pending:     '#eab308',
    in_progress: '#3b82f6',
    resolved:    '#16a34a',
    rejected:    '#ef4444',
  };

  const makeIcon = (status) => {
    const color = statusColors[status] || '#64748b';
    const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="32" viewBox="0 0 24 32">
      <path d="M12 0C5.37 0 0 5.37 0 12c0 8.27 12 20 12 20S24 20.27 24 12C24 5.37 18.63 0 12 0z"
            fill="${color}" stroke="#fff" stroke-width="1.5"/>
      <circle cx="12" cy="12" r="4" fill="#fff"/>
    </svg>`;
    return L.divIcon({
      html: svg,
      className: '',
      iconSize: [24, 32],
      iconAnchor: [12, 32],
      popupAnchor: [0, -34],
    });
  };

  const cluster = L.markerClusterGroup({ maxClusterRadius: 40 });
  map.addLayer(cluster);

  const filters = {
    status: document.getElementById('mf_status'),
    from:   document.getElementById('mf_from'),
    to:     document.getElementById('mf_to'),
    reset:  document.getElementById('mf_reset'),
  };

  const mapUrl = @json(route('admin.reports.map'));

  async function loadMarkers() {
    cluster.clearLayers();

    const bounds = map.getBounds();
    const qs = new URLSearchParams({
      nelat: bounds.getNorthEast().lat,
      nelng: bounds.getNorthEast().lng,
      swlat: bounds.getSouthWest().lat,
      swlng: bounds.getSouthWest().lng,
    });
    if (filters.status?.value) qs.set('status', filters.status.value);
    if (filters.from?.value)   qs.set('from', filters.from.value);
    if (filters.to?.value)     qs.set('to', filters.to.value);

    try {
      const res  = await fetch(mapUrl + '?' + qs, { headers: { Accept: 'application/json' } });
      if (!res.ok) return;
      const geo  = await res.json();

      (geo.features || []).forEach(f => {
        const [lng, lat] = f.geometry.coordinates;
        if (!lat || !lng) return;
        const props = f.properties;
        const marker = L.marker([lat, lng], { icon: makeIcon(props.status) });
        marker.bindPopup(`
          <div style="min-width:160px;font-family:inherit">
            <div style="font-weight:600;margin-bottom:4px">Report #${props.id}</div>
            <div style="font-size:12px;color:#555;text-transform:capitalize">${(props.status||'').replace('_',' ')}</div>
            <div style="font-size:11px;color:#888;margin-top:2px">${new Date(props.created_at).toLocaleDateString()}</div>
          </div>`);
        cluster.addLayer(marker);
      });
    } catch(e) { console.error('Map load error:', e); }
  }

  map.on('moveend', loadMarkers);
  loadMarkers();

  [filters.status, filters.from, filters.to].forEach(el => el?.addEventListener('change', loadMarkers));
  filters.reset?.addEventListener('click', () => {
    if (filters.status) filters.status.value = '';
    if (filters.from)   filters.from.value   = '';
    if (filters.to)     filters.to.value     = '';
    loadMarkers();
  });

});
</script>
@endpush
