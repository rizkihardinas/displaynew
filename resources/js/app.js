require('./bootstrap');
import $ from 'jquery';
window.$ = window.jQuery = $;

import axios from 'axios';
window.axios = axios;

// Optional: Set default header Laravel CSRF token (jika pakai form/axios)
let token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}