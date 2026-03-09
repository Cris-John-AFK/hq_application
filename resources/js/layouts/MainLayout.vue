<template>
    <div class="flex h-screen bg-gray-50">
        <!-- Sidebar -->
        <aside 
            class="w-72 bg-[#0f172a] text-slate-300 flex flex-col transition-all duration-300 transform fixed md:relative z-30 h-full border-r border-slate-800 shadow-xl" 
            :class="{ '-translate-x-full md:translate-x-0': !isSidebarOpen }"
        >
            <!-- Logo Area -->
            <router-link to="/dashboard" class="group flex items-center px-6 py-6 border-b border-slate-800 hover:bg-slate-800/10 transition-all duration-500">
                <div class="relative w-14 h-14 mr-4 flex items-center justify-center">
                    <!-- Subtle ambient glow for the logo -->
                    <div class="absolute inset-0 bg-teal-500/10 blur-xl rounded-full opacity-40"></div>
                    <img 
                        src="/logo_v2.svg" 
                        alt="HQ Logo" 
                        class="relative w-full h-auto object-contain filter brightness-0 invert opacity-90 group-hover:opacity-100 transition-all duration-300 scale-110"
                    />
                </div>
                <div class="relative">
                    <h1 class="text-2xl font-black text-white tracking-[-0.05em] leading-[0.8]">HATQ</h1>
                    <p class="text-[9px] font-black text-teal-500 uppercase tracking-[0.3em] mt-1.5 opacity-80 pl-0.5">Unified System</p>
                </div>
            </router-link>

            <!-- Navigation -->
            <div class="flex-1 relative overflow-hidden flex flex-col min-h-0 bg-transparent">
                <nav 
                    ref="navContainer"
                    class="flex-1 py-8 overflow-y-auto scrollbar-hide scroll-smooth"
                    @scroll="handleNavScroll"
                >
                    <div class="px-8 mb-6 flex flex-col gap-1">
                        <div class="text-[10px] font-black text-slate-500 uppercase tracking-[0.4em]">Navigator</div>
                        <div class="w-8 h-1 bg-slate-800 rounded-full"></div>
                    </div>
                    <ul class="space-y-2 px-4 pb-12">
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

                <!-- More Content Portal Indicator -->
                <div 
                    v-if="hasMoreNav"
                    class="absolute bottom-0 left-0 right-0 h-20 pointer-events-none bg-gradient-to-t from-[#0f172a] via-[#0f172a]/80 to-transparent z-10 flex flex-col items-center justify-end pb-2 overflow-hidden"
                >
                    <!-- Portal Ring Effect -->
                    <div class="absolute bottom-[-20px] w-32 h-32 bg-teal-500/20 rounded-full blur-2xl animate-pulse"></div>
                    <div class="absolute bottom-[-10px] w-16 h-4 bg-teal-400/30 rounded-[100%] blur-md animate-bounce"></div>
                    
                    <div class="relative flex flex-col items-center gap-1 opacity-80 mt-auto">
                        <span class="text-[9px] font-bold text-teal-400/80 uppercase tracking-[0.2em] animate-pulse">More Options</span>
                        <i class="pi pi-angle-double-down text-teal-400/60 text-xs animate-bounce"></i>
                    </div>
                </div>
            </div>

            <!-- Bottom Actions -->
            <div class="p-4 mt-auto border-t border-slate-800">
                <button 
                    @click="showLeaveModal = true" 
                    class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-teal-600 hover:bg-teal-700 text-white rounded-xl transition-all duration-200 font-bold text-sm shadow-lg shadow-teal-900/20 cursor-pointer mb-4 group"
                >
                    <i class="pi pi-calendar-plus group-hover:scale-110 transition-transform"></i>
                    <span>{{ user?.role === 'admin' ? 'File Employee Leave' : 'Request Leave' }}</span>
                </button>

                <!-- User Info (non-clickable) -->
                <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-800/40 border border-slate-700/50">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center text-sm font-bold text-white shadow-lg shadow-teal-900/20 ring-2 ring-slate-900 overflow-hidden">
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
                </div>
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


        <!-- Sticky Toasts Container -->
        <div class="fixed bottom-6 right-6 z-[100] flex flex-col gap-3 max-w-sm pointer-events-none">
            <transition-group name="toast-slide">
                <div 
                    v-for="note in unreadNotifications" 
                    :key="note.id"
                    class="pointer-events-auto bg-slate-800 text-white p-4 rounded-xl shadow-2xl border border-slate-700 flex gap-4 items-start w-80 relative overflow-hidden group"
                >
                    <div class="absolute inset-0 bg-gradient-to-br from-teal-500/10 to-purple-500/10 opacity-50"></div>
                    <!-- Icon -->
                    <div class="w-10 h-10 rounded-full bg-teal-500/20 flex items-center justify-center text-teal-400 shrink-0 relative z-10 border border-teal-500/30">
                        <i :class="['pi', note.type === 'info' ? 'pi-info-circle' : note.type === 'warning' ? 'pi-exclamation-triangle' : note.type === 'success' ? 'pi-check-circle' : 'pi-bell']"></i>
                    </div>
                    <!-- Content -->
                    <div class="flex-1 min-w-0 relative z-10">
                        <p class="text-sm font-black text-white leading-tight mb-1">{{ note.title }}</p>
                        <p class="text-[11px] text-slate-300 leading-snug mb-2">{{ note.message }}</p>
                        <div class="flex items-center gap-3 mt-1">
                            <router-link v-if="note.link" :to="note.link" @click="markNotificationRead(note.id)" class="text-[10px] font-bold text-teal-400 uppercase tracking-widest hover:text-teal-300 transition-colors">
                                View Details
                            </router-link>
                            <button @click="markNotificationRead(note.id)" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest hover:text-white transition-colors">
                                Dismiss
                            </button>
                        </div>
                    </div>
                </div>
            </transition-group>
        </div>

        <!-- Leave Request Modal -->
        <LeaveRequestModal 
            v-model="showLeaveModal"
            :isAdminMode="user?.role === 'admin'"
            @submit="handleLeaveSubmit"
        />

    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useSettingsStore } from '../stores/settings';
