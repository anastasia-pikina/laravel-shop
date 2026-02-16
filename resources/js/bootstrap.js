import axios from 'axios';
import { createApp } from 'vue';
import App from './components/App.vue';
import router from './router';
import { createPinia } from 'pinia';
import breadcrumbs from 'vue-3-breadcrumbs';
import PrimeVue from 'primevue/config';
import Aura from '@primeuix/themes/aura';
import Material from '@primeuix/themes/material';
import Lara from '@primeuix/themes/lara';
import { definePreset } from '@primeuix/themes';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Инициализация Vue и подключение маршрутизации
const app = createApp(App);
app.use(router);
const pinia = createPinia();
app.use(pinia);
app.use(breadcrumbs, {
    includeComponent: false // {boolean} [includeComponent=false] - Include global breadcrumbs component or not
});

const Noir = definePreset(Aura, {
    semantic: {
        primary: {
            50: '{amber.50}',
            100: '{amber.100}',
            200: '{amber.200}',
            300: '{amber.300}',
            400: '{amber.400}',
            500: '{amber.500}',
            600: '{amber.600}',
            700: '{amber.700}',
            800: '{amber.800}',
            900: '{amber.900}',
            950: '{amber.950}'
        },
        highlight: {
            background: '{primary.50}',
            color: '{primary.700}',
        },
        colorScheme: {
            light: {
                primary: {
                    color: '{amber.950}',
                    inverseColor: '#ffffff',
                    hoverColor: '{amber.500}',
                    activeColor: '{amber.800}'
                },
                highlight: {
                    background: '{amber.950}',
                    focusBackground: '{amber.700}',
                    color: '#ffffff',
                    focusColor: '#ffffff'
                }
            },
            dark: {
                primary: {
                    color: '{amber.50}',
                    inverseColor: '{amber.950}',
                    hoverColor: '{amber.100}',
                    activeColor: '{amber.200}'
                },
                highlight: {
                    background: 'rgba(250, 250, 250, .16)',
                    focusBackground: 'rgba(250, 250, 250, .24)',
                    color: 'rgba(255,255,255,.87)',
                    focusColor: 'rgba(255,255,255,.87)'
                }
            }
        }
    }
});

app.use(PrimeVue, {
    theme: {
        preset: Noir
    }
});
app.mount('#app');
