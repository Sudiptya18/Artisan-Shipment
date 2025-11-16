import './bootstrap';
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';

import '../scss/styles.scss';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import 'simple-datatables/dist/style.css';
import 'sweetalert2/dist/sweetalert2.min.css';

createApp(App).use(router).mount('#app');
