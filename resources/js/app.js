import './bootstrap';

import Alpine from 'alpinejs';
import { pushBanner } from './push';
import { liveSearch } from './live-search';

window.Alpine = Alpine;
window.pushBanner = pushBanner;
window.liveSearch = liveSearch;

Alpine.start();
