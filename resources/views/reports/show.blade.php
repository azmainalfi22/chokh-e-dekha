@extends(auth()->user()->is_admin ? 'layouts.admin' : 'layouts.app')

@section('title', 'Report Details')

@push('styles')
<style>
  /* keep cards readable in both modes */
  .cd-card { background: rgba(255,255,255,.88); backdrop-filter: blur(8px); }
  .dark .cd-card { background: rgba(20,22,26,.9); }

  .cd-chip{
    box-shadow: inset 0 1px 0 rgba(255,255,255,.6), 0 1px 2px rgba(0,0,0,.06);
    transition: background-color .18s ease, transform .08s ease;
  }
  .cd-chip:active{ transform: translateY(1px) }
</style>
@endpush

@section('content')
<div class="relative">
  {{-- soft background blobs --}}
  <div class="pointer-events-none absolute -top-20 -right-24 h-80 w-80 rounded-full blur-3xl opacity-20 bg-gradient-to-br from-amber-300 to-rose-300"></div>
  <div class="pointer-events-none absolute -bottom-24 -left-24 h-96 w-96 rounded-full blur-3xl opacity-20 bg-gradient-to-tr from-orange-300 to-pink-300"></div>

  <div class="max-w-5xl mx-auto p-4 md:p-8 relative">

    {{-- flashes --}}
    @if(session('success'))
      <div class="mb-4 rounded-xl bg-green-50 ring-1 ring-green-200 px-4 py-3 text-green-800">
        {{ session('success') }}
      </div>
    @endif
    @if($errors->any())
      <div class="mb-4 rounded-xl bg-rose-50 ring-1 ring-rose-200 px-4 py-3 text-rose-800">
        <ul class="list-disc pl-5 space-y-1">
          @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    @php
      $status = $report->status ?? 'pending';
      $badge = [
        'pending'     => 'bg-amber-100 text-amber-800 ring-amber-200',
        'in_progress' => 'bg-blue-100 text-blue-800 ring-blue-200',
        'resolved'    => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
        'rejected'    => 'bg-rose-100 text-rose-800 ring-rose-200',
      ][$status] ?? 'bg-gray-100 text-gray-800 ring-gray-200';

      $photoUrl = $report->photo_url ?? ($report->photo ? asset('storage/'.$report->photo) : null);

      $lat = $report->latitude  ? (float)$report->latitude  : null;
      $lng = $report->longitude ? (float)$report->longitude : null;
      $addr= $report->formatted_address ?: $report->location;
      $placeId = $report->place_id ?? null;

      $googleApiKey = config('services.google_maps.key'); // make sure it's set
      $hasCoords = is_finite($lat ?? NAN) && is_finite($lng ?? NAN);
      $mapsLink = $hasCoords
        ? "https://www.google.com/maps?q={$lat},{$lng}"
        : ($addr ? ("https://www.google.com/maps/search/?api=1&query=".urlencode($addr)) : null);
    @endphp

    <div class="cd-card rounded-2xl shadow-2xl p-6 ring-1 ring-amber-100 dark:ring-white/10">
      {{-- Header --}}
      <div class="flex items-start justify-between gap-4 mb-6">
        <div class="min-w-0">
          <h1 class="text-3xl font-extrabold text-amber-800 truncate dark:text-amber-200">
            {{ $report->title ?? ('Report #'.$report->id) }}
          </h1>
          <p class="text-sm text-gray-600 dark:text-amber-200/70 mt-1">
            Submitted by <span class="font-medium">{{ $report->user->name ?? 'Unknown' }}</span> •
            {{ optional($report->created_at)->format('M d, Y h:i a') }}
          </p>
        </div>
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ring-1 ring-inset {{ $badge }}">
          {{ \Illuminate\Support\Str::headline($status) }}
        </span>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Details + Attachment + Admin Notes --}}
        <div class="lg:col-span-2 space-y-6">
{{-- Engagement: Endorse + Rating + Comments --}}
<section class="rounded-2xl bg-white/85 backdrop-blur ring-1 ring-amber-900/10 shadow p-6 space-y-5">
  {{-- Endorse + Rating header --}}
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-3">
      @include('reports.partials._endorse_button', ['report' => $report])
      <div class="text-sm text-amber-900/80">
        <span class="font-semibold">{{ $report->endorsements_count ?? ($report->endorsements_count ?? 0) }}</span> endorsements
      </div>
    </div>

  

  {{-- Comments --}}
  <h2 class="text-lg font-semibold text-amber-800">Comments</h2>

  @auth
    @include('reports.partials._comment_form', ['report' => $report])
  @endauth

  <div class="divide-y divide-amber-100">
    @forelse($report->comments as $comment)
      @include('reports.partials._comment', ['comment' => $comment])
    @empty
      <p class="text-sm text-amber-900/70 py-3">No comments yet. Be the first to comment.</p>
    @endforelse
  </div>
