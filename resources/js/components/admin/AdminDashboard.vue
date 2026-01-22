<template>
    <div class="max-w-7xl mx-auto">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-3">
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
                    value=" 0" 
                    label="Absent" 
                    iconBgClass="bg-red-50"
                    iconTextClass="text-red-500"
                />
                <StatCard 
                    icon="pi-clock" 
                    :value="leaveStats.on_leave_today" 
                    label="On Leave" 
                    iconBgClass="bg-yellow-50"
                    iconTextClass="text-yellow-500"
                />
        </div>

        <!-- Two Column Layout: Leave Cards + Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-3">
            <!-- Left: Compact Leave Cards (2 columns) -->
            <div class="lg:col-span-1">
                <div class="grid grid-cols-2 gap-3">
                    <!-- Pending Leave Card (Compact) -->
                    <div class="group relative bg-gradient-to-br from-amber-50 to-orange-50 rounded-xl p-4 border border-amber-200/50 hover:border-amber-300 transition-all duration-300 hover:shadow-lg hover:shadow-amber-500/10 hover:-translate-y-0.5">
                        <div class="flex flex-col items-center text-center space-y-2">
                            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-md group-hover:scale-105 transition-transform">
                                <i class="pi pi-clock text-white text-lg"></i>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-800">{{ leaveStats.pending }}</p>
                                <p class="text-xs font-medium text-gray-600">Pending</p>
                            </div>
                        </div>
                    </div>

                    <!-- Approved Leave Card (Compact) -->
                    <div class="group relative bg-gradient-to-br from-emerald-50 to-teal-50 rounded-xl p-4 border border-emerald-200/50 hover:border-emerald-300 transition-all duration-300 hover:shadow-lg hover:shadow-emerald-500/10 hover:-translate-y-0.5">
                        <div class="flex flex-col items-center text-center space-y-2">
                            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center shadow-md group-hover:scale-105 transition-transform">
                                <i class="pi pi-check-circle text-white text-lg"></i>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-800">{{ leaveStats.approved }}</p>
                                <p class="text-xs font-medium text-gray-600">Approved</p>
                            </div>
                        </div>
                    </div>

                    <!-- Rejected Leave Card (Compact) -->
                    <div class="group relative bg-gradient-to-br from-rose-50 to-pink-50 rounded-xl p-4 border border-rose-200/50 hover:border-rose-300 transition-all duration-300 hover:shadow-lg hover:shadow-rose-500/10 hover:-translate-y-0.5">
                        <div class="flex flex-col items-center text-center space-y-2">
                            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-rose-400 to-pink-500 flex items-center justify-center shadow-md group-hover:scale-105 transition-transform">
                                <i class="pi pi-times-circle text-white text-lg"></i>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-800">{{ leaveStats.rejected }}</p>
                                <p class="text-xs font-medium text-gray-600">Rejected</p>
                            </div>
                        </div>
                    </div>

                    <!-- Cancelled Leave Card (Compact) -->
                    <div class="group relative bg-gradient-to-br from-slate-50 to-gray-50 rounded-xl p-4 border border-slate-200/50 hover:border-slate-300 transition-all duration-300 hover:shadow-lg hover:shadow-slate-500/10 hover:-translate-y-0.5">
                        <div class="flex flex-col items-center text-center space-y-2">
                            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-slate-400 to-gray-500 flex items-center justify-center shadow-md group-hover:scale-105 transition-transform">
                                <i class="pi pi-ban text-white text-lg"></i>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-800">{{ leaveStats.cancelled }}</p>
                                <p class="text-xs font-medium text-gray-600">Cancelled</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Recent Activity Panel with Tabs -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <!-- Tab Headers -->
                    <div class="flex border-b border-gray-200 bg-gray-50">
                        <button 
                            @click="activeTab = 'attendance'"
                            :class="[
                                'flex-1 px-6 py-3 text-sm font-semibold transition-all',
                                activeTab === 'attendance' 
                                    ? 'text-teal-600 border-b-2 border-teal-600 bg-white' 
                                    : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'
                            ]"
                        >
                            <i class="pi pi-clock mr-2"></i>
                            Recent Attendance
                        </button>
                        <button 
                            @click="activeTab = 'leaves'"
                            :class="[
                                'flex-1 px-6 py-3 text-sm font-semibold transition-all',
                                activeTab === 'leaves' 
                                    ? 'text-teal-600 border-b-2 border-teal-600 bg-white' 
                                    : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'
                            ]"
                        >
                            <i class="pi pi-calendar-times mr-2"></i>
                            Recent Leaves
                        </button>
                    </div>

                    <!-- Tab Content -->
                    <div class="p-4">
                        <!-- Attendance Tab -->
                        <div v-if="activeTab === 'attendance'" class="space-y-3">
                            <div v-if="recentAttendance.length === 0" class="py-10 text-center text-gray-500">
                                <i class="pi pi-info-circle mb-2 block text-xl"></i>
                                <p class="text-xs">No recent attendance records found.</p>
                            </div>
                            <div v-for="record in recentAttendance" :key="record.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-teal-100 flex items-center justify-center overflow-hidden ring-2 ring-white">
                                        <img v-if="record.avatar" :src="record.avatar" class="w-full h-full object-cover">
                                        <span v-else class="text-sm font-bold text-teal-600">{{ record.initials }}</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800 text-sm">{{ record.name }}</p>
                                        <p class="text-xs text-gray-500">{{ record.date }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="flex items-center gap-2 text-xs">
                                        <span class="text-green-600 font-medium">
                                            <i class="pi pi-sign-in mr-1"></i>{{ record.timeIn }}
                                        </span>
                                        <span class="text-gray-400">•</span>
                                        <span class="text-red-600 font-medium">
                                            <i class="pi pi-sign-out mr-1"></i>{{ record.timeOut }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Leaves Tab -->
                        <div v-if="activeTab === 'leaves'" class="space-y-3">
                            <div v-if="recentLeaves.length === 0" class="py-10 text-center text-gray-500">
                                <i class="pi pi-info-circle mb-2 block text-xl"></i>
                                <p class="text-xs">No recent leave requests found.</p>
                            </div>
                            <div 
                                v-for="leave in recentLeaves" 
                                :key="leave.id" 
                                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer"
                                @click="goToRequest(leave.id)"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center overflow-hidden ring-2 ring-white">
                                        <img v-if="leave.avatar" :src="leave.avatar" class="w-full h-full object-cover">
                                        <span v-else class="text-sm font-bold text-amber-600">{{ leave.initials }}</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800 text-sm">{{ leave.name }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ leave.type }} • {{ leave.duration }} • <span class="text-teal-600 font-medium">{{ leave.timeAgo }}</span>
                                        </p>
                                    </div>
                                </div>
                                <div>
                                    <span :class="[
                                        'px-3 py-1 rounded-full text-xs font-semibold',
                                        leave.status === 'Approved' ? 'bg-emerald-100 text-emerald-700' :
                                        leave.status === 'Pending' ? 'bg-amber-100 text-amber-700' :
                                        'bg-rose-100 text-rose-700'
                                    ]">
                                        {{ leave.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

                <!-- Left Column (Chart) -->
                <div class="lg:col-span-2 mb-3">
                    <AttendanceChart />
                </div>


            <!-- Bottom Section (Employee List) -->
            <div class="w-full">
                <EmployeeList />
            </div>
        </div>
</template>

<script setup>
    import { ref, onMounted, computed } from 'vue';
    import { useAuthStore } from '../../stores/auth';
    import { storeToRefs } from 'pinia';
    import StatCard from '../common/StatCard.vue';
    import AttendanceChart from '../common/AttendanceChart.vue';
    import EmployeeList from '../common/EmployeeList.vue';
    import axios from 'axios';

    const authStore = useAuthStore();
    const { user } = storeToRefs(authStore);
    const isLoadingEmployees = ref(false);
    const employees = ref([]);
    const totalUsers = computed(() => employees.value.length);
    const getInitials = (name) => {
        if (!name) return '??';
        return name
            .split(' ')
            .map(word => word[0])
            .join('')
            .toUpperCase()
            .substring(0, 2);
    };
    const fetchTotalUsers = async () => {
        try {
            const response = await axios.get('/api/users/total');
            totalUsers.value = response.data.total;
        } catch (err) {
            console.error('Failed to fetch total users:', err);
        }
    }

    const fetchEmployees = async () => {
        isLoadingEmployees.value = true;
        try {
            const response = await axios.get('/api/users');
            employees.value = response.data.map(user => ({
                id: user.id,
                name: user.name,
                email: user.email,
                avatar: user.avatar_url,
                initials: getInitials(user.name),
                empId: user.id_number || 'N/A',
                department: user.department || 'N/A',
                role: user.position || (user.role === 'admin' ? 'Administrator' : 'Employee'),
                status: user.status || 'Available',
                position: user.position
            }));
        } catch (error) {
            console.error('Failed to fetch employees:', error);
        } finally {
            isLoadingEmployees.value = false;
        }
    };

    // Tab state
    const activeTab = ref('attendance');

    const leaveStats = ref({
        pending: 0,
        approved: 0,
        rejected: 0,
        cancelled: 0,
        on_leave_today: 0,
        recent: []
    });

    const fetchLeaveStats = async () => {
        try {
            const res = await axios.get('/api/leave-stats');
            leaveStats.value = res.data;
        } catch (e) { console.error(e); }
    };

    onMounted(() => {
        fetchEmployees();
        fetchTotalUsers();
        fetchLeaveStats();
    });

    // Recent Attendance Data (Derived from real employees - still mock logic for now)
    const recentAttendance = computed(() => {
        if (employees.value.length === 0) return [];
        return employees.value.slice(0, 4).map(emp => ({
            id: emp.id,
            name: emp.name,
            initials: emp.initials,
            avatar: emp.avatar,
            date: 'Today',
            timeIn: '08:00 AM',
            timeOut: '05:00 PM'
        }));
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
