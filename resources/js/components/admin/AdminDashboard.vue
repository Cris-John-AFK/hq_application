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
                iconBgClass="bg-orange-50"
                iconTextClass="text-orange-500"
                :loading="loading"
            />
            <StatCard 
                icon="pi-check-circle" 
                :value="leaveStats.approved_this_month" 
                label="Monthly Approved" 
        
                iconBgClass="bg-green-50"
                iconTextClass="text-green-500"
                :loading="loading"
            />
            <StatCard 
                icon="pi-calendar-plus" 
                :value="leaveStats.scheduled" 
                label="Upcoming Leaves" 
    
                iconBgClass="bg-blue-50"
                iconTextClass="text-blue-500"
                :loading="loading"
            />
            <StatCard 
                icon="pi-money-bill" 
                :value="leaveStats.approved_paid" 
                label="Leaves with Pay" 
                iconBgClass="bg-teal-50"
                iconTextClass="text-teal-600"
                :loading="loading"
            />
            <StatCard 
                icon="pi-calendar" 
                :value="leaveStats.on_leave_today" 
                label="On Leave Today" 
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
                    <!-- WIP notice: only on attendance tab -->
                    <div v-if="activeTab === 'attendance'" class="px-4 py-2 bg-amber-50 border-b border-amber-100 flex items-center gap-2">
                        <i class="pi pi-wrench text-amber-500 text-xs"></i>
                        <span class="text-[10px] font-black text-amber-600 uppercase tracking-widest">Placeholder data — not connected to live time-keeping</span>
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
                                                <span class="text-[10px] font-bold text-teal-600 uppercase tracking-wider">Present</span>
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
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top Leave Reasons -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] flex items-center gap-2 mb-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                            Top Leave Reasons
                        </h3>
                        <p class="text-[9px] font-bold text-indigo-500/60 uppercase tracking-widest pl-3.5 italic">As of {{ new Date().getFullYear() }}</p>
                    </div>
                    <i class="pi pi-chart-pie text-gray-300"></i>
                </div>
                <div v-if="loading" class="space-y-4">
                    <div v-for="i in 3" :key="i" class="h-12 bg-gray-50 animate-pulse rounded-xl"></div>
                </div>
                <div v-else class="space-y-4">
                    <div v-for="(item, idx) in leaveStats.by_type" :key="idx" class="relative">
                        <div class="flex justify-between items-center mb-1 text-xs">
                            <span class="font-bold text-gray-700">{{ item.name }}</span>
                            <span class="font-black text-indigo-600 px-2 py-0.5 bg-indigo-50 rounded">{{ item.count }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden border border-gray-50">
                            <div 
                                class="h-full bg-indigo-500 rounded-full transition-all duration-1000 shadow-sm"
                                :style="{ width: (leaveStats.total_all_time > 0 ? (item.count / leaveStats.total_all_time * 100) : 0) + '%' }"
                            ></div>
                        </div>
                    </div>
                    <p v-if="!leaveStats.by_type?.length" class="text-center text-xs text-gray-400 py-10 italic">No leave data captured yet</p>
                </div>
            </div>

            <!-- Most Leave by Department -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] flex items-center gap-2 mb-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Department Volume
                        </h3>
                        <p class="text-[9px] font-bold text-emerald-500/60 uppercase tracking-widest pl-3.5 italic">As of {{ new Date().getFullYear() }}</p>
                    </div>
                    <i class="pi pi-building text-gray-300"></i>
                </div>
                <div v-if="loading" class="space-y-4">
                    <div v-for="i in 3" :key="i" class="h-12 bg-gray-50 animate-pulse rounded-xl"></div>
                </div>
                <div v-else class="space-y-3">
                    <div v-for="(dept, idx) in leaveStats.by_department" :key="idx" class="flex items-center justify-between p-3.5 bg-gray-50/50 rounded-xl border border-gray-100 hover:border-emerald-200 transition-all hover:bg-white hover:shadow-sm">
                        <div class="flex items-center gap-3">
                            <div :class="[
                                'w-8 h-8 rounded-lg flex items-center justify-center text-[10px] font-black border transition-all',
                                idx === 0 ? 'bg-emerald-500 text-white border-emerald-400 shadow-sm' : 'bg-white text-gray-400 border-gray-200'
                            ]">
                                {{ idx + 1 }}
                            </div>
                            <span class="text-xs font-bold text-gray-700">{{ dept.name }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-black text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100">{{ dept.count }}</span>
                            <span class="text-[9px] text-gray-400 font-bold uppercase tracking-tighter">Filings</span>
                        </div>
                    </div>
                    <p v-if="!leaveStats.by_department?.length" class="text-center text-xs text-gray-400 py-10 italic text-[10px] uppercase">No department data available</p>
                </div>
            </div>
        </div>
        <!-- 5. Employee Management -->
        <div class="w-full pt-4">
            <EmployeeList />
        </div>
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
    import EmployeeList from '../common/EmployeeList.vue';

    const authStore = useAuthStore();
    const leaveStore = useLeaveStore();
    const employeeStore = useEmployeeStore();
    const { user } = storeToRefs(authStore);
    const { stats: leaveStats } = storeToRefs(leaveStore);
    const { employees, loading: isLoadingEmployees } = storeToRefs(employeeStore);
    
    const loading = ref(true);
    const getInitials = (name) => employeeStore.getInitials(name);

    // Pagination & Tab state
    const activeTab = ref('leaves');
    const attendancePage = ref(1);
    const leavesPage = ref(1);
    const pageSize = ref(5);

    onMounted(async () => {
        loading.value = true;
        await Promise.all([
            employeeStore.fetchEmployees(),
            leaveStore.fetchStats()
        ]);
        loading.value = false;
    });

    // Recent Attendance Data
    const recentAttendance = computed(() => {
        if (employees.value.length === 0) return [];
        // Simulate attendance data from employees
        return employees.value.map((emp, index) => ({
            id: emp.id,
            name: emp.name,
            initials: emp.initials,
            avatar: emp.avatar,
            date: 'Jan 23, 2026',
            timeIn: '08:00 AM',
            timeOut: '05:00 PM'
        }));
    });

    const paginatedAttendance = computed(() => {
        const start = (attendancePage.value - 1) * pageSize.value;
        const end = start + pageSize.value;
        return recentAttendance.value.slice(start, end);
    });

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
