// Import Bootstrap globally
import 'bootstrap/dist/css/bootstrap.min.css';
import '@popperjs/core'; 
import * as bootstrap from 'bootstrap';

// Import fontawesome globally
import '@fortawesome/fontawesome-free/css/all.min.css'

import { createApp } from 'vue';
import App from './App.vue';
import router from './router';

window.bootstrap = bootstrap

const app = createApp(App);
app.use(router);
app.mount('#app');