import { useAuthStore } from '../stores/auth';
import { useRoute, useRouter } from 'vue-router';
import { useCalendarStore } from '../stores/calendar';
import { storeToRefs } from 'pinia';
import EventCalendar from '../components/common/EventCalendar.vue';
import ScrollIndicator from '../components/common/ScrollIndicator.vue';
import LeaveRequestModal from '../components/common/LeaveRequestModal.vue';
import { useLeaveStore } from '../stores/leaves';
import axios from 'axios';

const props = defineProps({
    user: Object
});

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const calendarStore = useCalendarStore();
const leaveStore = useLeaveStore();

const { events: allEvents } = storeToRefs(calendarStore);
const title = computed(() => route.meta.title?.split(' - ').pop() || '');

const settings = useSettingsStore();
const isSidebarOpen = ref(false);
const isCalendarOpen = ref(false);
const showLeaveModal = ref(false);
const unreadEventsCount = ref(0);
const currentTime = ref('');

// Portal indicator logic
const navContainer = ref(null);
const hasMoreNav = ref(false);

const handleNavScroll = () => {
    if (!navContainer.value) return;
    const { scrollTop, scrollHeight, clientHeight } = navContainer.value;
    // Show portal if there's more than 20px of content left to scroll
    hasMoreNav.value = scrollHeight > clientHeight && (scrollTop + clientHeight < scrollHeight - 20);
};

const checkNavOverflow = () => {
    // Check after a small delay to ensure DOM is ready
    setTimeout(() => {
        handleNavScroll();
    }, 500);
};

let timeInterval;
let unreadInterval;
let notifInterval;

const unreadNotifications = ref([]);

const fetchNotifications = async () => {
    try {
        const res = await axios.get('/api/notifications');
        // Filter only unread
        unreadNotifications.value = res.data.filter(n => !n.is_read);
    } catch (e) {
        console.error('Failed to fetch notifications', e);
    }
};

const markNotificationRead = async (id) => {
    try {
        await axios.put(`/api/notifications/${id}/read`);
        unreadNotifications.value = unreadNotifications.value.filter(n => n.id !== id);
    } catch (e) {
        console.error('Failed to mark notification read', e);
    }
};

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

