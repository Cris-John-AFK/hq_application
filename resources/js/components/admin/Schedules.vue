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
                    
                        <div class="flex items-center bg-gray-100 p-1 rounded-xl border border-gray-200 shadow-sm ml-2">
                            <button 
                                @click="viewMode = 'calendar'"
                                :class="[
                                    'cursor-pointer px-3 py-1.5 rounded-lg text-xs font-black uppercase transition-all flex items-center gap-2',
                                    viewMode === 'calendar' ? 'bg-white text-teal-600 shadow-sm' : 'text-gray-400 hover:text-gray-600'
                                ]"
                            >
                                <i class="pi pi-calendar"></i>
                                Grid
                            </button>
                            <button 
                                @click="viewMode = 'list'"
                                :class="[
                                    'cursor-pointer px-3 py-1.5 rounded-lg text-xs font-black uppercase transition-all flex items-center gap-2',
                                    viewMode === 'list' ? 'bg-white text-teal-600 shadow-sm' : 'text-gray-400 hover:text-gray-600'
                                ]"
                            >
                                <i class="pi pi-list"></i>
                                List
                            </button>
                        </div>

                        <div class="flex items-center gap-3 bg-white p-1 rounded-xl border border-gray-200 shadow-sm ml-2">
                            <button @click="changeMonth(-1)" class="cursor-pointer p-2 hover:bg-gray-100 rounded-md text-gray-500 transition-colors">
                                <i class="pi pi-chevron-left"></i>
                            </button>
                            <span class="min-w-[140px] text-center font-bold text-gray-800 text-sm uppercase tracking-widest">{{ currentMonthName }} {{ currentYear }}</span>
                            <button @click="changeMonth(1)" class="cursor-pointer p-2 hover:bg-gray-100 rounded-md text-gray-500 transition-colors">
                                <i class="pi pi-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Legend -->
                <div class="flex flex-wrap gap-4 mb-6">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-purple-100 border border-purple-300"></span>
                        <span class="text-xs font-medium text-gray-600">Leave</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-rose-100 border border-rose-300"></span>
                        <span class="text-xs font-medium text-gray-600">Holiday</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-blue-100 border border-blue-300"></span>
                        <span class="text-xs font-medium text-gray-600">Event</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-orange-100 border border-orange-300"></span>
                        <span class="text-xs font-medium text-gray-600">Special Non-Working</span>
                    </div>
                </div>

                <!-- Calendar Content -->
                <div v-if="viewMode === 'calendar'" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden animate-in fade-in duration-300">
                    <!-- Weekday Headers -->
                    <div class="grid grid-cols-7 border-b border-gray-200 bg-gray-50/50">
                        <div v-for="day in weekDays" :key="day" class="py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            {{ day }}
                        </div>
                    </div>
                    
                    <!-- Days -->
                    <div class="grid grid-cols-7 auto-rows-fr border-l border-t border-gray-200">
                        <div 
                            v-for="(date, index) in calendarDays" 
                            :key="index" 
                            @click="openDayDetails(date)"
                            class="min-h-[120px] bg-white relative group hover:bg-gray-50/50 transition-colors cursor-pointer border-r border-b border-gray-200"
                            :class="{'bg-gray-50/30 text-gray-400': date.isPadding, 'bg-blue-50/30': date.isToday}"
                        >
                            <div class="p-2 flex justify-between items-start mb-1">
                                <span :class="[
                                    'text-sm font-medium w-7 h-7 flex items-center justify-center rounded-full',
                                    date.isToday ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-700'
                                ]">
                                    {{ date.day }}
                                </span>
                            </div>

                            <div class="space-y-1 overflow-y-auto max-h-[85px] custom-scrollbar pb-2">
                                <div v-for="evt in date.customEvents" :key="evt.id || evt.name" 
                                    :class="[
                                        'px-2 py-0.5 text-[10px] font-semibold border-y truncate transition-all',
                                        getEventClass(evt),
                                        {
                                            'rounded-l ml-1 border-l': isEventStart(evt, date.fullDate),
                                            'rounded-r mr-1 border-r': isEventEnd(evt, date.fullDate),
                                            'border-l-0': !isEventStart(evt, date.fullDate),
                                            'border-r-0': !isEventEnd(evt, date.fullDate),
                                            'mb-0.5 shadow-sm': isEventStart(evt, date.fullDate) || isEventEnd(evt, date.fullDate)
                                        }
                                    ]"
                                    :title="evt.title || evt.name"
                                >
                                    <template v-if="isEventStart(evt, date.fullDate) || isFirstDayOfWeek(date.fullDate)">
                                        <span v-if="evt.type === 'holiday' || evt.type === 'Regular Holiday' || evt.type === 'Special Non-Working'">🎉</span>
                                        <span v-else>📅</span>
                                        {{ evt.title || evt.name }}
                                    </template>
                                    <span v-else>&nbsp;</span>
                                </div>

                                <div v-for="event in date.events" :key="event.id" 
                                    :class="[
                                        'px-2 py-0.5 text-[10px] font-semibold bg-purple-50 text-purple-700 border-y border-purple-100 truncate hover:bg-purple-100 transition-colors',
                                        {
                                            'rounded-l ml-1 border-l': isEventStart(event, date.fullDate),
                                            'rounded-r mr-1 border-r': isEventEnd(event, date.fullDate),
                                            'border-l-0': !isEventStart(event, date.fullDate),
                                            'border-r-0': !isEventEnd(event, date.fullDate),
                                            'mb-1 shadow-sm': isEventStart(event, date.fullDate) || isEventEnd(event, date.fullDate)
                                        }
                                    ]"
                                    :title="`${event.user_name} - ${event.leave_type}`"
                                >
                                    <template v-if="isEventStart(event, date.fullDate) || isFirstDayOfWeek(date.fullDate)">
                                        👤 {{ event.user_name }}
                                    </template>
                                    <span v-else>&nbsp;</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- List View -->
                <div v-else class="space-y-4 animate-in slide-in-from-bottom-5 duration-500">
                    <div v-if="listDays.length === 0" class="bg-white rounded-2xl p-20 border border-dashed border-gray-200 text-center">
                        <i class="pi pi-calendar-minus text-5xl text-gray-100 mb-4 block"></i>
                        <p class="text-gray-400 font-medium italic">No events or leaves scheduled for this month.</p>
                    </div>

                    <div v-for="date in listDays" :key="date.fullDate" class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex flex-col md:flex-row">
                            <div class="w-full md:w-32 bg-gray-50 flex flex-col items-center justify-center p-6 border-b md:border-b-0 md:border-r border-gray-100">
                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ getDayName(date.fullDate) }}</span>
                                <span class="text-3xl font-black text-gray-800">{{ date.day }}</span>
                                <span v-if="date.isToday" class="mt-1 px-2 py-0.5 bg-blue-600 text-white text-[8px] font-black uppercase rounded shadow-sm">Today</span>
                            </div>

                            <div class="cursor-pointer flex-1 p-2">
                                <div class="divide-y divide-gray-50">
                                    <div v-for="evt in date.customEvents" :key="evt.id" @click="openDayDetails(date)" class="p-4 hover:bg-gray-50 transition-colors cursor-pointer group flex items-center justify-between">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shadow-sm" :class="getEventClass(evt)">
                                                <i class="pi animate-bounce" :class="evt.type === 'holiday' || evt.type === 'Regular Holiday' ? 'pi-sun' : 'pi-calendar'"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-black text-gray-800 tracking-tight">{{ evt.title || evt.name }}</h4>
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ evt.type }}</span>
                                                    <span v-if="evt.from_date != evt.to_date && evt.to_date" class="text-[10px] text-teal-600 font-bold">Until {{ formatDateLabel(evt.to_date) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <i class="pi pi-chevron-right text-xs text-gray-300 group-hover:text-teal-500 transition-colors"></i>
                                    </div>

                                    <div v-for="leave in date.events" :key="leave.id" @click="openDayDetails(date)" class="p-4 hover:bg-gray-50 transition-colors cursor-pointer group flex items-center justify-between">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shadow-sm">
                                                <i class="pi pi-user"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-black text-gray-800 tracking-tight">{{ leave.user_name }}</h4>
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <span class="text-[10px] font-bold text-purple-400 uppercase tracking-widest">{{ leave.leave_type }}</span>
                                                    <span class="text-[10px] text-gray-400 font-medium">• {{ leave.days_taken }} Day(s)</span>
                                                </div>
                                            </div>
                                        </div>
                                        <i class="pi pi-chevron-right text-xs text-gray-300 group-hover:text-teal-500 transition-colors"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modals -->
            <AddEventModal v-model="isAddEventOpen" @success="refreshCalendar" />
            <DayDetailsModal v-model="isDayDetailsOpen" :date="selectedDate" @delete="refreshCalendar" />
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
    const viewMode = ref('calendar'); // 'calendar' or 'list'

    const listDays = computed(() => {
        return calendarDays.value.filter(day => !day.isPadding && (day.events.length > 0 || day.customEvents.length > 0));
    });

    const getDayName = (dateStr) => {
        return new Date(dateStr).toLocaleDateString('en-US', { weekday: 'short' });
    };

    const formatDateLabel = (dateStr) => {
        return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    };

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
        if (type === 'Regular Holiday' || type === 'holiday') return 'bg-rose-50 text-rose-700 border-rose-100';
        if (type === 'Special Non-Working') return 'bg-orange-50 text-orange-700 border-orange-100';
        if (type === 'meeting') return 'bg-indigo-50 text-indigo-700 border-indigo-100';
        return 'bg-blue-50 text-blue-700 border-blue-100'; // Default event
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
            
            // Stable sort by name/title to keep vertical alignment
            const mergedEvents = [...dayStaticHolidays, ...dayCustom].sort((a, b) => {
                const titleA = a.title || a.name || '';
                const titleB = b.title || b.name || '';
                return titleA.localeCompare(titleB);
            });
            
            dayLeaves.sort((a, b) => (a.user_name || '').localeCompare(b.user_name || ''));

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

    // Continuity Helpers
    const isEventStart = (evt, currentDate) => {
        if (!currentDate) return false;
        const start = evt.from_date || evt.date;
        return start === currentDate;
    };

    const isEventEnd = (evt, currentDate) => {
        if (!currentDate) return false;
        const end = evt.to_date || evt.date;
        return end === currentDate;
    };

    const isFirstDayOfWeek = (dateStr) => {
        if (!dateStr) return false;
        return new Date(dateStr).getDay() === 0; // Sunday
    };

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
