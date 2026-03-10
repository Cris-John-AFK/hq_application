<template>
    <div class="max-w-7xl mx-auto space-y-6 pb-10">
        <!-- 0. Company Bulletins -->
        <BulletinBoard />

        <!-- 1. Stats Grid (5 Columns) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
            <StatCard 
                icon="pi-users" 
                :value="leaveStats.total_employees" 
                label="Total Employees" 
                :subLabel="`As of ${currentMonthYear}`"
                iconBgClass="bg-orange-50"
                iconTextClass="text-orange-500"
                :loading="loading"
            />
            <StatCard 
                icon="pi-check-circle" 
                :value="leaveStats.approved_this_month" 
                label="Monthly Approved" 
                :subLabel="`This month · ${currentMonthYear}`"
                iconBgClass="bg-green-50"
                iconTextClass="text-green-500"
                :loading="loading"
            />
            <StatCard 
                icon="pi-calendar-plus" 
                :value="leaveStats.scheduled" 
                label="Upcoming Leaves" 
                subLabel="Scheduled ahead"
                iconBgClass="bg-blue-50"
                iconTextClass="text-blue-500"
                :loading="loading"
            />
            <StatCard 
                icon="pi-money-bill" 
                :value="leaveStats.approved_paid" 
                label="Leaves with Pay" 
                :subLabel="`This month · ${currentMonthYear}`"
                iconBgClass="bg-teal-50"
                iconTextClass="text-teal-600"
                :loading="loading"
            />
            <StatCard 
                icon="pi-calendar" 
                :value="leaveStats.on_leave_today" 
                label="On Leave Today" 
                :subLabel="`Today · ${currentDay}`"
                iconBgClass="bg-purple-50"
                iconTextClass="text-purple-500"
                :loading="loading"
            />
        </div>


        <!-- 2. Mid Section: Leave Overview & Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Compact Leave Cards -->
            <div class="lg:col-span-1 space-y-3">
                <div class="pl-1 flex items-center justify-between">
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                        Leave Requests
                    </h3>
                </div>
                <div v-if="loading" class="grid grid-cols-2 gap-3">
                    <div v-for="i in 4" :key="i" class="animate-pulse bg-gray-50 border border-gray-100 rounded-2xl h-32 flex flex-col items-center justify-center">
                        <div class="w-10 h-10 rounded-xl bg-gray-200 mb-3"></div>
                        <div class="h-4 w-8 bg-gray-200 rounded mb-2"></div>
                        <div class="h-2 w-12 bg-gray-200 rounded"></div>
                    </div>
                </div>
                <div v-else class="grid grid-cols-2 gap-3">
                    <!-- Status Cards -->
                    <div 
                        v-for="stat in [
                            { label: 'Pending', count: leaveStats.pending, bg: 'bg-amber-50', border: 'border-amber-200', text: 'text-amber-700', icon: 'pi-clock', iconBg: 'bg-amber-500' },
                            { label: 'Approved', count: leaveStats.approved, bg: 'bg-emerald-50', border: 'border-emerald-200', text: 'text-emerald-700', icon: 'pi-check-circle', iconBg: 'bg-emerald-500' },
                            { label: 'Rejected', count: leaveStats.rejected, bg: 'bg-rose-50', border: 'border-rose-200', text: 'text-rose-700', icon: 'pi-times-circle', iconBg: 'bg-rose-500' },
                            { label: 'Cancelled', count: leaveStats.cancelled, bg: 'bg-slate-50', border: 'border-slate-200', text: 'text-slate-700', icon: 'pi-ban', iconBg: 'bg-slate-500' }
                        ]" 
                        :key="stat.label" 
                        class="group relative rounded-2xl p-4 border transition-all duration-300 hover:shadow-md h-full flex flex-col items-center justify-center text-center"
                        :class="[stat.bg, stat.border]"
                    >
                        <div 
                            class="w-10 h-10 rounded-xl flex items-center justify-center shadow-sm mb-3 group-hover:scale-110 transition-transform text-white"
                            :class="stat.iconBg"
                        >
                            <i class="pi text-lg" :class="stat.icon"></i>
                        </div>
                        <div>
                            <p class="text-2xl font-black text-gray-900 leading-none mb-1">{{ stat.count }}</p>
                            <p class="text-[10px] font-black uppercase tracking-[0.1em]" :class="stat.text">{{ stat.label }}</p>
                        </div>
                    </div>
                </div>

                <!-- Attendance Trend Graph -->
                <div v-if="!loading" class="mt-6 flex-1">
                    <AttendanceGraph />
                </div>

            </div>

            <!-- Right: Recent Activity Panel with Tabs -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden h-full">
                    <!-- Tab Headers -->
                    <div class="flex border-b border-gray-200 bg-gray-50">
                        <button 
                            @click="activeTab = 'leaves'"
                            :class="[
                                'cursor-pointer flex-1 px-6 py-3 text-sm font-semibold transition-all relative h-16',
                                activeTab === 'leaves' 
                                    ? 'text-teal-600 border-b-2 border-teal-600 bg-white' 
                                    : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'
                            ]"
                        >
                            <i class="pi pi-calendar-times mr-2"></i>
                            Recent Leaves
                            <span v-if="leaveStats.pending > 0" class="ml-2 px-1.5 py-0.5 bg-rose-500 text-white text-[10px] rounded-full animate-pulse shadow-sm">
                                {{ leaveStats.pending }}
                            </span>
                        </button>
                        <button 
                            @click="activeTab = 'attendance'"
                            :class="[
                                'cursor-pointer flex-1 px-6 py-3 text-sm font-semibold transition-all relative flex flex-col items-center justify-center h-16',
                                activeTab === 'attendance' 
                                    ? 'text-teal-600 border-b-2 border-teal-600 bg-white' 
                                    : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'
                            ]"
                        >
                            <span><i class="pi pi-clock mr-2"></i>Recent Attendance</span>
                            <span class="text-[9px] font-medium opacity-60">Imported on Jan 23, 2026</span>
                        </button>
                    </div>


                    <!-- Tab Content -->
                    <div class="p-4">
                        <div v-if="loading" class="space-y-4">
                            <div v-for="i in 3" :key="i" class="animate-pulse flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-full bg-gray-200"></div>
                                    <div class="space-y-2">
                                        <div class="h-4 w-24 bg-gray-200 rounded"></div>
                                        <div class="h-2 w-16 bg-gray-200 rounded"></div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="h-8 w-12 bg-gray-200 rounded"></div>
                                    <div class="h-8 w-12 bg-gray-200 rounded"></div>
                                </div>
                            </div>
                        </div>
                        <template v-else>
                            <!-- Attendance Tab -->
                            <div v-if="activeTab === 'attendance'" class="space-y-3">
                                <div v-if="paginatedAttendance.length === 0" class="py-10 text-center text-gray-500">
                                    <i class="pi pi-info-circle mb-2 block text-xl"></i>
                                    <p class="text-xs">No recent attendance records found.</p>
                                </div>
                                <div v-for="record in paginatedAttendance" :key="record.id" class="group flex items-center justify-between p-4 bg-white rounded-xl hover:shadow-md transition-all duration-200 border border-gray-100 hover:border-teal-200 shadow-sm mb-3">
                                    <div class="flex items-center gap-4">
                                        <!-- Avatar with Ring -->
                                        <div class="relative">
                                            <div class="w-12 h-12 rounded-full overflow-hidden ring-4 ring-teal-50 shadow-inner flex items-center justify-center bg-gray-100 group-hover:scale-105 transition-transform duration-200">
                                                <img v-if="record.avatar" :src="record.avatar" class="w-full h-full object-cover">
                                                <span v-else class="text-sm font-black text-teal-600 uppercase tracking-widest">{{ record.initials }}</span>
                                            </div>
                                            <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 border-2 border-white rounded-full shadow-sm"></div>
                                        </div>

                                        <div>
                                            <p class="font-black text-gray-800 text-sm leading-tight mb-0.5 tracking-tight">{{ record.name }}</p>
                                            <div class="flex items-center gap-2">
                                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                                                    <i class="pi pi-calendar text-[10px]"></i>
                                                    {{ record.date }}
                                                </span>
                                                <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                                <span 
                                                    class="text-[10px] font-black underline uppercase tracking-wider"
                                                    :class="{
                                                        'text-emerald-600': record.status === 'Present',
                                                        'text-amber-600': record.status === 'Half Day',
                                                        'text-rose-600': record.status === 'Absent',
                                                        'text-orange-600': record.status === 'Late'
                                                    }"
                                                >
                                                    {{ record.status || 'Present' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-6">
                                        <div class="flex flex-col items-center">
                                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-[0.1em] mb-0.5">Time In</span>
                                            <div class="px-2.5 py-1 bg-emerald-50 rounded-lg border border-emerald-100">
                                                <span class="text-xs font-black text-emerald-700 tabular-nums uppercase">{{ record.timeIn }}</span>
                                            </div>
                                        </div>
                                        <div class="flex flex-col items-center">
                                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-[0.1em] mb-0.5">Time Out</span>
                                            <div class="px-2.5 py-1 bg-rose-50 rounded-lg border border-rose-100">
                                                <span class="text-xs font-black text-rose-700 tabular-nums uppercase">{{ record.timeOut }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Attendance Pagination -->
                                <div v-if="recentAttendance.length > pageSize" class="flex justify-center items-center gap-4 mt-4 pt-4 border-t border-gray-100">
                                    <button @click="attendancePage > 1 ? attendancePage-- : null" :disabled="attendancePage === 1" class="p-2 rounded-lg hover:bg-gray-100 disabled:opacity-30">
                                        <i class="pi pi-chevron-left text-xs"></i>
                                    </button>
                                    <span class="text-xs font-bold text-gray-400 uppercase">Page {{ attendancePage }} of {{ Math.ceil(recentAttendance.length / pageSize) }}</span>
                                    <button @click="attendancePage < Math.ceil(recentAttendance.length / pageSize) ? attendancePage++ : null" :disabled="attendancePage === Math.ceil(recentAttendance.length / pageSize)" class="p-2 rounded-lg hover:bg-gray-100 disabled:opacity-30">
                                        <i class="pi pi-chevron-right text-xs"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Leaves Tab -->
                            <div v-if="activeTab === 'leaves'" class="space-y-3">
                                <div v-if="paginatedLeaves.length === 0" class="py-10 text-center text-gray-500">
                                    <i class="pi pi-info-circle mb-2 block text-xl"></i>
                                    <p class="text-xs">No recent leave requests found.</p>
                                </div>
                                <div v-for="leave in paginatedLeaves" :key="leave.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer border border-gray-100 group" @click="goToRequest(leave.id)">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center overflow-hidden ring-2 ring-white shadow-sm">
                                            <img v-if="leave.avatar" :src="leave.avatar" class="w-full h-full object-cover">
                                            <span v-else class="text-sm font-bold text-amber-600">{{ leave.initials }}</span>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <p class="font-bold text-gray-800 text-sm">{{ leave.name }}</p>
                                                <span v-if="leave.status === 'Pending'" class="px-1.5 py-0.5 bg-rose-500 text-white text-[9px] font-black rounded uppercase tracking-tighter animate-bounce h-fit">New</span>
                                            </div>
                                            <p class="text-[10px] text-gray-500 font-medium">
                                                {{ leave.type }} • <span class="bg-amber-50 text-amber-700 px-1 rounded">{{ leave.duration }}</span> • <span class="text-teal-600 font-bold">{{ leave.timeAgo }}</span>
                                            </p>
                                        </div>
                                    </div>
                                    <span :class="[
                                        'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border shadow-sm',
                                        leave.status === 'Approved' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' :
                                        leave.status === 'Pending' ? 'bg-amber-50 text-amber-700 border-amber-100' :
                                        'bg-rose-50 text-rose-700 border-rose-100'
                                    ]">{{ leave.status }}</span>
                                </div>

                                <!-- Leaves Pagination -->
                                <div v-if="recentLeaves.length > pageSize" class="flex justify-center items-center gap-4 mt-4 pt-4 border-t border-gray-100">
                                    <button @click="leavesPage > 1 ? leavesPage-- : null" :disabled="leavesPage === 1" class="p-2 rounded-lg hover:bg-gray-100 disabled:opacity-30">
                                        <i class="pi pi-chevron-left text-xs"></i>
                                    </button>
                                    <span class="text-xs font-bold text-gray-400 uppercase">Page {{ leavesPage }} of {{ Math.ceil(recentLeaves.length / pageSize) }}</span>
                                    <button @click="leavesPage < Math.ceil(recentLeaves.length / pageSize) ? leavesPage++ : null" :disabled="leavesPage === Math.ceil(recentLeaves.length / pageSize)" class="p-2 rounded-lg hover:bg-gray-100 disabled:opacity-30">
                                        <i class="pi pi-chevron-right text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Leave Analytics Section -->
        <!-- 3. Attendance Roster & Watchlist Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Employee Attendance Roster -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm flex flex-col p-6 min-h-[400px]">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] flex items-center gap-2 mb-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                            Employee Roster
                        </h3>
                        <p class="text-[9px] font-bold text-teal-500/60 uppercase tracking-widest pl-3.5 italic">Attendance Directory</p>
                    </div>
                </div>

                <div class="relative mb-4">
                    <input 
                        v-model="rosterSearch"
                        @input="debounceRosterSearch"
                        type="text" 
                        placeholder="Search employee name or ID..." 
                        class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all"
                    >
                    <i class="pi pi-search absolute left-3.5 top-2.5 text-gray-400 text-sm"></i>
                </div>
                
                <div v-if="loadingRoster" class="flex-1 flex flex-col gap-3">
                    <div v-for="i in 5" :key="i" class="h-12 bg-gray-50 animate-pulse rounded-xl"></div>
                </div>
                <div v-else class="flex-1 flex flex-col justify-between overflow-hidden">
                    <div class="space-y-3 overflow-y-auto custom-scrollbar pr-2 max-h-[300px]">
                        <div 
                            v-for="emp in dashboardEmployees" 
                            :key="emp.id" 
                            @click="openEmployeeAttendance(emp)"
                            class="flex items-center justify-between p-3 bg-gray-50/50 rounded-xl border border-gray-100 hover:border-teal-200 transition-all hover:bg-white hover:shadow-sm cursor-pointer group"
                        >
                            <div class="flex items-center gap-3">
                                <div class="shrink-0 w-8 h-8 rounded-full bg-teal-50 flex items-center justify-center overflow-hidden border border-teal-100 text-[10px] font-black text-teal-600 uppercase">
                                    <img v-if="emp.avatar" :src="emp.avatar" class="w-full h-full object-cover">
                                    <span v-else>{{ getInitials(emp.name) }}</span>
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <span class="text-xs font-black text-gray-800 group-hover:text-teal-600 transition-colors truncate">{{ emp.name }}</span>
                                    <span class="text-[9px] text-gray-400 font-bold tracking-widest truncate">{{ emp.employee_id }}</span>
                                </div>
                            </div>
                            <i class="pi pi-angle-right text-gray-300 group-hover:text-teal-400 transition-colors"></i>
                        </div>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="flex justify-center flex-wrap items-center gap-4 mt-4 pt-3 border-t border-gray-100">
                        <button @click="fetchDashboardEmployees(empPage - 1)" :disabled="empPage === 1" class="p-1.5 rounded-lg hover:bg-gray-100 disabled:opacity-30 cursor-pointer text-gray-500 transition-colors">
                            <i class="pi pi-chevron-left text-[10px]"></i>
                        </button>
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Page {{ empPage }} of {{ totalEmpPages }}</span>
                        <button @click="fetchDashboardEmployees(empPage + 1)" :disabled="empPage === totalEmpPages" class="p-1.5 rounded-lg hover:bg-gray-100 disabled:opacity-30 cursor-pointer text-gray-500 transition-colors">
                            <i class="pi pi-chevron-right text-[10px]"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Chronic Offenders Tracker -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 flex flex-col min-h-[400px]">
                <div class="flex items-center justify-between mb-1">
                    <div>
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] flex items-center gap-2 mb-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                            Absenteeism Tracker
                        </h3>
                        <p class="text-[9px] font-bold text-rose-500/60 uppercase tracking-widest pl-3.5 italic">Chronic Lates &amp; Absences</p>
                    </div>
                    <div class="flex bg-gray-50 p-1 rounded-lg border border-gray-100 shadow-inner">
                        <button 
                            @click="offenderFilter = 'all'"
                            class="px-3 py-1 text-[9px] font-black uppercase tracking-widest rounded-md transition-all whitespace-nowrap"
                            :class="offenderFilter === 'all' ? 'bg-white text-gray-800 shadow-sm border border-gray-200' : 'text-gray-400 hover:text-gray-600'"
                        >
                            All
                        </button>
                        <button 
                            @click="offenderFilter = 'critical'"
                            class="px-3 py-1 text-[9px] font-black uppercase tracking-widest rounded-md transition-all whitespace-nowrap flex items-center gap-1"
                            :class="offenderFilter === 'critical' ? 'bg-rose-500 text-white shadow-sm' : 'text-rose-400 hover:text-rose-600'"
                        >
                            Critical
                        </button>
                        <button 
                            @click="offenderFilter = 'other'"
                            class="px-3 py-1 text-[9px] font-black uppercase tracking-widest rounded-md transition-all whitespace-nowrap"
                            :class="offenderFilter === 'other' ? 'bg-white text-gray-800 shadow-sm border border-gray-200' : 'text-gray-400 hover:text-gray-600'"
                        >
                            Other
                        </button>
                    </div>
                </div>

                <!-- Legend -->
                <div class="flex gap-3 mb-3 flex-wrap">
                    <span class="flex items-center gap-1 text-[9px] font-black text-rose-600 uppercase tracking-widest"><span class="w-2 h-2 rounded-full bg-rose-500 inline-block"></span> Critical: 5+ offenses</span>
                    <span class="flex items-center gap-1 text-[9px] font-black text-orange-500 uppercase tracking-widest"><span class="w-2 h-2 rounded-full bg-orange-400 inline-block"></span> Notice: 3–4</span>
                    <span class="flex items-center gap-1 text-[9px] font-black text-yellow-600 uppercase tracking-widest"><span class="w-2 h-2 rounded-full bg-yellow-400 inline-block"></span> Watch: 1–2</span>
                </div>

                <div v-if="loadingAnomalies" class="flex-1 space-y-3">
                    <div v-for="i in 5" :key="i" class="h-14 bg-gray-50 animate-pulse rounded-xl"></div>
                </div>
                <div v-else class="flex-1 flex flex-col justify-start">
                    <div class="space-y-2 overflow-y-auto custom-scrollbar pr-1 max-h-[310px]">
                        <div v-if="filteredOffenders.length === 0" class="flex flex-col items-center justify-center h-40 text-center">
                            <i class="pi pi-shield text-4xl text-emerald-200 mb-3"></i>
                            <p class="text-xs text-emerald-600 font-bold uppercase tracking-widest">No chronic offenders</p>
                            <p class="text-[10px] text-gray-400 mt-1">All employees are within normal range!</p>
                        </div>

                        <div
                            v-for="offender in filteredOffenders"
                            :key="offender.employee_id"
                            class="flex items-center gap-3 p-3 rounded-xl border transition-all hover:shadow-sm"
                            :class="{
                                'bg-rose-50/60 border-rose-200 hover:border-rose-400': offender.level === 'critical',
                                'bg-orange-50/60 border-orange-200 hover:border-orange-400': offender.level === 'notice',
                                'bg-yellow-50/40 border-yellow-200 hover:border-yellow-400': offender.level === 'watch',
                            }"
                        >
                            <!-- Severity Badge -->
                            <div
                                class="shrink-0 w-9 h-9 rounded-lg flex flex-col items-center justify-center text-white font-black text-[11px] shadow-sm"
                                :class="{
                                    'bg-rose-500': offender.level === 'critical',
                                    'bg-orange-400': offender.level === 'notice',
                                    'bg-yellow-400': offender.level === 'watch',
                                }"
                                :title="`${offender.total} total offense(s)`"
                            >
                                {{ offender.total }}
                            </div>

                            <!-- Name & Breakdown -->
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-black text-gray-800 truncate leading-tight">{{ offender.name }}</p>
                                <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                                    <span v-if="offender.lates > 0" class="text-[9px] font-bold text-orange-600 bg-orange-50 border border-orange-100 px-1.5 py-0.5 rounded-full">
                                        ⏰ {{ offender.lates }} Late{{ offender.lates > 1 ? 's' : '' }}
                                    </span>
                                    <span v-if="offender.absences > 0" class="text-[9px] font-bold text-rose-600 bg-rose-50 border border-rose-100 px-1.5 py-0.5 rounded-full">
                                        ✗ {{ offender.absences }} Absent{{ offender.absences > 1 ? 's' : '' }}
                                    </span>
                                    <span v-if="offender.halfDays > 0" class="text-[9px] font-bold text-yellow-600 bg-yellow-50 border border-yellow-100 px-1.5 py-0.5 rounded-full">
                                        ◑ {{ offender.halfDays }} Half Day{{ offender.halfDays > 1 ? 's' : '' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Warning Level -->
                            <div class="shrink-0 flex flex-col items-center gap-1">
                                <span
                                    class="text-[9px] font-black px-2 py-0.5 rounded-full uppercase tracking-widest border"
                                    :class="{
                                        'text-rose-700 bg-rose-100 border-rose-200': offender.level === 'critical',
                                        'text-orange-600 bg-orange-100 border-orange-200': offender.level === 'notice',
                                        'text-yellow-700 bg-yellow-100 border-yellow-200': offender.level === 'watch',
                                    }"
                                >
                                    {{ offender.level === 'critical' ? '🔴 Critical' : offender.level === 'notice' ? '🟠 Notice' : '🟡 Watch' }}
                                </span>
                                <span class="text-[8px] text-gray-400 font-medium">{{ offender.lastSeen }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 5. Leave Analytics -->
        <div class="w-full pt-4">
            <LeaveAnalytics />
        </div>

        <EmployeeAttendanceModal 
            v-model="showEmployeeModal" 
            :employee="selectedEmployee" 
            :records="employeeRecords"
            :loading="loadingEmployeeRecords" 
        />
    </div>
</template>

<script setup>
    import { ref, onMounted, computed } from 'vue';
    import { useAuthStore } from '../../stores/auth';
    import { useLeaveStore } from '../../stores/leaves';
    import { useEmployeeStore } from '../../stores/employees';
    import { storeToRefs } from 'pinia';
    import StatCard from '../common/StatCard.vue';
    import BulletinBoard from '../common/BulletinBoard.vue';
    import LeaveAnalytics from './LeaveAnalytics.vue';
    import AttendanceGraph from './AttendanceGraph.vue';
    import EmployeeAttendanceModal from '../common/EmployeeAttendanceModal.vue';
    import axios from 'axios';

    const authStore = useAuthStore();
    const leaveStore = useLeaveStore();
    const employeeStore = useEmployeeStore();
    const { user } = storeToRefs(authStore);
    const { stats: leaveStats } = storeToRefs(leaveStore);
    const { employees, loading: isLoadingEmployees } = storeToRefs(employeeStore);
    
    const loading = ref(true);
    const getInitials = (name) => employeeStore.getInitials(name);

    // Attendance Roster variables
    const showEmployeeModal = ref(false);
    const selectedEmployee = ref(null);
    const employeeRecords = ref([]);
    const empPage = ref(1);
    const totalEmpPages = ref(1);
    const dashboardEmployees = ref([]);
    const loadingRoster = ref(true);
    const loadingEmployeeRecords = ref(false);
    const rosterSearch = ref('');
    let rosterSearchTimeout = null;

    const debounceRosterSearch = () => {
        clearTimeout(rosterSearchTimeout);
        rosterSearchTimeout = setTimeout(() => {
            empPage.value = 1; // Reset to page 1 on new search
            fetchDashboardEmployees(1);
        }, 500);
    };

    const fetchDashboardEmployees = async (page = 1) => {
        loadingRoster.value = true;
        try {
            const { data } = await axios.get('/api/attendance-roster', { 
                params: { 
                    page,
                    search: rosterSearch.value 
                } 
            });
            dashboardEmployees.value = data.data;
            empPage.value = data.current_page;
            totalEmpPages.value = data.last_page;
        } catch (e) {
            console.error(e);
        } finally {
            loadingRoster.value = false;
        }
    };

    const openEmployeeAttendance = async (emp) => {
        selectedEmployee.value = {
            employee_name: emp.name,
            employee_id: emp.employee_id,
            department: emp.department?.name || '--',
            working_hours: emp.working_hours || null,
        };
        employeeRecords.value = []; // Clear old data
        showEmployeeModal.value = true;
        loadingEmployeeRecords.value = true;
        
        try {
            const { data } = await axios.get('/api/attendance-records', { params: { employee_id: emp.employee_id } });
            employeeRecords.value = data;
        } catch (e) {
            console.error(e);
        } finally {
            loadingEmployeeRecords.value = false;
        }
    };

    // Live date labels for stat card context
    const now = new Date();
    const currentMonthYear = now.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
    const currentDay = now.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });


    // Pagination & Tab state
    const activeTab = ref('leaves');
    const attendancePage = ref(1);
    const leavesPage = ref(1);
    const pageSize = ref(5);

    const recentAttendance = ref([]);
    const fetchRecentAttendance = async () => {
        try {
            // Requesting only 50 records to minimize data transfer
            const { data } = await axios.get('/api/attendance-records', { params: { limit: 50 } });
            recentAttendance.value = data.map(r => {
                return {
                    id: r.id,
                    name: r.employee_name,
                    initials: getInitials(r.employee_name),
                    avatar: r.employee_avatar, // Using optimized avatar from API join
                    date: new Date(r.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }),
                    timeIn: r.time_in,
                    timeOut: r.time_out,
                    status: r.status
                };
            });
        } catch (error) {
            console.error('Failed to fetch recent attendance:', error);
        }
    };

    onMounted(async () => {
        loading.value = true;
        // Optimization: Removed employeeStore.fetchEmployees() to avoid loading 1000+ records just for avatars
        // Using cache-friendly stat fetching
        await Promise.all([
            leaveStore.fetchStats(false),
            fetchRecentAttendance(),
            fetchDashboardEmployees(),
            fetchAnomalies()
        ]);
        loading.value = false;
    });



    const paginatedAttendance = computed(() => {
        const start = (attendancePage.value - 1) * pageSize.value;
        const end = start + pageSize.value;
        return recentAttendance.value.slice(start, end);
    });

    // ---- Chronic Offenders (replaces raw anomaly watchlist) ----
    const loadingAnomalies = ref(true);
    const rawAnomalies = ref([]);
    const offenderFilter = ref('all');

    const chronicalOffenders = computed(() => {
        // Group by employee name
        const map = {};
        rawAnomalies.value.forEach(r => {
            const key = r.employee_id_number || r.name;
            if (!map[key]) {
                map[key] = { name: r.name, employee_id: key, lates: 0, absences: 0, halfDays: 0, lastDate: r.date };
            }
            if (r.status === 'Late') map[key].lates++;
            else if (r.status === 'Absent') map[key].absences++;
            else if (r.status === 'Half Day') map[key].halfDays++;
            // Track latest offense date
            if (r.date > map[key].lastDate) map[key].lastDate = r.date;
        });

        return Object.values(map)
            .map(o => {
                const total = o.lates + o.absences + o.halfDays;
                const level = total >= 5 ? 'critical' : total >= 3 ? 'notice' : 'watch';
                const d = new Date(o.lastDate);
                const lastSeen = isNaN(d) ? o.lastDate : d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                return { ...o, total, level, lastSeen };
            })
            .sort((a, b) => b.total - a.total);
    });

    const filteredOffenders = computed(() => {
        if (offenderFilter.value === 'all') return chronicalOffenders.value;
        if (offenderFilter.value === 'other') return chronicalOffenders.value.filter(o => o.level !== 'critical');
        return chronicalOffenders.value.filter(o => o.level === offenderFilter.value);
    });

    const fetchAnomalies = async () => {
        loadingAnomalies.value = true;
        try {
            // Fetch a big batch so we can aggregate repeat offenses
            const { data } = await axios.get('/api/attendance-anomalies', { params: { limit: 500 } });
            rawAnomalies.value = data.map(r => ({
                id: r.id,
                name: r.employee_name,
                employee_id_number: r.employee_id_number,
                date: r.date,
                status: (r.status === 'Half Day') ? 'Half Day'
                    : ((r.status === 'Absent' || r.time_in === '-' || r.time_out === '-') && r.status !== 'Late' ? 'Absent' : 'Late')
            }));
        } catch (error) {
            console.error('Failed to fetch anomalies:', error);
        } finally {
            loadingAnomalies.value = false;
        }
    };
    // Old refs kept alive so existing code that references them doesn't break
    const recentAnomalies = computed(() => rawAnomalies.value.slice(0, 15));
    const anomalySearch = ref('');
    const anomalyFilter = ref('all');
    const debounceAnomalySearch = () => {};

    // Recent Leaves Data (From API)
    const recentLeaves = computed(() => {
        return leaveStats.value.recent.map(leave => {
            const name = leave.user?.name || leave.employee?.name || 'Unknown';
            const avatar = leave.user?.avatar_url || (leave.employee?.avatar ? `/storage/${leave.employee.avatar}` : null);
            
            return {
                id: leave.id,
                name: name,
                initials: leave.employee?.initials || getInitials(name),
                avatar: avatar,
                type: leave.leave_type,
                duration: leave.days_taken + ' days',
                status: leave.status,
                timeAgo: timeAgo(leave.created_at || leave.date_filed)
            };
        });
    });

    const paginatedLeaves = computed(() => {
        const start = (leavesPage.value - 1) * pageSize.value;
        const end = start + pageSize.value;
        return recentLeaves.value.slice(start, end);
    });

    const timeAgo = (dateParam) => {
        if (!dateParam) return null;
        const date = new Date(dateParam);
        const now = new Date();
        const seconds = Math.round((now - date) / 1000);
        const minutes = Math.round(seconds / 60);
        const hours = Math.round(minutes / 60);
        const days = Math.round(hours / 24);

        if (seconds < 60) return 'Just now';
        else if (minutes < 60) return `${minutes} min${minutes > 1 ? 's' : ''} ago`;
        else if (hours < 24) return `${hours} hr${hours > 1 ? 's' : ''} ago`;
        else if (days === 1) return 'Yesterday';
        else return `${days} days ago`;
    };

    const goToRequest = (id) => {
        window.location.href = `/manage-leaves?highlight=${id}`;
    };
</script>
