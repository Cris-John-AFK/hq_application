<template>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Upcoming Holidays</h3>
        <div class="space-y-4">
            <div v-for="(holiday, index) in holidays" :key="index" class="flex items-start gap-4">
                <div class="bg-teal-50 text-teal-700 rounded-lg p-2 text-center min-w-[60px]">
                    <span class="block text-xs font-bold uppercase">{{ holiday.month }}</span>
                    <span class="block text-xl font-bold">{{ holiday.day }}</span>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-800">{{ holiday.name }}</h4>
                    <p class="text-sm text-gray-500">{{ holiday.dayOfWeek }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    const holidays = [
        { month: 'Jul', day: '29', name: 'Eid - Al - Ada', dayOfWeek: 'Monday' },
        { month: 'Aug', day: '15', name: 'Independence Day', dayOfWeek: 'Tuesday' },
        { month: 'Aug', day: '16', name: 'Parsi New Year', dayOfWeek: 'Wednesday' },
        { month: 'Aug', day: '29', name: 'Onam', dayOfWeek: 'Tuesday' },
    ];
</script>
