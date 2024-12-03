window._ = require('lodash');

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from 'laravel-echo';

window.Pusher = require('pusher-js');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'local_key', // Sesuaikan dengan .env
    wsHost: window.location.hostname, // Host lokal
    wsPort: 6001, // Port WebSocket
    wssPort: 6001, // Jika menggunakan HTTPS
    forceTLS: false, // Nonaktifkan TLS jika HTTP
    disableStats: true, // Nonaktifkan statistik
    enabledTransports: ['ws'], // Hanya gunakan WebSocket
});
// Add listener for 'my-channel' and 'my-event'
window.Echo.channel('my-channel')
    .listen('.my-event', (e) => {
        console.log('Event received on my-channel:', e);
        console.log('Data:', e.data); // Tampilkan data yang diterima
    });
