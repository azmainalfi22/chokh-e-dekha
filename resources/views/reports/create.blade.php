@extends(auth()->user()->is_admin ? 'layouts.admin' : 'layouts.app')

@section('title', 'Submit a Report')

@push('styles')
<style>
  :root{
    --cd-card-light: rgba(255,255,255,0.98);  /* light panel surface */
    --cd-card-dark:  rgba(221, 213, 189, 1);  /* <— use light surface in dark too */
    --cd-input:      #ffffff;                 /* inputs stay light always */
    --cd-text:       #111827;                 /* slate-900 text (both modes) */
    --cd-ph:         #6b7280;                 /* gray-500 placeholder */
    --cd-amber:      245,158,11;
  }

  /* ---------- Card ---------- */
  .cd-card{
    background: var(--cd-card-light);
    color: var(--cd-text);
    backdrop-filter: blur(10px);
    border-radius: 0.75rem;
    padding: 1rem;
    position: relative;
    overflow: hidden;
  }
  .cd-card::before{
    content:"";
    position:absolute; inset:0; pointer-events:none;
    background:
      radial-gradient(1200px 400px at -10% -10%, rgba(251,191,36,.12), transparent 40%),
      radial-gradient(1000px 300px at 110% 110%, rgba(244,63,94,.10), transparent 45%);
  }
  /* dark mode: keep panel light so slate text stays readable */
  .dark .cd-card { background: var(--cd-card-dark); color: var(--cd-text); }

  /* ---------- Inputs (same in light & dark) ---------- */
  .cd-input,
  .cd-input[type="text"],
  .cd-input[type="search"],
  .cd-input[type="email"],
  .cd-input[type="file"],
  .cd-input select,
  .cd-input textarea {
    background: var(--cd-input) !important;
    color: var(--cd-text) !important;
    border: 1px solid #d1d5db !important;
    box-shadow:
      inset 0 1px 0 rgba(255,255,255,.65),
      inset 0 0 0 1px rgba(0,0,0,.04),
      0 1px 2px rgba(0,0,0,.04);
    transition: box-shadow .18s ease, border-color .18s ease, background-color .18s ease, color .18s ease;
  }
  .cd-input::placeholder { color: var(--cd-ph); }
  .cd-input:focus{
    outline: none;
    border-color: rgb(var(--cd-amber)) !important;
    box-shadow:
      0 0 0 4px rgba(var(--cd-amber), .18),
      inset 0 1px 0 rgba(255,255,255,.65),
      0 1px 2px rgba(0,0,0,.06);
  }
  .cd-input option { color: var(--cd-text); background: var(--cd-input); }

  .cd-chip{
    box-shadow: inset 0 1px 0 rgba(255,255,255,.6), 0 1px 2px rgba(0,0,0,.06);
    transition: background-color .18s ease, transform .08s ease, color .18s ease;
  }
  .cd-chip:active{ transform: translateY(1px) }
  .cd-badge{
    box-shadow: inset 0 1px 0 rgba(255,255,255,.6), 0 2px 8px rgba(0,0,0,.08);
  }
</style>
@endpush


