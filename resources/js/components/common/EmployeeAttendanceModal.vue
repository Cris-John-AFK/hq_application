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
                        <p v-if="employee?.working_hours" class="inline-flex items-center gap-1.5 mt-1 px-2.5 py-0.5 rounded-full bg-teal-100 text-teal-700 text-xs font-semibold">
                            <i class="pi pi-clock text-xs"></i>
                            {{ employee.working_hours }}
                        </p>
                        <p v-else class="inline-flex items-center gap-1.5 mt-1 px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-500 text-xs font-medium">
                            <i class="pi pi-clock text-xs"></i>
                            No schedule set
                        </p>
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
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div class="bg-white rounded-xl p-4 border border-gray-200">
                        <p class="text-xs text-gray-500 mb-1">Total Days</p>
                        <div v-if="loading" class="h-8 w-16 bg-gray-200 rounded animate-pulse mt-1"></div>
                        <p v-else class="text-2xl font-bold text-gray-800">{{ summary.totalDays }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                        <p class="text-xs text-green-600 mb-1">Present</p>
                        <div v-if="loading" class="h-8 w-16 bg-green-200/50 rounded animate-pulse mt-1"></div>
                        <p v-else class="text-2xl font-bold text-green-700">{{ summary.present }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-4 border border-red-200">
                        <p class="text-xs text-red-600 mb-1">Absent</p>
                        <div v-if="loading" class="h-8 w-16 bg-red-200/50 rounded animate-pulse mt-1"></div>
                        <p v-else class="text-2xl font-bold text-red-700">{{ summary.absent }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-4 border border-orange-200">
                        <p class="text-xs text-orange-600 mb-1">Late</p>
                        <div v-if="loading" class="h-8 w-16 bg-orange-200/50 rounded animate-pulse mt-1"></div>
                        <p v-else class="text-2xl font-bold text-orange-700">{{ summary.late }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-4 border border-yellow-200">
                        <p class="text-xs text-yellow-600 mb-1">Half Day</p>
                        <div v-if="loading" class="h-8 w-16 bg-yellow-200/50 rounded animate-pulse mt-1"></div>
                        <p v-else class="text-2xl font-bold text-yellow-700">{{ summary.halfDay }}</p>
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
                            <template v-if="loading">
                                <tr v-for="i in 5" :key="i" class="animate-pulse">
                                    <td class="px-6 py-3"><div class="h-4 bg-gray-200 rounded w-28"></div></td>
                                    <td class="px-4 py-3"><div class="h-4 bg-gray-200 rounded w-16 mx-auto"></div></td>
                                    <td class="px-4 py-3"><div class="h-4 bg-gray-200 rounded w-16 mx-auto"></div></td>
                                    <td class="px-4 py-3"><div class="h-4 bg-gray-200 rounded w-8 mx-auto"></div></td>
                                    <td class="px-4 py-3"><div class="h-5 bg-gray-200 rounded-full w-16 mx-auto"></div></td>
                                </tr>
                            </template>
                            <template v-else>
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
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer -->
            <div class="p-6 border-t border-gray-200 flex justify-between items-center bg-gray-50">
                <button 
                    @click="exportToExcel" 
                    :disabled="records.length === 0"
                    class="cursor-pointer px-6 py-2 bg-teal-600 hover:bg-teal-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white rounded-lg font-medium transition-all flex items-center gap-2 shadow-sm shadow-teal-100"
                >
                    <i class="pi pi-file-excel"></i>
                    Export to Excel
                </button>
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
import * as XLSX from 'xlsx';

const props = defineProps({
    modelValue: Boolean,
    employee: Object,
    records: {
        type: Array,
        default: () => []
    },
    loading: {
        type: Boolean,
        default: false
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
    const halfDay = props.records.filter(r => r.status === 'Half Day').length;
    
    return {
        totalDays: total,
        present,
        absent,
        late,
        halfDay
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

const exportToExcel = () => {
    if (!props.records.length) return;

    // Get the most accurate department from the DB records if available, otherwise use prop
    const recordDept = (sortedRecords.value.length > 0) ? (sortedRecords.value[0].employee_department || sortedRecords.value[0].department) : null;
    const finalDepartment = recordDept && recordDept !== '--' ? recordDept : (props.employee?.department || '--');

    // Prepare data with employee info at the top
    const ws_data = [
        ["EMPLOYEE ATTENDANCE REPORT"],
        ["Name:", props.employee?.employee_name],
        ["ID:", props.employee?.employee_id],
        ["Department:", finalDepartment],
        ["Generated:", new Date().toLocaleString()],
        [], // Empty row
        ["SUMMARY"],
        ["Total Days", summary.value.totalDays],
        ["Present", summary.value.present],
        ["Absent", summary.value.absent],
        ["Late", summary.value.late],
        ["Half Day", summary.value.halfDay],
        [], // Empty row
        ["ATTENDANCE HISTORY"],
        ["Date", "Time In", "Time Out", "Hours Worked", "Status"]
    ];

    // Add records
    sortedRecords.value.forEach(record => {
        ws_data.push([
            formatDate(record.date),
            record.time_in,
            record.time_out,
            record.hours_worked,
            record.status
        ]);
    });

    // Create worksheet
    const worksheet = XLSX.utils.aoa_to_sheet(ws_data);

    // Styling (some basic column widths)
    const wscols = [
        {wch: 25}, // A
        {wch: 25}, // B
        {wch: 15}, // C
        {wch: 15}, // D
        {wch: 15}  // E
    ];
    worksheet['!cols'] = wscols;

    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Attendance");

    // Generate filename
    const safeName = (props.employee?.employee_name || 'Employee').replace(/[^a-z0-9]/gi, '_').toLowerCase();
    const dateStr = new Date().toISOString().split('T')[0];
    const fileName = `${safeName}_attendance_${dateStr}.xlsx`;

    // Save file
    XLSX.writeFile(workbook, fileName);
};
</script>
