let map, marker, geocoder, autocomplete;

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

const $ = (id) => document.getElementById(id);
const setHidden = (lat, lng, placeId, addr) => {
  $('latitude').value = lat ?? '';
  $('longitude').value = lng ?? '';
  $('place_id').value = placeId ?? '';
  $('formatted_address').value = addr ?? '';
  $('addrBadge').textContent = addr ? addr : 'Pin a location or search above…';
};

const svgPin = (fill = '#e11d48') =>
  `data:image/svg+xml;utf8,` + encodeURIComponent(`
    <svg width="40" height="40" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
      <defs><filter id="s" x="-50%" y="-50%" width="200%" height="200%">
        <feGaussianBlur in="SourceAlpha" stdDeviation="2" result="blur"/><feOffset dy="2"/><feMerge><feMergeNode/><feMergeNode in="SourceGraphic"/></feMerge>
      </filter></defs>
      <g filter="url(#s)">
        <path d="M32 4c-10.5 0-19 8.5-19 19 0 13.2 19 37 19 37s19-23.8 19-37c0-10.5-8.5-19-19-19z" fill="${fill}"/>
        <circle cx="32" cy="23" r="6.5" fill="#fff"/>
      </g>
    </svg>`);

function initMap() {
  geocoder = new google.maps.Geocoder();

  map = new google.maps.Map(document.getElementById('reportMap'), {
    center: { lat: 23.777176, lng: 90.399452 }, // Dhaka fallback
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

  google.maps.event.addListener(marker, 'dragend', () => {
    const p = marker.getPosition();
    reverseGeocode(p.lat(), p.lng());
  });

  // Autocomplete
  const input = document.getElementById('place_search');
  autocomplete = new google.maps.places.Autocomplete(input, { fields: ['geometry','place_id','formatted_address'] });
  autocomplete.addListener('place_changed', () => {
    const place = autocomplete.getPlace();
    if (!place.geometry) return;
    const loc = place.geometry.location;
    map.panTo(loc); marker.setPosition(loc); map.setZoom(16);
    setHidden(loc.lat(), loc.lng(), place.place_id, place.formatted_address);
  });

  // Buttons
  document.getElementById('btnCenter').addEventListener('click', () => {
    map.panTo(marker.getPosition());
  });

  document.getElementById('btnGeolocate').addEventListener('click', () => {
    if (!navigator.geolocation) return alert('Geolocation not supported');
    navigator.geolocation.getCurrentPosition(pos => {
      const { latitude: lat, longitude: lng } = pos.coords;
      const latLng = new google.maps.LatLng(lat, lng);
      map.panTo(latLng); map.setZoom(16); marker.setPosition(latLng);
      reverseGeocode(lat, lng);
    }, () => alert('Unable to get location'));
  });

  // dark mode reactiveness
  try {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
      map.setOptions({ styles: e.matches ? stylesDark : stylesLight });
    });
  } catch {}
}

function reverseGeocode(lat, lng) {
  geocoder.geocode({ location: { lat, lng } }, (results, status) => {
    const addr = (status === 'OK' && results?.[0]) ? results[0].formatted_address : '';
    const pid  = (status === 'OK' && results?.[0]) ? results[0].place_id : '';
    setHidden(lat, lng, pid, addr);
  });
}

window.addEventListener('load', () => {
  // Ensure Google is ready; the script is async/defer
  const check = setInterval(() => {
    if (window.google && google.maps) { clearInterval(check); initMap(); }
  }, 50);
});
