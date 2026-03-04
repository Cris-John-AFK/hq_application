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
                                
                                <template v-if="selectedCategory">
                                    <i class="pi pi-chevron-right text-xs text-gray-300"></i>
                                    <button @click="backToCategoryRoot" class="text-gray-400 hover:text-black transition-colors text-base font-bold uppercase tracking-[0.2em]">
                                        {{ selectedCategory === 'leaves' ? 'Leave Requests' : 'Employee Records' }}
                                    </button>
                                </template>

                                <template v-if="selectedYear">
                                    <i class="pi pi-chevron-right text-xs text-gray-300"></i>
                                    <button @click="selectedMonth = null" class="text-gray-400 hover:text-black transition-colors text-base font-bold uppercase tracking-[0.2em]">{{ selectedYear.year }}</button>
                                </template>

                                <i v-if="selectedMonth" class="pi pi-chevron-right text-xs text-gray-300"></i>
                                <span v-if="selectedMonth" class="text-teal-600 text-base font-black uppercase tracking-[0.2em]">{{ selectedMonth.month_name }}</span>
                            </div>
                            <h1 class="text-4xl font-black text-black tracking-tighter flex items-center gap-4">
                                {{ currentViewTitle }}
                            </h1>
                        </div>
                        
                        <!-- Search Bar -->
                        <div v-if="isListView" class="relative group">
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

                        <!-- 1. Folder Grid View -->
                        <div v-if="!isListView" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-y-20 gap-x-12 pb-32">
                            <!-- Back Button -->
                            <div 
                                v-if="selectedCategory"
                                @click="handleBack"
                                class="flex flex-col items-center group cursor-pointer"
                            >
                                <div class="w-full aspect-square relative flex items-center justify-center mb-6 transition-transform group-hover:-translate-y-2 group-active:scale-95">
                                    <div class="w-32 h-32 rounded-[40px] bg-gray-50 border-2 border-gray-100 flex items-center justify-center text-gray-400 group-hover:bg-gray-100 group-hover:text-black shadow-sm transition-all">
                                        <i class="pi pi-arrow-left text-4xl"></i>
                                    </div>
                                </div>
                                <p class="text-sm font-black text-gray-500 uppercase tracking-widest text-center">Go Back</p>
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
                                        <!-- New Badge -->
                                        <div v-if="item.new_count > 0 && !isViewed(item.view_id)" class="absolute -top-4 -right-4 bg-red-600 text-white text-[11px] font-black px-2.5 py-1 rounded-full z-30 shadow-xl border-2 border-white flex items-center gap-1">
                                            <span class="animate-pulse">NEW</span>
                                            <span class="bg-white/20 px-1 rounded">{{ item.new_count }}</span>
                                        </div>

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

                        <!-- 2. Detailed List View -->
                        <div v-else class="space-y-10 pb-32">
                            <div class="bg-white rounded-[40px] border-2 border-gray-100 overflow-hidden shadow-2xl shadow-gray-200/50">
                                <table class="w-full text-left">
                                    <thead class="bg-gray-50 border-b-2 border-gray-100">
                                        <tr v-if="selectedCategory === 'leaves'">
                                            <th class="py-6 px-10 text-xs font-black text-gray-400 uppercase tracking-[0.3em]">Official Document / Subject</th>
                                            <th class="py-6 px-10 text-xs font-black text-gray-400 uppercase tracking-[0.3em]">Classification</th>
                                            <th class="py-6 px-10 text-xs font-black text-gray-400 uppercase tracking-[0.3em]">Temporal scope / Archived on</th>
                                            <th class="py-6 px-10 text-xs font-black text-gray-400 uppercase tracking-[0.3em] text-right">Operations</th>
                                        </tr>
                                        <tr v-else>
                                            <th class="py-6 px-10 text-xs font-black text-gray-400 uppercase tracking-[0.3em]">Employee Detail</th>
                                            <th class="py-6 px-10 text-xs font-black text-gray-400 uppercase tracking-[0.3em]">Organization Unit / Role</th>
                                            <th class="py-6 px-10 text-xs font-black text-gray-400 uppercase tracking-[0.3em]">Temporal Scope / Archived on</th>
                                            <th class="py-6 px-10 text-xs font-black text-gray-400 uppercase tracking-[0.3em] text-right">Operations</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y-2 divide-gray-50">
                                        <!-- Leave Records Row -->
                                        <template v-if="selectedCategory === 'leaves'">
                                            <tr 
                                                v-for="request in requests" 
                                                :key="request.id" 
                                                class="hover:bg-gray-50 transition-all group relative"
                                                :class="{ 'bg-red-50/70 border-l-4 border-red-500 animate-new-blink': isNewRecord(request.archived_at) }"
                                            >
                                                <td class="py-8 px-10">
                                                    <div class="flex items-center gap-6">
                                                        <div v-if="isNewRecord(request.archived_at)" class="absolute left-0 top-1/2 -translate-y-1/2 h-full w-1 bg-red-600"></div>
                                                        <div class="w-12 h-16 bg-gray-50 border-2 border-gray-100 rounded-lg flex flex-col p-2.5 shadow-sm group-hover:bg-white transition-colors">
                                                            <div class="h-[2px] w-full bg-gray-200 mb-2"></div>
                                                            <div class="h-[2px] w-3/4 bg-gray-200 mb-2"></div>
                                                            <div class="h-[2px] w-full bg-gray-200"></div>
                                                        </div>
                                                        <div>
                                                            <div class="flex items-center gap-3 mb-2">
                                                                <p class="text-xl font-black text-black leading-none transition-colors">
                                                                    {{ request.user?.name || (request.employee?.last_name + ', ' + request.employee?.first_name) }}
                                                                </p>
                                                                <span v-if="isNewRecord(request.archived_at)" class="px-2 py-0.5 bg-red-600 text-white text-[9px] font-black rounded uppercase tracking-widest animate-pulse">Just In</span>
                                                            </div>
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
                                                        <span class="text-base font-bold text-gray-500 tabular-nums">{{ formatDate(request.from_date) }} <i class="pi pi-arrow-right mx-1 text-[10px] text-gray-300"></i> {{ formatDate(request.to_date) }}</span>
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-[10px] font-black text-teal-600 uppercase tracking-widest">ARCHIVED: {{ formatFullDateTime(request.archived_at) }}</span>
                                                            <span class="h-1 w-1 rounded-full bg-gray-300"></span>
                                                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ request.days_taken }} DAY COUNT</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-8 px-10 text-right">
                                                    <div class="flex items-center justify-end gap-6">
                                                        <button @click="viewRecord(request)" class="p-3.5 bg-gray-50 hover:bg-black hover:text-white rounded-2xl text-gray-400 transition-all border-2 border-gray-100 shadow-sm">
                                                            <i class="pi pi-eye text-xl"></i>
                                                        </button>
                                                        <button @click="unarchiveLeave(request.id)" class="text-xs font-black uppercase tracking-[0.2em] px-8 py-3.5 bg-black text-white rounded-2xl hover:bg-teal-600 transition-all shadow-xl active:scale-95">
                                                            Restore
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>

                                        <!-- Employee Records Row -->
                                        <template v-else>
                                            <tr 
                                                v-for="emp in employees" 
                                                :key="emp.id" 
                                                class="hover:bg-gray-50 transition-all group relative"
                                                :class="{ 'bg-red-50/70 border-l-4 border-red-500 animate-new-blink': isNewRecord(emp.archived_at) }"
                                            >
                                                <td class="py-8 px-10">
                                                    <div class="flex items-center gap-6">
                                                        <div v-if="isNewRecord(emp.archived_at)" class="absolute left-0 top-1/2 -translate-y-1/2 h-full w-1 bg-red-600"></div>
                                                        <div class="w-14 h-14 rounded-2xl bg-teal-50 flex items-center justify-center text-teal-600 font-black text-xl border-2 border-teal-100 shadow-sm overflow-hidden whitespace-nowrap">
                                                            <img v-if="emp.avatar" :src="emp.avatar" class="w-full h-full object-cover">
                                                            <span v-else>{{ emp.initials }}</span>
                                                        </div>
                                                        <div>
                                                            <div class="flex items-center gap-3 mb-2">
                                                                <p class="text-xl font-black text-black leading-none transition-colors">
                                                                    {{ emp.last_name }}, {{ emp.first_name }}
                                                                </p>
                                                                <span v-if="isNewRecord(emp.archived_at)" class="px-2 py-0.5 bg-red-600 text-white text-[9px] font-black rounded uppercase tracking-widest animate-pulse">New Member</span>
                                                            </div>
                                                            <p class="text-xs font-bold text-gray-400 uppercase tracking-[0.2em]">ID-{{ emp.employee_id }}</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-8 px-10">
                                                    <div class="flex flex-col gap-1.5">
                                                        <span class="text-base font-black text-gray-700">{{ emp.department?.name }}</span>
                                                        <span class="text-xs text-teal-600 font-black uppercase tracking-tighter">{{ emp.position }}</span>
                                                    </div>
                                                </td>
                                                <td class="py-8 px-10">
                                                    <div class="flex flex-col gap-1.5">
                                                        <span class="text-base font-bold text-gray-700 tabular-nums">{{ emp.archived_at ? formatFullDateTime(emp.archived_at) : 'Historical Record' }}</span>
                                                        <span class="text-[10px] font-black text-teal-600 uppercase tracking-widest">{{ emp.employment_status }} DIGITAL SEAL</span>
                                                    </div>
                                                </td>
                                                <td class="py-8 px-10 text-right">
                                                    <div class="flex items-center justify-end gap-6">
                                                        <button @click="viewRecord(emp)" class="p-3.5 bg-gray-50 hover:bg-black hover:text-white rounded-2xl text-gray-400 transition-all border-2 border-gray-100 shadow-sm">
                                                            <i class="pi pi-eye text-xl"></i>
                                                        </button>
                                                        <button @click="unarchiveEmployee(emp.id)" class="text-xs font-black uppercase tracking-[0.2em] px-8 py-3.5 bg-black text-white rounded-2xl hover:bg-teal-600 transition-all shadow-xl active:scale-95">
                                                            Restore Employee
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
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
                                <p class="text-[11px] text-slate-500 font-bold uppercase tracking-[0.3em] mt-2">CLASSIFICATION: {{ selectedRecord.employee_id && !selectedRecord.leave_type ? 'EMPLOYEE IDENTIFIER' : 'SECURE ARCHIVE' }}</p>
                            </div>
                        </div>
                        <button @click="selectedRecord = null" class="w-12 h-12 rounded-full hover:bg-white/10 flex items-center justify-center text-slate-400 hover:text-white transition-all"><i class="pi pi-times text-xl"></i></button>
                    </div>

                    <div class="p-12 space-y-10 bg-[#222222]">
                        <div class="grid grid-cols-2 gap-12">
                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Legal Subject</label>
                                <p class="text-xl font-black text-white uppercase tracking-tight">
                                    {{ selectedRecord.employee_id && !selectedRecord.leave_type ? (selectedRecord.last_name + ', ' + selectedRecord.first_name) : (selectedRecord.user?.name || (selectedRecord.employee?.last_name + ', ' + selectedRecord.employee?.first_name)) }}
                                </p>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Document Status</label>
                                <p class="text-xl font-black text-teal-400 uppercase tracking-widest">{{ selectedRecord.employee_id && !selectedRecord.leave_type ? 'MASTERLIST ARCHIVE' : 'CAPTURED IN REGISTRY' }}</p>
                            </div>
                        </div>

                        <div class="bg-black/30 p-10 rounded-3xl border border-white/5 space-y-8 shadow-inner">
                            <template v-if="selectedRecord.employee_id && !selectedRecord.leave_type">
                                <div class="grid grid-cols-2 gap-10">
                                    <div class="space-y-1.5">
                                        <label class="text-[9px] font-black text-slate-600 uppercase tracking-[0.4em]">Organization Unit</label>
                                        <p class="text-base font-bold text-slate-200">{{ selectedRecord.department?.name }}</p>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-[9px] font-black text-slate-600 uppercase tracking-[0.4em]">Historical Role</label>
                                        <p class="text-base font-bold text-slate-200">{{ selectedRecord.position }}</p>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-[9px] font-black text-slate-600 uppercase tracking-[0.4em]">Hire Date</label>
                                        <p class="text-base font-bold text-slate-200">{{ formatDate(selectedRecord.date_hired) }}</p>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-[9px] font-black text-slate-600 uppercase tracking-[0.4em]">Termination/Archive Seal</label>
                                        <p class="text-base font-bold text-slate-200">{{ formatFullDateTime(selectedRecord.archived_at) }}</p>
                                    </div>
                                </div>
                            </template>
                            <template v-else>
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
                            </template>
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

