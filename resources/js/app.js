import './bootstrap';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import { pushBanner } from './push';
import { liveSearch } from './live-search';

window.Alpine = Alpine;
window.Chart = Chart;
window.pushBanner = pushBanner;
window.liveSearch = liveSearch;

Alpine.start();
