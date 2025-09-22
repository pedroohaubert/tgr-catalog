import './bootstrap';

import Alpine from 'alpinejs';

if (!window.Alpine) {
    window.Alpine = Alpine;
}

const startAlpineSafely = () => {
    if (!window.Alpine?.initialized && !document.documentElement.__alpine) {
        window.Alpine.start();
        window.Alpine.initialized = true;
    }
};

document.addEventListener('livewire:load', startAlpineSafely);
if (!window.Livewire) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startAlpineSafely);
    } else {
        startAlpineSafely();
    }
}
