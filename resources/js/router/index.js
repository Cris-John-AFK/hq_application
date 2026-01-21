import { createRouter, createWebHistory } from 'vue-router';
import Dashboard from '../components/Dashboard.vue';
import Employees from '../components/admin/Employees.vue';
import Attendance from '../components/admin/Attendance.vue';
import Schedules from '../components/admin/Schedules.vue';
import Reports from '../components/admin/Reports.vue';
import Settings from '../components/admin/Settings.vue';
import LoginForm from '../components/login/loginForm.vue';

const routes = [
    {
        path: '/login',
        name: 'Login',
        component: LoginForm,
        meta: { title: 'HQ Inc. - Login Page' }
    },
    {
        path: '/dashboard',
        name: 'Dashboard',
        component: Dashboard,
        meta: { title: 'HQ Inc. - Dashboard Page' }
    },
    {
        path: '/employees',
        name: 'Employees',
        component: Employees,
        meta: { title: 'HQ Inc. - Employees Page' }
    },
    {
        path: '/attendance',
        name: 'Attendance',
        component: Attendance,
        meta: { title: 'HQ Inc. - Attendance Page' }
    },
    {
        path: '/schedules',
        name: 'Schedules',
        component: Schedules,
        meta: { title: 'HQ Inc. - Schedules Page' }
    },
    {
        path: '/reports',
        name: 'Reports',
        component: Reports,
        meta: { title: 'HQ Inc. - Reports Page' }
    },
    {
        path: '/settings',
        name: 'Settings',
        component: Settings,
        meta: { title: 'HQ Inc. - Settings Page' }
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

router.beforeEach((to, from, next) => {
    document.title = to.meta.title || 'HQ Inc. - APP'; // Set title, or a default
    next();
});

export default router;
