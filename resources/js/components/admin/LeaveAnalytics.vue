<template>
    <div class="space-y-6">

        <!-- ── Header ── -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-black text-gray-800 tracking-tight flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-teal-500 animate-pulse"></span>
                    Leave Analytics
                </h2>
                <p class="text-xs text-gray-400 mt-0.5 pl-4">
                    Showing data for <span class="font-bold text-teal-600">{{ filterLabel }}</span>
                    · {{ totalFiltered }} total filings
                </p>
            </div>

            <!-- Filter Controls -->
            <div class="flex flex-wrap gap-2">

                <!-- Year Filter -->
                <select v-model="filterYear" class="px-3 py-2 text-xs font-bold border border-gray-200 rounded-lg bg-white text-gray-700 shadow-sm cursor-pointer focus:ring-2 focus:ring-teal-400 outline-none">
                    <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
                </select>

                <!-- Month Filter -->
                <select v-model="filterMonth" class="px-3 py-2 text-xs font-bold border border-gray-200 rounded-lg bg-white text-gray-700 shadow-sm cursor-pointer focus:ring-2 focus:ring-teal-400 outline-none">
                    <option value="">All Months</option>
                    <option v-for="(m, i) in months" :key="i" :value="i + 1">{{ m }}</option>
                </select>

                <!-- Status Filter -->
                <select v-model="filterStatus" class="px-3 py-2 text-xs font-bold border border-gray-200 rounded-lg bg-white text-gray-700 shadow-sm cursor-pointer focus:ring-2 focus:ring-teal-400 outline-none">
                    <option value="">All Statuses</option>
                    <option value="Approved">Approved</option>
                    <option value="Pending">Pending</option>
                    <option value="Rejected">Rejected</option>
                    <option value="Cancelled">Cancelled</option>
                </select>

                <!-- Leave Type Filter -->
                <select v-model="filterType" class="px-3 py-2 text-xs font-bold border border-gray-200 rounded-lg bg-white text-gray-700 shadow-sm cursor-pointer focus:ring-2 focus:ring-teal-400 outline-none">
                    <option value="">All Leave Types</option>
                    <option v-for="t in leaveTypes" :key="t" :value="t">{{ t }}</option>
                </select>

                <!-- Week Filter (Only if Month is selected) -->
                <select v-if="filterMonth" v-model="filterWeek" class="px-3 py-2 text-xs font-bold border border-gray-200 rounded-lg bg-white text-gray-700 shadow-sm cursor-pointer focus:ring-2 focus:ring-teal-400 outline-none">
                    <option value="">All Weeks</option>
                    <option v-for="w in 5" :key="w" :value="w">Week {{ w }}</option>
                </select>

                <!-- Day Filter (Only if Month is selected) -->
                <select v-if="filterMonth" v-model="filterDay" class="px-3 py-2 text-xs font-bold border border-gray-200 rounded-lg bg-white text-gray-700 shadow-sm cursor-pointer focus:ring-2 focus:ring-teal-400 outline-none">
                    <option value="">All Days</option>
                    <option v-for="d in 31" :key="d" :value="d">Day {{ d }}</option>
                </select>

                <!-- Refresh -->
                <button @click="fetchAnalytics" class="px-3 py-2 text-xs font-bold border border-teal-200 rounded-lg bg-teal-50 text-teal-700 hover:bg-teal-100 transition-colors cursor-pointer flex items-center gap-1.5 shadow-sm">
                    <i class="pi pi-refresh text-xs" :class="{ 'animate-spin': loading }"></i>
                    Refresh
                </button>

                <!-- Export Excel -->
                <button @click="exportExcel" :disabled="loading" class="px-3 py-2 text-xs font-bold border border-emerald-200 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition-colors cursor-pointer flex items-center gap-1.5 shadow-sm disabled:opacity-50">
                    <i class="pi pi-file-excel text-xs"></i>
                    Export Excel
                </button>
            </div>
        </div>

        <!-- ── Loading Skeleton ── -->
        <div v-if="loading" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div v-for="i in 6" :key="i" class="bg-white rounded-2xl border border-gray-100 p-6 animate-pulse">
                <div class="h-3 w-24 bg-gray-100 rounded mb-4"></div>
                <div class="space-y-3">
                    <div v-for="j in 4" :key="j" class="h-6 bg-gray-100 rounded-lg" :style="`width: ${100 - j * 15}%`"></div>
                </div>
            </div>
        </div>

        <template v-else>

            <!-- ── Row 1: KPI Summary Cards ── -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div v-for="kpi in kpiCards" :key="kpi.label"
                    class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
                    <div :class="`w-11 h-11 rounded-xl flex items-center justify-center ${kpi.bg} shrink-0`">
                        <i :class="`pi ${kpi.icon} ${kpi.text} text-lg`"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-gray-800 leading-none">{{ kpi.value }}</p>
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-0.5">{{ kpi.label }}</p>
                    </div>
                </div>
            </div>

            <!-- ── Row 2: Monthly Trend Bar + Top Leave Types ── -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Monthly Trend -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] flex items-center gap-2 mb-0.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                                Monthly Leave Trend
                            </h3>
                            <p class="text-[9px] text-teal-500/70 font-bold uppercase tracking-widest pl-3.5">{{ filterYear }}</p>
                        </div>
                        <div class="flex items-center gap-3 text-[9px] font-black uppercase tracking-widest flex-wrap">
                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-teal-500 inline-block"></span>Approved</span>
                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-amber-400 inline-block"></span>Pending</span>
                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-rose-400 inline-block"></span>Rejected</span>
                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-slate-400 inline-block"></span>Cancelled</span>
                        </div>
                    </div>

                    <!-- Bar Chart (pure CSS) -->
                    <div class="flex items-end gap-1.5 h-40">
                        <div v-for="(bar, idx) in monthlyBars" :key="idx" class="flex-1 flex flex-col items-center gap-1 group">
                            <div class="w-full flex flex-col justify-end gap-px" style="height: 128px">
                                <div
                                    v-if="bar.approved > 0"
                                    :title="`Approved: ${bar.approved}`"
                                    class="w-full bg-teal-500 rounded-t transition-all duration-700 cursor-pointer hover:opacity-80"
                                    :style="`height: ${(bar.approved / maxMonthlyVal) * 120}px; min-height: 2px`"
                                ></div>
                                <div
                                    v-if="bar.pending > 0"
                                    :title="`Pending: ${bar.pending}`"
                                    class="w-full bg-amber-400 transition-all duration-700 cursor-pointer hover:opacity-80"
                                    :style="`height: ${(bar.pending / maxMonthlyVal) * 120}px; min-height: 2px`"
                                ></div>
                                <div
                                    v-if="bar.rejected > 0"
                                    :title="`Rejected: ${bar.rejected}`"
                                    class="w-full bg-rose-400 transition-all duration-700 cursor-pointer hover:opacity-80"
                                    :style="`height: ${(bar.rejected / maxMonthlyVal) * 120}px; min-height: 2px`"
                                ></div>
                                <div
                                    v-if="bar.cancelled > 0"
                                    :title="`Cancelled: ${bar.cancelled}`"
                                    class="w-full bg-slate-400 rounded-b transition-all duration-700 cursor-pointer hover:opacity-80"
                                    :style="`height: ${(bar.cancelled / maxMonthlyVal) * 120}px; min-height: 2px`"
                                ></div>
                                <div v-if="bar.total === 0" class="w-full bg-gray-100 rounded" style="height: 4px"></div>
                            </div>
                            <span class="text-xs font-bold text-gray-500 group-hover:text-teal-600 transition-colors">{{ bar.label }}</span>
                            <span v-if="bar.total > 0" class="text-xs font-black text-gray-700">{{ bar.total }}</span>
                        </div>
                    </div>
                </div>

                <!-- Top Leave Types Pie-like -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                            By Leave Type
                        </h3>
                        <i class="pi pi-chart-pie text-gray-200 text-lg"></i>
                    </div>
                    <div class="space-y-3">
                        <div v-for="(item, idx) in byType" :key="idx">
                            <div class="flex justify-between items-center text-xs mb-1">
                                <span class="font-bold text-gray-700 truncate max-w-[130px]" :title="item.name">{{ item.name }}</span>
                                <span class="font-black px-2 py-0.5 rounded text-[10px]" :class="typeColors[idx % typeColors.length].badge">{{ item.count }}</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                <div
                                    class="h-full rounded-full transition-all duration-1000"
                                    :class="typeColors[idx % typeColors.length].bar"
                                    :style="`width: ${totalFiltered > 0 ? Math.round(item.count / totalFiltered * 100) : 0}%`"
                                ></div>
                            </div>
                            <p class="text-[9px] text-gray-400 mt-0.5 text-right">{{ totalFiltered > 0 ? Math.round(item.count / totalFiltered * 100) : 0 }}%</p>
                        </div>
                        <p v-if="!byType.length" class="text-center text-xs text-gray-400 py-6 italic">No data for selected filters</p>
                    </div>
                </div>
            </div>

            <!-- ── Row 3: Department Breakdown + Status Breakdown ── -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Department Leaderboard -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] flex items-center gap-2 mb-0.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Department Volume
                            </h3>
                            <p class="text-[9px] text-emerald-500/70 font-bold uppercase tracking-widest pl-3.5">Ranked by total filings</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <select v-model="deptLimit" class="text-[10px] font-bold border border-gray-200 rounded-lg px-2 py-1 bg-gray-50 text-gray-600 cursor-pointer">
                                <option :value="5">Top 5</option>
                                <option :value="10">Top 10</option>
                                <option :value="999">All</option>
                            </select>
                        </div>
                    </div>
                    <div class="space-y-2.5">
                        <div
                            v-for="(dept, idx) in byDepartment.slice(0, deptLimit)"
                            :key="idx"
                            class="group"
                        >
                            <div class="flex items-center gap-3 mb-1">
                                <div :class="[
                                    'w-7 h-7 rounded-lg flex items-center justify-center text-[9px] font-black border shrink-0 transition-all',
                                    idx === 0 ? 'bg-emerald-500 text-white border-emerald-400 shadow-sm' :
                                    idx === 1 ? 'bg-sky-500 text-white border-sky-400' :
                                    idx === 2 ? 'bg-amber-500 text-white border-amber-400' :
                                    'bg-white text-gray-400 border-gray-200'
                                ]">{{ idx + 1 }}</div>
                                <span class="text-xs font-bold text-gray-700 truncate flex-1" :title="dept.name">{{ dept.name }}</span>
                                <span class="text-xs font-black text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100 shrink-0">{{ dept.count }}</span>
                            </div>
                            <div class="pl-10 w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                <div
                                    class="h-full bg-emerald-400 rounded-full transition-all duration-1000"
                                    :style="`width: ${byDepartment[0]?.count > 0 ? Math.round(dept.count / byDepartment[0].count * 100) : 0}%`"
                                ></div>
                            </div>
                        </div>
                        <p v-if="!byDepartment.length" class="text-center text-xs text-gray-400 py-6 italic">No department data available</p>
                    </div>
                </div>

                <!-- Status Donut + Paid/Unpaid -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-6">

                    <!-- Status Breakdown -->
                    <div>
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] flex items-center gap-2 mb-5">
                            <span class="w-1.5 h-1.5 rounded-full bg-violet-500"></span>
                            Status Breakdown
                        </h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div v-for="stat in statusCards" :key="stat.label"
                                class="flex items-center gap-3 p-3 rounded-xl border transition-all hover:shadow-sm"
                                :class="stat.bg + ' ' + stat.border">
                                <div :class="`w-9 h-9 rounded-full flex items-center justify-center ${stat.iconBg}`">
                                    <i :class="`pi ${stat.icon} text-sm ${stat.iconText}`"></i>
                                </div>
                                <div>
                                    <p class="text-xl font-black text-gray-800 leading-none">{{ stat.value }}</p>
                                    <p :class="`text-[9px] font-black uppercase tracking-widest mt-0.5 ${stat.text}`">{{ stat.label }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pay Breakdown -->
                    <div>
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] flex items-center gap-2 mb-4">
                            <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                            Pay Classification
                            <span class="text-[8px] font-bold text-gray-300 normal-case tracking-normal">· Approved only</span>
                        </h3>
                        <div class="relative h-5 bg-gray-100 rounded-full overflow-hidden">
                            <div
                                class="absolute left-0 top-0 h-full bg-sky-500 rounded-full transition-all duration-1000"
                                :style="`width: ${paidPct}%`"
                            ></div>
                        </div>
                        <div class="flex justify-between text-[10px] font-black mt-2">
                            <span class="text-sky-600">Paid — {{ analytics.approved_paid ?? 0 }} ({{ paidPct }}%)</span>
                            <span class="text-gray-400">Unpaid — {{ analytics.approved_unpaid ?? 0 }} ({{ 100 - paidPct }}%)</span>
                        </div>
                    </div>
                </div>
            </div>

        </template>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import { useLeaveStore } from '../../stores/leaves';

