<template>
    <div v-if="modelValue" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col">
            <!-- Header -->
            <div class="p-6 border-b border-gray-200 flex items-center justify-between bg-gradient-to-r from-teal-50 to-blue-50">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full bg-teal-100 flex items-center justify-center text-teal-600 font-bold text-lg">
                        {{ getInitials(employee?.employee_name) }}
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">{{ employee?.employee_name }}</h2>
                        <p class="text-sm text-gray-500">{{ employee?.employee_id }} • {{ employee?.department }}</p>
                    </div>
                </div>
                <button 
                    @click="closeModal" 
                    class="w-10 h-10 rounded-full bg-white hover:bg-gray-100 flex items-center justify-center transition-colors shadow-sm"
                >
                    <i class="pi pi-times text-gray-500"></i>
                </button>
            </div>

            <!-- Summary Cards -->
            <div class="p-6 bg-gray-50 border-b border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white rounded-xl p-4 border border-gray-200">
                        <p class="text-xs text-gray-500 mb-1">Total Days</p>
                        <p class="text-2xl font-bold text-gray-800">{{ summary.totalDays }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                        <p class="text-xs text-green-600 mb-1">Present</p>
                        <p class="text-2xl font-bold text-green-700">{{ summary.present }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-4 border border-red-200">
                        <p class="text-xs text-red-600 mb-1">Absent</p>
                        <p class="text-2xl font-bold text-red-700">{{ summary.absent }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-4 border border-orange-200">
                        <p class="text-xs text-orange-600 mb-1">Late</p>
                        <p class="text-2xl font-bold text-orange-700">{{ summary.late }}</p>
                    </div>
                </div>
            </div>

            <!-- Attendance Records -->
            <div class="flex-1 overflow-y-auto p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Attendance History</h3>
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-gray-900 text-white uppercase tracking-wider text-xs">
                                <th class="px-6 py-3 font-semibold">Date</th>
                                <th class="px-4 py-3 text-center font-semibold">Time In</th>
                                <th class="px-4 py-3 text-center font-semibold">Time Out</th>
                                <th class="px-4 py-3 text-center font-semibold">Hours</th>
                                <th class="px-4 py-3 text-center font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="record in sortedRecords" :key="record.id" class="hover:bg-gray-50">
                                <td class="px-6 py-3 font-medium text-gray-800">{{ formatDate(record.date) }}</td>
                                <td class="px-4 py-3 text-center text-gray-600">{{ record.time_in }}</td>
                                <td class="px-4 py-3 text-center text-gray-600">{{ record.time_out }}</td>
                                <td class="px-4 py-3 text-center font-bold text-teal-600">{{ record.hours_worked }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span 
                                        :class="{
                                            'bg-green-100 text-green-700': record.status === 'Present',
                                            'bg-red-100 text-red-700': record.status === 'Absent',
                                            'bg-orange-100 text-orange-700': record.status === 'Late',
                                            'bg-yellow-100 text-yellow-700': record.status === 'Half Day'
                                        }"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    >
                                        {{ record.status }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="records.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                    No attendance records found for this employee.
                                </td>
                            </tr>
                        </tbody>
                    </table>
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
import { computed } from 'vue';

const props = defineProps({
    modelValue: Boolean,
    employee: Object,
    records: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['update:modelValue']);

const sortedRecords = computed(() => {
    return [...props.records].sort((a, b) => new Date(b.date) - new Date(a.date));
});

const summary = computed(() => {
    const total = props.records.length;
    const present = props.records.filter(r => r.status === 'Present').length;
    const absent = props.records.filter(r => r.status === 'Absent').length;
    const late = props.records.filter(r => r.status === 'Late').length;
    
    return {
        totalDays: total,
        present,
        absent,
        late
    };
});

const getInitials = (name) => {
    if (!name) return '??';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
};

const formatDate = (dateStr) => {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
};

const closeModal = () => {
    emit('update:modelValue', false);
};
</script>
