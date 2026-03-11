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
                            :disabled="isExporting"
                            class="h-10 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-medium shadow-sm shadow-emerald-200 transition-all flex items-center gap-2 cursor-pointer disabled:opacity-70 disabled:cursor-not-allowed"
                        >
                            <i v-if="isExporting" class="pi pi-spin pi-spinner text-sm"></i>
                            <i v-else class="pi pi-file-excel"></i>
                            {{ isExporting ? 'Preparing Excel...' : 'Export to Excel' }}
                        </button>
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

                    <div v-if="activeTab === 'daily'" class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-600">Date:</span>
                        <select v-model="selectedDate" class="h-9 px-3 border border-gray-200 rounded-lg text-sm bg-white outline-none focus:ring-2 focus:ring-teal-500">
                            <option v-for="(date, i) in availableDates" :key="i" :value="date">{{ new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) }}</option>
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
                        @click="activeTab = 'daily'"
                        :class="[
                            'cursor-pointer px-4 py-2 rounded-lg text-sm font-medium transition-all',
                            activeTab === 'daily' ? 'bg-white text-teal-700 shadow-sm' : 'text-gray-500 hover:text-gray-700'
                        ]"
                    >
                        Daily Report
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

                    <!-- Annual Report Table (Format 1: Rows=Metrics, Cols=Months) -->
                    <div v-if="activeTab === 'annual'" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden animate-in fade-in duration-300">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead>
                                    <tr>
                                        <th colspan="2" class="px-6 py-4 font-bold text-center border border-gray-800 bg-gray-900 text-white">YEAR</th>
                                        <th colspan="12" class="px-6 py-4 font-bold text-center border border-gray-800 bg-gray-900 text-yellow-500">{{ selectedYear }}</th>
                                    </tr>
                                    <tr class="bg-gray-800 text-gray-200 text-xs text-center border border-gray-700">
                                        <th class="px-4 py-3 font-semibold w-48 border-r border-gray-700">Metric</th>
                                        <th class="px-4 py-3 font-semibold w-72 border-r border-gray-700">Details</th>
                                        <th v-for="m in months" :key="m" class="px-3 py-3 font-semibold border-r border-gray-700">{{ m }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 border border-gray-200">
                                    <template v-if="isLoadingAnnual">
                                        <tr v-for="i in 6" :key="i" class="animate-pulse bg-gray-50/50">
                                            <td class="px-4 py-3 border-r border-gray-200"><div class="h-4 bg-gray-200 rounded w-3/4"></div></td>
                                            <td class="px-4 py-3 border-r border-gray-200"><div class="h-3 bg-gray-200 rounded w-full"></div></td>
                                            <td v-for="m in 12" :key="m" class="px-3 py-3 border-r border-gray-200"><div class="h-4 bg-gray-200 rounded w-8 mx-auto"></div></td>
                                        </tr>
                                    </template>
                                    <template v-else>
                                        <tr class="hover:bg-gray-50 border-b border-gray-200" title="Total number of unique dates where at least one attendance record was logged.">
                                        <td class="px-4 py-3 font-bold text-gray-800 border-r border-gray-200 bg-gray-100/50">Total Working Days</td>
                                        <td class="px-4 py-3 text-gray-600 text-xs border-r border-gray-200 bg-gray-100/50">Calendar working days - official holidays</td>
                                        <td v-for="m in months" :key="m" class="px-3 py-3 text-center border-r border-gray-200">
                                            {{ annualData.find(d => d.month === m)?.total_working_days || '' }}
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-gray-50 border-b border-gray-200" title="Count of regular employees hired on or before the end of this month and not archived before start of month.">
                                        <td class="px-4 py-3 font-bold text-gray-800 border-r border-gray-200 bg-gray-100/50">Total Employees</td>
                                        <td class="px-4 py-3 text-gray-600 text-xs border-r border-gray-200 bg-gray-100/50">Headcount at start of period</td>
                                        <td v-for="m in months" :key="m" class="px-3 py-3 text-center border-r border-gray-200">
                                            {{ annualData.find(d => d.month === m)?.headcount || '' }}
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-gray-50 border-b border-gray-200" title="Sum of all instances an employee was logged as Present or Late.">
                                        <td class="px-4 py-3 font-bold text-gray-800 border-r border-gray-200 bg-gray-100/50">Total Present Days</td>
                                        <td class="px-4 py-3 text-gray-600 text-xs border-r border-gray-200 bg-gray-100/50">&Sigma; Present days for all employees</td>
                                        <td v-for="m in months" :key="m" class="px-3 py-3 text-center border-r border-gray-200">
                                            {{ annualData.find(d => d.month === m)?.total_present_days || '' }}
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-gray-50 border-b border-gray-200" title="Formula: (Headcount × Total Working Days) - Actual Recorded Days. Also includes days with only a Time-In or Time-Out.">
                                        <td class="px-4 py-3 font-bold text-gray-800 border-r border-gray-200 bg-gray-100/50">Total Absent Days</td>
                                        <td class="px-4 py-3 text-gray-600 text-xs border-r border-gray-200 bg-gray-100/50">&Sigma; Absent days for all employees</td>
                                        <td v-for="m in months" :key="m" class="px-3 py-3 text-center border-r border-gray-200">
                                            {{ annualData.find(d => d.month === m)?.total_absent_days || '' }}
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-gray-50 border-b border-gray-200" title="Sum of exact minutes past the employee's specific scheduled start time.">
                                        <td class="px-4 py-3 font-bold text-gray-800 border-r border-gray-200 bg-gray-100/50">Total Tardiness (mins)</td>
                                        <td class="px-4 py-3 text-gray-600 text-xs border-r border-gray-200 bg-gray-100/50">&Sigma; minutes late for all employees</td>
                                        <td v-for="m in months" :key="m" class="px-3 py-3 text-center border-r border-gray-200">
                                            {{ annualData.find(d => d.month === m)?.total_late_mins || '' }}
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-gray-50" title="Sum of exact minutes clocked out before the employee's scheduled end time. (Half Day counts as 240 lost mins).">
                                        <td class="px-4 py-3 font-bold text-gray-800 border-r border-gray-200 bg-gray-100/50">Total Undertimes/Half day (mins)</td>
                                        <td class="px-4 py-3 text-gray-600 text-xs border-r border-gray-200 bg-gray-100/50">&Sigma; minutes early departure</td>
                                        <td v-for="m in months" :key="m" class="px-3 py-3 text-center border-r border-gray-200">
                                            {{ annualData.find(d => d.month === m)?.total_undertime_mins || '' }}
                                        </td>
                                    </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Department Report Table (Not entirely specified in current images, keeping closest format but simplifying) -->
                    <div v-else-if="activeTab === 'department'" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden animate-in fade-in duration-300">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead>
                                    <tr class="bg-gray-900 text-white uppercase tracking-wider text-xs border border-gray-800">
                                        <th class="px-6 py-4 font-semibold w-48 border-r border-gray-800 text-center">Department</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800 text-center" title="Count of regular employees hired on or before the end of this month inside this department.">Total Employees</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800 text-center" title="Total number of unique dates where at least one attendance record was logged.">Actual Working Days</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800 text-center" title="Employees' scheduled shift length × Actual Working Days">Total Scheduled Hrs</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800 text-center" title="Count of employees who were late at least once">Late Employees</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800 text-center" title="Sum of minutes late">Total Late (mins)</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800 text-center" title="Standard hours worked (excluding overtime)">Regular Hrs</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800 text-center" title="Hours worked beyond scheduled shifts">Overtime Hrs</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800 text-center" title="Sum of all actual hours rendered (Regular + Overtime)">Total Actual Hrs</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 border border-gray-200">
                                    <template v-if="isLoadingDepartment">
                                        <tr v-for="i in 5" :key="i" class="animate-pulse bg-gray-50/50">
                                            <td class="px-6 py-3 border-r border-gray-200"><div class="h-4 bg-gray-200 rounded w-48"></div></td>
                                            <td class="px-4 py-3 border-r border-gray-200"><div class="h-4 bg-gray-200 rounded w-12 mx-auto"></div></td>
                                            <td class="px-4 py-3 border-r border-gray-200"><div class="h-4 bg-gray-200 rounded w-12 mx-auto"></div></td>
                                            <td class="px-4 py-3 border-r border-gray-200"><div class="h-4 bg-gray-200 rounded w-12 mx-auto"></div></td>
                                            <td class="px-4 py-3 border-r border-gray-200"><div class="h-4 bg-gray-200 rounded w-12 mx-auto"></div></td>
                                            <td class="px-4 py-3 border-r border-gray-200"><div class="h-4 bg-gray-200 rounded w-12 mx-auto"></div></td>
                                            <td class="px-4 py-3 border-r border-gray-200"><div class="h-4 bg-gray-200 rounded w-12 mx-auto"></div></td>
                                            <td class="px-4 py-3 border-r border-gray-200"><div class="h-4 bg-gray-200 rounded w-12 mx-auto"></div></td>
                                            <td class="px-4 py-3 border-r border-gray-200"><div class="h-4 bg-gray-200 rounded w-12 mx-auto"></div></td>
                                        </tr>
                                    </template>
                                    <template v-else-if="departmentData.length === 0">
                                        <tr>
                                            <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                                                <i class="pi pi-box mb-2 text-3xl opacity-50"></i>
                                                <p>No departmental data found for this period.</p>
                                            </td>
                                        </tr>
                                    </template>
                                    <template v-else>
                                        <tr v-for="row in departmentData" :key="row.department" class="hover:bg-gray-50">
                                        <td class="px-6 py-3 font-medium text-gray-800 border-r border-gray-200">{{ row.department }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-200">{{ row.total_employees }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-200">{{ row.total_working_days }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-200 font-medium text-gray-600">{{ row.total_scheduled_hours }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-200 text-orange-600">{{ row.total_late_employees }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-200 text-orange-600 font-bold">{{ row.total_late_mins }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-200">{{ row.regular_actual_hours }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-200 text-indigo-600 font-semibold">+ {{ row.overtime_actual_hours }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-200 font-bold text-teal-600">{{ row.total_actual_hours }}</td>
                                    </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Daily Report Table -->
                    <div v-else-if="activeTab === 'daily'" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden animate-in fade-in duration-300">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead>
                                    <tr class="bg-gray-900 text-white uppercase tracking-wider text-xs border border-gray-800">
                                        <th class="px-6 py-4 font-semibold w-48 border-r border-gray-800 text-center">Department</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800 text-center">Total Employees</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800 text-center text-teal-300">Employees Present</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800 text-center text-red-300">Employees Absent</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800 text-center text-orange-300">Late Employees</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800 text-center text-orange-300">Total Late (mins)</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800 text-center">Regular Hrs</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800 text-center text-indigo-300">Overtime Hrs</th>
                                        <th class="px-4 py-4 text-center border-r border-gray-800 text-center text-teal-300">Total Actual Hrs</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 border border-gray-200">
                                    <template v-if="isLoadingDaily">
                                        <tr v-for="i in 5" :key="i" class="animate-pulse bg-gray-50/50">
                                            <td colspan="9" class="px-6 py-3 border-r border-gray-200"><div class="h-4 bg-gray-200 rounded w-full"></div></td>
                                        </tr>
                                    </template>
                                    <template v-else-if="dailyData.length === 0">
                                        <tr>
                                            <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                                                <i class="pi pi-box mb-2 text-3xl opacity-50"></i>
                                                <p>No departmental data found for this date.</p>
                                            </td>
                                        </tr>
                                    </template>
                                    <template v-else>
                                        <tr v-for="row in dailyData" :key="row.department" class="hover:bg-gray-50">
                                        <td class="px-6 py-3 font-medium text-gray-800 border-r border-gray-200">{{ row.department }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-200">{{ row.total_employees }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-200 text-teal-600 font-bold">{{ row.total_present }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-200 text-red-600 font-bold">{{ row.total_absent }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-200 text-orange-600">{{ row.total_late_employees }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-200 text-orange-600 font-bold">{{ row.total_late_mins }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-200">{{ row.regular_actual_hours }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-200 text-indigo-600 font-semibold">+ {{ row.overtime_actual_hours }}</td>
                                        <td class="px-4 py-3 text-center border-r border-gray-200 font-bold text-teal-600">{{ row.total_actual_hours }}</td>
                                    </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Yearly Summary Table (Format 2 and 3 style) -->
                    <div v-else-if="activeTab === 'yearly'" class="bg-white rounded-2xl shadow-sm overflow-hidden animate-in fade-in duration-300 overflow-x-auto">
                        <table class="w-[1200px] max-w-none text-left border-collapse text-sm">
                            <thead>
                                <tr>
                                    <th colspan="2" class="px-5 py-3 font-bold text-center border border-gray-800 bg-gray-900 text-white">YEAR</th>
                                    <th colspan="1" class="px-5 py-3 font-bold text-center border border-gray-800 bg-gray-900 text-yellow-500">{{ selectedYear }}</th>
                                    <th colspan="7" class="border border-white bg-white"></th>
                                </tr>
                                <tr class="bg-gray-800 text-gray-200 text-xs text-center border border-gray-700">
                                    <th class="px-4 py-3 font-semibold border-r border-gray-700 w-32">Month</th>
                                    <th class="px-4 py-3 font-semibold border-r border-gray-700 w-32" title="Active regular employees by end of month.">Headcount</th>
                                    <th class="px-4 py-3 font-semibold border-r border-gray-700 w-32" title="Total instances an employee was marked Present or Late.">Total Present<br>Days</th>
                                    <th class="px-4 py-3 font-semibold border-r border-gray-700 w-32" title="Total unique dates with records for the company.">Total Working<br>Days</th>
                                    <th class="px-4 py-3 font-semibold border-r border-gray-700 w-36" title="Formula: (Present Days + Undertimes) ÷ (Headcount × Working Days)">Attendance Rate<br>(%)</th>
                                    <th class="px-4 py-3 font-semibold border-r border-gray-700 w-32" title="Total days no record is found or explicitly marked absent for active employees.">Total Absent<br>Days</th>
                                    <th class="px-4 py-3 font-semibold border-r border-gray-700 w-36" title="Formula: (Absent Days) ÷ (Headcount × Working Days)">Absenteeism<br>Rate (%)</th>
                                     <th class="px-4 py-3 font-semibold border-r border-gray-700 w-36" title="Count of times employees clocked in past their scheduled standard shift time.">No. of employees<br>late</th>
                                     <th class="px-4 py-3 font-semibold border-r border-gray-700 w-36" title="Sum of all minutes late across all employees. Formula: Σ (Actual Time-In − Scheduled Start) per late record. Capped at 480 mins per incident.">Total Late<br>(mins)</th>
                                     <th class="px-4 py-3 font-semibold border-r border-gray-700 w-36" title="Formula: (Employees Late) ÷ (Headcount × Working Days)">Tardiness<br>Frequency (%)</th>
                                    <th class="px-4 py-3 font-semibold border-r border-gray-700 w-36" title="Count of times employees clocked out early or only fulfilled Half Day hours.">No. of employees<br>undertime/half day</th>
                                    <th class="px-4 py-3 font-semibold w-40" title="Formula: (Employees Undertime) ÷ (Headcount × Working Days)">Undertime / Half<br>day Frequency<br>(%)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 border border-gray-200">
                                <template v-if="isLoadingAnnual">
                                    <tr v-for="i in 12" :key="i" class="animate-pulse bg-gray-50/50 text-center">
                                        <td class="px-4 py-2 border-r border-gray-200 text-left bg-gray-100/30"><div class="h-4 bg-gray-200 rounded w-20"></div></td>
                                        <td v-for="col in 11" :key="col" class="px-4 py-2 border-r border-gray-200"><div class="h-4 bg-gray-200 rounded w-8 mx-auto"></div></td>
                                    </tr>
                                </template>
                                <template v-else>
                                    <tr v-for="m in months" :key="m" class="hover:bg-gray-50 border-b border-gray-200 text-center">
                                    <td class="px-4 py-2 font-medium text-gray-800 border-r border-gray-200 text-left bg-gray-100/30">{{ m }}</td>
                                    
                                    <td class="px-4 py-2 border-r border-gray-200">
                                        {{ annualData.find(d => d.month === m)?.headcount || '0' }}
                                    </td>
                                    <td class="px-4 py-2 border-r border-gray-200">
                                        {{ annualData.find(d => d.month === m)?.total_present_days || '0' }}
                                    </td>
                                    <td class="px-4 py-2 border-r border-gray-200">
                                        {{ annualData.find(d => d.month === m)?.total_working_days || '0' }}
                                    </td>
                                    <td class="px-4 py-2 border-r border-gray-200 font-medium">
                                        {{ annualData.find(d => d.month === m)?.total_working_days ? annualData.find(d => d.month === m).attendance_rate : '-' }}
                                    </td>
                                    <td class="px-4 py-2 border-r border-gray-200">
                                        {{ annualData.find(d => d.month === m)?.total_absent_days || '0' }}
                                    </td>
                                    <td class="px-4 py-2 border-r border-gray-200 font-medium">
                                        {{ annualData.find(d => d.month === m)?.total_working_days ? annualData.find(d => d.month === m).absenteeism_rate : '-' }}
                                    </td>
                                     <td class="px-4 py-2 border-r border-gray-200">
                                         {{ annualData.find(d => d.month === m)?.employees_late || '0' }}
                                     </td>
                                     <td class="px-4 py-2 border-r border-gray-200 font-medium text-orange-600"
                                         title="Sum of all exact minutes late for this month. Capped at 480 mins per incident.">
                                         {{ annualData.find(d => d.month === m)?.total_working_days ? (annualData.find(d => d.month === m)?.total_late_mins || 0) : '-' }}
                                     </td>
                                    <td class="px-4 py-2 border-r border-gray-200 font-medium bg-gray-50">
                                        {{ annualData.find(d => d.month === m)?.total_working_days ? annualData.find(d => d.month === m).tardiness_frequency : '-' }}
                                    </td>
                                    <td class="px-4 py-2 border-r border-gray-200">
                                        {{ annualData.find(d => d.month === m)?.employees_undertime || '0' }}
                                    </td>
                                    <td class="px-4 py-2 font-medium bg-gray-50">
                                        {{ annualData.find(d => d.month === m)?.total_working_days ? annualData.find(d => d.month === m).undertime_frequency : '-' }}
                                    </td>
                                </tr>
                                </template>
                            </tbody>
                        </table>
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
const selectedYear = ref(new Date().getFullYear());
const selectedMonth = ref(new Date().getMonth() + 1); // current month
const selectedDate = ref(new Date().toISOString().split('T')[0]);
const isExporting = ref(false);
const isLoadingAnnual = ref(false);
const isLoadingDepartment = ref(false);
const isLoadingDaily = ref(false);
const availableDates = ref([]);

const months = [
    'January', 'February', 'March', 'April', 'May', 'June', 
    'July', 'August', 'September', 'October', 'November', 'December'
];

const annualData = ref([]);
const departmentData = ref([]);
const dailyData = ref([]);


// Computed property for yearly summary / totals
const annualTotals = computed(() => {
    if (!annualData.value || annualData.value.length === 0) {
        return {
            headcount: 0,
            total_present_days: 0,
            total_working_days: 0,
            attendance_rate: '0%',
            total_absent_days: 0,
            absenteeism_rate: '0%',
            employees_late: 0,
            tardiness_frequency: '0%',
            employees_undertime: 0,
            total_undertime_mins: 0,
            undertime_frequency: '0%'
        };
    }
    
    let stats = {
        headcount: 0,
        total_present_days: 0,
        total_working_days: 0,
        possible_person_days: 0,
        total_absent_days: 0,
        employees_late: 0,
        employees_undertime: 0,
        total_undertime_mins: 0
    };
    
    annualData.value.forEach(row => {
        stats.headcount = Math.max(stats.headcount, row.headcount || 0);
        stats.total_present_days += row.total_present_days || 0;
        stats.total_working_days += row.total_working_days || 0;
        stats.possible_person_days += row.possible_person_days || 0;
        stats.total_absent_days += row.total_absent_days || 0;
        stats.employees_late += row.employees_late || 0;
        stats.employees_undertime += row.employees_undertime || 0;
        stats.total_undertime_mins += row.total_undertime_mins || 0;
    });
    
    const attRate = stats.possible_person_days > 0 ? (stats.total_present_days / stats.possible_person_days * 100).toFixed(1) : 0;
    const absRate = stats.possible_person_days > 0 ? (stats.total_absent_days / stats.possible_person_days * 100).toFixed(1) : 0;
    const lateFreq = stats.possible_person_days > 0 ? (stats.employees_late / stats.possible_person_days * 100).toFixed(1) : 0;
    const undFreq = stats.possible_person_days > 0 ? (stats.employees_undertime / stats.possible_person_days * 100).toFixed(1) : 0;
    
    return {
        ...stats,
        attendance_rate: attRate + '%',
        absenteeism_rate: absRate + '%',
        tardiness_frequency: lateFreq + '%',
        undertime_frequency: undFreq + '%'
    };
});

const calculateDividendPerZero = (a, b) => {
    return b > 0 ? (a/b*100).toFixed(1) + '%' : '-';
};

const fetchAnnualReport = async () => {
    isLoadingAnnual.value = true;
    try {
        const response = await axios.get(`/api/reports/annual?year=${selectedYear.value}`);
        annualData.value = response.data;
    } catch (error) {
        console.error('Failed to fetch annual report:', error);
    } finally {
        isLoadingAnnual.value = false;
    }
};

const fetchDepartmentReport = async () => {
    isLoadingDepartment.value = true;
    try {
        const response = await axios.get(`/api/reports/monthly-department?year=${selectedYear.value}&month=${selectedMonth.value}`);
        departmentData.value = response.data;
    } catch (error) {
        console.error('Failed to fetch department report:', error);
    } finally {
        isLoadingDepartment.value = false;
    }
};

const fetchDailyReport = async () => {
    isLoadingDaily.value = true;
    try {
        const response = await axios.get(`/api/reports/daily-department?date=${selectedDate.value}`);
        dailyData.value = response.data;
    } catch (error) {
        console.error('Failed to fetch daily report:', error);
    } finally {
        isLoadingDaily.value = false;
    }
};

const fetchAvailableDates = async () => {
    try {
        const response = await axios.get('/api/attendance-records/dates');
        availableDates.value = response.data;
        if (availableDates.value.length > 0) {
            selectedDate.value = availableDates.value[0];
        }
    } catch (error) {
        console.error('Failed to fetch available dates:', error);
    }
};

onMounted(() => {
    authStore.fetchUser();
    fetchAvailableDates();
    fetchAnnualReport();
    fetchDepartmentReport();
    fetchDailyReport();
});

watch([selectedYear, selectedMonth], () => {
    fetchAnnualReport();
    fetchDepartmentReport();
});

watch(selectedDate, () => {
    fetchDailyReport();
});

watch(activeTab, () => {
    if (activeTab.value === 'annual' || activeTab.value === 'yearly') {
        fetchAnnualReport();
    } else if (activeTab.value === 'department') {
        fetchDepartmentReport();
    } else if (activeTab.value === 'daily') {
        fetchDailyReport();
    }
});

const getRateColor = (rateStr) => {
    if (typeof rateStr !== 'string') return '';
    const rate = parseFloat(rateStr);
    if (isNaN(rate)) return 'text-gray-400';
    if (rate >= 95) return 'text-green-600';
    if (rate >= 85) return 'text-yellow-600';
    return 'text-red-600';
};

const downloadExcel = async () => {
    if (isExporting.value) return;
    
    isExporting.value = true;
    try {
        const url = `/api/reports/attendance/export?year=${selectedYear.value}&month=${selectedMonth.value}`;
        const response = await axios({
            url: url,
            method: 'GET',
            responseType: 'blob',
        });

        const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const link = document.createElement('a');
        link.href = window.URL.createObjectURL(blob);
        link.download = `HatQ_Reports_${selectedYear.value}_${months[selectedMonth.value-1]}.xlsx`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } catch (error) {
        console.error('Export failed:', error);
        alert('Failed to generate export. Please try again.');
    } finally {
        isExporting.value = false;
    }
};
</script>
