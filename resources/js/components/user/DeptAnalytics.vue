<template>
    <div v-if="user">
        <MainLayout :user="user">
            <div class="p-6 max-w-7xl mx-auto space-y-8">

        <!-- PAGE HEADER -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-gray-800 flex items-center gap-2">
                    <i class="pi pi-chart-line text-teal-600"></i>
                    Department Analytics
                </h1>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-0.5">
                    Leave patterns, trends and workforce insights for your department
                </p>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <div class="flex items-center gap-2 bg-white rounded-xl border border-gray-200 px-3 py-2 shadow-sm">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Year</label>
                    <select v-model="selectedYear" @change="fetchAll"
                        class="text-sm font-bold text-gray-700 bg-transparent border-none outline-none cursor-pointer">
                        <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
                    </select>
                </div>
                <div class="flex items-center gap-2 bg-white rounded-xl border border-gray-200 px-3 py-2 shadow-sm">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Month</label>
                    <select v-model="selectedMonth" @change="fetchAll"
                        class="text-sm font-bold text-gray-700 bg-transparent border-none outline-none cursor-pointer">
                        <option v-for="(m,i) in months" :key="i" :value="i+1">{{ m }}</option>
                    </select>
                </div>
                <button @click="exportAll"
                    class="flex items-center gap-1.5 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all shadow-sm active:scale-95">
                    <i class="pi pi-download"></i> Export Report
                </button>
            </div>
        </div>

        <!-- TOP KPI CARDS -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col gap-3 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Filed</span>
                    <div class="w-9 h-9 rounded-xl bg-teal-50 flex items-center justify-center text-teal-600">
                        <i class="pi pi-file text-sm"></i>
                    </div>
                </div>
                <div>
                    <p class="text-4xl font-black text-gray-800">{{ summary.total ?? '—' }}</p>
                    <p class="text-[10px] text-gray-400 font-bold mt-1">requests this month</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-emerald-100 shadow-sm p-5 flex flex-col gap-3 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black text-emerald-400 uppercase tracking-widest">Approved</span>
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                        <i class="pi pi-verified text-sm"></i>
                    </div>
                </div>
                <div>
                    <p class="text-4xl font-black text-emerald-600">{{ summary.approved ?? '—' }}</p>
                    <p class="text-[10px] text-gray-400 font-bold mt-1">
                        <span v-if="summary.total">{{ pct(summary.approved, summary.total) }}</span> of total
                    </p>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-rose-100 shadow-sm p-5 flex flex-col gap-3 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black text-rose-400 uppercase tracking-widest">Rejected</span>
                    <div class="w-9 h-9 rounded-xl bg-rose-50 flex items-center justify-center text-rose-500">
                        <i class="pi pi-times-circle text-sm"></i>
                    </div>
                </div>
                <div>
                    <p class="text-4xl font-black text-rose-500">{{ summary.rejected ?? '—' }}</p>
                    <p class="text-[10px] text-gray-400 font-bold mt-1">
                        <span v-if="summary.total">{{ pct(summary.rejected, summary.total) }}</span> of total
                    </p>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-5 flex flex-col gap-3 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black text-amber-500 uppercase tracking-widest">Total Leave Days</span>
                    <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center text-amber-500">
                        <i class="pi pi-calendar text-sm"></i>
                    </div>
                </div>
                <div>
                    <p class="text-4xl font-black text-amber-600">{{ summary.total_days ?? '—' }}</p>
                    <p class="text-[10px] text-gray-400 font-bold mt-1">approved + sent to HR</p>
                </div>
            </div>
        </div>

        <!-- GRID: Charts Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Leave Type Breakdown (bar chart) -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h3 class="text-xs font-black text-gray-500 uppercase tracking-widest mb-6 flex items-center gap-2">
                    <i class="pi pi-th-large text-teal-500"></i> Leave Type Breakdown
                </h3>
                <div v-if="loading" class="space-y-3">
                    <div v-for="i in 5" :key="i" class="h-8 bg-gray-50 animate-pulse rounded-lg"></div>
                </div>
                <div v-else-if="byType.length === 0" class="flex flex-col items-center justify-center h-40 text-center">
                    <i class="pi pi-inbox text-3xl text-gray-200 mb-2"></i>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">No data this month</p>
                </div>
                <div v-else class="space-y-3">
                    <div v-for="item in byType" :key="item.leave_type" class="flex items-center gap-3">
                        <div class="w-28 shrink-0 text-[10px] font-black text-gray-600 uppercase tracking-tight truncate">{{ item.leave_type }}</div>
                        <div class="flex-1 bg-gray-100 rounded-full h-3 overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r from-teal-500 to-emerald-400 transition-all duration-700"
                                :style="{ width: byTypeMax ? `${(item.count / byTypeMax) * 100}%` : '0%' }"></div>
                        </div>
                        <div class="w-10 text-right text-xs font-black text-gray-700 shrink-0">{{ item.count }}</div>
                        <div class="w-12 text-right text-[10px] text-gray-400 font-bold shrink-0">{{ pct(item.count, summary.total) }}</div>
                    </div>
                </div>
            </div>

            <!-- Status Distribution (donut-like) -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h3 class="text-xs font-black text-gray-500 uppercase tracking-widest mb-6 flex items-center gap-2">
                    <i class="pi pi-chart-pie text-purple-500"></i> Status Distribution
                </h3>
                <div v-if="loading" class="space-y-3">
                    <div v-for="i in 5" :key="i" class="h-8 bg-gray-50 animate-pulse rounded-lg"></div>
                </div>
                <div v-else class="space-y-4">
                    <div v-for="item in statusDistribution" :key="item.label" class="flex items-center gap-4">
                        <div class="w-2.5 h-2.5 rounded-full shrink-0" :class="item.dot"></div>
                        <div class="flex-1 text-xs font-black text-gray-700">{{ item.label }}</div>
                        <div class="w-full max-w-[200px] bg-gray-100 rounded-full h-2.5 overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-700" :class="item.bar"
                                :style="{ width: summary.total ? `${(item.value / summary.total) * 100}%` : '0%' }"></div>
                        </div>
                        <div class="w-8 text-right text-xs font-black text-gray-700 shrink-0">{{ item.value }}</div>
                    </div>
                    <!-- Visual Total Days pill -->
                    <div class="mt-6 p-4 bg-gradient-to-r from-teal-50 to-emerald-50 rounded-xl border border-teal-100 flex items-center gap-3">
                        <i class="pi pi-sun text-teal-500 text-xl"></i>
                        <div>
                            <p class="text-[10px] font-black text-teal-600 uppercase tracking-widest">Total Approved Days Off</p>
                            <p class="text-2xl font-black text-teal-700">{{ summary.total_days ?? 0 }} <span class="text-sm text-teal-400 font-bold">days</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MONTHLY TREND: 12-month bar chart -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-xs font-black text-gray-500 uppercase tracking-widest mb-6 flex items-center gap-2">
                <i class="pi pi-chart-bar text-blue-500"></i> Monthly Trend — {{ selectedYear }}
                <span class="ml-auto text-gray-300 font-bold normal-case text-[10px] tracking-normal">Approved requests per month</span>
            </h3>
            <div v-if="loadingTrend" class="flex gap-2 items-end h-40">
                <div v-for="i in 12" :key="i" class="flex-1 bg-gray-100 animate-pulse rounded-t-lg" :style="{ height: `${(Math.random()*70+20).toFixed(0)}%` }"></div>
            </div>
            <div v-else class="flex gap-2 items-end" style="height: 160px;">
                <div v-for="(item, idx) in trend" :key="idx"
                    class="flex-1 flex flex-col items-center gap-1.5 group"
                    :title="`${item.month}: ${item.total} requests, ${item.approved} approved`">
                    <!-- Bar -->
                    <div class="w-full flex flex-col justify-end rounded-t-lg overflow-hidden bg-gray-100 transition-all duration-500"
                        style="height: 130px;">
                        <div class="w-full rounded-t-lg transition-all duration-700 group-hover:opacity-80"
                            :class="item.month === currentMonthLabel ? 'bg-teal-500' : 'bg-slate-300 group-hover:bg-teal-400'"
                            :style="{ height: trendMax ? `${Math.max(4, (item.approved / trendMax) * 100)}%` : '4%' }">
                        </div>
                    </div>
                    <!-- Count -->
                    <span class="text-[9px] font-black text-gray-500">{{ item.approved }}</span>
                    <!-- Month label -->
                    <span class="text-[8px] font-black uppercase tracking-widest"
                        :class="item.month === currentMonthLabel ? 'text-teal-600' : 'text-gray-400'">
                        {{ item.month.substring(0, 3) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- PER-EMPLOYEE TABLE -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-xs font-black text-gray-500 uppercase tracking-widest flex items-center gap-2">
                    <i class="pi pi-users text-indigo-500"></i> Per-Employee Summary
                </h3>
                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">
                    {{ selectedMonthLabel }} {{ selectedYear }}
                </span>
            </div>
            <div v-if="loading" class="p-6 space-y-3">
                <div v-for="i in 5" :key="i" class="h-12 bg-gray-50 animate-pulse rounded-lg"></div>
            </div>
            <div v-else-if="perEmployee.length === 0" class="p-10 text-center">
                <i class="pi pi-inbox text-3xl text-gray-200 mb-3 block"></i>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">No employee data this month</p>
            </div>
            <div v-else class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100">
                            <th class="px-6 py-3">Employee</th>
                            <th class="px-4 py-3 text-center">Filed</th>
                            <th class="px-4 py-3 text-center">Approved</th>
                            <th class="px-4 py-3 text-center">Rejected</th>
                            <th class="px-4 py-3 text-center">Pending</th>
                            <th class="px-4 py-3 text-center">Days Used</th>
                            <th class="px-4 py-3 text-center">Leave Types</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="emp in perEmployee" :key="emp.name"
                            class="hover:bg-gray-50/60 transition-colors group">
                            <td class="px-6 py-3.5 flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center font-black text-xs border border-teal-200 shrink-0">
                                    {{ getInitials(emp.name) }}
                                </div>
                                <div>
                                    <p class="font-black text-gray-800 text-xs">{{ emp.name }}</p>
                                    <p class="text-[10px] text-gray-400">{{ emp.position }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center font-black text-gray-600 text-sm">{{ emp.total }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-block px-2 py-0.5 bg-emerald-100 text-emerald-700 text-xs font-black rounded-full">{{ emp.approved }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-block px-2 py-0.5 bg-rose-100 text-rose-700 text-xs font-black rounded-full">{{ emp.rejected }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-block px-2 py-0.5 bg-amber-100 text-amber-700 text-xs font-black rounded-full">{{ emp.pending }}</span>
                            </td>
                            <td class="px-4 py-3 text-center font-black text-teal-600 text-sm">{{ emp.days }}</td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex flex-wrap gap-1 justify-center">
                                    <span v-for="t in emp.types" :key="t"
                                        class="px-1.5 py-0.5 bg-indigo-50 text-indigo-600 text-[9px] font-black rounded border border-indigo-100 uppercase tracking-tight">
                                        {{ t }}
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- RECENT ACTIVITY FEED -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                <i class="pi pi-history text-gray-400"></i>
                <h3 class="text-xs font-black text-gray-500 uppercase tracking-widest">Recent Leave Activity</h3>
            </div>
            <div v-if="loading" class="p-6 space-y-3">
                <div v-for="i in 5" :key="i" class="h-10 bg-gray-50 animate-pulse rounded-lg"></div>
            </div>
            <div v-else-if="recentActivity.length === 0" class="p-10 text-center">
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">No recent activity</p>
            </div>
            <div v-else class="divide-y divide-gray-50">
                <div v-for="item in recentActivity" :key="item.id" class="px-6 py-3.5 hover:bg-gray-50 transition-all flex items-center gap-4">
                    <div class="w-2 h-2 rounded-full shrink-0" :class="{
                        'bg-emerald-400': item.status === 'Approved',
                        'bg-rose-400':    item.status === 'Rejected',
                        'bg-amber-400':   item.status === 'Pending',
                        'bg-blue-400':    item.status === 'Dept Approved',
                        'bg-gray-300':    item.status === 'Cancelled',
                    }"></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-black text-gray-700 truncate">
                            <span class="text-teal-600">{{ item.employee_name }}</span>
                            filed <span class="italic">{{ item.leave_type }}</span> — {{ item.days_taken }}d
                        </p>
                        <p class="text-[10px] text-gray-400 mt-0.5">{{ formatDate(item.from_date) }}</p>
                    </div>
                    <span :class="statusBadge(item.status)" class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest border shrink-0">
                        {{ item.status }}
                    </span>
                </div>
            </div>
        </div>

            </div>
        </MainLayout>
    </div>

    <!-- Unauthorized / Loading -->
    <div v-else class="flex items-center justify-center h-screen">
        <div class="text-center">
            <i class="pi pi-spin pi-spinner text-3xl text-teal-400 mb-3 block"></i>
            <p class="text-sm text-gray-400 font-bold">Loading analytics…</p>
        </div>
    </div>
</template>

<script setup>
import { useAuthStore } from '../../stores/auth';
import { storeToRefs } from 'pinia';
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import MainLayout from '../../layouts/MainLayout.vue';

const authStore = useAuthStore();
const { user } = storeToRefs(authStore);

const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const now = new Date();
const selectedYear  = ref(now.getFullYear());
const selectedMonth = ref(now.getMonth() + 1);

const yearOptions = computed(() => {
    const y = now.getFullYear();
    return [y - 1, y, y + 1];
});

const selectedMonthLabel = computed(() => months[selectedMonth.value - 1]);
const currentMonthLabel  = computed(() => months[now.getMonth()].substring(0, 3));

const loading      = ref(true);
const loadingTrend = ref(true);

const summary       = ref({});
const byType        = ref([]);
const perEmployee   = ref([]);
const recentActivity = ref([]);
const trend         = ref([]);

const byTypeMax = computed(() => Math.max(...byType.value.map(t => t.count), 1));
const trendMax  = computed(() => Math.max(...trend.value.map(t => t.approved), 1));

const statusDistribution = computed(() => [
    { label: 'Approved',     value: summary.value.approved     ?? 0, dot: 'bg-emerald-400', bar: 'bg-emerald-400' },
    { label: 'Dept Approved',value: summary.value.dept_approved ?? 0, dot: 'bg-blue-400',    bar: 'bg-blue-400' },
    { label: 'Pending',      value: summary.value.pending      ?? 0, dot: 'bg-amber-400',   bar: 'bg-amber-400' },
    { label: 'Rejected',     value: summary.value.rejected     ?? 0, dot: 'bg-rose-400',    bar: 'bg-rose-400' },
    { label: 'Cancelled',    value: summary.value.cancelled    ?? 0, dot: 'bg-gray-300',    bar: 'bg-gray-300' },
]);

const getInitials = (name) => {
    if (!name) return '??';
    return name.split(' ').map(w => w[0]).join('').toUpperCase().substring(0, 2);
};
const formatDate = (d) => {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};
const pct = (n, total) => {
    if (!total || total === 0) return '0%';
    return `${Math.round((n / total) * 100)}%`;
};
const statusBadge = (s) => {
    const map = {
        'Approved':      'bg-emerald-50 text-emerald-700 border-emerald-200',
        'Dept Approved': 'bg-blue-50 text-blue-700 border-blue-200',
        'Pending':       'bg-amber-50 text-amber-700 border-amber-200',
        'Rejected':      'bg-rose-50 text-rose-700 border-rose-200',
        'Cancelled':     'bg-gray-50 text-gray-500 border-gray-200',
    };
    return map[s] ?? 'bg-gray-50 text-gray-500 border-gray-200';
};

const fetchAll = async () => {
    loading.value = true;
    try {
        const res = await axios.get('/api/dept-head/report', {
            params: { year: selectedYear.value, month: selectedMonth.value }
        });
        const data = res.data;
        summary.value = data.summary;
        recentActivity.value = [
            ...data.pending,
            ...data.dept_approved,
            ...data.approved,
            ...data.rejected,
        ].sort((a, b) => new Date(b.filed_on) - new Date(a.filed_on)).slice(0, 10);

        // Group by type
        const typeMap = {};
        [...data.pending, ...data.dept_approved, ...data.approved, ...data.rejected, ...data.cancelled].forEach(r => {
            typeMap[r.leave_type] = (typeMap[r.leave_type] ?? 0) + 1;
        });
        byType.value = Object.entries(typeMap)
            .map(([leave_type, count]) => ({ leave_type, count }))
            .sort((a, b) => b.count - a.count);

        // Per-employee
        const empMap = {};
        [...data.pending, ...data.dept_approved, ...data.approved, ...data.rejected, ...data.cancelled].forEach(r => {
            if (!empMap[r.employee_name]) {
                empMap[r.employee_name] = { name: r.employee_name, position: r.position, total: 0, approved: 0, rejected: 0, pending: 0, days: 0, types: new Set() };
            }
            const e = empMap[r.employee_name];
            e.total++;
            e.types.add(r.leave_type);
            e.days += Number(r.days_taken ?? 0);
            if (['Approved', 'Dept Approved'].includes(r.status)) e.approved++;
            else if (r.status === 'Rejected') e.rejected++;
            else if (r.status === 'Pending') e.pending++;
        });
        perEmployee.value = Object.values(empMap)
            .map(e => ({ ...e, types: [...e.types], days: parseFloat(e.days.toFixed(2)) }))
            .sort((a, b) => b.total - a.total);
    } catch (e) {
        console.error('Failed to load analytics data', e);
    } finally {
        loading.value = false;
    }
    await fetchTrend();
};

const fetchTrend = async () => {
    loadingTrend.value = true;
    try {
        const results = await Promise.all(
            months.map((_, i) =>
                axios.get('/api/dept-head/report', {
                    params: { year: selectedYear.value, month: i + 1 }
                }).then(r => ({
                    month: months[i].substring(0, 3),
                    total:    r.data.summary?.total    ?? 0,
                    approved: r.data.summary?.approved ?? 0,
                }))
            )
        );
        trend.value = results;
    } catch (e) {
        console.error('Failed to load trend', e);
    } finally {
        loadingTrend.value = false;
    }
};

const exportAll = () => {
    const params = new URLSearchParams({ year: selectedYear.value, month: selectedMonth.value });
    window.open(`/api/dept-head/report/export?${params.toString()}`, '_blank');
};

onMounted(async () => {
    if (!user.value) await authStore.fetchUser();
    await fetchAll();
});
</script>
