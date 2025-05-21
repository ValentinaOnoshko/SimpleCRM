import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import './assets/main.css'
import Vuelidate from '@vuelidate/core';

const app = createApp(App);
app.use(router);
app.use(Vuelidate);
app.mount('#app');
