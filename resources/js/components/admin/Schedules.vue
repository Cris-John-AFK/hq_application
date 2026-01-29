<template>
    <div v-if="user">
        <MainLayout :user="user">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Header -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Calendar Activities</h1>
                        <p class="text-sm text-gray-500 mt-1">Manage leaves, holidays, and company events.</p>
                    </div>

                    <div class="flex items-center gap-3">
                         <button 
                            @click="isAddEventOpen = true"
                            class="cursor-pointer flex items-center gap-2 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg transition-colors font-medium text-sm shadow-sm"
                        >
                            <i class="pi pi-plus"></i>
                            Add Event
                        </button>
                    
                        <div class="flex items-center gap-3 bg-white p-1 rounded-lg border border-gray-200 shadow-sm ml-2">
                            <button @click="changeMonth(-1)" class="cursor-pointer p-2 hover:bg-gray-100 rounded-md text-gray-500 transition-colors">
                                <i class="pi pi-chevron-left"></i>
                            </button>
                            <span class="min-w-[140px] text-center font-bold text-gray-800 text-lg">{{ currentMonthName }} {{ currentYear }}</span>
                            <button @click="changeMonth(1)" class="cursor-pointer p-2 hover:bg-gray-100 rounded-md text-gray-500 transition-colors">
                                <i class="pi pi-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Legend -->
                <div class="flex flex-wrap gap-4 mb-6">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-violet-100 border border-violet-400"></span>
                        <span class="text-xs font-semibold text-gray-700">Leave</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-rose-100 border border-rose-400"></span>
                        <span class="text-xs font-semibold text-gray-700">Holiday</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-sky-100 border border-sky-400"></span>
                        <span class="text-xs font-semibold text-gray-700">Event</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-amber-100 border border-amber-400"></span>
                        <span class="text-xs font-semibold text-gray-700">Special Non-Working</span>
                    </div>
                </div>

                <!-- Calendar Grid -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <!-- Weekday Headers -->
                    <div class="grid grid-cols-7 border-b border-gray-200 bg-gray-50/50">
                        <div v-for="day in weekDays" :key="day" class="py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            {{ day }}
                        </div>
                    </div>
                    
                    <!-- Days -->
                    <div class="grid grid-cols-7 auto-rows-fr bg-gray-200 gap-px border-b border-gray-200">
                        <div 
                            v-for="(date, index) in calendarDays" 
                            :key="index" 
                            @click="openDayDetails(date)"
                            class="min-h-[120px] bg-white p-2 relative group hover:bg-gray-50/50 transition-colors cursor-pointer"
                            :class="{'bg-gray-50/30 text-gray-400': date.isPadding, 'bg-blue-50/30': date.isToday}"
                        >
                            <div class="flex justify-between items-start mb-1">
                                <span :class="[
                                    'text-sm font-medium w-7 h-7 flex items-center justify-center rounded-full',
                                    date.isToday ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-700'
                                ]">
                                    {{ date.day }}
                                </span>
                            </div>

                            <!-- Events List -->
                            <div class="space-y-1.5 overflow-y-auto max-h-[90px] pr-1 custom-scrollbar">
                                <!-- Holidays & Custom Events -->
                                <div v-for="evt in date.customEvents" :key="evt.id" 
                                    :class="[
                                        'px-2 py-1 rounded text-[10px] font-medium border truncate',
                                        getEventClass(evt)
                                    ]"
                                    :title="evt.title || evt.name"
                                >
                                    <span v-if="evt.type === 'holiday' || evt.type === 'Regular Holiday' || evt.type === 'Special Non-Working'">🎉</span>
                                    <span v-else>📅</span>
                                    {{ evt.title || evt.name }}
                                </div>

                                <!-- Leaves -->
                                <div v-for="event in date.events" :key="event.id" 
                                    class="px-2 py-1 rounded text-[10px] font-bold bg-violet-100 text-violet-900 border border-violet-200 truncate hover:bg-violet-200 transition-colors"
                                    :title="`${event.user_name} - ${event.leave_type}`"
                                >
                                    👤 {{ event.user_name }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modals -->
            <AddEventModal 
                v-model="isAddEventOpen" 
                @success="refreshCalendar"
            />
            
            <DayDetailsModal
                v-model="isDayDetailsOpen"
                :date="selectedDate"
                @delete="refreshCalendar"
            />

        </MainLayout>
    </div>
</template>

<script setup>
    import { ref, computed, onMounted, watch } from 'vue';
    import axios from 'axios';
    import { useAuthStore } from '../../stores/auth';
    import { useCalendarStore } from '../../stores/calendar';
    import { storeToRefs } from 'pinia';
    import MainLayout from '../../layouts/MainLayout.vue';
    import { getHolidays } from '../../utils/holidays';
    import AddEventModal from './AddEventModal.vue';
    import DayDetailsModal from './DayDetailsModal.vue';

    const authStore = useAuthStore();
    const calendarStore = useCalendarStore();
    const { user } = storeToRefs(authStore);
    const { events: allLeaves } = storeToRefs(calendarStore);

    const isAddEventOpen = ref(false);
    const isDayDetailsOpen = ref(false);
    const selectedDate = ref(null);

    const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    const currentDate = ref(new Date());

    const currentYear = computed(() => currentDate.value.getFullYear());
    const currentMonth = computed(() => currentDate.value.getMonth());
    const currentMonthName = computed(() => currentDate.value.toLocaleString('default', { month: 'long' }));

    const phHolidays = computed(() => getHolidays(currentYear.value));

    // Unified fetch using store
    const refreshCalendar = async () => {
        await calendarStore.fetchEvents(true);
    };

    const getEventClass = (evt) => {
        // Handle both PH holidays (static) and Custom Events (dynamic)
        const type = evt.type;
        if (type === 'Regular Holiday' || type === 'holiday') return 'bg-rose-100 text-rose-900 border-rose-200';
        if (type === 'Special Non-Working') return 'bg-amber-100 text-amber-900 border-amber-200';
        if (type === 'meeting') return 'bg-indigo-100 text-indigo-900 border-indigo-200';
        return 'bg-sky-100 text-sky-900 border-sky-200'; // Default event
    };

    const calendarDays = computed(() => {
        const year = currentYear.value;
        const month = currentMonth.value;
        
        const firstDayOfMonth = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const daysInPrevMonth = new Date(year, month, 0).getDate();
        
        const days = [];
        
        // Previous month padding
        for (let i = firstDayOfMonth - 1; i >= 0; i--) {
            days.push({
                day: daysInPrevMonth - i,
                isPadding: true,
                isToday: false,
                events: [],
                customEvents: [],
                fullDate: null
            });
        }
        
        const today = new Date();
        for (let i = 1; i <= daysInMonth; i++) {
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
            
            // Filter Leaves
            const dayLeaves = allLeaves.value.filter(e => {
                return e.type === 'leave' && e.from_date <= dateStr && e.to_date >= dateStr;
            });

            // Filter Custom Events (Holidays, Meetings, Company Events from DB)
            const dayCustom = allLeaves.value.filter(e => {
                return e.type !== 'leave' && e.from_date <= dateStr && e.to_date >= dateStr;
            });

            // Filter PH Holidays (Static list)
            const dayStaticHolidays = phHolidays.value.filter(h => h.date === dateStr);
            
            const mergedEvents = [...dayStaticHolidays, ...dayCustom];

            const isToday = 
                i === today.getDate() && 
                month === today.getMonth() && 
                year === today.getFullYear();

            days.push({
                day: i,
                isPadding: false,
                isToday: isToday,
                events: dayLeaves,
                customEvents: mergedEvents,
                fullDate: dateStr
            });
        }
        
        // Next month padding
        const remainingCells = 42 - days.length; 
        for (let i = 1; i <= remainingCells; i++) {
            days.push({
                day: i,
                isPadding: true,
                isToday: false,
                events: [],
                customEvents: [],
                fullDate: null
            });
        }
        
        return days;
    });

    const changeMonth = (step) => {
        currentDate.value = new Date(currentYear.value, currentMonth.value + step, 1);
    };

    const openDayDetails = (dateObj) => {
        if (dateObj.isPadding) return;
        selectedDate.value = dateObj;
        isDayDetailsOpen.value = true;
    };

    onMounted(async () => {
        await authStore.fetchUser();
        calendarStore.fetchEvents();
    });
</script>
