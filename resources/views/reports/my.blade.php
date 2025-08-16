@extends(auth()->user()->is_admin ? 'layouts.admin' : 'layouts.app')

@section('title', 'My Reports')

@push('styles')
<style>
  :root{
    --card-light: rgba(255,255,255,.88);
    --card-dark:  rgba(22,24,28,.92);
    --text-body:  #0f172a;
  }
  .cd-card{
    background: var(--card-light);
    color: var(--text-body);
    backdrop-filter: blur(10px);
    transition: transform .15s ease, box-shadow .15s ease, background-color .2s ease;
  }
  .cd-card:hover{ transform: translateY(-1px); box-shadow: 0 10px 24px rgba(0,0,0,.08) }
  @media (prefers-color-scheme: dark){ .dark .cd-card{ background: var(--card-dark) } }

  .chip-btn{ @apply text-xs px-2 py-1 rounded-lg ring-1 ring-amber-900/10 bg-white text-gray-700 hover:bg-amber-50; }
  .dark .chip-btn{ background:#1b1f24 !important; color:#f5f5f5 !important; }

  /* filter pills */
  .pill{ @apply inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm ring-1 ring-amber-900/10 bg-white text-amber-900/90; }
  .dark .pill{ background:#1b1f24; color:#f5f5f5; }

  /* Map container */
  #myReportsMap{ height: 420px; border-radius: 1rem; overflow: hidden; }
</style>
@endpush

@section('content')
<div class="relative">
  {{-- ambient blobs --}}
  <div class="pointer-events-none absolute -top-20 -right-24 h-80 w-80 rounded-full blur-3xl opacity-20 bg-gradient-to-br from-amber-300 to-rose-300"></div>
  <div class="pointer-events-none absolute -bottom-24 -left-24 h-96 w-96 rounded-full blur-3xl opacity-20 bg-gradient-to-tr from-orange-300 to-pink-300"></div>

  <div class="max-w-7xl mx-auto p-4 md:p-8 relative z-[1]">

    {{-- header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div class="min-w-0">
        <h1 class="text-3xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-amber-700 via-orange-700 to-rose-700">
          My Reports
        </h1>
        <p class="text-sm text-amber-600">Your submitted issues, all in one place.</p>
      </div>

      <div class="flex items-center gap-2">
        <button id="toggleMap"
                class="pill">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8 2 5 5 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-4-3-7-7-7zm0 9.5a2.5 2.5 0 110-5 2.5 2.5 0 010 5z"/></svg>
          <span>Map view</span>
        </button>
        @if(Route::has('report.create'))
          <a href="{{ route('report.create') }}"
             class="inline-flex items-center gap-2 px-4 py-2 rounded-xl shadow hover:shadow-md bg-amber-600 text-white hover:bg-amber-700 transition">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M11 5h2v14h-2zM5 11h14v2H5z"/></svg>
            New Report
          </a>
        @endif
      </div>
    </div>

    {{-- FILTER BAR (GET) --}}
    @php
      $q        = request('q','');
      $city     = request('city_corporation');
      $category = request('category');
      $status   = request('status');
      $statuses = ['pending'=>'Pending','in_progress'=>'In Progress','resolved'=>'Resolved','rejected'=>'Rejected'];

      // Fallback lists if controller didn't pass $cities/$categories
      $cities     = $cities     ?? ['Dhaka North','Dhaka South','Chattogram','Gazipur','Khulna','Rajshahi','Sylhet','Barishal','Cumilla','Narayanganj','Mymensingh'];
      $categories = $categories ?? ['Road Damage','Broken Road','Street Light','Electricity','Water Supply','Drainage','Waste Management','Garbage','Sewage','Public Safety','Traffic','Parks','Health','Education','Other'];
    @endphp

    <form action="{{ route('reports.my') }}" method="GET" class="cd-card rounded-2xl ring-1 ring-amber-100 shadow p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div class="md:col-span-2">
          <label class="sr-only" for="q">Search</label>
          <input id="q" name="q" value="{{ $q }}" type="search" placeholder="Search title, description, address…"
                 class="w-full rounded-xl border border-amber-200/60 bg-white px-3 py-2 placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-amber-400">
        </div>
        <div>
          <select name="city_corporation" class="w-full rounded-xl border border-amber-200/60 bg-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400">
            <option value="">All cities</option>
            @foreach($cities as $c)
              <option value="{{ $c }}" @selected($city===$c)>{{ $c }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <select name="category" class="w-full rounded-xl border border-amber-200/60 bg-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400">
            <option value="">All categories</option>
            @foreach($categories as $c)
              <option value="{{ $c }}" @selected($category===$c)>{{ $c }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <select name="status" class="w-full rounded-xl border border-amber-200/60 bg-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400">
            <option value="">Any status</option>
            @foreach($statuses as $k=>$label)
              <option value="{{ $k }}" @selected($status===$k)>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="md:col-span-2 flex items-center gap-2">
          <button class="px-4 py-2 rounded-xl bg-amber-600 text-white hover:bg-amber-700">Apply</button>
          @if($q || $city || $category || $status)
            <a href="{{ route('reports.my') }}" class="px-4 py-2 rounded-xl ring-1 ring-amber-900/10 bg-white text-amber-900/90">Reset</a>
          @endif
        </div>
      </div>
    </form>

    {{-- MAP PANEL (toggle) --}}
    <div id="mapPanel" class="mb-6 hidden">
      <div class="cd-card rounded-2xl ring-1 ring-amber-100 shadow p-4">
        <div class="flex items-center justify-between mb-3">
          <h2 class="text-lg font-semibold">Map</h2>
          <div class="text-xs text-amber-900/70">Pins show the reports from this page.</div>
        </div>
        <div id="myReportsMap" class="ring-1 ring-amber-100"></div>
      </div>
    </div>

    {{-- STATUS BADGE helper --}}
    @php
      $badge = function($report) {
        $status = $report->status ?? 'pending';
        $map = [
          'pending'     => 'bg-amber-100 text-amber-800 ring-amber-200',
          'in_progress' => 'bg-blue-100 text-blue-800 ring-blue-200',
          'resolved'    => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
          'rejected'    => 'bg-rose-100 text-rose-800 ring-rose-200',
        ];
        $cls = $map[$status] ?? 'bg-gray-100 text-gray-800 ring-gray-200';
        return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ring-1 ring-inset '.$cls.'">'.\Illuminate\Support\Str::headline($status).'</span>';
      };

      // Data for map (current page only)
      $mapReports = collect($reports->items() ?? $reports)->map(function($r){
        return [
          'id'     => $r->id,
          'title'  => $r->title,
          'status' => $r->status,
          'lat'    => $r->latitude ? (float)$r->latitude : null,
          'lng'    => $r->longitude? (float)$r->longitude: null,
          'url'    => route('reports.show', $r),
        ];
      })->filter(fn($x)=>$x['lat'] && $x['lng'])->values();
    @endphp

    {{-- CARDS --}}
    @if($reports->isEmpty())
      <div class="rounded-2xl border border-dashed border-amber-300 cd-card px-6 py-12 text-center shadow">
        <div class="mx-auto mb-3 inline-flex h-12 w-12 items-center justify-center rounded-full ring-1 ring-amber-200 bg-amber-50">
          <svg class="h-5 w-5 text-amber-700" viewBox="0 0 24 24" fill="currentColor"><path d="M3 5h18v2H3zM3 10h18v2H3zM3 15h12v2H3z"/></svg>
        </div>
        <h3 class="text-lg font-semibold">You haven’t submitted any reports</h3>
        <p class="text-sm text-amber-900/70 mt-1">Create your first one to help improve the city.</p>
      </div>
    @else
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($reports as $report)
          <article class="cd-card rounded-2xl shadow hover:shadow-xl transition overflow-hidden ring-1 ring-amber-100">
            <div class="p-5 flex flex-col gap-3">
              <div class="flex items-start justify-between gap-3">
                <h3 class="text-lg font-bold leading-snug line-clamp-2">{{ $report->title }}</h3>
                {!! $badge($report) !!}
              </div>

              <ul class="text-sm space-y-1">
                <li class="flex items-center gap-2">
                  <svg class="h-4 w-4 text-amber-800/80" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8 2 5 5 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-4-3-7-7-7zm0 9.5a2.5 2.5 0 110-5 2.5 2.5 0 010 5z"/></svg>
                  <span class="font-medium">{{ $report->location ?? 'N/A' }}</span>
                </li>
                <li class="flex items-center gap-2">
                  <svg class="h-4 w-4 text-amber-800/80" viewBox="0 0 24 24" fill="currentColor"><path d="M4 6h16v2H4zM4 10h16v8H4z"/></svg>
                  <span>{{ $report->category ?? 'General' }}</span>
                </li>
                <li class="flex items-center gap-2">
                  <svg class="h-4 w-4 text-amber-800/80" viewBox="0 0 24 24" fill="currentColor"><path d="M5 4h14v2H5zM5 8h14v12H5z"/></svg>
                  <span>{{ $report->city_corporation ?? '—' }}</span>
                </li>
                <li class="flex items-center gap-2">
                  <svg class="h-4 w-4 text-amber-800/80" viewBox="0 0 24 24" fill="currentColor"><path d="M7 2h10v2H7zM5 6h14v14H5zM9 8h6v6H9z"/></svg>
                  <span>{{ optional($report->created_at)->format('M d, Y h:i a') }}</span>
                </li>
                @if(!empty($report->formatted_address))
                  <li class="pt-1">
                    <div class="text-xs text-amber-900/70">Address</div>
                    <div class="mt-0.5 flex flex-wrap items-center gap-2">
                      <span class="text-sm font-medium truncate max-w-[16rem]" title="{{ $report->formatted_address }}">
                        {{ $report->formatted_address }}
                      </span>
                      <button type="button" class="chip-btn"
                              onclick="navigator.clipboard?.writeText(`{{ $report->formatted_address }}`)">Copy</button>
                      @php
                        $link = !empty($report->latitude) && !empty($report->longitude)
                            ? 'https://www.google.com/maps/search/?api=1&query='.urlencode($report->latitude.','.$report->longitude)
                            : (!empty($report->formatted_address)
                                ? 'https://www.google.com/maps/search/?api=1&query='.urlencode($report->formatted_address)
                                : null);
                      @endphp
                      @if($link)
                        <a href="{{ $link }}" target="_blank" rel="noopener" class="chip-btn">Open in Maps</a>
                      @endif
                    </div>
                  </li>
                @endif
              </ul>

              <div class="mt-3 flex items-center justify-between">
                <a href="{{ route('reports.show', $report) }}"
                   class="inline-flex items-center gap-1 text-amber-700 hover:text-amber-800 font-medium">
                  View details
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M10 6l6 6-6 6-1.4-1.4L12.2 12 8.6 7.4z"/></svg>
                </a>
              </div>
            </div>
          </article>
        @endforeach
      </div>

      {{-- pagination --}}
      @if(method_exists($reports,'links'))
        <div class="mt-6">
          {{ $reports->links() }}
        </div>
      @endif
    @endif
  </div>
</div>

{{-- Google Maps (needs your key in services.google_maps.key) --}}
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&loading=async" defer></script>

@push('scripts')
<script>
(function(){
  const mapPanel = document.getElementById('mapPanel');
  const toggle   = document.getElementById('toggleMap');
  let map, info, markers = [];

  const reports = @json($mapReports);

  const stylesLight = [
    { elementType: "geometry", stylers: [{ saturation: -5 }, { lightness: 5 }] },
    { featureType: "poi", stylers: [{ visibility: "off" }] },
    { featureType: "road", elementType: "geometry", stylers: [{ lightness: 20 }] },
    { featureType: "water", stylers: [{ saturation: -10 }] },
  ];
  const stylesDark = [
    { elementType: "geometry", stylers: [{ color: "#1f1f1f" }] },
    { elementType: "labels.text.stroke", stylers: [{ color: "#1f1f1f" }] },
    { elementType: "labels.text.fill", stylers: [{ color: "#bdbdbd" }] },
    { featureType: "road", elementType: "geometry", stylers: [{ color: "#2f2f2f" }] },
    { featureType: "poi", stylers: [{ visibility: "off" }] },
    { featureType: "water", stylers: [{ color: "#0f1115" }] },
  ];
  const isDark = () => window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

  function pin(fill){
    return 'data:image/svg+xml;utf8,'+encodeURIComponent(`
      <svg width="40" height="40" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
        <path d="M32 4c-10.5 0-19 8.5-19 19 0 13.2 19 37 19 37s19-23.8 19-37c0-10.5-8.5-19-19-19z" fill="${fill}"/>
        <circle cx="32" cy="23" r="6.5" fill="#fff"/>
      </svg>`);
  }
  function colorFor(status){
    switch(status){
      case 'resolved': return '#10b981';
      case 'in_progress': return '#3b82f6';
      case 'rejected': return '#ef4444';
      default: return '#f59e0b';
    }
  }

  function initMap(){
    const el = document.getElementById('myReportsMap');
    map = new google.maps.Map(el, {
      center: {lat: 23.777176, lng: 90.399452},
      zoom: 12,
      clickableIcons: false,
      mapTypeControl: false,
      streetViewControl: false,
      fullscreenControl: false,
      styles: isDark()? stylesDark : stylesLight,
    });
    info = new google.maps.InfoWindow();

    const bounds = new google.maps.LatLngBounds();
    markers = reports.map(r => {
      const m = new google.maps.Marker({
        position: {lat: r.lat, lng: r.lng},
        map,
        icon: { url: pin(colorFor(r.status)), scaledSize: new google.maps.Size(40,40), anchor: new google.maps.Point(20,40) },
        title: r.title
      });
      m.addListener('click', () => {
        info.setContent(`<div style="min-width:200px">
          <div style="font-weight:700;margin-bottom:4px">${escapeHtml(r.title)}</div>
          <a href="${r.url}">Open details →</a>
        </div>`);
        info.open(map, m);
      });
      bounds.extend(m.getPosition());
      return m;
    });
    if (reports.length){ map.fitBounds(bounds); }

    try {
      window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
        map.setOptions({ styles: e.matches ? stylesDark : stylesLight });
      });
    } catch {}
  }

  function escapeHtml(s){ return (s||'').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m])) }

  // Toggle panel + lazy init map
  let initialized = false;
  toggle?.addEventListener('click', () => {
    mapPanel.classList.toggle('hidden');
    if (!initialized && !mapPanel.classList.contains('hidden')) {
      initialized = true;
      const t = setInterval(() => {
        if (window.google && google.maps) { clearInterval(t); initMap(); }
      }, 50);
    }
  });
})();
</script>
@endpush
@endsection