// ── State ──────────────────────────────────────────
const loading = ref(true);
const analytics = ref({});
const byType = ref([]);
const byDepartment = ref([]);
const monthlyTrend = ref([]);

// ── Auto-refresh when a leave is filed anywhere in the app ──
const leaveStore = useLeaveStore();
watch(() => leaveStore.analyticsKey, () => fetchAnalytics());

// ── Filters ────────────────────────────────────────
const now = new Date();
const filterYear = ref(now.getFullYear());
const filterMonth = ref('');      // '' = all months
const filterWeek = ref('');
const filterDay = ref('');
const filterStatus = ref('');
const filterType = ref('');
const deptLimit = ref(5);

const yearOptions = Array.from({ length: 5 }, (_, i) => now.getFullYear() - i);
const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

const leaveTypes = [
    'Sick Leave', 'Vacation Leave', 'Emergency Leave', 'Maternity Leave',
    'Paternity Leave', 'Solo Parent', 'Special Non-Working', 'VAWC',
    'Rehabilitation Leave', 'Study Leave', 'SIL'
];

watch(filterMonth, (newVal) => {
    if (!newVal) {
        filterWeek.value = '';
        filterDay.value = '';
    }
});

// ── Derived Labels ─────────────────────────────────
const filterLabel = computed(() => {
    const m = filterMonth.value ? months[filterMonth.value - 1] : 'All Months';
    const w = filterWeek.value ? `Week ${filterWeek.value}` : '';
    const d = filterDay.value ? `Day ${filterDay.value}` : '';
    const timeDetail = [m, w, d].filter(Boolean).join(', ');
    const s = filterStatus.value || 'All Statuses';
    const t = filterType.value || 'All Types';
    return `${timeDetail}, ${filterYear.value} · ${s} · ${t}`;
});

