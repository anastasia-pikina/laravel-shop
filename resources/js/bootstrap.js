import axios from 'axios';
import { createApp } from 'vue';
import App from './components/App.vue';
import router from './router';
import { createPinia } from 'pinia';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Инициализация Vue и подключение маршрутизации
const app = createApp(App);
app.use(router);
const pinia = createPinia();
app.use(pinia);
app.mount('#app');
