<template>
    <div v-if="user">
        <MainLayout :user="user">
            <div class="max-w-7xl mx-auto space-y-6">
                <!-- Header -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Reports Center</h2>
                        <p class="text-gray-500">Generate and export detailed attendance and departmental reports.</p>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <button 
                            @click="downloadExcel"
                            class="h-10 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-medium shadow-sm shadow-emerald-200 transition-all flex items-center gap-2 cursor-pointer"
                        >
                            <i class="pi pi-file-excel"></i>
                            Export to Excel
                        </button>
                    </div>
                </div>

                <!-- WIP Banner -->
                <div class="flex items-center gap-4 bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                        <i class="pi pi-wrench text-amber-600 text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm font-black text-amber-800">This page is a Work in Progress — All data shown is a placeholder.</p>
                        <p class="text-xs text-amber-600 mt-0.5 font-medium">The Reports Center requires attendance data integration before it can generate real reports. Export and filter features are non-functional at this time.</p>
                    </div>
                    <div class="ml-auto flex-shrink-0">
                        <span class="text-[10px] font-black uppercase tracking-widest text-amber-500 bg-amber-100 border border-amber-200 px-3 py-1.5 rounded-full">Coming Soon</span>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-wrap gap-4 items-center">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-600">Year:</span>
                        <select v-model="selectedYear" class="h-9 px-3 border border-gray-200 rounded-lg text-sm bg-white focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none">
                            <option v-for="year in [2024, 2025, 2026]" :key="year" :value="year">{{ year }}</option>
                        </select>
                    </div>
                    
                    <div v-if="activeTab === 'department'" class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-600">Month:</span>
                        <select v-model="selectedMonth" class="h-9 px-3 border border-gray-200 rounded-lg text-sm bg-white focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none">
                            <option v-for="(m, i) in months" :key="i" :value="i + 1">{{ m }}</option>
                        </select>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="flex gap-1 bg-gray-100/50 p-1 rounded-xl w-fit">
                    <button 
                        @click="activeTab = 'annual'"
                        :class="[
                            'cursor-pointer px-4 py-2 rounded-lg text-sm font-medium transition-all',
                            activeTab === 'annual' ? 'bg-white text-teal-700 shadow-sm' : 'text-gray-500 hover:text-gray-700'
                        ]"
                    >
                        Annual Company Report
                    </button>
                    <button 
                        @click="activeTab = 'department'"
                        :class="[
                            'cursor-pointer px-4 py-2 rounded-lg text-sm font-medium transition-all',
                            activeTab === 'department' ? 'bg-white text-teal-700 shadow-sm' : 'text-gray-500 hover:text-gray-700'
                        ]"
                    >
                        Monthly Department Report
                    </button>
                    <button 
                        @click="activeTab = 'yearly'"
                        :class="[
                            'cursor-pointer px-4 py-2 rounded-lg text-sm font-medium transition-all',
                            activeTab === 'yearly' ? 'bg-white text-teal-700 shadow-sm' : 'text-gray-500 hover:text-gray-700'
                        ]"
                    >
                        Yearly Summary
                    </button>
                </div>

                <!-- Tab Content wrapped in WIP overlay -->
                <div class="relative">
                    <!-- Frosted Glass WIP Overlay -->
                    <div class="absolute inset-0 z-10 rounded-2xl backdrop-blur-[3px] bg-white/60 flex flex-col items-center justify-center gap-4 border border-dashed border-amber-300">
                        <div class="bg-white rounded-2xl shadow-xl border border-amber-100 px-10 py-8 flex flex-col items-center gap-3 max-w-md text-center">
                            <div class="w-16 h-16 rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-center">
                                <i class="pi pi-chart-bar text-3xl text-amber-500"></i>
                            </div>
                            <h3 class="text-lg font-black text-gray-900">Reports Not Yet Available</h3>
                            <p class="text-sm text-gray-500 font-medium leading-relaxed">This section will generate real attendance, department, and yearly summary reports once the time-keeping data pipeline is connected. All figures shown below are <strong class="text-amber-600">sample/placeholder data only</strong>.</p>
                            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-500 bg-amber-50 border border-amber-200 px-4 py-2 rounded-full mt-1">🔧 Under Construction</span>
                        </div>
                    </div>

                    <!-- Annual Report Table -->
                    <div v-if="activeTab === 'annual'" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden animate-in fade-in duration-300">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead>
                                    <tr class="bg-gray-900 text-white uppercase tracking-wider text-xs">
                                        <th class="px-6 py-4 font-semibold w-32 border-r border-gray-800">Month</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800">Headcount</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800">Total Present<br>Days</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800">Total Working<br>Days</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800">Attendance<br>Rate (%)</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800">Total Absent<br>Days</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800">Absenteeism<br>Rate (%)</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800">Late<br>Count</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800">Tardiness<br>Freq (%)</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800">Undertime /<br>Half Day</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800">Total Undertime<br>(mins)</th>
                                        <th class="px-4 py-4 text-center">Undertime<br>Freq (%)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="row in annualData" :key="row.month" class="hover:bg-gray-50">
                                        <td class="px-6 py-3 font-medium text-gray-800 border-r border-gray-100">{{ row.month }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-100">{{ row.headcount }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-100">{{ row.total_present_days }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-100">{{ row.total_working_days }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-100 font-bold" :class="getRateColor(row.attendance_rate)">{{ row.attendance_rate }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-100">{{ row.total_absent_days }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-100 font-medium">{{ row.absenteeism_rate }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-100">{{ row.employees_late }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-100">{{ row.tardiness_frequency }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-100">{{ row.employees_undertime }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-100">{{ row.total_undertime_mins }}</td>
                                        <td class="px-4 py-3 text-center">{{ row.undertime_frequency }}</td>
                                    </tr>
                                    <!-- Yearly Total Row -->
                                    <tr v-if="annualTotals" class="bg-gray-900 text-white font-bold border-t-2 border-gray-900">
                                        <td class="px-6 py-3 font-bold text-white border-r border-gray-800">TOTAL</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-800">{{ annualTotals.headcount }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-800">{{ annualTotals.total_present_days }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-800">{{ annualTotals.total_working_days }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-800">{{ annualTotals.attendance_rate }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-800">{{ annualTotals.total_absent_days }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-800">{{ annualTotals.absenteeism_rate }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-800">{{ annualTotals.employees_late }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-800">{{ annualTotals.tardiness_frequency }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-800">{{ annualTotals.employees_undertime }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-800">{{ annualTotals.total_undertime_mins }}</td>
                                        <td class="px-4 py-3 text-center">{{ annualTotals.undertime_frequency }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Department Report Table -->
                    <div v-else-if="activeTab === 'department'" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden animate-in fade-in duration-300">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead>
                                    <tr class="bg-gray-900 text-white uppercase tracking-wider text-xs">
                                        <th class="px-6 py-4 font-semibold w-48 border-r border-gray-800">Department</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800">Total<br>Employees</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800">Total<br>Working Days</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800">Total<br>Scheduled Hrs</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800">Total<br>Actual Hrs</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800 bg-gray-800">Regular<br>Actual Hrs</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800 bg-gray-800">Overtime<br>Hours</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800 bg-gray-800">Excess<br>Hours (>2)</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800 bg-gray-800"># Emp w/<br>Excess OT</th>
                                        <th class="px-4 py-4 text-center">Avg Daily<br>Hrs / Emp</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="row in departmentData" :key="row.department" class="hover:bg-gray-50">
                                        <td class="px-6 py-3 font-medium text-gray-800 border-r border-gray-100">{{ row.department }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-100">{{ row.total_employees }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-100">{{ row.total_working_days }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-100 text-gray-500">{{ row.total_scheduled_hours }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-100 font-bold text-teal-600">{{ row.total_actual_hours }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-100 text-gray-600">{{ row.regular_actual_hours }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-100 bg-yellow-50/50 font-medium">{{ row.overtime_actual_hours }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-100 bg-red-50/50 text-red-600">{{ row.excess_hours_worked }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-100">{{ row.employees_with_excess_ot }}</td>
                                        <td class="px-4 py-3 text-center font-mono text-xs">{{ row.avg_daily_working_hours }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Yearly Summary Table -->
                    <div v-else-if="activeTab === 'yearly'" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden animate-in fade-in duration-300">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead>
                                    <tr class="bg-gray-900 text-white uppercase tracking-wider text-xs">
                                        <th class="px-6 py-4 font-semibold w-32 border-r border-gray-800">Month</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800">Total Working<br>Days</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800">Total<br>Employees</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800">Total Present<br>Days</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800">Total Absent<br>Days</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800">Total Tardiness<br>(mins)</th>
                                        <th class="px-4 py-4 text-center">Total Undertime<br>(mins)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="row in annualData" :key="row.month" class="hover:bg-gray-50">
                                        <td class="px-6 py-3 font-medium text-gray-800 border-r border-gray-100">{{ row.month }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-100 font-bold text-teal-600">{{ row.total_working_days }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-100 font-bold text-teal-600">{{ row.headcount }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-100 font-bold text-green-600">{{ row.total_present_days }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-100 font-bold text-red-600">{{ row.total_absent_days }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-100 font-bold text-orange-600">{{ row.employees_late * 2 }}</td>
                                        <td class="px-4 py-3 text-center font-bold text-purple-600">{{ row.total_undertime_mins }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </MainLayout>
    </div>
    <div v-else class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="text-center">
             <i class="pi pi-spin pi-spinner text-4xl mb-4 text-teal-600"></i>
             <p class="text-gray-500">Loading...</p>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import * as XLSX from 'xlsx';
import { useAuthStore } from '../../stores/auth';
import { storeToRefs } from 'pinia';
import MainLayout from '../../layouts/MainLayout.vue';

const authStore = useAuthStore();
const { user } = storeToRefs(authStore);

const activeTab = ref('annual');
const selectedYear = ref(2026);
const selectedMonth = ref(1);

const months = [
    'January', 'February', 'March', 'April', 'May', 'June', 
    'July', 'August', 'September', 'October', 'November', 'December'
];

const annualData = ref([]);
const departmentData = ref([]);

// Computed property for annual totals
const annualTotals = computed(() => {
    if (!annualData.value || annualData.value.length === 0) return null;
    
    const totals = {
        headcount: 0,
        total_present_days: 0,
        total_working_days: 0,
        total_absent_days: 0,
        employees_late: 0,
        employees_undertime: 0,
        total_undertime_mins: 0
    };
    
    annualData.value.forEach(row => {
        if (typeof row.headcount === 'number') totals.headcount += row.headcount;
        if (typeof row.total_present_days === 'number') totals.total_present_days += row.total_present_days;
        if (typeof row.total_working_days === 'number') totals.total_working_days += row.total_working_days;
        if (typeof row.total_absent_days === 'number') totals.total_absent_days += row.total_absent_days;
        if (typeof row.employees_late === 'number') totals.employees_late += row.employees_late;
        if (typeof row.employees_undertime === 'number') totals.employees_undertime += row.employees_undertime;
        if (typeof row.total_undertime_mins === 'number') totals.total_undertime_mins += row.total_undertime_mins;
    });
    
    // Calculate rates
    const attendanceRate = totals.total_working_days > 0 
        ? ((totals.total_present_days / totals.total_working_days) * 100).toFixed(2) + '%'
        : '0%';
    
    const absenteeismRate = totals.total_working_days > 0 
        ? ((totals.total_absent_days / totals.total_working_days) * 100).toFixed(2) + '%'
        : '0%';
    
    const tardinessFreq = totals.total_working_days > 0 
        ? ((totals.employees_late / totals.total_working_days) * 100).toFixed(2) + '%'
        : '0%';
    
    const undertimeFreq = totals.total_working_days > 0 
        ? ((totals.employees_undertime / totals.total_working_days) * 100).toFixed(2) + '%'
        : '0%';
    
    return {
        ...totals,
        attendance_rate: attendanceRate,
        absenteeism_rate: absenteeismRate,
        tardiness_frequency: tardinessFreq,
        undertime_frequency: undertimeFreq
    };
});

// Computed property for yearly summary
const yearlySummary = computed(() => {
    if (!annualData.value || annualData.value.length === 0) {
        return {
            total_working_days: 0,
            total_employees: 0,
            total_present_days: 0,
            total_absent_days: 0,
            total_tardiness_mins: 0,
            total_undertime_mins: 0
        };
    }
    
    const summary = {
        total_working_days: 0,
        total_employees: 0,
        total_present_days: 0,
        total_absent_days: 0,
        total_tardiness_mins: 0,
        total_undertime_mins: 0,
        employees_late_count: 0
    };
    
    annualData.value.forEach(row => {
        if (typeof row.total_working_days === 'number') summary.total_working_days += row.total_working_days;
        if (typeof row.headcount === 'number') summary.total_employees = Math.max(summary.total_employees, row.headcount);
        if (typeof row.total_present_days === 'number') summary.total_present_days += row.total_present_days;
        if (typeof row.total_absent_days === 'number') summary.total_absent_days += row.total_absent_days;
        if (typeof row.total_undertime_mins === 'number') summary.total_undertime_mins += row.total_undertime_mins;
        if (typeof row.employees_late === 'number') summary.employees_late_count += row.employees_late;
    });
    
    // Estimate tardiness (2 mins per late employee as per user's image)
    summary.total_tardiness_mins = summary.employees_late_count * 2;
    
    return summary;
});

const fetchAnnualReport = async () => {
    try {
        const response = await axios.get(`/api/reports/annual?year=${selectedYear.value}`);
        annualData.value = response.data;
    } catch (error) {
        console.error('Failed to fetch annual report:', error);
    }
};

const fetchDepartmentReport = async () => {
    try {
        const response = await axios.get(`/api/reports/monthly-department?year=${selectedYear.value}&month=${selectedMonth.value}`);
        departmentData.value = response.data;
    } catch (error) {
        console.error('Failed to fetch department report:', error);
    }
};

onMounted(() => {
    authStore.fetchUser();
    fetchAnnualReport();
    fetchDepartmentReport();
});

watch([selectedYear, selectedMonth], () => {
    if (activeTab.value === 'annual') fetchAnnualReport();
    else fetchDepartmentReport();
});

watch(activeTab, () => {
    if (activeTab.value === 'annual') fetchAnnualReport();
    else fetchDepartmentReport();
});

const getRateColor = (rateStr) => {
    if (typeof rateStr !== 'string') return '';
    const rate = parseFloat(rateStr);
    if (isNaN(rate)) return 'text-gray-400';
    if (rate >= 95) return 'text-green-600';
    if (rate >= 85) return 'text-yellow-600';
    return 'text-red-600';
};

const downloadExcel = () => {
    let data = activeTab.value === 'annual' ? annualData.value : departmentData.value;
    let filename = activeTab.value === 'annual' 
        ? `Annual_Report_${selectedYear.value}.xlsx` 
        : `Department_Report_${months[selectedMonth.value-1]}_${selectedYear.value}.xlsx`;

    const ws = XLSX.utils.json_to_sheet(data);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Report");
    XLSX.writeFile(wb, filename);
};
</script>
