<template>
    <div class="max-w-7xl mx-auto space-y-6 pb-10">
        <!-- 1. Stats Grid (5 Columns) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
            <StatCard 
                icon="pi-users" 
                :value="totalUsers" 
                label="Total Employees" 
                iconBgClass="bg-orange-50"
                iconTextClass="text-orange-500"
            />
            <StatCard 
                icon="pi-check-circle" 
                value="16" 
                label="Present" 
                iconBgClass="bg-green-50"
                iconTextClass="text-green-500"
            />
            <StatCard 
                icon="pi-times-circle" 
                value="0" 
                label="Absent" 
                iconBgClass="bg-red-50"
                iconTextClass="text-red-500"
            />
            <StatCard 
                icon="pi-clock" 
                value="2" 
                label="Late" 
                iconBgClass="bg-amber-50"
                iconTextClass="text-amber-500"
            />
            <StatCard 
                icon="pi-calendar" 
                :value="leaveStats.on_leave_today" 
                label="On Leave" 
                iconBgClass="bg-purple-50"
                iconTextClass="text-purple-500"
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
                <div class="grid grid-cols-2 gap-3">
                    <!-- Status Cards -->
                    <div v-for="stat in [
                        { label: 'Pending', count: leaveStats.pending, from: 'amber-50', to: 'orange-50', border: 'amber-200/50', icon: 'pi-clock', iconFrom: 'amber-400', iconTo: 'orange-500' },
                        { label: 'Approved', count: leaveStats.approved, from: 'emerald-50', to: 'teal-50', border: 'emerald-200/50', icon: 'pi-check-circle', iconFrom: 'emerald-400', iconTo: 'teal-500' },
                        { label: 'Rejected', count: leaveStats.rejected, from: 'rose-50', to: 'pink-50', border: 'rose-200/50', icon: 'pi-times-circle', iconFrom: 'rose-400', iconTo: 'pink-500' },
                        { label: 'Cancelled', count: leaveStats.cancelled, from: 'slate-50', to: 'gray-50', border: 'slate-200/50', icon: 'pi-ban', iconFrom: 'slate-400', iconTo: 'gray-500' }
                    ]" :key="stat.label" 
                    class="group relative bg-gradient-to-br rounded-xl p-4 border transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5"
                    :class="[`from-${stat.from} to-${stat.to} border-${stat.border} hover:border-${stat.from.split('-')[0]}-300`]"
                    >
                        <div class="flex flex-col items-center text-center space-y-2">
                            <div class="w-12 h-12 rounded-lg bg-gradient-to-br flex items-center justify-center shadow-md group-hover:scale-105 transition-transform"
                                 :class="[`from-${stat.iconFrom} to-${stat.iconTo}`]">
                                <i class="pi text-white text-lg" :class="stat.icon"></i>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-800">{{ stat.count }}</p>
                                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">{{ stat.label }}</p>
                            </div>
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
                            @click="activeTab = 'attendance'"
                            :class="[
                                'flex-1 px-6 py-3 text-sm font-semibold transition-all relative flex flex-col items-center justify-center h-16',
                                activeTab === 'attendance' 
                                    ? 'text-teal-600 border-b-2 border-teal-600 bg-white' 
                                    : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'
                            ]"
                        >
                            <span><i class="pi pi-clock mr-2"></i>Recent Attendance</span>
                            <span class="text-[9px] font-medium opacity-60">Imported on Jan 23, 2026</span>
                        </button>
                        <button 
                            @click="activeTab = 'leaves'"
                            :class="[
                                'flex-1 px-6 py-3 text-sm font-semibold transition-all relative h-16',
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
                    </div>

                    <!-- Tab Content -->
                    <div class="p-4">
                        <!-- Attendance Tab -->
                        <div v-if="activeTab === 'attendance'" class="space-y-3">
                            <div v-if="paginatedAttendance.length === 0" class="py-10 text-center text-gray-500">
                                <i class="pi pi-info-circle mb-2 block text-xl"></i>
                                <p class="text-xs">No recent attendance records found.</p>
                            </div>
                            <div v-for="record in paginatedAttendance" :key="record.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors border border-gray-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-teal-100 flex items-center justify-center overflow-hidden ring-2 ring-white shadow-sm">
                                        <img v-if="record.avatar" :src="record.avatar" class="w-full h-full object-cover">
                                        <span v-else class="text-sm font-bold text-teal-600">{{ record.initials }}</span>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-800 text-sm mb-0.5">{{ record.name }}</p>
                                        <p class="text-[10px] text-gray-500 uppercase tracking-tighter flex items-center gap-1">
                                            <i class="pi pi-calendar text-[10px]"></i> {{ record.date }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="flex items-center gap-3">
                                        <div class="flex flex-col items-end">
                                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">In</span>
                                            <span class="text-xs font-black text-green-600">{{ record.timeIn }}</span>
                                        </div>
                                        <div class="w-px h-8 bg-gray-200"></div>
                                        <div class="flex flex-col items-end">
                                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Out</span>
                                            <span class="text-xs font-black text-red-600">{{ record.timeOut }}</span>
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
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Performance & Data Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <AttendanceChart />
            <AttendanceLeaderboard />
        </div>

        <!-- 4. Department Indicators -->
        <div class="w-full">
            <DepartmentStats />
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
    import AttendanceChart from '../common/AttendanceChart.vue';
    import EmployeeList from '../common/EmployeeList.vue';
    import DepartmentStats from './DepartmentStats.vue';
    import AttendanceLeaderboard from './AttendanceLeaderboard.vue';

    const authStore = useAuthStore();
    const leaveStore = useLeaveStore();
    const employeeStore = useEmployeeStore();
    const { user } = storeToRefs(authStore);
    const { stats: leaveStats } = storeToRefs(leaveStore);
    const { employees, loading: isLoadingEmployees } = storeToRefs(employeeStore);
    
    const totalUsers = computed(() => employees.value.length);
    const getInitials = (name) => employeeStore.getInitials(name);

    // Pagination & Tab state
    const activeTab = ref('attendance');
    const attendancePage = ref(1);
    const leavesPage = ref(1);
    const pageSize = ref(5);

    onMounted(() => {
        employeeStore.fetchEmployees();
        leaveStore.fetchStats();
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
        return leaveStats.value.recent.map(leave => ({
            id: leave.id,
            name: leave.user?.name || 'Unknown',
            initials: getInitials(leave.user?.name),
            avatar: leave.user?.avatar_url,
            type: leave.leave_type,
            duration: leave.days_taken + ' days',
            status: leave.status,
            timeAgo: timeAgo(leave.created_at || leave.date_filed)
        }));
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
