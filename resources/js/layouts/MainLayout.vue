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
                    <img 
                        src="/logo.png" 
                        alt="HQ Logo" 
                        class="w-full h-full object-cover rounded-xl shadow-lg shadow-teal-500/20"
                    />
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white tracking-tight">HatQ Inc.</h1>
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

            <!-- Bottom Actions -->
            <div class="p-4 mt-auto border-t border-slate-800">
                <button 
                    @click="router.push('/leave-requests')" 
                    class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-teal-600 hover:bg-teal-700 text-white rounded-xl transition-all duration-200 font-bold text-sm shadow-lg shadow-teal-900/20 cursor-pointer mb-4 group"
                >
                    <i class="pi pi-calendar-plus group-hover:scale-110 transition-transform"></i>
                    <span>Request Leave</span>
                </button>

                <!-- User Profile -->
                <router-link to="/profile">
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
                </router-link>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col h-screen overflow-hidden">
            <!-- Topbar -->
            <header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-6 shadow-sm z-20">
                <button @click="isSidebarOpen = !isSidebarOpen" class="md:hidden text-gray-500 hover:text-gray-700">
                    <i class="pi pi-bars text-xl cursor-pointer"></i>
                </button>

                <!-- Breadcrumbs -->
                <div class="hidden md:flex items-center gap-2 text-sm text-gray-500">
                    <span>{{ title }}</span>
                </div>

                <!-- Right Actions -->
                <div class="flex items-center gap-6">
                    <!-- Real-time Clock -->
                    <div class="hidden md:flex items-center gap-2 text-teal-600 font-medium bg-teal-50 px-3 py-1.5 rounded-lg border border-teal-100 shadow-sm">
                        <i class="pi pi-clock text-sm"></i>
                        <span class="tabular-nums text-sm font-bold tracking-tight">{{ currentTime }}</span>
                    </div>

                    <!-- Calendar Toggle -->
                    <div v-if="user?.role === 'admin'" class="flex items-center gap-4">
                        <button 
                            @click="toggleCalendar"
                            class="relative p-1 bg-gray-50 border border-gray-200 rounded-lg text-gray-400 hover:text-gray-600 transition-colors cursor-pointer"
                        >
                            <i class="pi pi-calendar text-lg"></i>
                            <span class="ml-2 text-sm font-medium text-gray-600">Calendar</span>
                            <span v-if="unreadEventsCount > 0" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center shadow-lg animate-pulse">
                                {{ unreadEventsCount }}
                            </span>
                        </button>
                        <div class="w-px h-6 bg-gray-200"></div>
                    </div>

                    <!-- Notifications -->
                    <NotificationDropdown />

                    <div class="w-px h-6 bg-gray-200"></div>

                    <!-- Logout -->
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

        <!-- Calendar Modal -->
        <div 
            v-if="isCalendarOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            @click.self="isCalendarOpen = false"
        >
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col animate-in fade-in zoom-in-95 duration-200">
                <!-- Modal Header -->
                <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2">
                        <i class="pi pi-calendar text-teal-600"></i>
                        Calendar &amp; Events
                    </h3>
                    <div class="flex items-center gap-3">
                        <router-link 
                            v-if="user?.role === 'admin'"
                            to="/schedules" 
                            @click="isCalendarOpen = false"
                            class="text-xs font-bold text-teal-600 hover:text-teal-700 underline flex items-center gap-1"
                        >
                            View Full Calendar
                            <i class="pi pi-external-link text-[10px]"></i>
                        </router-link>
                        <div v-if="user?.role === 'admin'" class="w-px h-4 bg-gray-200"></div>
                        <button 
                            @click="isCalendarOpen = false"
                            class="p-2 hover:bg-gray-200 rounded-full transition-colors text-gray-500"
                        >
                            <i class="pi pi-times"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Modal Body -->
                <div class="p-6 overflow-y-auto">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Left: Calendar Component -->
                        <div>
                            <EventCalendar :events="allEvents" />
                        </div>

                        <!-- Right: Today's Schedule -->
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                            <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <i class="pi pi-list text-teal-600"></i>
                                Today's Schedule
                            </h4>

                            <div class="mb-4 pb-3 border-b border-gray-200">
                                <p class="text-sm text-gray-500">{{ todayFormatted }}</p>
                            </div>

                            <div class="space-y-3">
                                <div v-if="todaySchedule.length === 0" class="text-center py-8 text-gray-400">
                                    <i class="pi pi-calendar-times text-3xl mb-2"></i>
                                    <p class="text-sm">No events scheduled for today</p>
                                </div>

                                <div 
                                    v-for="item in todaySchedule" 
                                    :key="item.id"
                                    class="flex items-start gap-3 p-3 bg-white rounded-lg border border-gray-200 hover:border-teal-300 transition-colors"
                                >
                                    <div 
                                        :class="[
                                            'w-2 h-2 rounded-full mt-1.5 flex-shrink-0',
                                            item.type === 'leave' ? 'bg-orange-500' : 'bg-teal-500'
                                        ]"
                                    ></div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-800 text-sm">{{ item.title }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ item.description }}</p>
                                        
                                        <div class="flex flex-wrap gap-1.5 mt-1.5 mb-1">
                                            <span v-if="item.id_number" class="text-[10px] font-mono bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded border border-gray-200">{{ item.id_number }}</span>
                                            <span v-if="item.department" class="text-[10px] bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded border border-blue-100">{{ item.department }}</span>
                                            <span v-if="item.position" class="text-[10px] bg-purple-50 text-purple-600 px-1.5 py-0.5 rounded border border-purple-100">{{ item.position }}</span>
                                        </div>

                                        <div class="flex items-center gap-2 mt-1">
                                            <span 
                                                :class="[
                                                    'text-xs px-2 py-0.5 rounded-full font-medium',
                                                    item.type === 'leave' 
                                                        ? 'bg-orange-100 text-orange-700' 
                                                        : 'bg-teal-100 text-teal-700'
                                                ]"
                                            >
                                                {{ item.type === 'leave' ? 'Leave' : 'Event' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Global Scroll Indicator -->
        <ScrollIndicator />

    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRoute, useRouter } from 'vue-router';
import { useCalendarStore } from '../stores/calendar';
import { storeToRefs } from 'pinia';
import EventCalendar from '../components/common/EventCalendar.vue';
import ScrollIndicator from '../components/common/ScrollIndicator.vue';
import NotificationDropdown from '../components/common/NotificationDropdown.vue';
import axios from 'axios';

const props = defineProps({
    user: Object
});

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const calendarStore = useCalendarStore();

const { events: allEvents } = storeToRefs(calendarStore);
const title = computed(() => route.meta.title?.split(' - ').pop() || '');

const isSidebarOpen = ref(false);
const isCalendarOpen = ref(false);
const unreadEventsCount = ref(0);
const currentTime = ref('');

let timeInterval;
let unreadInterval;

const updateTime = () => {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit',
        hour12: true 
    });
};

