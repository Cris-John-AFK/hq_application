<template>
    <div v-if="modelValue" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh] overflow-hidden flex flex-col">
            <!-- Header -->
            <div class="p-6 border-b border-gray-200 flex items-center justify-between bg-gradient-to-r from-purple-50 to-blue-50">
                <div class="flex items-center gap-4">
                    <div
                        class="w-14 h-14 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 font-bold text-lg bg-cover bg-center"
                        :style="employee?.avatar ? { backgroundImage: `url(${employee.avatar})` } : {}"
                    >
                        <span v-if="!employee?.avatar">
                            {{ getInitials(employee?.name) }}
                        </span>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">{{ employee?.name }}</h2>
                        <p class="text-sm text-gray-500">{{ employee?.position }} • {{ employee?.empId }}</p>
                    </div>
                </div>
                <button 
                    @click="closeModal" 
                    class="w-10 h-10 rounded-full bg-white hover:bg-gray-100 flex items-center justify-center transition-colors shadow-sm"
                >
                    <i class="pi pi-times text-gray-500"></i>
                </button>
            </div>

            <!-- Tab Navigation -->
            <div class="flex gap-1 bg-gray-100/50 p-1 mx-6 mt-4 rounded-xl w-fit">
                <button 
                    @click="activeTab = 'weekly'"
                    :class="[
                        'cursor-pointer px-4 py-2 rounded-lg text-sm font-medium transition-all',
                        activeTab === 'weekly' ? 'bg-white text-purple-700 shadow-sm' : 'text-gray-500 hover:text-gray-700'
                    ]"
                >
                    Weekly Report
                </button>
                <button 
                    @click="activeTab = 'monthly'"
                    :class="[
                        'cursor-pointer px-4 py-2 rounded-lg text-sm font-medium transition-all',
                        activeTab === 'monthly' ? 'bg-white text-purple-700 shadow-sm' : 'text-gray-500 hover:text-gray-700'
                    ]"
                >
                    Monthly Report
                </button>
                <button 
                    @click="activeTab = 'yearly'"
                    :class="[
                        'cursor-pointer px-4 py-2 rounded-lg text-sm font-medium transition-all',
                        activeTab === 'yearly' ? 'bg-white text-purple-700 shadow-sm' : 'text-gray-500 hover:text-gray-700'
                    ]"
                >
                    Yearly Report
                </button>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-6">
                <!-- Weekly Report -->
                <div v-if="activeTab === 'weekly'" class="space-y-4">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Weekly Working Hours</h3>
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-gray-900 text-white uppercase tracking-wider text-xs">
                                    <th class="px-6 py-4 font-semibold">Week</th>
                                    <th class="px-4 py-4 text-center">Total Hours</th>
                                    <th class="px-4 py-4 text-center">Regular Hours</th>
                                    <th class="px-4 py-4 text-center">Overtime</th>
                                    <th class="px-4 py-4 text-center">Days Worked</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="week in weeklyData" :key="week.week" class="hover:bg-gray-50">
                                    <td class="px-6 py-3 font-medium text-gray-800">{{ week.week }}</td>
                                    <td class="px-4 py-3 text-center font-bold text-purple-600">{{ week.total_hours }}</td>
                                    <td class="px-4 py-3 text-center text-gray-600">{{ week.regular_hours }}</td>
                                    <td class="px-4 py-3 text-center text-orange-600">{{ week.overtime }}</td>
                                    <td class="px-4 py-3 text-center text-teal-600">{{ week.days_worked }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Monthly Report -->
                <div v-if="activeTab === 'monthly'" class="space-y-4">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Monthly Working Hours</h3>
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-gray-900 text-white uppercase tracking-wider text-xs">
                                    <th class="px-6 py-4 font-semibold">Month</th>
                                    <th class="px-4 py-4 text-center">Total Hours</th>
                                    <th class="px-4 py-4 text-center">Regular Hours</th>
                                    <th class="px-4 py-4 text-center">Overtime</th>
                                    <th class="px-4 py-4 text-center">Days Worked</th>
                                    <th class="px-4 py-4 text-center">Absences</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="month in monthlyData" :key="month.month" class="hover:bg-gray-50">
                                    <td class="px-6 py-3 font-medium text-gray-800">{{ month.month }}</td>
                                    <td class="px-4 py-3 text-center font-bold text-purple-600">{{ month.total_hours }}</td>
                                    <td class="px-4 py-3 text-center text-gray-600">{{ month.regular_hours }}</td>
                                    <td class="px-4 py-3 text-center text-orange-600">{{ month.overtime }}</td>
                                    <td class="px-4 py-3 text-center text-teal-600">{{ month.days_worked }}</td>
                                    <td class="px-4 py-3 text-center text-red-600">{{ month.absences }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Yearly Report -->
                <div v-if="activeTab === 'yearly'" class="space-y-4">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Yearly Summary</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200">
                            <p class="text-sm text-purple-600 font-medium mb-1">Total Hours Worked</p>
                            <p class="text-3xl font-bold text-purple-700">{{ yearlyData.total_hours }}</p>
                        </div>
                        <div class="bg-gradient-to-br from-teal-50 to-teal-100 rounded-xl p-6 border border-teal-200">
                            <p class="text-sm text-teal-600 font-medium mb-1">Days Worked</p>
                            <p class="text-3xl font-bold text-teal-700">{{ yearlyData.days_worked }}</p>
                        </div>
                        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-6 border border-orange-200">
                            <p class="text-sm text-orange-600 font-medium mb-1">Total Overtime</p>
                            <p class="text-3xl font-bold text-orange-700">{{ yearlyData.overtime }} hrs</p>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-200 p-6">
                        <h4 class="font-bold text-gray-800 mb-4">Breakdown</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-gray-600">Regular Hours</span>
                                <span class="font-bold text-gray-800">{{ yearlyData.regular_hours }} hrs</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-gray-600">Total Absences</span>
                                <span class="font-bold text-red-600">{{ yearlyData.absences }} days</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-gray-600">Tardiness Count</span>
                                <span class="font-bold text-orange-600">{{ yearlyData.tardiness }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600">Undertime Count</span>
                                <span class="font-bold text-purple-600">{{ yearlyData.undertime }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="p-6 border-t border-gray-200 flex justify-end gap-3 bg-gray-50">
                <button 
                    @click="closeModal" 
                    class="cursor-pointer px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-medium transition-colors"
                >
                    Close
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    modelValue: Boolean,
    employee: Object
});

const emit = defineEmits(['update:modelValue']);

const activeTab = ref('weekly');

// Mock data - in real app, fetch from API based on employee.id
const weeklyData = ref([
    { week: 'Week 1 (Jan 1-7)', total_hours: 40, regular_hours: 40, overtime: 0, days_worked: 5 },
    { week: 'Week 2 (Jan 8-14)', total_hours: 45, regular_hours: 40, overtime: 5, days_worked: 5 },
    { week: 'Week 3 (Jan 15-21)', total_hours: 42, regular_hours: 40, overtime: 2, days_worked: 5 },
    { week: 'Week 4 (Jan 22-28)', total_hours: 40, regular_hours: 40, overtime: 0, days_worked: 5 },
]);

const monthlyData = ref([
    { month: 'January', total_hours: 176, regular_hours: 160, overtime: 16, days_worked: 22, absences: 0 },
    { month: 'February', total_hours: 168, regular_hours: 160, overtime: 8, days_worked: 21, absences: 1 },
    { month: 'March', total_hours: 184, regular_hours: 168, overtime: 16, days_worked: 23, absences: 0 },
    { month: 'April', total_hours: 160, regular_hours: 160, overtime: 0, days_worked: 20, absences: 2 },
]);

const yearlyData = ref({
    total_hours: 2080,
    regular_hours: 1920,
    overtime: 160,
    days_worked: 240,
    absences: 5,
    tardiness: 3,
    undertime: 2
});

const getInitials = (name) => {
    if (!name) return '??';
    return name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
};

const closeModal = () => {
    emit('update:modelValue', false);
};
</script>
