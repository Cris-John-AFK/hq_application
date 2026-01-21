<template>
    <div class="flex h-screen bg-gray-50">
        <!-- Sidebar -->
        <aside class="w-64 bg-slate-900 text-white flex flex-col transition-all duration-300 transform fixed md:relative z-30 h-full" :class="{ '-translate-x-full md:translate-x-0': !isSidebarOpen }">
            <a href="/dashboard" class="p-6 flex items-center gap-3 border-b border-slate-800 hover:bg-slate-800/50 transition-colors">
                <div class="w-8 h-8 bg-teal-500 rounded-lg flex items-center justify-center font-bold text-white">HQ</div>
                <h1 class="text-xl font-bold tracking-wider">HQ App</h1>
            </a>

            <nav class="flex-1 overflow-y-auto py-6">
                <ul class="space-y-1 px-4">
                    <li v-for="item in menuItems" :key="item.label">
                        <router-link :to="item.href" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-colors group" active-class="bg-slate-800 text-white">
                            <i :class="`pi ${item.icon} text-slate-400 group-hover:text-teal-400`"></i>
                            <span class="font-medium">{{ item.label }}</span>
                        </router-link>
                    </li>
                </ul>
            </nav>

            <div class="p-4 border-t border-slate-800">
                <div class="flex items-center gap-3 px-4 py-3 rounded-lg bg-slate-800/50">
                    <div class="w-8 h-8 rounded-full bg-teal-500 flex items-center justify-center text-sm font-bold">
                        {{ user?.name?.charAt(0) || 'U' }}
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-sm font-medium truncate">{{ user?.name }}</p>
                        <p class="text-xs text-slate-400 capitalize">{{ user?.role }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col h-screen overflow-hidden">
            <!-- Topbar -->
            <header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-6 shadow-sm z-20">
                <button @click="isSidebarOpen = !isSidebarOpen" class="md:hidden text-gray-500 hover:text-gray-700">
                    <i class="pi pi-bars text-xl"></i>
                </button>

                <!-- Breadcrumbs / Title Placeholder -->
                <div class="hidden md:flex items-center gap-2 text-sm text-gray-500">
                    <span>{{ title }}</span>
                </div>

                <!-- Right Actions -->
                <div class="flex items-center gap-4">
                    <button class="relative p-2 text-gray-400 hover:text-gray-600 transition-colors cursor-pointer">
                        <i class="pi pi-bell text-lg"></i>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full border border-white"></span>
                    </button>
                    
                    <div class="w-px h-6 bg-gray-200"></div>

                    <button 
                        @click="handleLogout" 
                        class="cursor-pointer flex items-center gap-2 text-gray-600 hover:text-red-600 transition-colors text-sm font-medium"
                    >
                        <span>Logout</span>
                        <i class="pi pi-sign-out"></i>
                    </button>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto p-6 md:p-8 bg-gray-50">
                <slot></slot>
            </main>
        </div>

        <!-- Mobile Overlay -->
        <div 
            v-if="isSidebarOpen" 
            @click="isSidebarOpen = false"
            class="fixed inset-0 bg-black/50 z-20 md:hidden"
        ></div>
    </div>
</template>

<script setup>
    import { ref, computed } from 'vue';
    import { useAuthStore } from '../stores/auth';
    import { useRoute } from 'vue-router';

    const route = useRoute();
    const title = computed(() => route.meta.title?.split(' - ').pop() || '');

    const props = defineProps({
        user: Object
    });

    const authStore = useAuthStore();
    const isSidebarOpen = ref(false); // Default logic: hidden on mobile, effectively ignored on desktop by CSS

    const handleLogout = () => {
        authStore.logout();
    };

    const menuItems = computed(() => {
        if (props.user?.role === 'admin') {
            return [
                { label: 'Dashboard', icon: 'pi-objects-column', href: '/dashboard' },
                { label: 'Employees', icon: 'pi-users', href: '/employees' },
                { label: 'Attendance', icon: 'pi-calendar-clock', href: '/attendance' },
                { label: 'Schedules', icon: 'pi-calendar', href: '/schedules' },
                { label: 'Reports', icon: 'pi-chart-bar', href: '/reports' },
                { label: 'Settings', icon: 'pi-cog', href: '/settings' },
            ];
        } else {
            return [
                { label: 'Dashboard', icon: 'pi-home', href: '/dashboard' },
                { label: 'My Profile', icon: 'pi-user', href: '/profile' },
                { label: 'My Attendance', icon: 'pi-clock', href: '/attendance' },
                { label: 'Leave Requests', icon: 'pi-calendar-plus', href: '/leave-requests' },
            ];
        }
    });
</script>