// ── API Fetch ──────────────────────────────────────
const fetchAnalytics = async () => {
    loading.value = true;
    try {
        const params = { year: filterYear.value };
        if (filterMonth.value) params.month = filterMonth.value;
        if (filterWeek.value) params.week = filterWeek.value;
        if (filterDay.value) params.day = filterDay.value;
        if (filterStatus.value) params.status = filterStatus.value;
        if (filterType.value) params.leave_type = filterType.value;

        const { data } = await axios.get('/api/leave-analytics', { params });
        analytics.value = data;
        byType.value = data.by_type ?? [];
        byDepartment.value = data.by_department ?? [];
        monthlyTrend.value = data.monthly_trend ?? [];
    } catch (e) {
        console.error('Analytics fetch failed', e);
    } finally {
        loading.value = false;
    }
};

const exportExcel = () => {
    const params = new URLSearchParams({ 
        year: filterYear.value,
        month: filterMonth.value,
        week: filterWeek.value,
        day: filterDay.value,
        status: filterStatus.value,
        leave_type: filterType.value
    });
    window.location.href = `/api/leave-analytics/export?${params.toString()}`;
};

watch([filterYear, filterMonth, filterWeek, filterDay, filterStatus, filterType], fetchAnalytics);
onMounted(fetchAnalytics);

