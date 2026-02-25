<template>
    <div v-if="user" class="min-h-screen bg-white">
        <MainLayout :user="user">
            <!-- Windows 11 Explorer Surface - Light Mode -->
            <div class="p-8 md:p-16">
                <div class="max-w-7xl mx-auto space-y-12">
                    
                    <!-- Explorer Header -->
                    <div class="flex items-center justify-between border-b-2 border-gray-100 pb-5">
                        <div class="space-y-4">
                            <div class="flex items-center gap-4">
                                <button @click="resetNavigation" class="text-gray-400 hover:text-black transition-colors text-base font-bold uppercase tracking-[0.2em]">Archive Registry</button>
                                <i v-if="selectedYear" class="pi pi-chevron-right text-xs text-gray-300"></i>
                                <button v-if="selectedYear" @click="selectedMonth = null" class="text-gray-400 hover:text-black transition-colors text-base font-bold uppercase tracking-[0.2em]">{{ selectedYear.year }}</button>
                                <i v-if="selectedMonth" class="pi pi-chevron-right text-xs text-gray-300"></i>
                                <span v-if="selectedMonth" class="text-teal-600 text-base font-black uppercase tracking-[0.2em]">{{ selectedMonth.month_name }}</span>
                            </div>
                            <h1 class="text-4xl font-black text-black tracking-tighter flex items-center gap-4">
                                {{ currentViewTitle }}
                            </h1>
                        </div>
                        
                        <!-- Search Bar -->
                        <div v-if="selectedMonth" class="relative group">
                            <input 
                                v-model="searchQuery" 
                                type="text" 
                                placeholder="Search Records..." 
                                class="w-96 bg-gray-50 border-2 border-gray-100 rounded-2xl px-14 py-4 text-lg text-black placeholder-gray-400 focus:outline-none focus:border-teal-500/50 transition-all shadow-sm"
                            >
                            <i class="pi pi-search absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 text-xl"></i>
                        </div>
                    </div>

                    <!-- Explorer Grid Space -->
                    <div class="min-h-[600px] relative">
                        <!-- Loading -->
                        <div v-if="loading" class="absolute inset-0 flex items-center justify-center z-10 bg-white/80 backdrop-blur-sm rounded-3xl">
                            <div class="flex flex-col items-center gap-6">
                                <div class="w-20 h-20 border-4 border-teal-500/20 border-t-teal-500 rounded-full animate-spin"></div>
                                <p class="text-sm font-black uppercase tracking-[0.3em] text-teal-600">Accessing Archive</p>
                            </div>
                        </div>

                        <!-- 1. Year/Month Folder Grid -->
                        <div v-if="!selectedMonth" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-y-20 gap-x-12 pb-32">
                            <!-- Back Button -->
                            <div 
                                v-if="selectedYear && !selectedMonth"
                                @click="selectedYear = null"
                                class="flex flex-col items-center group cursor-pointer"
                            >
                                <div class="w-full aspect-square relative flex items-center justify-center mb-6 transition-transform group-hover:-translate-y-2 group-active:scale-95">
                                    <div class="w-32 h-32 rounded-[40px] bg-gray-50 border-2 border-gray-100 flex items-center justify-center text-gray-400 group-hover:bg-gray-100 group-hover:text-black shadow-sm transition-all">
                                        <i class="pi pi-arrow-left text-4xl"></i>
                                    </div>
                                </div>
                                <p class="text-sm font-black text-gray-500 uppercase tracking-widest text-center">Back to Index</p>
                            </div>

                            <!-- Folders -->
                            <div 
                                v-for="item in displayFolders" 
                                :key="item.id"
                                @click="item.action()"
                                class="flex flex-col items-center group cursor-pointer"
                            >
                                <div class="w-full aspect-square relative mb-8 transition-transform group-hover:-translate-y-3 group-active:scale-95 flex items-center justify-center">
                                    <div class="win11-folder-container relative w-36 h-28">
                                        <!-- Paper -->
                                        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 w-16 h-20 bg-white rounded-md shadow-md opacity-90 transition-transform group-hover:-translate-y-6 z-0 border border-gray-100">
                                            <div class="p-3 space-y-2 mt-4">
                                                <div class="h-[2px] w-full bg-gray-100"></div>
                                                <div class="h-[2px] w-4/5 bg-gray-100"></div>
                                                <div class="h-[2px] w-full bg-gray-100"></div>
                                            </div>
                                        </div>
                                        <!-- Back flap -->
                                        <div class="absolute inset-0 bg-gradient-to-br from-[#ffda5c] to-[#f4b400] rounded-2xl shadow-xl z-10 folder-shadow"></div>
                                        <!-- Top Tab -->
                                        <div class="absolute -top-3 left-0 w-16 h-8 bg-[#ffda5c] rounded-t-xl z-10 transition-colors group-hover:bg-[#ffe380]"></div>
                                        <!-- Front flap -->
                                        <div class="absolute bottom-0 left-0 right-0 h-20 bg-gradient-to-t from-[#ffb000] to-[#ffda5c] rounded-b-2xl rounded-tr-xl z-20 border-t border-white/40 shadow-inner"></div>
                                    </div>
                                </div>
                                
                                <p class="text-xl font-black text-black uppercase tracking-tight text-center truncate w-full group-hover:text-teal-600 transition-colors">
                                    {{ item.label }}
                                </p>
                                <p class="text-sm font-bold text-gray-400 uppercase tracking-widest text-center mt-2">
                                    {{ item.sub }} records captured
                                </p>
                            </div>
                        </div>

                        <!-- 2. Detailed File View (Windows 11 List View Style) -->
                        <div v-else class="space-y-10 pb-32">
                            <div class="bg-white rounded-[40px] border-2 border-gray-100 overflow-hidden shadow-2xl shadow-gray-200/50">
                                <table class="w-full text-left">
                                    <thead class="bg-gray-50 border-b-2 border-gray-100">
                                        <tr>
                                            <th class="py-6 px-10 text-xs font-black text-gray-400 uppercase tracking-[0.3em]">Official Document / Subject</th>
                                            <th class="py-6 px-10 text-xs font-black text-gray-400 uppercase tracking-[0.3em]">Classification</th>
                                            <th class="py-6 px-10 text-xs font-black text-gray-400 uppercase tracking-[0.3em]">Interval</th>
                                            <th class="py-6 px-10 text-xs font-black text-gray-400 uppercase tracking-[0.3em] text-right">Operations</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y-2 divide-gray-50">
                                        <tr v-for="request in requests" :key="request.id" class="hover:bg-gray-50 transition-all group">
                                            <td class="py-8 px-10">
                                                <div class="flex items-center gap-6">
                                                    <div class="w-12 h-16 bg-gray-50 border-2 border-gray-100 rounded-lg flex flex-col p-2.5 shadow-sm group-hover:bg-white transition-colors">
                                                        <div class="h-[2px] w-full bg-gray-200 mb-2"></div>
                                                        <div class="h-[2px] w-3/4 bg-gray-200 mb-2"></div>
                                                        <div class="h-[2px] w-full bg-gray-200"></div>
                                                    </div>
                                                    <div>
                                                        <p class="text-xl font-black text-black leading-none mb-2 transition-colors">
                                                            {{ request.user?.name || (request.employee?.last_name + ', ' + request.employee?.first_name) }}
                                                        </p>
                                                        <p class="text-xs font-bold text-gray-400 uppercase tracking-[0.2em]">ID-{{ request.user?.id_number || request.employee?.employee_id }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-8 px-10">
                                                <div class="flex flex-col gap-1.5">
                                                    <span class="text-base font-black text-gray-700">{{ request.leave_type }}</span>
                                                    <span class="text-xs text-teal-600 font-black uppercase tracking-tighter">{{ request.request_type }} CATEGORY</span>
                                                </div>
                                            </td>
                                            <td class="py-8 px-10">
                                                <div class="flex flex-col gap-1.5">
                                                    <span class="text-base font-bold text-gray-500 tabular-nums">{{ formatDate(request.from_date) }}</span>
                                                    <span class="text-xs font-black text-gray-400 uppercase tracking-widest">{{ request.days_taken }} Day Count</span>
                                                </div>
                                            </td>
                                            <td class="py-8 px-10 text-right">
                                                <div class="flex items-center justify-end gap-6">
                                                    <button @click="viewRecord(request)" class="p-3.5 bg-gray-50 hover:bg-black hover:text-white rounded-2xl text-gray-400 transition-all border-2 border-gray-100 shadow-sm">
                                                        <i class="pi pi-eye text-xl"></i>
                                                    </button>
                                                    <button @click="unarchiveRequest(request.id)" class="text-xs font-black uppercase tracking-[0.2em] px-8 py-3.5 bg-black text-white rounded-2xl hover:bg-teal-600 transition-all shadow-xl active:scale-95">
                                                        Restore
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!-- Explorer-like footer -->
                            <div class="flex justify-between items-center px-6 py-4 bg-white/5 rounded-2xl border border-white/10 shadow-xl">
                                <span class="text-xs font-black text-slate-500 uppercase tracking-[0.2em]">Archive Metadata | Total Items: {{ totalRecords }}</span>
                                <div class="flex gap-4">
                                    <button @click="page--" :disabled="page === 1" class="p-2 disabled:opacity-20 hover:text-teal-400 text-slate-400 transition-colors"><i class="pi pi-chevron-left text-xs"></i></button>
                                    <button @click="page++" :disabled="page >= lastPage" class="p-2 disabled:opacity-20 hover:text-teal-400 text-slate-400 transition-colors"><i class="pi pi-chevron-right text-xs"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Static Record View Modal (Explorer Sheet) -->
            <div v-if="selectedRecord" class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-black/90 backdrop-blur-xl animate-in fade-in duration-300">
                <div class="bg-[#2a2a2a] rounded-[32px] shadow-2xl w-full max-w-2xl overflow-hidden border border-white/10 shadow-[0_0_100px_rgba(0,0,0,0.8)]">
                    <div class="p-10 border-b border-white/5 flex justify-between items-center bg-white/[0.02]">
                        <div class="flex items-center gap-6">
                            <div class="w-16 h-20 bg-white/[0.1] border border-white/10 rounded-lg relative p-4 flex flex-col gap-3">
                                <div class="absolute top-0 right-0 w-5 h-5 bg-[#2a2a2a] rotate-45 translate-x-1/2 -translate-y-1/2 border-l border-b border-white/10"></div>
                                <div class="h-[2px] w-full bg-white/50"></div>
                                <div class="h-[2px] w-full bg-white/50"></div>
                                <div class="h-[2px] w-3/4 bg-white/50"></div>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-white tracking-tight">Record Properties</h3>
                                <p class="text-[11px] text-slate-500 font-bold uppercase tracking-[0.3em] mt-2">CLASSIFICATION: SECURE ARCHIVE</p>
                            </div>
                        </div>
                        <button @click="selectedRecord = null" class="w-12 h-12 rounded-full hover:bg-white/10 flex items-center justify-center text-slate-400 hover:text-white transition-all"><i class="pi pi-times text-xl"></i></button>
                    </div>

                    <div class="p-12 space-y-10 bg-[#222222]">
                        <div class="grid grid-cols-2 gap-12">
                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Legal Subject</label>
                                <p class="text-xl font-black text-white uppercase tracking-tight">{{ selectedRecord.user?.name || (selectedRecord.employee?.last_name + ', ' + selectedRecord.employee?.first_name) }}</p>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Document Status</label>
                                <p class="text-xl font-black text-teal-400 uppercase tracking-widest">CAPTURED IN REGISTRY</p>
                            </div>
                        </div>

                        <div class="bg-black/30 p-10 rounded-3xl border border-white/5 space-y-8 shadow-inner">
                            <div class="grid grid-cols-2 gap-10">
                                <div class="space-y-1.5">
                                    <label class="text-[9px] font-black text-slate-600 uppercase tracking-[0.4em]">Leave Category</label>
                                    <p class="text-base font-bold text-slate-200">{{ selectedRecord.leave_type }}</p>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[9px] font-black text-slate-600 uppercase tracking-[0.4em]">Temporal Scope</label>
                                    <p class="text-base font-bold text-slate-200 tabular-nums">{{ formatDate(selectedRecord.from_date) }} - {{ formatDate(selectedRecord.to_date) }}</p>
                                </div>
                            </div>
                            <div class="pt-6 border-t border-white/5">
                                <label class="text-[9px] font-black text-slate-600 uppercase tracking-[0.4em] mb-3 block">Audit Narrative</label>
                                <p class="text-sm text-slate-400 leading-relaxed italic border-l-4 border-teal-500/40 pl-6 py-2">
                                    "{{ selectedRecord.reason || 'No specific metadata captured for this historical interval.' }}"
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="p-10 bg-[#2a2a2a] border-t border-white/5 flex justify-end">
                        <button @click="selectedRecord = null" class="px-10 py-4 bg-white/10 hover:bg-teal-600 text-white rounded-xl text-xs font-black uppercase tracking-[0.2em] transition-all shadow-xl">
                            Dismiss Properties
                        </button>
                    </div>
                </div>
            </div>
        </MainLayout>
    </div>
</template>

<style scoped>
.folder-shadow {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.4), 0 4px 6px -2px rgba(0, 0, 0, 0.2);
}

