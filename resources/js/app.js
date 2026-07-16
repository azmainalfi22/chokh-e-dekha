import './bootstrap';
import L from 'leaflet';
import 'leaflet.markercluster';
window.L = L;
import './engagement';
import './social-interactions';
import './notifications';
import './infinite-scroll';
import './quick-actions';
import './report-form-enhancements';
import './photo-gallery';
import './search-enhancements';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
