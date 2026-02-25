import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import Dashboard from '../components/Dashboard.vue';
import Employees from '../components/admin/Employees.vue';
import AdminAttendance from '../components/admin/Attendance.vue';
import Schedules from '../components/admin/Schedules.vue';
import Reports from '../components/admin/Reports.vue';
import ManageLeaves from '../components/admin/ManageLeaves.vue';
import ArchiveLeaves from '../components/admin/ArchiveLeaves.vue';
import Inventory from '../components/admin/Inventory.vue';
import Settings from '../components/admin/Settings.vue';
import LoginForm from '../components/login/loginForm.vue';
import SystemLogs from '../pages/SystemLogs.vue';

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
        meta: { title: 'HQ Inc. - Employees Page', requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/attendance',
        name: 'Attendance',
        component: AdminAttendance,
        meta: { title: 'HQ Inc. - Attendance Page', requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/schedules',
        name: 'Schedules',
        component: Schedules,
        meta: { title: 'HQ Inc. - Schedules Page', requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/reports',
        name: 'Reports',
        component: Reports,
        meta: { title: 'HQ Inc. - Reports Page', requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/manage-leaves',
        name: 'ManageLeaves',
        component: ManageLeaves,
        meta: { title: 'HQ Inc. - Manage Leaves', requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/archive-leaves',
        name: 'ArchiveLeaves',
        component: ArchiveLeaves,
        meta: { title: 'HQ Inc. - Archive Registry', requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/inventory',
        name: 'Inventory',
        component: Inventory,
        meta: { title: 'HQ Inc. - Audit Inventory', requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/activity-logs',
        name: 'SystemLogs',
        component: SystemLogs,
        meta: { title: 'HQ Inc. - System Logs', requiresAuth: true, requiresAdmin: true }
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
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

router.beforeEach(async (to, from, next) => {
    document.title = to.meta.title || 'HQ Inc. - APP';

    const authStore = useAuthStore();

    // 1. Check Authentication
    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        await authStore.fetchUser();

        if (!authStore.isAuthenticated) {
            next('/login');
            return;
        }
    }

    // 2. Redirect logged-in users away from login page
    if (to.path === '/login' && authStore.isAuthenticated) {
        next('/dashboard');
        return;
    }

    // 3. Role-Based Access Control (RBAC) Implementation
    if (to.meta.requiresAdmin) {
        // Ensure user data is loaded (double check)
        if (!authStore.user) {
            await authStore.fetchUser();
        }

        // Check if user is Admin
        if (authStore.user?.role !== 'admin') {
            console.warn(`Unauthorized access attempt to ${to.path} by user ${authStore.user?.name}`);
            next('/dashboard'); // or redirect to a 403 Forbidden page
            return;
        }
    }

    next();
});

export default router;
