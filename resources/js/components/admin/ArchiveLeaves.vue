<template>
    <div v-if="user">
        <MainLayout :user="user">
            <div class="p-6 max-w-7xl mx-auto">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <router-link to="/manage-leaves" class="text-teal-600 hover:text-teal-700 font-bold text-sm flex items-center gap-1">
                                <i class="pi pi-arrow-left text-xs"></i>
                                Back to Active Requests
                            </router-link>
                        </div>
                        <h1 class="text-2xl font-black text-gray-900 tracking-tight flex items-center gap-3">
                            <i class="pi pi-archive text-gray-400"></i>
                            Archive Registry
                        </h1>
                        <p class="text-gray-500 text-sm mt-1">Archived leave data for permanent record tracing and compliance audits.</p>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-6 flex flex-col md:flex-row gap-4 items-center">
                    <div class="relative flex-1 group">
                        <i class="pi pi-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-teal-600 transition-colors"></i>
                        <input 
                            v-model="searchQuery" 
                            type="text" 
                            placeholder="Search archived name or employee id..." 
                            class="w-full pl-12 pr-6 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all font-medium text-gray-700 shadow-inner"
                        >
                    </div>
                    <select v-model="filters.status" class="px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm font-bold text-gray-700 focus:ring-2 focus:ring-teal-500/10 outline-none cursor-pointer">
                        <option value="">All Statuses</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>

                <!-- Archive Table -->
                <div class="bg-white rounded-3xl border border-gray-100 shadow-xl shadow-gray-200/50 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse whitespace-nowrap">
                            <thead>
                                <tr class="bg-gray-50/50">
                                    <th class="py-4 px-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Archived Employee</th>
                                    <th class="py-4 px-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Type / Category</th>
                                    <th class="py-4 px-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Original Dates</th>
                                    <th class="py-4 px-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Archived On</th>
                                    <th class="py-4 px-6 text-right pr-8 text-[10px] font-black text-gray-400 uppercase tracking-widest">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-if="loading" class="animate-pulse">
                                    <td colspan="5" class="py-12 text-center text-gray-400">Loading archive records...</td>
                                </tr>
                                <tr v-else-if="requests.length === 0">
                                    <td colspan="5" class="py-20 text-center text-gray-400">
                                        <div class="flex flex-col items-center gap-3">
                                            <i class="pi pi-box text-5xl opacity-20"></i>
                                            <p class="font-bold">No archived records found.</p>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-for="request in requests" :key="request.id" class="hover:bg-gray-50/50 transition-colors group">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center font-bold text-gray-500 text-xs border border-gray-200">
                                                {{ getInitials(request.user?.name || (request.employee?.first_name + ' ' + request.employee?.last_name)) }}
                                            </div>
                                            <div>
                                                <p class="font-black text-gray-900 text-sm leading-none mb-1">{{ request.user?.name || (request.employee?.last_name + ', ' + request.employee?.first_name) }}</p>
                                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tight">{{ request.user?.id_number || request.employee?.employee_id }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex flex-col gap-1">
                                            <span class="text-xs font-black text-gray-800 leading-none">{{ request.leave_type }}</span>
                                            <span class="text-[9px] font-bold text-teal-600 bg-teal-50 px-1.5 py-0.5 rounded-md border border-teal-100 w-fit">{{ request.request_type }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="text-xs font-bold text-gray-600">
                                            <span>{{ formatDate(request.from_date) }}</span>
                                            <span v-if="request.to_date && request.to_date !== request.from_date" class="text-gray-400 ml-1">→ {{ formatDate(request.to_date) }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-2">
                                            <i class="pi pi-calendar-times text-gray-300 text-xs"></i>
                                            <span class="text-xs font-black text-gray-500">{{ request.archived_at ? formatDateTime(request.archived_at) : 'Manual Migration' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-right pr-8">
                                        <div class="flex items-center justify-end gap-2">
                                            <button 
                                                @click="unarchiveRequest(request.id)"
                                                class="px-4 py-2 bg-white border border-gray-200 text-gray-700 hover:text-teal-600 hover:border-teal-200 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm cursor-pointer"
                                            >
                                                <i class="pi pi-refresh mr-1 text-[8px]"></i>
                                                Restore
                                            </button>
                                            <button 
                                                @click="viewRecord(request)"
                                                class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 hover:bg-teal-600 hover:text-white transition-all flex items-center justify-center cursor-pointer"
                                                title="View Static Record"
                                            >
                                                <i class="pi pi-eye text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="p-6 bg-gray-50/50 border-t border-gray-100 flex justify-between items-center">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Audit Summary: {{ totalRecords }} Records</span>
                        <div class="flex gap-2">
                            <button 
                                @click="page--" 
                                :disabled="page === 1"
                                class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-gray-400 hover:text-teal-600 disabled:opacity-30 cursor-pointer shadow-sm transition-all"
                            >
                                <i class="pi pi-chevron-left text-xs"></i>
                            </button>
                            <button 
                                @click="page++" 
                                :disabled="page >= lastPage"
                                class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-gray-400 hover:text-teal-600 disabled:opacity-30 cursor-pointer shadow-sm transition-all"
                            >
                                <i class="pi pi-chevron-right text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Static Record View Modal -->
                <div v-if="selectedRecord" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-md animate-in fade-in duration-300">
                    <div class="bg-white rounded-[32px] shadow-2xl w-full max-w-2xl overflow-hidden border border-white/20">
                        <div class="p-8 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-2xl bg-teal-600 flex items-center justify-center text-white shadow-xl shadow-teal-500/20">
                                    <i class="pi pi-file text-2xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-black text-gray-900 leading-tight">Archived Document</h3>
                                    <p class="text-[10px] text-gray-400 font-extrabold uppercase tracking-[0.2em]">Record ID: HQR-{{ selectedRecord.id }}</p>
                                </div>
                            </div>
                            <button @click="selectedRecord = null" class="w-10 h-10 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-400 cursor-pointer transition-colors">
                                <i class="pi pi-times"></i>
                            </button>
                        </div>

                        <div class="p-8 space-y-6">
                            <div class="grid grid-cols-2 gap-8">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Subject</label>
                                    <p class="font-black text-gray-800">{{ selectedRecord.user?.name || (selectedRecord.employee?.last_name + ', ' + selectedRecord.employee?.first_name) }}</p>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Status at Archival</label>
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-green-100 text-green-700 border border-green-200">
                                        {{ selectedRecord.status }}
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-8 py-6 border-y border-gray-100">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Requested Leave</label>
                                    <p class="text-sm font-bold text-gray-700">{{ selectedRecord.leave_type }}</p>
                                    <p class="text-[10px] font-medium text-gray-400 mt-1">{{ selectedRecord.request_type }} ({{ selectedRecord.category || 'Standard' }})</p>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Duration</label>
                                    <p class="text-sm font-bold text-gray-700">{{ selectedRecord.days_taken }} Day(s)</p>
                                    <p class="text-[10px] font-medium text-gray-400 mt-1">{{ formatDate(selectedRecord.from_date) }} - {{ formatDate(selectedRecord.to_date) }}</p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Employee's Reason</label>
                                <div class="bg-gray-50 p-4 rounded-2xl italic text-gray-600 text-sm border border-gray-100">
                                    "{{ selectedRecord.reason || 'No reason provided.' }}"
                                </div>
                            </div>

                            <div v-if="selectedRecord.admin_remarks">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Admin Remarks / Justification</label>
                                <div class="bg-teal-50/50 p-4 rounded-2xl text-teal-800 text-sm border border-teal-100/50">
                                    {{ selectedRecord.admin_remarks }}
                                </div>
                            </div>
                        </div>

                        <div class="p-8 bg-gray-50 border-t border-gray-100 flex justify-end">
                            <button @click="selectedRecord = null" class="px-8 py-3 bg-white border border-gray-200 rounded-xl font-black text-xs uppercase tracking-widest text-gray-700 hover:bg-gray-100 transition-all cursor-pointer">
                                Close Document
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </MainLayout>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../../stores/auth';
import { storeToRefs } from 'pinia';
import MainLayout from '../../layouts/MainLayout.vue';

const authStore = useAuthStore();
const { user } = storeToRefs(authStore);

const requests = ref([]);
const loading = ref(true);
const page = ref(1);
const lastPage = ref(1);
const totalRecords = ref(0);
const searchQuery = ref('');
const selectedRecord = ref(null);

const filters = ref({
    status: ''
});

const fetchArchived = async () => {
    loading.value = true;
    try {
        const params = {
            page: page.value,
            archived: true,
            status: filters.value.status,
            search: searchQuery.value
        };
        const response = await axios.get('/api/leave-requests', { params });
        requests.value = response.data.data;
        lastPage.value = response.data.last_page;
        totalRecords.value = response.data.total;
    } catch (e) {
        console.error("Archive fetch failed", e);
    } finally {
        loading.value = false;
    }
};

const unarchiveRequest = async (id) => {
    if (!confirm('Restore this record to the active list?')) return;
    try {
        await axios.post(`/api/leave-requests/${id}/unarchive`);
        requests.value = requests.value.filter(r => r.id !== id);
        totalRecords.value--;
        alert('Record restored successfully.');
    } catch (e) {
        alert('Restore failed.');
    }
};

const viewRecord = (req) => {
    selectedRecord.value = req;
};

const getInitials = (name) => name ? name.match(/(\b\S)?/g).join("").match(/(^\S|\S$)?/g).join("").toUpperCase() : 'U';
const formatDate = (d) => new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
const formatDateTime = (d) => new Date(d).toLocaleString('en-US', { 
    month: 'short', 
    day: 'numeric', 
    year: 'numeric',
    hour: 'numeric',
    minute: '2-digit'
});

let debounceTimer = null;
watch([searchQuery, filters], () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        page.value = 1;
        fetchArchived();
    }, 400);
}, { deep: true });

watch(page, () => fetchArchived());

onMounted(() => {
    authStore.fetchUser();
    fetchArchived();
});
</script>
