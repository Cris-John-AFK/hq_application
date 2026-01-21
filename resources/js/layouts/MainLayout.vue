<template>
    <div class="flex h-screen bg-gray-50">
        <!-- Sidebar -->

        <aside 
            class="w-72 bg-[#0f172a] text-slate-300 flex flex-col transition-all duration-300 transform fixed md:relative z-30 h-full border-r border-slate-800 shadow-xl" 
            :class="{ '-translate-x-full md:translate-x-0': !isSidebarOpen }"
        >
            <!-- Logo Area -->
            <router-link to="/dashboard" class="h-16 flex items-center px-8 border-b border-slate-800 hover:bg-slate-800/30 transition-colors group">
                <div class="relative w-10 h-10 mr-4 transform transition-transform group-hover:scale-105">
                    <!-- Placeholder Logo: Replace src with your actual logo path (e.g., /images/logo.png) -->
                    <img 
                        src="https://placehold.co/100x100/14b8a6/ffffff?text=HQ" 
                        alt="HQ Logo" 
                        class="w-full h-full object-cover rounded-xl shadow-lg shadow-teal-500/20"
                    />
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white tracking-tight">HQ App</h1>
                    <p class="text-[10px] font-semibold text-teal-500 uppercase tracking-widest leading-none mt-0.5">Management</p>
                </div>
            </router-link>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-8">
                <div class="px-6 mb-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Main Menu</div>
                <ul class="space-y-2 px-4">
                    <li v-for="item in menuItems" :key="item.label">
                        <router-link 
                            :to="item.href" 
                            class="relative flex items-center gap-3 px-4 py-3.5 rounded-xl transition-all duration-200 group font-medium text-sm" 
                            active-class="bg-gradient-to-r from-teal-600/10 to-transparent text-teal-400"
                            :class="[
                                $route.path === item.href 
                                    ? '' 
                                    : 'hover:bg-slate-800/50 hover:text-white'
                            ]"
                        >
                            <!-- Active Indicator Border -->
                            <span 
                                v-if="$route.path === item.href" 
                                class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-teal-500 rounded-r-full shadow-[0_0_10px_rgba(20,184,166,0.5)]"
                            ></span>
                            
                            <i :class="[`pi ${item.icon} text-lg transition-colors`, $route.path === item.href ? 'text-teal-400' : 'text-slate-500 group-hover:text-teal-400']"></i>
                            <span class="relative z-10">{{ item.label }}</span>
                        </router-link>
                    </li>
                </ul>
            </nav>

            <!-- User Profile -->
            <router-link to="/profile">
            <div class="p-4 border-t border-slate-800 bg-[#0f172a]">
                <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-800/40 border border-slate-700/50 hover:bg-slate-800/80 transition-colors cursor-pointer group">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center text-sm font-bold text-white shadow-lg shadow-teal-900/20 ring-2 ring-slate-900 group-hover:ring-teal-500/30 transition-all overflow-hidden">
                        <img v-if="user?.avatar_url" :src="user.avatar_url" alt="Profile" class="w-full h-full object-cover">
                        <span v-else>{{ user?.name?.charAt(0) || 'U' }}</span>
                    </div>
                    <div class="overflow-hidden flex-1">
                        <p class="text-sm font-bold text-white truncate">{{ user?.name }}</p>
                        <p class="text-xs text-slate-400 capitalize flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                            {{ user?.role }}
                        </p>
                    </div>
                    <i class="pi pi-chevron-right text-xs text-slate-500 group-hover:text-white transition-colors"></i>
                </div>
            </div>
            </router-link>
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

                <!-- Clock and Right Actions -->
                <div class="flex items-center gap-6">
                    <!-- Real-time Clock -->
                    <div class="hidden md:flex items-center gap-2 text-teal-600 font-medium bg-teal-50 px-3 py-1.5 rounded-lg border border-teal-100 shadow-sm">
                        <i class="pi pi-clock text-sm"></i>
                        <span class="tabular-nums text-sm font-bold tracking-tight">{{ currentTime }}</span>
                    </div>
                    <!-- Calendar -->
                    <div class="flex items-center gap-4">
                        <button 
                            @click="toggleCalendar"
                            class="relative p-2 text-gray-400 hover:text-gray-600 transition-colors cursor-pointer"
                        >
                            <i class="pi pi-calendar text-lg"></i>
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

        <!-- Calendar Modal -->
        <div 
            v-if="isCalendarOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            @click.self="isCalendarOpen = false"
        >
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col animate-in fade-in zoom-in-95 duration-200">
                <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2">
                        <i class="pi pi-calendar text-teal-600"></i>
                        Calendar & Events
                    </h3>
                    <button 
                        @click="isCalendarOpen = false"
                        class="p-2 hover:bg-gray-200 rounded-full transition-colors text-gray-500"
                    >
                        <i class="pi pi-times"></i>
                    </button>
                </div>
                
                <div class="p-6 overflow-y-auto">
                    <EventCalendar />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { ref, computed, onMounted, onUnmounted } from 'vue';
    import { useAuthStore } from '../stores/auth';
    import { useRoute } from 'vue-router';
    import EventCalendar from '../components/common/EventCalendar.vue';

    const route = useRoute();
    const title = computed(() => route.meta.title?.split(' - ').pop() || '');
    
    // Time Logic
    const currentTime = ref('');
    let timeInterval;

    const updateTime = () => {
        const now = new Date();
        currentTime.value = now.toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit',
            hour12: true 
        });
    };

    onMounted(() => {
        updateTime();
        timeInterval = setInterval(updateTime, 1000);
    });

    onUnmounted(() => {
        if (timeInterval) clearInterval(timeInterval);
    });

    const props = defineProps({
        user: Object
    });

    const authStore = useAuthStore();
    const isSidebarOpen = ref(false); // Default logic: hidden on mobile, effectively ignored on desktop by CSS
    const isCalendarOpen = ref(false);

    const toggleCalendar = () => {
        isCalendarOpen.value = !isCalendarOpen.value;
    };

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
                { label: 'My Attendance', icon: 'pi-clock', href: '/my-attendance' },
                { label: 'Leave Requests', icon: 'pi-calendar-plus', href: '/leave-requests' },
            ];
        }
    });
</script>
