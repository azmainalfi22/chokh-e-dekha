import { MarkerClusterer } from '@googlemaps/markerclusterer';

let map, clusterer, heatmap;
const styles = [{ featureType:"poi", stylers:[{visibility:"off"}] }];

async function fetchGeo(bounds) {
  const params = new URLSearchParams({
    nelat: bounds.getNorthEast().lat(),
    nelng: bounds.getNorthEast().lng(),
    swlat: bounds.getSouthWest().lat(),
    swlng: bounds.getSouthWest().lng(),
  });
  const res = await fetch(`/admin/reports/map?${params.toString()}`, { headers: { 'Accept':'application/json' }});
  return res.json();
}

function markerIcon(status) {
  const color = status === 'Resolved' ? '#16a34a' : status === 'Pending' ? '#eab308' : '#ef4444';
  return { url: svg(color), scaledSize: new google.maps.Size(28,28), anchor: new google.maps.Point(14,28) };
}

const svg = (fill) => `data:image/svg+xml;utf8,` + encodeURIComponent(`
<svg width="28" height="28" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg"><path d="M32 4c-10.5 0-19 8.5-19 19 0 13.2 19 37 19 37s19-23.8 19-37c0-10.5-8.5-19-19-19z" fill="${fill}"/><circle cx="32" cy="23" r="6.5" fill="#fff"/></svg>`);

async function init() {
  map = new google.maps.Map(document.getElementById('adminMap'), {
    center: { lat: 23.777176, lng: 90.399452 },
    zoom: 11,
    styles,
    streetViewControl: false,
    mapTypeControl: false,
  });

  const load = async () => {
    const geo = await fetchGeo(map.getBounds());
    const markers = geo.features.map(f => {
      const [lng, lat] = f.geometry.coordinates;
      const m = new google.maps.Marker({
        position: { lat, lng },
        icon: markerIcon(f.properties.status),
      });
      const infowin = new google.maps.InfoWindow({
        content: `
          <div class="min-w-[180px]">
            <div class="font-semibold">#${f.properties.id}</div>
            <div class="text-xs text-neutral-600">${f.properties.status}</div>
            <div class="text-xs">${new Date(f.properties.created_at).toLocaleString()}</div>
          </div>`
      });
      m.addListener('click', () => infowin.open({ anchor: m, map }));
      return m;
    });

    if (clusterer) clusterer.clearMarkers();
    clusterer = new MarkerClusterer({ map, markers });

    // heatmap points
    const points = geo.features.map(f => {
      const [lng, lat] = f.geometry.coordinates;
      return new google.maps.LatLng(lat, lng);
    });
    if (!heatmap) {
      heatmap = new google.maps.visualization.HeatmapLayer({ data: points, map: null, radius: 24 });
    } else {
      heatmap.setData(points);
    }
  };

  map.addListener('idle', load);

  // heat toggle
  const toggle = document.getElementById('toggleHeat');
  toggle?.addEventListener('change', (e) => {
    heatmap?.setMap(e.target.checked ? map : null);
  });
}

window.addEventListener('load', () => {
  const check = setInterval(() => {
    if (window.google && google.maps) { clearInterval(check); init(); }
  }, 50);
});

