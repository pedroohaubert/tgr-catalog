import axios from 'axios';
import jQuery from 'jquery';

window.$ = jQuery;
window.jQuery = jQuery;

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
$(document).ready(function() {
    if (window.initCart) {
        window.initCart();
    }
});
