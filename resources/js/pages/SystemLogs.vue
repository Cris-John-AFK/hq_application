<template>
    <div v-if="user">
        <MainLayout :user="user">
            <div class="max-w-7xl mx-auto space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Action Audit Trail</h3>
                            <p class="text-sm text-gray-500">Historical record of all administrative leave actions and decisions.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="fetchLogs" class="p-2 text-gray-400 hover:text-teal-600 transition-colors">
                                <i class="pi pi-refresh"></i>
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs md:text-sm">
                            <thead>
                                <tr class="bg-gray-50 text-gray-500 text-[10px] uppercase tracking-widest font-black">
                                    <th class="px-6 py-4">Timestamp</th>
                                    <th class="px-6 py-4">User</th>
                                    <th class="px-6 py-4">Module</th>
                                    <th class="px-6 py-4">Action</th>
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
                                            <span class="font-bold text-gray-800">{{ log.user?.name || 'System' }}</span>
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
                                     <td class="px-6 py-4 max-w-md">
                                         <div class="flex flex-col gap-1">
                                             <p class="text-gray-700 font-bold leading-tight">
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
                            <div class="flex flex-wrap gap-6">
                                <div>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase">Performer</p>
                                    <p class="text-sm font-black text-gray-800">{{ selectedLogForDiff.user?.name || 'System' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase">IP Address</p>
                                    <p class="text-sm font-mono text-gray-600">{{ selectedLogForDiff.ip_address || 'N/A' }}</p>
                                </div>
                                <div class="flex-1">
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

const viewTechnicalDetails = (log) => {
    selectedLogForDiff.value = log;
};

const fetchLogs = async (p = 1) => {
    loading.value = true;
    page.value = p;
    try {
        const response = await axios.get(`/api/system-logs?page=${p}`);
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
    return name ? name.split(' ').map(n => n[0]).join('').slice(0, 2) : '??';
};

const getActionClass = (action) => {
    if (!action) return 'bg-gray-50 text-gray-700 border-gray-100';
    switch (action.toLowerCase()) {
        case 'approved': return 'bg-emerald-50 text-emerald-700 border-emerald-100';
        case 'rejected': return 'bg-rose-50 text-rose-700 border-rose-100';
        case 'cancelled': return 'bg-slate-50 text-slate-700 border-slate-100';
        default: return 'bg-amber-50 text-amber-700 border-amber-100';
    }
};

onMounted(() => {
    authStore.fetchUser();
    fetchLogs();
});
</script>
