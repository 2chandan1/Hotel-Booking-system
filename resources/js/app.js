import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia'
import router from './router'

// Configure axios defaults
window.axios.defaults.baseURL = window.location.origin;
window.axios.defaults.withCredentials = true;
window.axios.defaults.headers.common['Accept'] = 'application/json';
window.axios.defaults.headers.common['Content-Type'] = 'application/json';
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
import App from './App.vue'


const app = createApp(App);
const penia = createPinia();
app.use(penia);
app.use(router);

app.mount('#app');