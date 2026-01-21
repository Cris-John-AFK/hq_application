<template>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Calendar</h3>
            <div class="text-sm font-medium text-gray-600 cursor-pointer hover:text-teal-600">
                July 2026 <i class="pi pi-chevron-down text-xs ml-1"></i>
            </div>
        </div>
        
        <div class="grid grid-cols-7 gap-1 text-center mb-2">
            <span v-for="day in weekDays" :key="day" class="text-xs font-medium text-gray-400 uppercase py-2">
                {{ day }}
            </span>
        </div>
        
        <div class="grid grid-cols-7 gap-1">
            <div 
                v-for="(date, index) in calendarDays" 
                :key="index" 
                class="aspect-square flex items-center justify-center rounded-full text-sm relative group cursor-pointer hover:bg-gray-50 transition-colors"
                :class="{
                    'text-gray-300': date.isPadding,
                    'font-medium text-gray-700': !date.isPadding,
                    'bg-teal-600 text-white hover:bg-teal-700 shadow-md': date.isActive
                }"
            >
                {{ date.day }}
                <span v-if="date.hasEvent && !date.isActive" class="absolute bottom-1 h-1.5 w-1.5 rounded-full bg-teal-400"></span>
            </div>
        </div>
    </div>
</template>

<script setup>
const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

// Simplified calendar generation logic for demo purposes
// Ideally this would be dynamic based on current date
const calendarDays = [
    { day: 29, isPadding: true }, { day: 30, isPadding: true }, // Prev month
    { day: 1, hasEvent: true }, { day: 2, hasEvent: true }, { day: 3 }, { day: 4, hasEvent: true }, { day: 5 },
    { day: 6 }, { day: 7 }, { day: 8, hasEvent: true }, { day: 9, hasEvent: true }, { day: 10, hasEvent: true }, { day: 11, hasEvent: true }, { day: 12 },
    { day: 13 }, { day: 14, hasEvent: true }, { day: 15, hasEvent: true }, { day: 16 }, { day: 17 }, { day: 18 }, { day: 19 },
    { day: 20 }, { day: 21, hasEvent: true }, { day: 22 }, { day: 23, hasEvent: true }, { day: 24 }, { day: 25, isActive: true }, { day: 26 },
    { day: 27 }, { day: 28 }, { day: 29 }, { day: 30 }, { day: 31 },
    { day: 1, isPadding: true }, { day: 2, isPadding: true } // Next month
];
</script>