</section>

          {{-- Details --}}
          <div class="rounded-2xl bg-white/85 dark:bg-white/5 backdrop-blur ring-1 ring-amber-900/10 dark:ring-white/10 shadow p-6">
            <h2 class="text-lg font-semibold text-amber-900 dark:text-amber-100 mb-3">Details</h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
              <div>
                <dt class="text-amber-900/70 dark:text-amber-200/70">Location</dt>
                <dd class="font-medium text-amber-900 dark:text-amber-100">{{ $report->location ?? '—' }}</dd>
              </div>
              <div>
                <dt class="text-amber-900/70 dark:text-amber-200/70">Category</dt>
                <dd class="font-medium text-amber-900 dark:text-amber-100">{{ $report->category ?? '—' }}</dd>
              </div>
              <div>
                <dt class="text-amber-900/70 dark:text-amber-200/70">City Corporation</dt>
                <dd class="font-medium text-amber-900 dark:text-amber-100">{{ $report->city_corporation ?? '—' }}</dd>
              </div>
              <div>
                <dt class="text-amber-900/70 dark:text-amber-200/70">Current Status</dt>
                <dd class="font-medium text-amber-900 dark:text-amber-100">{{ \Illuminate\Support\Str::headline($status) }}</dd>
              </div>
              @if($addr)
  <div class="sm:col-span-2">
    <dt class="text-amber-900/70 dark:text-amber-200/70">Formatted Address</dt>
    <dd class="mt-1 flex flex-wrap items-center gap-2">
      <span id="cdAddress" class="font-medium text-gray-800 dark:text-amber-100">{{ $addr }}</span>

      <button type="button" id="copyAddrBtn"
              class="cd-chip text-xs px-2 py-1 rounded-lg ring-1 ring-amber-900/10 
                     bg-white text-gray-700 hover:bg-amber-50 
                     dark:bg-[#1b1f24] dark:ring-white/10 dark:hover:bg-[#232830] 
                     dark:text-amber-100">
        Copy
      </button>

      @if($mapsLink)
        <a href="{{ $mapsLink }}" target="_blank" rel="noopener"
           class="cd-chip text-xs px-2 py-1 rounded-lg ring-1 ring-amber-900/10 
                  bg-white text-gray-700 hover:bg-amber-50 
                  dark:bg-[#1b1f24] dark:ring-white/10 dark:hover:bg-[#232830] 
                  dark:text-amber-100">
          Open in Google Maps
        </a>
      @endif
    </dd>
  </div>
@endif

              <div class="sm:col-span-2">
                <dt class="text-amber-900/70 dark:text-amber-200/70">Description</dt>
                <dd class="mt-1 whitespace-pre-line text-gray-700 dark:text-amber-100/90">{{ $report->description ?? 'No description provided.' }}</dd>
              </div>
            </dl>
          </div>

          {{-- Attachment w/ lightbox --}}
          <div class="rounded-2xl bg-white/85 dark:bg-white/5 backdrop-blur ring-1 ring-amber-900/10 dark:ring-white/10 shadow p-6">
            <h2 class="text-lg font-semibold text-amber-900 dark:text-amber-100 mb-3">Attachment</h2>

            @php $files = $report->attachments ?? []; @endphp

            @if(!empty($files))
              <ul class="space-y-2 text-sm">
                @foreach($files as $file)
                  <li class="flex items-center justify-between gap-3 rounded-xl bg-amber-50 px-3 py-2 dark:bg-amber-900/20">
                    <span class="truncate text-amber-900 dark:text-amber-100">{{ basename($file) }}</span>
                    <a href="{{ Storage::url($file) }}" target="_blank" class="text-rose-700 dark:text-rose-300 hover:underline">View</a>
                  </li>
                @endforeach
              </ul>

            @elseif($photoUrl)
              <figure class="relative group">
                <button type="button"
                        data-photo="{{ $photoUrl }}"
                        class="block w-full rounded-xl ring-1 ring-amber-900/10 dark:ring-white/10 shadow overflow-hidden">
                  <img src="{{ $photoUrl }}"
                       alt="Report Attachment"
                       class="w-full max-h-[420px] object-contain bg-amber-50 dark:bg-amber-900/20"
                       loading="lazy"
                       onerror="this.style.display='none'; this.nextElementSibling.style.display='grid';">
                  {{-- error fallback --}}
                  <div class="hidden place-items-center w-full h-[240px] text-amber-900/60 dark:text-amber-200/70 bg-amber-50 dark:bg-amber-900/20">
                    Image could not be loaded.
                  </div>
                </button>
                <figcaption class="mt-2 text-xs text-amber-900/70 dark:text-amber-200/70 truncate">
                  {{ $report->photo_name ?? 'Attachment' }} — click to zoom
                </figcaption>
              </figure>

            @else
              <div class="w-full h-48 grid place-items-center rounded-xl ring-1 ring-amber-900/10 dark:ring-white/10 text-amber-900/60 dark:text-amber-200/70">
                No attachment
              </div>
            @endif
          </div>

          {{-- Admin Notes (read-only) --}}
          <section class="rounded-2xl bg-white/85 dark:bg-white/5 backdrop-blur ring-1 ring-amber-900/10 dark:ring-white/10 shadow p-6">
            <h2 class="text-lg font-semibold text-amber-900 dark:text-amber-100 mb-4">Admin Notes</h2>
            @if(method_exists($report, 'notes') && ($report->relationLoaded('notes') || filled(optional($report)->notes)))
              @forelse($report->notes as $note)
                <div class="rounded-xl bg-amber-50 ring-1 ring-amber-100 p-4 mb-3 dark:bg-amber-900/20 dark:ring-amber-900/40">
                  <div class="text-sm text-amber-900/90 dark:text-amber-100">{{ $note->body }}</div>
                  <div class="mt-2 text-xs text-amber-900/70 dark:text-amber-200/70">
                    — {{ $note->admin?->name ?? 'Admin' }} • {{ $note->created_at->diffForHumans() }}
                  </div>
                </div>
              @empty
                <p class="text-sm text-amber-900/70 dark:text-amber-200/70">No notes yet.</p>
              @endforelse
            @else
              <p class="text-sm text-amber-900/70 dark:text-amber-200/70">No notes yet.</p>
            @endif
          </section>
        </div>

        {{-- Right: Map + Back --}}
        <div class="space-y-6">
          {{-- Map card --}}
          <section class="rounded-2xl bg-white/85 dark:bg-white/5 backdrop-blur ring-1 ring-amber-900/10 dark:ring-white/10 shadow p-6">
            <h2 class="text-lg font-semibold text-amber-900 dark:text-amber-100 mb-3">Location Map</h2>

            @if($hasCoords && $googleApiKey)
              <div class="relative rounded-2xl overflow-hidden ring-1 ring-gray-200 dark:ring-white/10">
                <div id="reportShowMap" class="h-[320px] w-full"></div>

                <div class="absolute bottom-3 left-3 right-3">
                  <div id="addrBadge"
                       class="truncate px-3 py-2 rounded-xl text-sm
                              bg-white/95 text-gray-900 ring-1 ring-gray-200
                              dark:bg-[#1b1f24]/95 dark:text-amber-100 dark:ring-white/10">
                    {{ $addr ?? 'Pinned location' }}
                  </div>
                </div>
              </div>
              <div class="mt-3 flex flex-wrap gap-2">
                <a href="{{ $mapsLink }}" target="_blank" rel="noopener"
                   class="cd-chip inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm
                          bg-white text-amber-700 ring-1 ring-gray-200 hover:bg-amber-50
                          dark:bg-[#1b1f24] dark:text-amber-100 dark:ring-white/10 dark:hover:bg-[#232830]">
                  Open in Google Maps
                </a>
                <button type="button" id="copyCoordBtn"
                        class="cd-chip inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm
                               bg-white ring-1 ring-gray-200 hover:bg-amber-50
                               dark:bg-[#1b1f24] dark:text-amber-100 dark:ring-white/10 dark:hover:bg-[#232830]">
                  Copy coordinates
                </button>
              </div>
            @else
              <div class="rounded-xl ring-1 ring-amber-900/10 dark:ring-white/10 p-4 text-sm text-amber-900/70 dark:text-amber-200/70">
                @if(!$addr)
                  No map data for this report.
                @elseif(!$googleApiKey)
                  Map cannot load (API key missing). You can still
                  <a class="text-rose-700 dark:text-rose-300 underline" href="{{ $mapsLink }}" target="_blank">open in Google Maps</a>.
                @else
                  Location not pinned, but we have the address. <a class="text-rose-700 dark:text-rose-300 underline" href="{{ $mapsLink }}" target="_blank">Open in Google Maps</a>.
                @endif
              </div>
            @endif
          </section>

          <a href="{{ route('reports.index') }}"
             class="block text-center rounded-xl px-4 py-2 bg-white ring-1 ring-amber-900/10
                    text-amber-900/90 shadow hover:shadow-md transition dark:bg-white/10 dark:text-amber-100 dark:ring-white/10">
            ← Back to All Reports
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Lightbox Modal (unchanged) --}}
<div id="cd-lightbox"
     class="fixed inset-0 z-[70] hidden bg-black/70 backdrop-blur-sm">
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="relative w-full max-w-6xl">
      <div class="absolute right-3 top-3 z-10 flex gap-2">
        <button id="lbClose"
                class="rounded-xl bg-white/90 px-2.5 py-1.5 text-amber-900 shadow hover:bg-white">✕</button>
      </div>
      <div class="absolute left-3 top-3 z-10 flex gap-2">
        <button id="lbZoomOut" class="rounded-xl bg-white/90 px-2.5 py-1.5 text-amber-900 shadow hover:bg-white">−</button>
        <button id="lbZoomIn"  class="rounded-xl bg-white/90 px-2.5 py-1.5 text-amber-900 shadow hover:bg-white">+</button>
        <button id="lbReset"   class="rounded-xl bg-white/90 px-2.5 py-1.5 text-amber-900 shadow hover:bg-white">100%</button>
        <a id="lbDownload" href="#" download
           class="rounded-xl bg-white/90 px-2.5 py-1.5 text-amber-900 shadow hover:bg-white">Download</a>
      </div>
      <div id="lbViewport" class="bg-white/10 rounded-2xl ring-1 ring-white/20 shadow-2xl overflow-auto max-h-[85vh]">
        <img id="lbImg" src="" alt="Attachment preview" class="select-none block m-auto origin-center max-w-none" draggable="false" />
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
@if($hasCoords && $googleApiKey)
  <script src="https://maps.googleapis.com/maps/api/js?key={{ $googleApiKey }}&loading=async" defer></script>
@endif
<script>
(function () {
  // Copy address
  const copyBtn = document.getElementById('copyAddrBtn');
  const addrEl  = document.getElementById('cdAddress');
  copyBtn?.addEventListener('click', async () => {
    try {
      await navigator.clipboard.writeText(addrEl?.textContent?.trim() || '');
      const old = copyBtn.textContent; copyBtn.textContent = 'Copied!'; setTimeout(()=>copyBtn.textContent = old, 900);
    } catch {}
  });

  // Copy coordinates
  const copyCoordBtn = document.getElementById('copyCoordBtn');
  copyCoordBtn?.addEventListener('click', async () => {
    try {
      await navigator.clipboard.writeText('{{ $lat }},{{ $lng }}');
      const old = copyCoordBtn.textContent; copyCoordBtn.textContent = 'Copied!'; setTimeout(()=>copyCoordBtn.textContent = old, 900);
    } catch {}
  });

  // Map init (if present)
  @if($hasCoords && $googleApiKey)
    let map, marker;
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

    const svgPin = (fill = '#e11d48') =>
      'data:image/svg+xml;utf8,' + encodeURIComponent(`
        <svg width="40" height="40" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
          <defs><filter id="s" x="-50%" y="-50%" width="200%" height="200%">
            <feGaussianBlur in="SourceAlpha" stdDeviation="2" result="blur"/><feOffset dy="2"/>
            <feMerge><feMergeNode/><feMergeNode in="SourceGraphic"/></feMerge>
          </filter></defs>
          <g filter="url(#s)">
            <path d="M32 4c-10.5 0-19 8.5-19 19 0 13.2 19 37 19 37s19-23.8 19-37c0-10.5-8.5-19-19-19z" fill="${fill}"/>
            <circle cx="32" cy="23" r="6.5" fill="#fff"/>
          </g>
        </svg>`);

    function initShowMap(){
      const el = document.getElementById('reportShowMap');
      if (!el || !window.google || !google.maps) return;
      const center = { lat: {{ $lat }}, lng: {{ $lng }} };

      map = new google.maps.Map(el, {
        center, zoom: 16, clickableIcons: false,
        mapTypeControl: false, fullscreenControl: false, streetViewControl: false,
        styles: isDark() ? stylesDark : stylesLight,
      });

      marker = new google.maps.Marker({
        map, position: center,
        icon: { url: svgPin(), scaledSize: new google.maps.Size(40, 40), anchor: new google.maps.Point(20, 40) }
      });

      try {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
          map.setOptions({ styles: e.matches ? stylesDark : stylesLight });
        });
      } catch {}
    }

    window.addEventListener('load', () => {
      const t = setInterval(() => {
        if (window.google && google.maps) { clearInterval(t); initShowMap(); }
      }, 50);
    });
  @endif

  // Lightbox (unchanged core)
  const modal     = document.getElementById('cd-lightbox');
  const viewport  = document.getElementById('lbViewport');
  const img       = document.getElementById('lbImg');
  const btnClose  = document.getElementById('lbClose');
  const btnIn     = document.getElementById('lbZoomIn');
  const btnOut    = document.getElementById('lbZoomOut');
  const btnReset  = document.getElementById('lbReset');
  const aDownload = document.getElementById('lbDownload');

  let scale = 1;
  function openLightbox(src){ img.src = src; aDownload.href = src; scale=1; apply(); modal.classList.remove('hidden'); document.documentElement.style.overflow='hidden'; }
  function closeLightbox(){ modal.classList.add('hidden'); img.src=''; document.documentElement.style.overflow=''; }
  function apply(){ img.style.transform=`scale(${scale})`; img.style.transition='transform 120ms ease'; }

  document.querySelectorAll('button[data-photo]').forEach(b => b.addEventListener('click', () => openLightbox(b.getAttribute('data-photo'))));
  btnClose?.addEventListener('click', closeLightbox);
  btnIn?.addEventListener('click', () => { scale = Math.min(5, +(scale + 0.2).toFixed(2)); apply(); });
  btnOut?.addEventListener('click', () => { scale = Math.max(0.2, +(scale - 0.2).toFixed(2)); apply(); });
  btnReset?.addEventListener('click', () => { scale = 1; apply(); });
  modal.addEventListener('click', (e) => { if (e.target === modal) closeLightbox(); });
  window.addEventListener('keydown', (e) => {
    if (!modal.classList.contains('hidden') && e.key === 'Escape') closeLightbox();
    if (!modal.classList.contains('hidden') && (e.key === '+' || e.key === '=')) { e.preventDefault(); btnIn.click(); }
    if (!modal.classList.contains('hidden') && (e.key === '-' || e.key === '_')) { e.preventDefault(); btnOut.click(); }
  });
  viewport?.addEventListener('wheel', (e) => {
    if (e.ctrlKey || e.metaKey) {
      e.preventDefault();
      const delta = -Math.sign(e.deltaY) * 0.1;
      scale = Math.min(5, Math.max(0.2, +(scale + delta).toFixed(2)));
      apply();
    }
  }, { passive: false });
})();
</script>
@endpush