@keyframes new-blink {
    0% { background-color: rgba(254, 242, 242, 0.7); opacity: 0.8; }
    50% { background-color: rgba(254, 242, 242, 0.3); opacity: 1; }
    100% { background-color: rgba(254, 242, 242, 0.7); opacity: 0.8; }
}

.animate-new-blink {
    animation: new-blink 1.5s infinite ease-in-out;
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
const archiveBaseStats = ref({ leaves: [], total_new_leaves: 0, employees: { count: 0, new_count: 0 } });
const selectedCategory = ref(null);
const selectedYear = ref(null);
const selectedMonth = ref(null);

// Records State
const requests = ref([]);
const employees = ref([]);
const loading = ref(false);
const page = ref(1);
const lastPage = ref(1);
const totalRecords = ref(0);
const searchQuery = ref('');
const selectedRecord = ref(null);

// Viewed State Logic
const VIEWED_KEY = 'archive_viewed_folders';
const viewedFolders = ref(JSON.parse(localStorage.getItem(VIEWED_KEY) || '[]'));

const isViewed = (id) => viewedFolders.value.includes(id);

const markViewed = (id) => {
    if (!viewedFolders.value.includes(id)) {
        viewedFolders.value.push(id);
        localStorage.setItem(VIEWED_KEY, JSON.stringify(viewedFolders.value));
    }
};

const currentViewTitle = computed(() => {
    if (selectedMonth.value) return selectedMonth.value.month_name;
    if (selectedYear.value) return selectedYear.value.year.toString();
    if (selectedCategory.value === 'employees') return 'Archived Employees';
    if (selectedCategory.value === 'leaves') return 'Historical Leaves';
    return 'Archive Registry';
});

const isListView = computed(() => {
    return (selectedCategory.value === 'leaves' && selectedMonth.value) || 
           (selectedCategory.value === 'employees');
});

const displayFolders = computed(() => {
    if (!selectedCategory.value) {
        return [
            {
                id: 'leaves',
                view_id: 'cat:leaves',
                label: 'Leave Requests',
                sub: archiveBaseStats.value.leaves.reduce((acc, y) => acc + y.total, 0),
                new_count: archiveBaseStats.value.total_new_leaves || 0,
                action: () => { 
                    markViewed('cat:leaves');
                    selectedCategory.value = 'leaves'; 
                }
            },
            {
                id: 'employees',
                view_id: 'cat:employees',
                label: 'Employee Records',
                sub: archiveBaseStats.value.employees?.count || 0,
                new_count: archiveBaseStats.value.employees?.new_count || 0,
                action: () => { 
                    markViewed('cat:employees');
                    selectedCategory.value = 'employees';
                    fetchArchivedEmployees();
                }
            }
        ];
    }

    // Stage 2: Leaves (Years)
    if (selectedCategory.value === 'leaves' && !selectedYear.value) {
        return archiveBaseStats.value.leaves.map(y => ({
            id: y.year,
            view_id: `year:leaves:${y.year}`,
            label: y.year.toString(),
            sub: y.total,
            new_count: y.new_total || 0,
            action: () => {
                markViewed(`year:leaves:${y.year}`);
                selectYear(y);
            }
        }));
    }

    // Stage 3: Leaves (Months)
    if (selectedCategory.value === 'leaves' && selectedYear.value && !selectedMonth.value) {
        return selectedYear.value.months.map(m => ({
            id: m.month,
            view_id: `month:leaves:${selectedYear.value.year}:${m.month}`,
            label: m.month_name,
            sub: m.count,
            new_count: m.new_count || 0,
            action: () => {
                markViewed(`month:leaves:${selectedYear.value.year}:${m.month}`);
                selectMonth(m);
            }
        }));
    }

    return [];
});

const fetchArchiveIndex = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/leave-requests/archive-index');
        archiveBaseStats.value = response.data;
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

const fetchArchivedEmployees = async () => {
    loading.value = true;
    try {
        const params = {
            page: page.value,
            archived: true,
            search: searchQuery.value
        };
        const response = await axios.get('/api/employees', { params });
        employees.value = response.data.data;
        lastPage.value = response.data.last_page;
        totalRecords.value = response.data.total;
    } catch (e) {
        console.error("Employee archive fetch failed", e);
    } finally {
        loading.value = false;
    }
};

const handleBack = () => {
    if (selectedMonth.value) {
        selectedMonth.value = null;
    } else if (selectedYear.value) {
        selectedYear.value = null;
    } else {
        selectedCategory.value = null;
    }
};

const backToCategoryRoot = () => {
    selectedYear.value = null;
    selectedMonth.value = null;
    if (selectedCategory.value === 'employees') {
        fetchArchivedEmployees();
    }
};

const resetNavigation = () => {
    selectedCategory.value = null;
    selectedYear.value = null;
    selectedMonth.value = null;
    searchQuery.value = '';
    fetchArchiveIndex();
};

const unarchiveLeave = async (id) => {
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

const unarchiveEmployee = async (id) => {
    if (!confirm('Restore this employee to the active masterlist?')) return;
    try {
        await axios.post(`/api/employees/${id}/unarchive`);
        employees.value = employees.value.filter(e => e.id !== id);
        totalRecords.value--;
        alert('Employee restored successfully.');
        fetchArchiveIndex();
    } catch (e) {
        alert('Restore failed.');
    }
};

const viewRecord = (req) => {
    selectedRecord.value = req;
};

const isNewRecord = (archivedAt) => {
    if (!archivedAt) return false;
    const archivedDate = new Date(archivedAt);
    const now = new Date();
    const diffInHours = (now - archivedDate) / (1000 * 60 * 60);
    return diffInHours <= 24;
};

const getInitials = (name) => name ? name.match(/(\b\S)?/g).join("").match(/(^\S|\S$)?/g).join("").toUpperCase() : 'U';
const formatDate = (d) => new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
const formatFullDateTime = (d) => {
    if (!d) return 'N/A';
    return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) + 
           ' ' + new Date(d).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
};

let debounceTimer = null;
watch(searchQuery, () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        if (selectedCategory.value === 'leaves' && selectedMonth.value) {
            page.value = 1;
            fetchMonthlyRequests();
        } else if (selectedCategory.value === 'employees') {
            page.value = 1;
            fetchArchivedEmployees();
        }
    }, 400);
});

watch(page, () => {
    if (selectedCategory.value === 'leaves' && selectedMonth.value) {
        fetchMonthlyRequests();
    } else if (selectedCategory.value === 'employees') {
        fetchArchivedEmployees();
    }
});

onMounted(() => {
    authStore.fetchUser();
    fetchArchiveIndex();
});
</script>
