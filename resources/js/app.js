import './bootstrap';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import { pushBanner } from './push';
import { liveSearch } from './live-search';
import { liveRefresh } from './live-refresh';

window.Alpine = Alpine;
window.Chart = Chart;
window.pushBanner = pushBanner;
window.liveSearch = liveSearch;
window.liveRefresh = liveRefresh;

Alpine.start();
