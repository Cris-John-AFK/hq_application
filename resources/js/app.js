import './bootstrap';
import 'primeicons/primeicons.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import ExampleComponent from './components/ExampleComponent.vue';
import LoginForm from './components/login/loginForm.vue';
import Dashboard from './components/Dashboard.vue';

const app = createApp({});
const pinia = createPinia();

app.use(pinia);
app.use(router);

app.component('example-component', ExampleComponent);
app.component('login-form', LoginForm);
app.component('dashboard-component', Dashboard);

app.mount('#app');