@section('content')
<div class="relative">
  <div class="pointer-events-none absolute -top-20 -right-24 h-80 w-80 rounded-full blur-3xl opacity-20 bg-gradient-to-br from-amber-300 to-rose-300"></div>
  <div class="pointer-events-none absolute -bottom-24 -left-24 h-96 w-96 rounded-full blur-3xl opacity-20 bg-gradient-to-tr from-orange-300 to-pink-300"></div>

  <div class="max-w-4xl mx-auto p-4 md:p-8 relative">
    <header class="mb-8">
      <h1 class="text-3xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-amber-700 via-orange-700 to-rose-700">
        Submit a City Issue
      </h1>
      <p class="text-sm text-gray-400">Describe the problem clearly so authorities can act fast.</p>
    </header>

    @if ($errors->any())
      <div class="mb-6 rounded-2xl ring-1 ring-rose-200 bg-rose-50 px-4 py-3 text-rose-800 shadow-sm">
        <div class="font-semibold mb-1">Please fix the following:</div>
        <ul class="list-disc list-inside text-sm">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('report.store') }}" method="POST" enctype="multipart/form-data"
          class="cd-card rounded-2xl shadow-2xl p-6 md:p-7 space-y-6 ring-1 ring-gray-200/80">
      @csrf

      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
          <label for="title" class="block text-sm font-medium text-gray-900 mb-1">
            Title <span class="text-rose-600">*</span>
          </label>
          <input id="title" type="text" name="title" value="{{ old('title') }}" required
                 class="cd-input w-full rounded-xl placeholder:text-gray-500 focus:ring-0"/>
          @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
          <label for="category" class="block text-sm font-medium text-gray-900 mb-1">
            Category <span class="text-rose-600">*</span>
          </label>
          @php
            $cats = (isset($categories) && is_array($categories) && count($categories))
              ? $categories
              : ['Road Damage','Broken Road','Street Light','Electricity','Water Supply','Drainage','Waste Management','Garbage','Sewage','Public Safety','Traffic','Parks','Health','Education','Other'];
            $oldCat = old('category');
          @endphp
          <select id="category" name="category" required class="cd-input w-full rounded-xl focus:ring-0">
            <option value="">-- Select Category --</option>
            @foreach($cats as $c)
              <option value="{{ $c }}" @selected($oldCat === $c)>{{ $c }}</option>
            @endforeach
          </select>
          @error('category') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
          <label for="city_corporation" class="block text-sm font-medium text-gray-900 mb-1">
            City Corporation <span class="text-rose-600">*</span>
          </label>
          @php $cc = old('city_corporation'); @endphp
          <select name="city_corporation" id="city_corporation" required class="cd-input w-full rounded-xl focus:ring-0">
              <option value="">-- Select a City Corporation --</option>
              <option value="Dhaka North"   @selected($cc==='Dhaka North')>Dhaka North City Corporation</option>
              <option value="Dhaka South"   @selected($cc==='Dhaka South')>Dhaka South City Corporation</option>
              <option value="Chattogram"    @selected($cc==='Chattogram')>Chattogram City Corporation</option>
              <option value="Gazipur"       @selected($cc==='Gazipur')>Gazipur City Corporation</option>
              <option value="Khulna"        @selected($cc==='Khulna')>Khulna City Corporation</option>
              <option value="Rajshahi"      @selected($cc==='Rajshahi')>Rajshahi City Corporation</option>
              <option value="Sylhet"        @selected($cc==='Sylhet')>Sylhet City Corporation</option>
              <option value="Barishal"      @selected($cc==='Barishal')>Barishal City Corporation</option>
              <option value="Cumilla"       @selected($cc==='Cumilla')>Cumilla City Corporation</option>
              <option value="Narayanganj"   @selected($cc==='Narayanganj')>Narayanganj City Corporation</option>
              <option value="Mymensingh"    @selected($cc==='Mymensingh')>Mymensingh City Corporation</option>
          </select>
          @error('city_corporation') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
          <label for="location" class="block text-sm font-medium text-gray-900 mb-1">
            Location <span class="text-rose-600">*</span>
          </label>
          <input id="location" type="text" name="location" value="{{ old('location') }}" required
                 class="cd-input w-full rounded-xl placeholder:text-gray-500 focus:ring-0"
                 placeholder="Road name, area..." />
          @error('location') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>

      <div>
        <label for="description" class="block text-sm font-medium text-gray-900 mb-1">
          Description <span class="text-rose-600">*</span>
        </label>
        <textarea id="description" name="description" rows="5"
                  class="cd-input w-full rounded-xl placeholder:text-gray-500 focus:ring-0"
                  placeholder="Explain what’s wrong, since when, and any ID/landmark...">{{ old('description') }}</textarea>
        @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>

      <div class="space-y-3">
        <label class="block text-sm font-medium text-gray-900">Search on Map</label>
        <div class="relative">
          <input id="place_search" type="text" autocomplete="off"
                 class="cd-input w-full rounded-2xl pl-12 placeholder:text-gray-500 focus:ring-0"
                 placeholder="Search a place, road, landmark…">
          <div class="pointer-events-none absolute inset-y-0 left-0 pl-4 flex items-center text-gray-500">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M10 2a8 8 0 0 1 6.32 12.9l4.39 4.39-1.42 1.42-4.39-4.39A8 8 0 1 1 10 2zm0 2a6 6 0 1 0 0 12 6 6 0 0 0 0-12z"/></svg>
          </div>
        </div>

        <div class="relative rounded-2xl overflow-hidden ring-1 ring-gray-200">
          <div id="reportMap" class="h-[380px] w-full"></div>

          <div class="absolute top-3 left-3 flex gap-2">
            <button type="button" id="btnGeolocate"
                    class="cd-chip inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm
                           bg-white text-amber-700 ring-1 ring-gray-200 hover:bg-amber-50">
              📍 Use my location
            </button>
            <button type="button" id="btnCenter"
                    class="cd-chip px-3 py-1.5 rounded-full text-sm
                           bg-white ring-1 ring-gray-200 hover:bg-amber-50">
              Center on pin
            </button>
          </div>

          <div class="absolute bottom-3 left-3 right-3">
            <div id="addrBadge"
                 class="cd-badge truncate px-3 py-2 rounded-xl text-sm
                        bg-white/95 text-gray-900 ring-1 ring-gray-200">
              Address will appear here…
            </div>
          </div>
        </div>

        <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
        <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
        <input type="hidden" name="place_id" id="place_id" value="{{ old('place_id') }}">
        <input type="hidden" name="formatted_address" id="formatted_address" value="{{ old('formatted_address') }}">
      </div>

      <div>
        <label for="photo" class="block text-sm font-medium text-gray-900 mb-2">
          Attach Photo (optional)
        </label>
        <input id="photo" type="file" name="photo" accept="image/*"
               class="block w-full text-sm text-gray-800
                      file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0
                      file:bg-amber-600 file:text-white hover:file:bg-amber-700">
        @error('photo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>

      <div class="flex items-center justify-between">
        <a href="{{ route('reports.index') }}" class="text-gray-700 hover:text-gray-900">← Back to All Reports</a>
        <button type="submit"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-600 to-rose-600 text-white hover:brightness-110 shadow-lg">
          <span>Submit</span>
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M3 12l18-9-9 18-2-7-7-2z"/></svg>
        </button>
      </div>
    </form>
  </div>
</div>

<script src="https://maps.googleapis.com/maps/api/js?key={{ $googleApiKey }}&libraries=places&loading=async" defer></script>

<script>
  let map, marker, geocoder, autocomplete, lastCenter;
  const $ = (id) => document.getElementById(id);

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

  function setHidden(lat, lng, placeId, addr) {
    $('latitude').value = lat ?? '';
    $('longitude').value = lng ?? '';
    $('place_id').value = placeId ?? '';
    $('formatted_address').value = addr ?? '';
    $('addrBadge').textContent = addr ? addr : 'Pin a location or search above…';
  }

  function reverseGeocode(lat, lng) {
    geocoder.geocode({ location: { lat, lng } }, (results, status) => {
      const addr = (status === 'OK' && results?.[0]) ? results[0].formatted_address : '';
      const pid  = (status === 'OK' && results?.[0]) ? results[0].place_id : '';
      setHidden(lat, lng, pid, addr);
    });
  }

  function setMarkerAndCenter(lat, lng, zoom = 16, doReverse = true) {
    const pos = new google.maps.LatLng(lat, lng);
    marker.setPosition(pos);
    map.panTo(pos);
    map.setZoom(zoom);
    lastCenter = pos;
    if (doReverse) reverseGeocode(lat, lng);
  }

  function wireButtons(){
    const btnGeo   = $('btnGeolocate');
    const btnCenter= $('btnCenter');

    btnGeo?.addEventListener('click', () => {
      if (!navigator.geolocation) return alert('Geolocation not supported on this browser.');
      btnGeo.disabled = true;
      btnGeo.textContent = 'Locating…';
      navigator.geolocation.getCurrentPosition(
        (pos) => {
          const { latitude, longitude } = pos.coords;
          setMarkerAndCenter(latitude, longitude, 17, true);
          btnGeo.disabled = false;
          btnGeo.textContent = '📍 Use my location';
        },
        (err) => {
          console.error('Geolocation error:', err);
          alert('Unable to get your location. Please allow permission and try again.');
          btnGeo.disabled = false;
          btnGeo.textContent = '📍 Use my location';
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
      );
    });

    btnCenter?.addEventListener('click', () => {
      if (marker?.getPosition()) {
        map.panTo(marker.getPosition());
        map.setZoom(Math.max(map.getZoom(), 16));
      } else if (lastCenter) {
        map.panTo(lastCenter);
      }
    });
  }

  function initMap() {
    geocoder = new google.maps.Geocoder();

    map = new google.maps.Map($('reportMap'), {
      center: { lat: 23.777176, lng: 90.399452 },
      zoom: 14,
      clickableIcons: false,
      mapTypeControl: false,
      fullscreenControl: false,
      streetViewControl: false,
      styles: isDark() ? stylesDark : stylesLight,
    });

    marker = new google.maps.Marker({
      map,
      draggable: true,
      position: map.getCenter(),
      icon: { url: svgPin(), scaledSize: new google.maps.Size(40, 40), anchor: new google.maps.Point(20, 40) }
    });

    // Drag to move
    google.maps.event.addListener(marker, 'dragend', () => {
      const p = marker.getPosition();
      reverseGeocode(p.lat(), p.lng());
    });

    // Click map to move pin
    map.addListener('click', (e) => {
      const lat = e.latLng.lat(), lng = e.latLng.lng();
      setMarkerAndCenter(lat, lng, map.getZoom(), true);
    });

    // Autocomplete
    const input = $('place_search');
    autocomplete = new google.maps.places.Autocomplete(input, { fields: ['geometry','place_id','formatted_address'] });
    autocomplete.addListener('place_changed', () => {
      const place = autocomplete.getPlace();
      if (!place.geometry) return;
      const loc = place.geometry.location;
      setMarkerAndCenter(loc.lat(), loc.lng(), 16, false);
      setHidden(loc.lat(), loc.lng(), place.place_id, place.formatted_address);
    });

    // Rehydrate from old() lat/lng if present
    const oldLat = parseFloat($('latitude').value);
    const oldLng = parseFloat($('longitude').value);
    if (!Number.isNaN(oldLat) && !Number.isNaN(oldLng)) {
      setMarkerAndCenter(oldLat, oldLng, 16, false);
      $('addrBadge').textContent = $('formatted_address').value || 'Pinned location restored.';
    } else {
      // No old values: just reverse geocode the default center to populate address hint
      const c = map.getCenter();
      reverseGeocode(c.lat(), c.lng());
    }

    // Auto-switch styles when OS theme changes
    try {
      window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
        map.setOptions({ styles: e.matches ? stylesDark : stylesLight });
      });
    } catch {}

    wireButtons();
  }

  // Guard: ensure lat/lng exist before submit
  function enforceLocationBeforeSubmit(){
    const form = document.querySelector('form[action="{{ route('report.store') }}"]');
    form?.addEventListener('submit', (e) => {
      if (!$('latitude').value || !$('longitude').value) {
        e.preventDefault();
        alert('Please pick a point on the map or search for a location.');
        $('place_search').focus();
      }
    });
  }

  window.addEventListener('load', () => {
    const check = setInterval(() => {
      if (window.google && google.maps) { clearInterval(check); initMap(); enforceLocationBeforeSubmit(); }
    }, 50);
  });
</script>

@endsection