/* Custom animation for explorer feel */
.animate-in {
    animation-timing-function: cubic-bezier(0, 0, 0.2, 1);
}

@keyframes folder-in {
    from {
        opacity: 0;
        transform: scale(0.9) translateY(10px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.display-folders-item {
    animation: folder-in 0.3s ease-out forwards;
}
</style>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../../stores/auth';
import { storeToRefs } from 'pinia';
import MainLayout from '../../layouts/MainLayout.vue';

const authStore = useAuthStore();
const { user } = storeToRefs(authStore);

// Navigation State
const archiveIndex = ref([]);
const selectedYear = ref(null);
const selectedMonth = ref(null);

// Records State
const requests = ref([]);
const loading = ref(false);
const page = ref(1);
const lastPage = ref(1);
const totalRecords = ref(0);
const searchQuery = ref('');
const selectedRecord = ref(null);

const currentViewTitle = computed(() => {
    if (selectedMonth.value) return selectedMonth.value.month_name;
    if (selectedYear.value) return selectedYear.value.year.toString();
    return 'Document Registry';
});

const displayFolders = computed(() => {
    if (selectedYear.value && !selectedMonth.value) {
        return selectedYear.value.months.map(m => ({
            id: m.month,
            label: m.month_name,
            sub: m.count,
            action: () => selectMonth(m)
        }));
    }
    return archiveIndex.value.map(y => ({
        id: y.year,
        label: y.year.toString(),
        sub: y.total,
        action: () => selectYear(y)
    }));
});

const fetchArchiveIndex = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/leave-requests/archive-index');
        archiveIndex.value = response.data;
    } catch (e) {
        console.error("Archive index fetch failed", e);
    } finally {
        loading.value = false;
    }
};

const selectYear = (yearData) => {
    selectedYear.value = yearData;
    selectedMonth.value = null;
    page.value = 1;
};

const selectMonth = (monthData) => {
    selectedMonth.value = monthData;
    page.value = 1;
    fetchMonthlyRequests();
};

const fetchMonthlyRequests = async () => {
    if (!selectedYear.value || !selectedMonth.value) return;
    
    loading.value = true;
    try {
        const params = {
            page: page.value,
            archived: true,
            year: selectedYear.value.year,
            month: selectedMonth.value.month,
            search: searchQuery.value
        };
        const response = await axios.get('/api/leave-requests', { params });
        requests.value = response.data.data;
        lastPage.value = response.data.last_page;
        totalRecords.value = response.data.total;
    } catch (e) {
        console.error("Monthly fetch failed", e);
    } finally {
        loading.value = false;
    }
};

const resetNavigation = () => {
    selectedYear.value = null;
    selectedMonth.value = null;
    searchQuery.value = '';
    fetchArchiveIndex();
};

const unarchiveRequest = async (id) => {
    if (!confirm('Restore this record to the active list?')) return;
    try {
        await axios.post(`/api/leave-requests/${id}/unarchive`);
        requests.value = requests.value.filter(r => r.id !== id);
        totalRecords.value--;
        alert('Record restored successfully.');
        if (requests.value.length === 0) fetchArchiveIndex();
    } catch (e) {
        alert('Restore failed.');
    }
};

const viewRecord = (req) => {
    selectedRecord.value = req;
};

const getInitials = (name) => name ? name.match(/(\b\S)?/g).join("").match(/(^\S|\S$)?/g).join("").toUpperCase() : 'U';
const formatDate = (d) => new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

let debounceTimer = null;
watch(searchQuery, () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        if (selectedMonth.value) {
            page.value = 1;
            fetchMonthlyRequests();
        }
    }, 400);
});

watch(page, () => {
    if (selectedMonth.value) {
        fetchMonthlyRequests();
    }
});

onMounted(() => {
    authStore.fetchUser();
    fetchArchiveIndex();
});
</script>