// ── Computed ───────────────────────────────────────
const totalFiltered = computed(() => analytics.value.total ?? 0);

const kpiCards = computed(() => [
    { label: 'Total Filings',    value: analytics.value.total ?? 0,       icon: 'pi-file',         bg: 'bg-teal-50',   text: 'text-teal-600' },
    { label: 'Approved',         value: analytics.value.approved ?? 0,    icon: 'pi-check-circle', bg: 'bg-emerald-50',text: 'text-emerald-600' },
    { label: 'Days Consumed',    value: analytics.value.days_taken ?? 0,  icon: 'pi-calendar-minus',bg: 'bg-indigo-50', text: 'text-indigo-600' },
    { label: 'Avg Days / Filing',value: analytics.value.avg_days ?? '—',  icon: 'pi-chart-line',   bg: 'bg-amber-50',  text: 'text-amber-600' },
]);

const statusCards = computed(() => [
    { label: 'Approved',  value: analytics.value.approved  ?? 0, icon: 'pi-check-circle', bg: 'bg-emerald-50', border: 'border-emerald-100', text: 'text-emerald-600', iconBg: 'bg-emerald-100', iconText: 'text-emerald-600' },
    { label: 'Pending',   value: analytics.value.pending   ?? 0, icon: 'pi-clock',         bg: 'bg-amber-50',   border: 'border-amber-100',   text: 'text-amber-600',   iconBg: 'bg-amber-100',   iconText: 'text-amber-600' },
    { label: 'Rejected',  value: analytics.value.rejected  ?? 0, icon: 'pi-times-circle',  bg: 'bg-rose-50',    border: 'border-rose-100',    text: 'text-rose-600',    iconBg: 'bg-rose-100',    iconText: 'text-rose-600' },
    { label: 'Cancelled', value: analytics.value.cancelled ?? 0, icon: 'pi-ban',           bg: 'bg-slate-50',   border: 'border-slate-100',   text: 'text-slate-600',   iconBg: 'bg-slate-100',   iconText: 'text-slate-500' },
]);

