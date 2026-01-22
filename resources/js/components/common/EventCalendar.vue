<template>
    <div class="bg-gray-200 p-4 rounded-2xl shadow-sm border border-gray-100 relative max-w-sm mx-auto">
        <div class="flex justify-between items-center mb-3">
            <h3 class="text-base font-bold text-gray-800">Calendar</h3>
            <div @click="toggleMonthPicker" class="text-sm font-medium text-gray-600 cursor-pointer hover:text-teal-600 flex items-center gap-1 select-none">
                {{ currentMonthName }} {{ currentYear }} 
                <i :class="['pi text-xs ml-1 transition-transform', showMonthPicker ? 'pi-chevron-up' : 'pi-chevron-down']"></i>
            </div>
        </div>

        <!-- Month Selection Overlay -->
        <div v-if="showMonthPicker" class="absolute inset-0 bg-white rounded-2xl z-20 flex flex-col p-4 animate-fade-in">
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <button @click="changeYear(-1)" class="p-1 hover:bg-gray-100 rounded text-gray-600 cursor-pointer"><i class="pi pi-chevron-left"></i></button>
                <span class="font-bold text-gray-800">{{ currentYear }}</span>
                <button @click="changeYear(1)" class="p-1 hover:bg-gray-100 rounded text-gray-600 cursor-pointer"><i class="pi pi-chevron-right"></i></button>
            </div>
            <div class="grid grid-cols-3 gap-2 flex-1">
                <button 
                    v-for="(month, index) in months" 
                    :key="month"
                    @click="selectMonth(index)"
                    :class="['text-xs rounded py-2 transition-colors cursor-pointer hover:bg-gray-200', currentMonth === index ? 'bg-teal-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-50']"
                >
                    {{ month }}
                </button>
            </div>
        </div>
        
        <div class="grid grid-cols-7 gap-1 text-center mb-1">
            <span v-for="day in weekDays" :key="day" class="text-[10px] font-medium text-gray-400 uppercase py-1 cursor-default">
                {{ day }}
            </span>
        </div>
        
        <div class="grid grid-cols-7 gap-1 mb-2">
            <div 
                v-for="(date, index) in calendarDays" 
                :key="index" 
                class="aspect-square flex items-center justify-center rounded-lg text-sm relative group cursor-pointer transition-colors"
                :class="{
                    'text-gray-300': date.isPadding,
                    'font-medium text-gray-700 hover:bg-gray-50': !date.isPadding && !date.isToday,
                    'bg-teal-600 text-white shadow-md hover:bg-teal-700': date.isToday
                }"
            >
                {{ date.day }}
                <!-- Event Indicator -->
                <span v-if="!date.isPadding && date.hasEvent && !date.isToday" class="absolute bottom-1 h-1.5 w-1.5 rounded-full bg-rose-500 shadow-sm ring-1 ring-white"></span>
                <span v-if="date.isToday && date.hasEvent" class="absolute bottom-1 h-1.5 w-1.5 rounded-full bg-white shadow-sm"></span>

                <!-- Tooltip -->
                <div 
                    v-if="!date.isPadding"
                    class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 invisible opacity-0 group-hover:visible group-hover:opacity-100 transition-all duration-200 z-50 w-max max-w-[120px] p-2 bg-gray-800 text-white text-[10px] rounded leading-tight text-center shadow-lg pointer-events-none"
                >
                    {{ date.eventTitle }}
                    <!-- Tooltip Arrow -->
                    <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-800"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
const currentDate = ref(new Date());
const showMonthPicker = ref(false);

const currentYear = computed(() => currentDate.value.getFullYear());
const currentMonth = computed(() => currentDate.value.getMonth());
const currentMonthName = computed(() => currentDate.value.toLocaleString('default', { month: 'long' }));

// Sample event types for demo
const eventTypes = [
    'Team Meeting', 
    'Project Deadline', 
    'Public Holiday', 
    'John\'s Leave', 
    'Review'
];

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
            hasEvent: false,
            eventTitle: '',
            isToday: false
        });
    }
    
    // Current month days
    const today = new Date();
    for (let i = 1; i <= daysInMonth; i++) {
        // Randomly assign events for demo (approx 20% of days)
        const hasEvent = Math.random() < 0.2; 
        const isToday = 
            i === today.getDate() && 
            month === today.getMonth() && 
            year === today.getFullYear();

        let eventTitle = 'No events scheduled';
        if (hasEvent) {
             eventTitle = eventTypes[Math.floor(Math.random() * eventTypes.length)];
        } else if (isToday) {
            eventTitle = 'Today';
        }

        days.push({
            day: i,
            isPadding: false,
            hasEvent: hasEvent,
            eventTitle: eventTitle,
            isToday: isToday
        });
    }
    
    // Next month padding to fill grid (up to 42 cells for 6 rows max)
    const remainingCells = 42 - days.length;
    for (let i = 1; i <= remainingCells; i++) {
        days.push({
            day: i,
            isPadding: true,
            hasEvent: false,
            eventTitle: '',
            isToday: false
        });
    }
    
    return days;
});

const toggleMonthPicker = () => {
    showMonthPicker.value = !showMonthPicker.value;
};

const changeYear = (step) => {
    currentDate.value = new Date(currentYear.value + step, currentMonth.value, 1);
};

const selectMonth = (monthIndex) => {
    currentDate.value = new Date(currentYear.value, monthIndex, 1);
    showMonthPicker.value = false;
};
</script>
