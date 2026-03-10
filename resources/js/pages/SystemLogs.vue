<template>
    <div v-if="user">
        <MainLayout :user="user">
            <div class="max-w-7xl mx-auto space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-800 uppercase tracking-tight">Action Audit Trail</h3>
                                <p class="text-[11px] text-gray-500 font-bold uppercase tracking-widest mt-0.5">Historical record of all administrative leave actions and decisions.</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <button @click="showFilters = !showFilters" :class="[showFilters ? 'bg-teal-600 text-white' : 'bg-gray-50 text-gray-600 hover:bg-gray-100']" class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm border border-transparent">
                                    <i class="pi" :class="showFilters ? 'pi-filter-slash' : 'pi-filter'"></i>
                                    {{ showFilters ? 'Hide Filters' : 'Toggle Filters' }}
                                </button>
                                <button @click="resetFilters" class="p-2.5 text-gray-400 hover:text-teal-600 transition-colors bg-gray-50 hover:bg-gray-100 rounded-xl border border-gray-100" title="Clear & Refresh">
                                    <i class="pi pi-refresh"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Search and Filters Section -->
                        <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 -translate-y-4" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-4">
                            <div v-if="showFilters" class="mt-8 space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4">
                                    <!-- Global Search -->
                                    <div class="md:col-span-2 relative group">
                                        <i class="pi pi-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-teal-500 transition-colors"></i>
                                        <input 
                                            v-model="filters.search" 
                                            @input="debounceFetch"
                                            placeholder="Search by User, Description, IP, or Device..." 
                                            class="w-full pl-11 pr-4 py-3 bg-gray-50/50 border border-gray-100 rounded-2xl text-[12px] font-bold focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all placeholder:text-gray-300"
                                        >
                                    </div>
                                    
                                    <!-- Module Filter -->
                                    <div>
                                        <select v-model="filters.module" @change="fetchLogs(1)" class="w-full px-4 py-3 bg-gray-50/50 border border-gray-100 rounded-2xl text-[10px] font-black uppercase tracking-wider focus:border-teal-500 outline-none transition-all cursor-pointer">
                                            <option value="">All Modules</option>
                                            <option v-for="mod in modulesList" :key="mod" :value="mod">{{ mod }}</option>
                                        </select>
                                    </div>

                                    <!-- Action Filter -->
                                    <div>
                                        <select v-model="filters.action" @change="fetchLogs(1)" class="w-full px-4 py-3 bg-gray-50/50 border border-gray-100 rounded-2xl text-[10px] font-black uppercase tracking-wider focus:border-teal-500 outline-none transition-all cursor-pointer">
                                            <option value="">All Actions</option>
                                            <option v-for="act in actionsList" :key="act" :value="act">{{ act }}</option>
                                        </select>
                                    </div>

                                    <!-- Date From -->
                                    <div class="relative group">
                                        <input 
                                            type="date" 
                                            v-model="filters.date_from" 
                                            @change="fetchLogs(1)"
                                            class="w-full px-4 py-3 bg-gray-50/50 border border-gray-100 rounded-2xl text-[10px] font-black uppercase tracking-wider focus:border-teal-500 outline-none transition-all cursor-pointer"
                                        >
                                        <span class="absolute -top-2 left-4 bg-white px-1 text-[8px] font-bold text-gray-400 uppercase tracking-widest leading-none">From</span>
                                    </div>

                                    <!-- Date To -->
                                    <div class="relative group">
                                        <input 
                                            type="date" 
                                            v-model="filters.date_to" 
                                            @change="fetchLogs(1)"
                                            class="w-full px-4 py-3 bg-gray-50/50 border border-gray-100 rounded-2xl text-[10px] font-black uppercase tracking-wider focus:border-teal-500 outline-none transition-all cursor-pointer"
                                        >
                                        <span class="absolute -top-2 left-4 bg-white px-1 text-[8px] font-bold text-gray-400 uppercase tracking-widest leading-none">To</span>
                                    </div>
                                </div>
                            </div>
                        </transition>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs md:text-sm">
                            <thead>
                                <tr class="bg-gray-50 text-gray-500 text-[10px] uppercase tracking-widest font-black">
                                    <th class="px-6 py-4">Timestamp</th>
                                    <th class="px-6 py-4">User</th>
                                    <th class="px-6 py-4">Module</th>
                                    <th class="px-6 py-4">Action</th>
                                    <th class="px-6 py-4">Access Origin</th>
                                    <th class="px-6 py-4">Description</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-if="loading" v-for="i in 5" :key="i" class="animate-pulse">
                                    <td v-for="j in 5" :key="j" class="px-6 py-4">
                                        <div class="h-4 bg-gray-100 rounded"></div>
                                    </td>
                                </tr>
                                
                                <tr v-for="log in logs" :key="log.id" class="hover:bg-gray-50/50 transition-colors text-[13px]">
                                    <td class="px-6 py-4 text-[10px] font-mono text-gray-500">{{ formatDate(log.created_at) }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-teal-50 flex items-center justify-center text-[10px] font-black text-teal-600 border border-teal-100 uppercase">
                                                {{ getInitials(log.user?.name) }}
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-bold text-gray-800">{{ log.user?.name || 'System' }}</span>
                                                <span v-if="log.user?.role === 'admin'" class="text-[8px] font-black text-rose-500 uppercase tracking-widest mt-0.5">Administrator</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                         <span class="px-2 py-0.5 rounded bg-slate-600 text-white font-black uppercase text-[9px] tracking-widest shadow-sm border border-slate-700">
                                             {{ log.module }}
                                         </span>
                                     </td>
                                     <td class="px-6 py-4">
                                         <span :class="getActionClass(log.action)" class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-current shadow-sm shadow-current/10">
                                             {{ log.action }}
                                         </span>
                                     </td>
                                    <td class="px-6 py-4">
                                         <div class="flex flex-col gap-1">
                                             <div class="flex items-center gap-1.5 text-gray-800">
                                                 <i :class="['pi', getDeviceIcon(log.device)]" class="text-[11px]"></i>
                                                 <span class="text-[11px] font-bold uppercase tracking-tight">{{ log.device || 'Unidentified Device' }}</span>
                                             </div>
                                             <div class="flex items-center gap-1.5">
                                                 <i class="pi pi-directions text-slate-400 text-[10px]"></i>
                                                 <span class="text-[10px] font-mono font-bold text-slate-500 uppercase tracking-tighter">IP: {{ log.ip_address || '0.0.0.0' }}</span>
                                             </div>
                                             <div class="flex items-center gap-1.5 opacity-60">
                                                 <i class="pi pi-map-marker text-emerald-500 text-[9px]"></i>
                                                 <span class="text-[9px] font-bold text-emerald-600 uppercase tracking-tighter">{{ log.location || 'Local Network' }}</span>
                                             </div>
                                         </div>
                                     </td>
                                     <td class="px-6 py-4 max-w-md">
                                         <div class="flex flex-col gap-1">
                                             <p class="text-gray-700 font-bold leading-tight line-clamp-2">
                                                 {{ log.description }}
                                             </p>
                                            <button 
                                                v-if="log.old_data || log.new_data" 
                                                @click="viewTechnicalDetails(log)"
                                                class="mt-1 w-fit text-[10px] text-teal-600 font-black uppercase flex gap-1.5 items-center hover:text-teal-700 transition-colors cursor-pointer group"
                                            >
                                                <i class="pi pi-code"></i>
                                                <span>View Technical Diff</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="!loading && logs.length === 0">
                                    <td colspan="5" class="px-6 py-20 text-center">
                                        <i class="pi pi-database text-gray-200 text-5xl mb-4 block"></i>
                                        <p class="text-gray-400 font-medium italic">No administrative actions logged yet.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Placeholder -->
                    <div v-if="logs.length > 0" class="p-4 bg-gray-50 border-t border-gray-100 flex justify-between items-center">
                        <span class="text-xs text-gray-400 font-bold uppercase tracking-widest">Total Logs: {{ total }}</span>
                        <div class="flex items-center gap-4">
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Page {{ page }} of {{ lastPage }}</span>
                            <div class="flex gap-2">
                                <button 
                                    @click="fetchLogs(page - 1)"
                                    :disabled="page === 1 || loading"
                                    class="px-3 py-1 bg-white border border-gray-200 rounded text-xs font-bold uppercase tracking-tighter hover:bg-gray-50 disabled:opacity-30 cursor-pointer"
                                >
                                    Prev
                                </button>
                                <button 
                                    @click="fetchLogs(page + 1)"
                                    :disabled="page >= lastPage || loading"
                                    class="px-3 py-1 bg-white border border-gray-200 rounded text-xs font-bold uppercase tracking-tighter hover:bg-gray-50 disabled:opacity-30 cursor-pointer"
                                >
                                    Next
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Technical Diff Modal -->
            <div v-if="selectedLogForDiff" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" @click.self="selectedLogForDiff = null">
                <div class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl overflow-hidden flex flex-col max-h-[90vh] animate-in fade-in zoom-in duration-300">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-teal-600 flex items-center justify-center text-white shadow-lg shadow-teal-200">
                                <i class="pi pi-code text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-black text-gray-800 uppercase tracking-tight">Technical Data Analysis</h2>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">
                                    Log ID: {{ selectedLogForDiff.id }} • {{ formatDate(selectedLogForDiff.created_at) }}
                                </p>
                            </div>
                        </div>
                        <button @click="selectedLogForDiff = null" class="p-2 hover:bg-gray-100 rounded-full text-gray-400 transition-colors cursor-pointer">
                            <i class="pi pi-times"></i>
                        </button>
                    </div>

                    <div class="p-8 overflow-y-auto bg-slate-50">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Old Data -->
                            <div class="space-y-4">
                                <h3 class="text-xs font-black text-rose-500 uppercase tracking-widest flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span> Previous State (Old Data)
                                </h3>
                                <div class="bg-gray-900 rounded-2xl p-6 text-emerald-400 font-mono text-xs overflow-auto shadow-inner border border-gray-800 max-h-[50vh]">
                                    <pre v-if="selectedLogForDiff.old_data">{{ JSON.stringify(selectedLogForDiff.old_data, null, 4) }}</pre>
                                    <div v-else class="text-gray-600 italic text-[11px] leading-relaxed">No previous state recorded (Initial action).</div>
                                </div>
                            </div>

                            <!-- New Data -->
                            <div class="space-y-4">
                                <h3 class="text-xs font-black text-emerald-500 uppercase tracking-widest flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Updated State (New Data)
                                </h3>
                                <div class="bg-gray-900 rounded-2xl p-6 text-teal-400 font-mono text-xs overflow-auto shadow-inner border border-gray-800 max-h-[50vh]">
                                    <pre v-if="selectedLogForDiff.new_data">{{ JSON.stringify(selectedLogForDiff.new_data, null, 4) }}</pre>
                                    <div v-else class="text-gray-600 italic text-[11px] leading-relaxed">No updated state recorded.</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 p-6 bg-white border border-gray-200 rounded-2xl shadow-sm">
                            <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Action Context</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                                <div>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase">Performer</p>
                                    <p class="text-sm font-black text-gray-800">{{ selectedLogForDiff.user?.name || 'System' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase">IP Address</p>
                                    <p class="text-sm font-mono text-gray-600">{{ selectedLogForDiff.ip_address || 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase">Device</p>
                                    <p class="text-sm font-bold text-teal-600">{{ selectedLogForDiff.device || 'Unknown PC' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase">Location</p>
                                    <p class="text-sm font-bold text-emerald-600">{{ selectedLogForDiff.location || 'PH (Local)' }}</p>
                                </div>
                                <div class="col-span-2 md:col-span-4 border-t border-gray-50 pt-4 mt-2">
                                    <p class="text-[10px] font-bold text-gray-400 uppercase">Description</p>
                                    <p class="text-sm text-gray-600 font-medium">{{ selectedLogForDiff.description }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </MainLayout>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import { storeToRefs } from 'pinia';
import MainLayout from '../layouts/MainLayout.vue';
import axios from 'axios';

const authStore = useAuthStore();
const { user } = storeToRefs(authStore);

const logs = ref([]);
const loading = ref(true);
const page = ref(1);
const lastPage = ref(1);
const total = ref(0);
const selectedLogForDiff = ref(null);

const showFilters = ref(true);
const filters = ref({
    search: '',
    module: '',
    action: '',
    date_from: '',
    date_to: ''
});

const modulesList = ['AUTHENTICATION', 'LEAVES', 'USERS', 'EMPLOYEES', 'DEPARTMENTS', 'INVENTORY', 'NOTIFICATIONS', 'CALENDAR', 'SYSTEM'];
const actionsList = ['LOGIN (PORTAL)', 'UPDATED', 'CREATED', 'DELETED', 'ARCHIVED', 'EXPORTED', 'APPROVED', 'REJECTED', 'CANCELLED', 'LOGOUT', 'EXPIRED'];

let debounceTimer = null;
const debounceFetch = () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        fetchLogs(1);
    }, 400);
};

const resetFilters = () => {
    filters.value = {
        search: '',
        module: '',
        action: '',
        date_from: '',
        date_to: ''
    };
    fetchLogs(1);
};

const viewTechnicalDetails = (log) => {
    selectedLogForDiff.value = log;
};

const fetchLogs = async (p = 1) => {
    loading.value = true;
    page.value = p;
    try {
        const params = new URLSearchParams();
        params.append('page', p);
        if (filters.value.search) params.append('search', filters.value.search);
        if (filters.value.module) params.append('module', filters.value.module);
        if (filters.value.action) params.append('action', filters.value.action);
        if (filters.value.date_from) params.append('date_from', filters.value.date_from);
        if (filters.value.date_to) params.append('date_to', filters.value.date_to);

        const response = await axios.get(`/api/system-logs?${params.toString()}`);
        logs.value = response.data.data;
        lastPage.value = response.data.last_page;
        total.value = response.data.total;
    } catch (error) {
        console.error('Failed to fetch logs');
    } finally {
        loading.value = false;
    }
};

const formatDate = (iso) => {
    return new Date(iso).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
};

const getInitials = (name) => {
    if (!name) return '??';
    const parts = name.trim().split(' ').filter(p => p.length > 0);
    return parts.map(part => part.charAt(0)).join('').toUpperCase().slice(0, 2);
};

const getActionClass = (action) => {
    if (!action) return 'bg-gray-50 text-gray-700 border-gray-100';
    const a = action.toLowerCase();
    
    if (a.includes('approved')) return 'bg-emerald-50 text-emerald-700 border-emerald-100';
    if (a.includes('rejected')) return 'bg-rose-50 text-rose-700 border-rose-100';
    if (a.includes('failed')) return 'bg-rose-50 text-rose-700 border-rose-100';
    if (a.includes('cancelled')) return 'bg-slate-50 text-slate-700 border-slate-100';
    if (a.includes('logout')) return 'bg-slate-50 text-slate-700 border-slate-100 text-[9px] px-1';
    if (a.includes('login')) return 'bg-teal-50 text-teal-700 border-teal-100';
    if (a.includes('expired')) return 'bg-orange-50 text-orange-700 border-orange-100';
    
    return 'bg-amber-50 text-amber-700 border-amber-100';
};

const getDeviceIcon = (device) => {
    if (!device) return 'pi-desktop';
    const d = device.toLowerCase();
    if (d.includes('phone') || d.includes('mobile')) return 'pi-mobile';
    if (d.includes('tablet')) return 'pi-tablet';
    if (d.includes('mac') || d.includes('apple')) return 'pi-apple';
    return 'pi-desktop';
};

onMounted(() => {
    authStore.fetchUser();
    fetchLogs();
});
</script>