const fetchUnreadEventsCount = async () => {
    try {
        const response = await axios.get('/api/custom-events');
        unreadEventsCount.value = response.data.unread_count || 0;
    } catch (error) {
        console.error('Failed to fetch unread events count:', error);
    }
};

const toggleCalendar = async () => {
    isCalendarOpen.value = !isCalendarOpen.value;
    if (isCalendarOpen.value) {
        calendarStore.fetchEvents();
        if (unreadEventsCount.value > 0) {
            try {
                await axios.post('/api/custom-events/mark-read');
                unreadEventsCount.value = 0;
            } catch (error) {
                console.error('Failed to mark events as read:', error);
            }
        }
    }
};

const handleLogout = () => {
    authStore.logout();
};

onMounted(() => {
    updateTime();
    timeInterval = setInterval(updateTime, 1000);
    calendarStore.fetchEvents();
    
    if (props.user?.role === 'admin') {
        fetchUnreadEventsCount();
        unreadInterval = setInterval(fetchUnreadEventsCount, 30000);
    }
});

onUnmounted(() => {
    if (timeInterval) clearInterval(timeInterval);
    if (unreadInterval) clearInterval(unreadInterval);
});

const todayFormatted = computed(() => {
    const today = new Date();
    return today.toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
});

const todaySchedule = computed(() => {
    const today = new Date().toISOString().split('T')[0];
    return allEvents.value.filter(event => {
        return event.from_date <= today && event.to_date >= today;
    }).map(event => {
        if (event.type === 'leave') {
            return {
                id: event.id,
                type: 'leave',
                title: event.title,
                description: event.request_type === 'Leave' ? 'Full Day Leave' : `${event.request_type}`,
                id_number: event.user_id_number,
                department: event.user_department,
                position: event.user_position
            };
        } else {
            return {
                id: event.id,
                type: event.type,
                title: event.title,
                description: event.description || 'Company Event',
                id_number: null,
                department: null,
                position: null
            };
        }
    });
});

const menuItems = computed(() => {
    const isAdmin = props.user?.role === 'admin';
    return isAdmin ? [
        { label: 'Dashboard', icon: 'pi-objects-column', href: '/dashboard' },
        { label: 'Employees', icon: 'pi-users', href: '/employees' },
        { label: 'Attendance', icon: 'pi-calendar-clock', href: '/attendance' },
        { label: 'Calendar', icon: 'pi-calendar', href: '/schedules' },
        { label: 'Reports', icon: 'pi-chart-bar', href: '/reports' },
        { label: 'Manage Leaves', icon: 'pi-calendar-times', href: '/manage-leaves' },
        { label: 'My Leaves', icon: 'pi-calendar-plus', href: '/leave-requests' },
        { label: 'Activity Logs', icon: 'pi-list', href: '/activity-logs' },
    ] : [
        { label: 'Dashboard', icon: 'pi-home', href: '/dashboard' },
        { label: 'My Profile', icon: 'pi-user', href: '/profile' },
        { label: 'My Attendance', icon: 'pi-clock', href: '/my-attendance' },
        { label: 'Leave Requests', icon: 'pi-calendar-plus', href: '/leave-requests' },
    ];
});
</script>
