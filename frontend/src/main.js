// Import Bootstrap and FontAwesome
import 'bootstrap/dist/css/bootstrap.min.css';
import '@popperjs/core';
import * as bootstrap from 'bootstrap';
import '@fortawesome/fontawesome-free/css/all.min.css';

import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import { setupCalendar } from 'v-calendar';

const app = createApp(App);

app.use(router);
app.use(setupCalendar, {});
app.mount('#app');

// Now expose Bootstrap to window after mounting
window.bootstrap = bootstrap;
