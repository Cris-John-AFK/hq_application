<template>
    <div class="max-w-5xl mx-auto space-y-6 py-2">

        <!-- ══ SUMMARY CHIPS ══════════════════════════════════════════════════════ -->
        <div v-if="user?.role === 'dept_head'">

            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-black text-gray-800 flex items-center gap-2">
                        <i class="pi pi-sitemap text-teal-600"></i>
                        Department Leave Management
                    </h1>
                    <p class="text-xs text-gray-400 mt-0.5 font-bold uppercase tracking-widest">
                        Review and process your team's leave requests
                    </p>
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    <div class="flex items-center gap-2 bg-white rounded-xl border border-gray-200 px-3 py-2 shadow-sm">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Month</label>
                        <input type="month" v-model="reportMonth" @change="fetchReport"
                            class="text-sm font-bold text-gray-700 bg-transparent border-none outline-none cursor-pointer">
                    </div>
                    <button @click="exportReport()"
                        class="flex items-center gap-1.5 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all shadow-sm active:scale-95">
                        <i class="pi pi-download"></i> Export CSV
                    </button>
                </div>
            </div>

            <!-- Summary Stat Chips -->
            <div v-if="report" class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
                <div class="bg-amber-50 border border-amber-100 rounded-2xl p-4 text-center shadow-sm">
                    <p class="text-3xl font-black text-amber-600">{{ report.summary.pending }}</p>
                    <p class="text-[10px] font-black text-amber-500 uppercase tracking-widest mt-1">Pending</p>
                </div>
                <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 text-center shadow-sm">
                    <p class="text-3xl font-black text-blue-600">{{ report.summary.dept_approved }}</p>
                    <p class="text-[10px] font-black text-blue-500 uppercase tracking-widest mt-1">Sent to HR</p>
                </div>
                <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-4 text-center shadow-sm">
                    <p class="text-3xl font-black text-emerald-600">{{ report.summary.approved }}</p>
                    <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mt-1">Approved</p>
                </div>
                <div class="bg-rose-50 border border-rose-100 rounded-2xl p-4 text-center shadow-sm">
                    <p class="text-3xl font-black text-rose-600">{{ report.summary.rejected }}</p>
                    <p class="text-[10px] font-black text-rose-500 uppercase tracking-widest mt-1">Rejected</p>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 text-center shadow-sm">
                    <p class="text-3xl font-black text-gray-500">{{ report.summary.cancelled }}</p>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-1">Cancelled</p>
                </div>
            </div>
            <!-- skeleton -->
            <div v-else class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
                <div v-for="i in 5" :key="i" class="h-24 bg-gray-100 animate-pulse rounded-2xl"></div>
            </div>

            <!-- ▶ TOP: PENDING — ACTION REQUIRED -->
            <div class="bg-white rounded-2xl shadow-md border border-amber-100 overflow-hidden mb-6">
                <div class="flex items-center justify-between px-6 py-4 border-b border-amber-100 bg-amber-50">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse"></span>
                        <h2 class="text-sm font-black text-amber-700 uppercase tracking-widest">Pending Requests — Action Required</h2>
                    </div>
                    <span class="px-3 py-1 bg-amber-200 text-amber-800 text-[10px] font-black rounded-full uppercase tracking-widest">
                        {{ report ? report.pending.length : '…' }} Pending
                    </span>
                </div>

                <div v-if="loadingReport" class="p-6 space-y-3">
                    <div v-for="i in 3" :key="i" class="h-24 bg-gray-50 animate-pulse rounded-xl"></div>
                </div>
                <div v-else-if="!report || report.pending.length === 0" class="p-12 text-center">
                    <i class="pi pi-check-circle text-5xl text-emerald-200 block mb-3"></i>
                    <p class="text-sm font-black text-gray-400 uppercase tracking-widest">All Clear!</p>
                    <p class="text-xs text-gray-400 mt-1">No pending requests from your team this month.</p>
                </div>
                <div v-else class="divide-y divide-gray-50">
                    <div v-for="req in report.pending" :key="req.id" class="p-5 hover:bg-amber-50/30 transition-all group">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-full bg-teal-100 flex items-center justify-center text-teal-600 font-black text-sm border border-teal-200 shrink-0 group-hover:scale-105 transition-transform">
                                    {{ getInitials(req.employee_name) }}
                                </div>
                                <div>
                                    <p class="font-black text-gray-800">{{ req.employee_name }}</p>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mt-0.5">{{ req.position }}</p>
                                    <p class="text-xs text-teal-700 font-black mt-1">{{ req.leave_type }} • {{ req.days_taken }} Day(s)</p>
                                    <p class="text-[10px] text-gray-500 mt-0.5">
                                        {{ formatDate(req.from_date) }}
                                        <template v-if="req.to_date && req.to_date !== req.from_date"> → {{ formatDate(req.to_date) }}</template>
                                    </p>
                                </div>
                            </div>
                            <!-- Action -->
                            <div class="flex flex-col gap-2 min-w-[220px]">
                                <input v-model="remarksMap[req.id]" type="text" placeholder="Remarks (optional)"
                                    class="w-full px-3 py-1.5 text-xs border border-gray-200 rounded-lg outline-none focus:ring-2 focus:ring-teal-300 bg-gray-50 placeholder-gray-400">
                                <div class="flex gap-2">
                                    <button @click="processRequest(req, 'Dept Approved')"
                                        class="flex-1 px-3 py-2 bg-teal-500 hover:bg-teal-600 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all active:scale-95 shadow-sm flex items-center justify-center gap-1">
                                        <i class="pi pi-arrow-right text-[10px]"></i> Forward to HR
                                    </button>
                                    <button @click="processRequest(req, 'Rejected')"
                                        class="flex-1 px-3 py-2 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-xl text-xs font-black uppercase tracking-widest transition-all active:scale-95 border border-rose-200 flex items-center justify-center gap-1">
                                        <i class="pi pi-times text-[10px]"></i> Reject
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 p-3 bg-gray-50 rounded-xl border border-gray-100 text-xs text-gray-600 italic">
                            <span class="font-black text-gray-400 uppercase tracking-widest not-italic text-[10px]">Reason: </span>"{{ req.reason }}"
                        </div>
                    </div>
                </div>
            </div>

            <!-- ▶ BOTTOM: Approved (left) + Rejected (right) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- APPROVED PANEL -->
                <div class="bg-white rounded-2xl shadow-md border border-emerald-100 overflow-hidden flex flex-col">
                    <div class="flex items-center justify-between px-5 py-4 bg-emerald-50 border-b border-emerald-100">
                        <div class="flex items-center gap-2">
                            <i class="pi pi-verified text-emerald-600"></i>
                            <span class="text-xs font-black text-emerald-700 uppercase tracking-widest">Officially Approved</span>
                        </div>
                        <div class="flex gap-1.5 items-center">
                            <span class="px-2 py-0.5 bg-emerald-200 text-emerald-800 text-[10px] font-black rounded-full">
                                {{ report ? report.approved.length : '…' }}
                            </span>
                            <button @click="exportReport('Approved')"
                                class="px-2 py-0.5 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 text-[10px] font-black rounded-lg transition-all border border-emerald-200">
                                <i class="pi pi-download text-[9px]"></i>
                            </button>
                        </div>
                    </div>
                    <div class="flex-1 overflow-y-auto max-h-96">
                        <div v-if="loadingReport" class="p-4 space-y-2">
                            <div v-for="i in 3" :key="i" class="h-16 bg-gray-50 animate-pulse rounded-lg"></div>
                        </div>
                        <div v-else-if="!report || report.approved.length === 0" class="flex flex-col items-center justify-center h-40 text-center p-4">
                            <i class="pi pi-inbox text-3xl text-gray-200 mb-2"></i>
                            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">No approved requests</p>
                        </div>
                        <div v-else class="divide-y divide-gray-50">
                            <div v-for="req in report.approved" :key="req.id" class="px-5 py-3.5 hover:bg-emerald-50/30 transition-all">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-9 h-9 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 font-black text-xs border border-emerald-200 shrink-0">
                                            {{ getInitials(req.employee_name) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-black text-gray-800 text-xs truncate">{{ req.employee_name }}</p>
                                            <p class="text-[10px] text-gray-500 mt-0.5">{{ req.leave_type }} • {{ req.days_taken }}d</p>
                                            <p class="text-[10px] text-emerald-600 font-bold">{{ formatDate(req.from_date) }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="text-[9px] text-gray-400 font-bold uppercase">HR Approved By</p>
                                        <p class="text-[10px] font-black text-emerald-700">{{ req.hr_approver_name || '—' }}</p>
                                        <p class="text-[9px] text-gray-400 mt-0.5">{{ formatShortDT(req.hr_approved_at) }}</p>
                                    </div>
                                </div>
                                <div v-if="req.dept_head_name" class="mt-1.5 flex items-center gap-1 text-[9px] text-gray-400 font-bold">
                                    <i class="pi pi-user text-[8px] text-teal-400"></i>
                                    Dept Head: <span class="text-teal-600 ml-0.5">{{ req.dept_head_name }}</span>
                                    <span v-if="req.dept_head_remarks" class="text-gray-400 ml-1">— "{{ req.dept_head_remarks }}"</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- REJECTED PANEL -->
                <div class="bg-white rounded-2xl shadow-md border border-rose-100 overflow-hidden flex flex-col">
                    <div class="flex items-center justify-between px-5 py-4 bg-rose-50 border-b border-rose-100">
                        <div class="flex items-center gap-2">
                            <i class="pi pi-times-circle text-rose-500"></i>
                            <span class="text-xs font-black text-rose-700 uppercase tracking-widest">Rejected</span>
                        </div>
                        <div class="flex gap-1.5 items-center">
                            <span class="px-2 py-0.5 bg-rose-200 text-rose-800 text-[10px] font-black rounded-full">
                                {{ report ? report.rejected.length : '…' }}
                            </span>
                            <button @click="exportReport('Rejected')"
                                class="px-2 py-0.5 bg-rose-100 hover:bg-rose-200 text-rose-700 text-[10px] font-black rounded-lg transition-all border border-rose-200">
                                <i class="pi pi-download text-[9px]"></i>
                            </button>
                        </div>
                    </div>
                    <div class="flex-1 overflow-y-auto max-h-96">
                        <div v-if="loadingReport" class="p-4 space-y-2">
                            <div v-for="i in 3" :key="i" class="h-16 bg-gray-50 animate-pulse rounded-lg"></div>
                        </div>
                        <div v-else-if="!report || report.rejected.length === 0" class="flex flex-col items-center justify-center h-40 text-center p-4">
                            <i class="pi pi-inbox text-3xl text-gray-200 mb-2"></i>
                            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">No rejected requests</p>
                        </div>
                        <div v-else class="divide-y divide-gray-50">
                            <div v-for="req in report.rejected" :key="req.id" class="px-5 py-3.5 hover:bg-rose-50/30 transition-all">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-9 h-9 rounded-full bg-rose-100 flex items-center justify-center text-rose-600 font-black text-xs border border-rose-200 shrink-0">
                                            {{ getInitials(req.employee_name) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-black text-gray-800 text-xs truncate">{{ req.employee_name }}</p>
                                            <p class="text-[10px] text-gray-500 mt-0.5">{{ req.leave_type }} • {{ req.days_taken }}d</p>
                                            <p class="text-[10px] text-rose-600 font-bold">{{ formatDate(req.from_date) }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="text-[9px] text-gray-400 font-bold uppercase">Rejected By</p>
                                        <p class="text-[10px] font-black text-rose-600">{{ req.dept_head_name || req.hr_approver_name || '—' }}</p>
                                        <p class="text-[9px] text-gray-400 mt-0.5">{{ formatShortDT(req.dept_reviewed_at || req.hr_approved_at) }}</p>
                                    </div>
                                </div>
                                <div v-if="req.dept_head_remarks || req.hr_remarks" class="mt-2 p-2 bg-rose-50 rounded-lg text-[10px] text-rose-700 italic border border-rose-100 line-clamp-2">
                                    "{{ req.dept_head_remarks || req.hr_remarks }}"
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CANCELLED — collapsible -->
            <details class="mt-6 bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden group">
                <summary class="flex items-center justify-between px-6 py-4 cursor-pointer bg-gray-50 hover:bg-gray-100 transition-all list-none">
                    <div class="flex items-center gap-2">
                        <i class="pi pi-ban text-gray-400"></i>
                        <span class="text-xs font-black text-gray-500 uppercase tracking-widest">Cancelled This Month</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 bg-gray-200 text-gray-600 text-[10px] font-black rounded-full">{{ report ? report.cancelled.length : '…' }}</span>
                        <button @click.prevent="exportReport('Cancelled')" class="px-2 py-0.5 bg-gray-100 hover:bg-gray-200 text-gray-500 text-[10px] font-black rounded-lg transition-all border border-gray-200">
                            <i class="pi pi-download text-[9px]"></i>
                        </button>
                        <i class="pi pi-chevron-down text-gray-400 group-open:rotate-180 transition-transform text-xs"></i>
                    </div>
                </summary>
                <div class="divide-y divide-gray-50">
                    <div v-if="!report || report.cancelled.length === 0" class="p-8 text-center">
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">No cancelled requests this month</p>
                    </div>
                    <div v-for="req in (report?.cancelled ?? [])" :key="req.id" class="px-6 py-4 hover:bg-gray-50 transition-all flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 font-black text-xs border border-gray-200">
                                {{ getInitials(req.employee_name) }}
                            </div>
                            <div>
                                <p class="font-black text-gray-700 text-xs">{{ req.employee_name }}</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">{{ req.leave_type }} • {{ formatDate(req.from_date) }}</p>
                            </div>
                        </div>
                        <span class="text-[9px] text-gray-400 font-bold">Filed {{ formatDate(req.filed_on) }}</span>
                    </div>
                </div>
            </details>

            <!-- My Own Credits (For Dept Head as well) -->
            <div class="mt-8 pt-8 border-t border-gray-100">
                <div class="flex items-center gap-2 mb-4">
                    <i class="pi pi-user-circle text-gray-400"></i>
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest">My Personal Leave Credits</h3>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-9 gap-3">
                    <div v-for="type in leaveTypesList" :key="type.key" 
                        :class="[type.bg, type.border, 'p-3 rounded-xl border border-dashed text-center opacity-80 hover:opacity-100 transition-opacity']">
                        <p class="text-[8px] font-black uppercase tracking-widest text-gray-400">{{ type.label }}</p>
                        <p :class="[type.color, 'text-xl font-black mt-0.5']">
                            {{ user.employee?.[type.key] ?? '0' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- For regular users (non dept_head): show their own leave highlights -->
        <div v-else-if="user" class="space-y-6">
            <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-black text-gray-800">My Leave Credits</h2>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">Real-time balances from HR masterlist</p>
                    </div>
                    <button @click="$router.push('/leave-requests')" class="text-xs font-black text-teal-600 uppercase tracking-widest hover:text-teal-700 transition-colors">
                        View Details <i class="pi pi-arrow-right ml-1 text-[8px]"></i>
                    </button>
                </div>

                <div class="grid grid-cols-3 md:grid-cols-5 lg:grid-cols-9 gap-3">
                    <div v-for="type in leaveTypesList" :key="type.key" 
                        :class="[type.bg, type.border, 'p-4 rounded-xl border shadow-sm text-center group transition-all hover:scale-105']">
                        <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400 group-hover:text-gray-600">{{ type.label }}</p>
                        <p :class="[type.color, 'text-2xl font-black mt-1']">
                            {{ user.employee?.[type.key] ?? '0' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Quick Action -->
            <div class="bg-teal-600 rounded-2xl p-8 text-center text-white shadow-xl shadow-teal-100">
                 <i class="pi pi-calendar-plus text-5xl mb-4 opacity-50 block"></i>
                 <h3 class="text-xl font-black">Plan your next time off?</h3>
                 <p class="text-teal-50 mt-2 mb-6 max-w-sm mx-auto opacity-80">Submit a new leave request or check your filing history with just one click.</p>
                 <button @click="$router.push('/leave-requests')"
                    class="px-8 py-3 bg-white text-teal-600 rounded-xl font-black uppercase tracking-widest text-sm transition-all hover:bg-teal-50 active:scale-95 shadow-lg">
                    Request Leave Now
                </button>
            </div>
        </div>

        <div v-else class="flex items-center justify-center h-64">
            <div class="text-center">
                <i class="pi pi-spin pi-spinner text-3xl text-teal-400 mb-3 block"></i>
                <p class="text-sm text-gray-400 font-bold">Loading…</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useAuthStore } from '../../stores/auth';
import { storeToRefs } from 'pinia';
import { ref, onMounted } from 'vue';
import axios from 'axios';

const authStore = useAuthStore();
const { user } = storeToRefs(authStore);

const report        = ref(null);
const loadingReport = ref(false);
const remarksMap    = ref({});

const now = new Date();
const reportMonth = ref(`${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`);

const leaveTypesList = ref([]);

const colorThemes = [
    { color: 'text-purple-600', bg: 'bg-purple-50', border: 'border-purple-100' },
    { color: 'text-pink-600', bg: 'bg-pink-50', border: 'border-pink-100' },
    { color: 'text-blue-600', bg: 'bg-blue-50', border: 'border-blue-100' },
    { color: 'text-emerald-600', bg: 'bg-emerald-50', border: 'border-emerald-100' },
    { color: 'text-gray-600', bg: 'bg-gray-50', border: 'border-gray-200' },
    { color: 'text-rose-600', bg: 'bg-rose-50', border: 'border-rose-100' },
    { color: 'text-amber-600', bg: 'bg-amber-50', border: 'border-amber-100' },
    { color: 'text-indigo-600', bg: 'bg-indigo-50', border: 'border-indigo-100' },
    { color: 'text-orange-600', bg: 'bg-orange-50', border: 'border-orange-100' },
    { color: 'text-teal-600', bg: 'bg-teal-50', border: 'border-teal-100' },
];

const loadLeaveSettings = async () => {
    try {
        const res = await axios.get('/api/settings/leave_types');
        const typesList = res.data;
        leaveTypesList.value = typesList.map((label, idx) => {
            let abbr = '';
            const typeLower = label.toLowerCase();

            // Explicit PH standard abbreviations to avoid duplicates (VA vs VAWC, SL vs SIL)
            if (typeLower.includes('sick')) abbr = 'SL';
            else if (typeLower.includes('vacation')) abbr = 'VL';
            else if (typeLower.includes('sil')) abbr = 'SI';
            else if (typeLower.includes('vawc')) abbr = 'VW';
            else if (typeLower.includes('solo parent')) abbr = 'SP';
            else if (typeLower.includes('maternity')) abbr = 'ML';
            else if (typeLower.includes('paternity')) abbr = 'PL';
            else if (typeLower.includes('emergency')) abbr = 'EL';
            else if (typeLower.includes('bereavement')) abbr = 'BL';
            else if (typeLower.includes('magna carta')) abbr = 'MC';
            else {
                const match = label.match(/\(([^)]+)\)/);
                if (match) {
                    abbr = match[1];
                } else {
                    const words = label.replace(/leave/i, '').trim().split(' ');
                    if (words.length > 1) {
                        abbr = (words[0][0] + words[1][0]).toUpperCase();
                    } else {
                        abbr = words[0].substring(0, 2).toUpperCase();
                    }
                }
            }
            
            // Generate valid DB column
            let col = '';
            if (typeLower.includes('paternity')) col = 'paternity_leave';
            else if (typeLower.includes('solo')) col = 'solo_parent_leave';
            else if (typeLower.includes('bereavement')) col = 'bereavement_leave';
            else if (typeLower.includes('vawc') || typeLower.includes('vaws')) col = 'vawc_leave';
            else if (typeLower.includes('maternity')) col = 'maternity_leave';
            else if (typeLower.includes('magna') || typeLower.includes('carta')) col = 'magna_carta_leave';
            else if (typeLower.includes('emergency')) col = 'emergency_leave';
            else if (typeLower.includes('sick') || typeLower === 'sl') col = 'sick_leave';
            else if (typeLower.includes('vacation') || typeLower === 'vl') col = 'vacation_leave';
            else {
                col = typeLower.replace(/ *\([^)]*\) */g, "").trim().replace(/ /g, '_');
                if (!col.endsWith('_leave')) col += '_leave';
            }
            
            const theme = colorThemes[idx % colorThemes.length];
            return { key: col, label: abbr, color: theme.color, bg: theme.bg, border: theme.border };
        });
    } catch (error) {
        console.error('Failed to load leave types settings', error);
    }
};

const getInitials = (name) => {
    if (!name) return '??';
    return name.split(' ').map(w => w[0]).join('').toUpperCase().substring(0, 2);
};

const formatDate = (d) => {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const formatShortDT = (d) => {
    if (!d) return '—';
    return new Date(d).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const fetchReport = async () => {
    if (!user.value || user.value.role !== 'dept_head') return;
    loadingReport.value = true;
    try {
        const [y, m] = reportMonth.value.split('-');
        const res = await axios.get('/api/dept-head/report', { params: { year: y, month: m } });
        report.value = res.data;
    } catch (e) {
        console.error('Failed to fetch dept report', e);
    } finally {
        loadingReport.value = false;
    }
};

const processRequest = async (req, status) => {
    const label = status === 'Dept Approved' ? 'forward to HR for final approval' : 'reject';
    if (!confirm(`Are you sure you want to ${label} the request from ${req.employee_name}?`)) return;
    try {
        await axios.put(`/api/leave-requests/${req.id}`, {
            status,
            remarks: remarksMap.value[req.id] || '',
        });
        remarksMap.value[req.id] = '';
        await fetchReport();
    } catch (e) {
        console.error('Failed to process request', e);
        alert('Action failed. Please try again.');
    }
};

const exportReport = (statusFilter = null) => {
    const [y, m] = reportMonth.value.split('-');
    const params = new URLSearchParams({ year: y, month: m });
    if (statusFilter) params.set('status', statusFilter);
    window.open(`/api/dept-head/report/export?${params.toString()}`, '_blank');
};

onMounted(async () => {
    await loadLeaveSettings();
    if (!user.value) await authStore.fetchUser();
    if (user.value?.role === 'dept_head') await fetchReport();
});
</script>

<style scoped>
details summary::-webkit-details-marker { display: none; }
</style>