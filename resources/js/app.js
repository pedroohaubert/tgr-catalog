import './bootstrap';

import Alpine from 'alpinejs';

// Avoid starting Alpine multiple times across HMR or duplicate inits
if (!window.Alpine) {
    window.Alpine = Alpine;
}

const startAlpineSafely = () => {
    // Alpine exposes a started flag we can check to avoid duplicates
    if (!window.Alpine?.initialized && !document.documentElement.__alpine) {
        window.Alpine.start();
        window.Alpine.initialized = true;
    }
};

// Prefer Livewire DOM event for correct Alpine init order
document.addEventListener('livewire:load', startAlpineSafely);

// Fallback if Livewire is not present on the page
if (!window.Livewire) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startAlpineSafely);
    } else {
        startAlpineSafely();
    }
}
