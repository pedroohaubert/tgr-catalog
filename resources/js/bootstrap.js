import axios from 'axios';
import jQuery from 'jquery';

// Expose jQuery globally for inline scripts that rely on $ / $.ajax
// This avoids timing issues and removes dependency on CDN order
window.$ = jQuery;
window.jQuery = jQuery;

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Initialize cart after jQuery is loaded and DOM is ready
$(document).ready(function() {
    if (window.initCart) {
        window.initCart();
    }
});
