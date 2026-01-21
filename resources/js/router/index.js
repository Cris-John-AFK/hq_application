import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import Dashboard from '../components/Dashboard.vue';
import Employees from '../components/admin/Employees.vue';
import AdminAttendance from '../components/admin/Attendance.vue';
import Schedules from '../components/admin/Schedules.vue';
import Reports from '../components/admin/Reports.vue';
import Settings from '../components/admin/Settings.vue';
import LoginForm from '../components/login/loginForm.vue';

// User Components
import UserDashboard from '../components/user/UserDashboard.vue';
import UserProfile from '../components/user/Profile.vue';
import UserAttendance from '../components/user/Attendance.vue';
import UserLeaveRequests from '../components/user/LeaveRequests.vue';

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
        meta: { title: 'HQ Inc. - Dashboard Page', requiresAuth: true }
    },
    // Admin Routes
    {
        path: '/employees',
        name: 'Employees',
        component: Employees,
        meta: { title: 'HQ Inc. - Employees Page', requiresAuth: true }
    },
    {
        path: '/attendance',
        name: 'Attendance',
        component: AdminAttendance,
        meta: { title: 'HQ Inc. - Attendance Page', requiresAuth: true }
    },
    {
        path: '/schedules',
        name: 'Schedules',
        component: Schedules,
        meta: { title: 'HQ Inc. - Schedules Page', requiresAuth: true }
    },
    {
        path: '/reports',
        name: 'Reports',
        component: Reports,
        meta: { title: 'HQ Inc. - Reports Page', requiresAuth: true }
    },
    {
        path: '/settings',
        name: 'Settings',
        component: Settings,
        meta: { title: 'HQ Inc. - Settings Page', requiresAuth: true }
    },
    // User Routes
    {
        path: '/profile',
        name: 'Profile',
        component: UserProfile,
        meta: { title: 'HQ Inc. - My Profile', requiresAuth: true }
    },
    {
        path: '/my-attendance',
        name: 'MyAttendance',
        component: UserAttendance,
        meta: { title: 'HQ Inc. - My Attendance', requiresAuth: true }
    },
    {
        path: '/leave-requests',
        name: 'LeaveRequests',
        component: UserLeaveRequests,
        meta: { title: 'HQ Inc. - Leave Requests', requiresAuth: true }
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

router.beforeEach(async (to, from, next) => {
    document.title = to.meta.title || 'HQ Inc. - APP';

    const authStore = useAuthStore();

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        // Try to fetch user to restore session if it exists (e.g. on refresh)
        await authStore.fetchUser();

        if (!authStore.isAuthenticated) {
            next('/login');
        } else {
            next();
        }
    } else if (to.path === '/login' && authStore.isAuthenticated) {
        next('/dashboard');
    } else {
        next();
    }
});

export default router;