const paidTotal = computed(() => (analytics.value.approved_paid ?? 0) + (analytics.value.approved_unpaid ?? 0));
const paidPct = computed(() => paidTotal.value > 0 ? Math.round((analytics.value.approved_paid ?? 0) / paidTotal.value * 100) : 0);

// Monthly bar chart
const monthlyBars = computed(() => {
    return months.map((label, i) => {
        const entry = monthlyTrend.value.find(e => e.month === i + 1) ?? {};
        return {
            label,
            approved:  entry.approved  ?? 0,
            pending:   entry.pending   ?? 0,
            rejected:  entry.rejected  ?? 0,
            cancelled: entry.cancelled ?? 0,
            total: (entry.approved ?? 0) + (entry.pending ?? 0) + (entry.rejected ?? 0) + (entry.cancelled ?? 0),
        };
    });
});

const maxMonthlyVal = computed(() => {
    const max = Math.max(...monthlyBars.value.map(b => b.total), 1);
    return max;
});

const typeColors = [
    { bar: 'bg-indigo-500', badge: 'bg-indigo-50 text-indigo-700' },
    { bar: 'bg-teal-500',   badge: 'bg-teal-50   text-teal-700' },
    { bar: 'bg-amber-500',  badge: 'bg-amber-50  text-amber-700' },
    { bar: 'bg-rose-500',   badge: 'bg-rose-50   text-rose-700' },
    { bar: 'bg-sky-500',    badge: 'bg-sky-50    text-sky-700' },
    { bar: 'bg-violet-500', badge: 'bg-violet-50 text-violet-700' },
    { bar: 'bg-emerald-500',badge: 'bg-emerald-50 text-emerald-700' },
];
</script>