const handleLeaveSubmit = async (payload, resolve, reject) => {
    try {
        const formData = new FormData();
        Object.keys(payload).forEach(key => {
            const val = payload[key];
            if (val !== null && val !== undefined) {
                if (key === 'additional_details' && typeof val === 'object' && !Array.isArray(val)) {
                    Object.entries(val).forEach(([subKey, subVal]) => {
                        formData.append(`${key}[${subKey}]`, subVal);
                    });
                } else {
                    const value = typeof val === 'boolean' ? (val ? 1 : 0) : val;
                    formData.append(key, value);
                }
            }
        });
        
        await axios.post('/api/leave-requests', formData);

        // Refresh relevant data
        calendarStore.fetchEvents();
        if (typeof leaveStore.fetchStats === 'function') {
            leaveStore.fetchStats(true);
        }
        leaveStore.bumpAnalytics(); // triggers LeaveAnalytics to re-fetch

        resolve?.(); // Signal modal to close
    } catch (e) {
        const msg = e.response?.data?.message || 'Server error. Please try again.';
        console.error('Leave submission failed', e);
        reject?.(msg); // Send error message back to modal
    }
};


onMounted(() => {
    updateTime();
    timeInterval = setInterval(updateTime, 1000);
    
    // DELAY non-critical background data to free up the network for the main page content
    setTimeout(() => {
        calendarStore.fetchEvents();
        checkNavOverflow();
        
        if (props.user?.role === 'admin') {
            fetchUnreadEventsCount();
            fetchNotifications();
            unreadInterval = setInterval(fetchUnreadEventsCount, 30000);
            notifInterval = setInterval(fetchNotifications, 15000); // Check every 15s
        }
    }, 1500); // 1.5s delay gives priority to the actual page data (like Inventory)

    window.addEventListener('resize', handleNavScroll);
});

onUnmounted(() => {
    if (timeInterval) clearInterval(timeInterval);
    if (unreadInterval) clearInterval(unreadInterval);
    if (notifInterval) clearInterval(notifInterval);
    window.removeEventListener('resize', handleNavScroll);
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
        // Date range must cover today
        if (!(event.from_date <= today && event.to_date >= today)) return false;
        // For leave events: only show if status is Approved (or no status = custom calendar event)
        if (event.type === 'leave' && event.status && event.status !== 'Approved') return false;
        return true;
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
    if (!isAdmin) {
        const items = [
            { label: 'Dashboard', icon: 'pi-home', href: '/dashboard' },
            { label: 'Leave Requests', icon: 'pi-calendar-times', href: '/leave-requests' },
        ];
        if (props.user?.role === 'dept_head') {
            items.push({ label: 'Analytics', icon: 'pi-chart-line', href: '/dept-analytics' });
        }
        return items;
    }

    const items = [
        { label: 'Dashboard', icon: 'pi-objects-column', href: '/dashboard' },
        { label: 'Employees', icon: 'pi-users', href: '/employees' },
    ];

    items.push(
        { label: 'Attendance', icon: 'pi-calendar-clock', href: '/attendance' },
        { label: 'Calendar', icon: 'pi-calendar', href: '/schedules' },
        { label: 'Reports', icon: 'pi-chart-bar', href: '/reports' },
    );

    items.push(
        { label: 'Manage Leaves', icon: 'pi-calendar-times', href: '/manage-leaves' },
        { label: 'Assets', icon: 'pi-box', href: '/inventory' },
        { label: 'Activity Logs', icon: 'pi-list', href: '/activity-logs' },
        { label: 'Archive Registry', icon: 'pi-folder', href: '/archive-leaves' },
    );

    return items;
});
</script>

<style scoped>
/* Custom subtle scrollbar for the dark sidebar */
nav::-webkit-scrollbar {
    width: 4px;
}

nav::-webkit-scrollbar-track {
    background: transparent;
}

nav::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}

nav::-webkit-scrollbar-thumb:hover {
    background: rgba(20, 184, 166, 0.3); /* Teal hover state */
}

/* Hide scrollbar for Chrome, Safari and Opera */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

/* Hide scrollbar for IE, Edge and Firefox */
.scrollbar-hide {
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}


/* Toast Slide Animation */
.toast-slide-enter-active,
.toast-slide-leave-active {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.toast-slide-enter-from {
    opacity: 0;
    transform: translateX(100px) scale(0.9);
}
.toast-slide-leave-to {
    opacity: 0;
    transform: scale(0.9) translateY(20px);
}
</style>
