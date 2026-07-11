import './bootstrap';

import Alpine from 'alpinejs';
import { pushBanner } from './push';

window.Alpine = Alpine;
window.pushBanner = pushBanner;

Alpine.start();
