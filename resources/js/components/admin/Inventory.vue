<template>
    <div v-if="user">
        <MainLayout :user="user">
            <div class="max-w-7xl mx-auto space-y-8 pb-16 animate-in fade-in duration-700">
                <!-- Header Section -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 rounded-3xl border border-gray-200 shadow-sm relative overflow-hidden group">
                    <div class="relative z-10">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 rounded-xl bg-teal-500/10 flex items-center justify-center text-teal-600 shadow-sm border border-teal-500/20">
                                <i class="pi pi-box text-xl"></i>
                            </div>
                            <span class="text-xs font-black uppercase tracking-[0.3em] text-teal-600">Resource Registry</span>
                        </div>
                        <h1 class="text-3xl font-black text-gray-900 tracking-tight">Organization <span class="text-teal-600">Inventory</span></h1>
                        <p class="text-gray-500 text-sm mt-1 font-medium italic">Comprehensive asset auditing and lifecycle management.</p>
                    </div>
                    
                    <div class="flex items-center gap-3 relative z-10">
                        <div class="flex flex-col items-end mr-4 hidden lg:flex">
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none">Total Assets</span>
                            <span class="text-2xl font-black text-gray-900">{{ totalItems }}</span>
                        </div>
                        <label class="px-6 py-3 bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 rounded-2xl transition-all duration-300 font-bold text-sm shadow-sm flex items-center gap-2 group/btn cursor-pointer">
                            <i class="pi pi-upload text-gray-400 group-hover/btn:text-teal-600 transition-colors"></i>
                            <span :class="{'text-gray-400': importing}">{{ importing ? 'Importing...' : 'Import Data' }}</span>
                            <input type="file" class="hidden" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" @change="handleImport" :disabled="importing">
                        </label>
                        <button 
                            @click="openAddModal"
                            class="px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white rounded-2xl transition-all duration-300 font-bold text-sm shadow-lg shadow-teal-500/20 flex items-center gap-2 group/btn cursor-pointer"
                        >
                            <i class="pi pi-plus group-hover/btn:rotate-90 transition-transform duration-300"></i>
                            Add New Asset
                        </button>
                    </div>

                    <!-- Decorative BG -->
                    <i class="pi pi-box absolute -right-8 -bottom-8 text-[180px] text-gray-50 -rotate-12 group-hover:rotate-0 transition-all duration-1000 pointer-events-none"></i>
                </div>

                <!-- Search and Filters Section -->
                <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm flex flex-col lg:flex-row gap-4 items-center">
                    <!-- Search Bar -->
                    <div class="relative flex-1 group">
                        <i class="pi pi-search absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-teal-600 transition-colors"></i>
                        <input 
                            v-model="searchQuery" 
                            type="text" 
                            placeholder="Search by name, asset tag, department..." 
                            class="w-full pl-14 pr-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-2 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all font-medium text-gray-700 placeholder:text-gray-400 shadow-inner"
                        >
                    </div>

                    <!-- Filters Group -->
                    <div class="flex items-center gap-3 w-full lg:w-auto">
                        <div class="relative min-w-[160px]">
                            <select v-model="typeFilter" class="w-full pl-5 pr-10 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-2 focus:ring-teal-500/10 focus:border-teal-500 outline-none appearance-none transition-all font-bold text-gray-700 text-sm cursor-pointer shadow-inner">
                                <option value="">All Types</option>
                                <option v-for="t in assetTypes" :key="t" :value="t">{{ t }}</option>
                            </select>
                            <i class="pi pi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                        </div>

                        <button 
                            @click="resetFilters"
                            class="p-4 text-gray-400 hover:text-rose-500 transition-colors cursor-pointer flex items-center justify-center gap-2"
                            v-if="searchQuery || typeFilter"
                        >
                            <i class="pi pi-refresh"></i>
                            <span class="text-[10px] font-bold uppercase tracking-tight">Reset</span>
                        </button>
                    </div>
                </div>

                <!-- Data Table Showcase -->
                <div v-if="items.length > 0" class="bg-white rounded-[32px] border border-gray-100 shadow-sm overflow-hidden flex flex-col min-h-[500px]">
                    <div class="flex-1 overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse whitespace-nowrap">
                            <thead>
                                <tr class="bg-gray-50/50">
                                    <th class="py-3 px-4 pl-6 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Type</th>
                                    <th class="py-3 px-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Location</th>
                                    <th class="py-3 px-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Asset Tag</th>
                                    <th class="py-3 px-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">SN</th>
                                    <th class="py-3 px-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Nomenclature</th>
                                    <th class="py-3 px-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Brand / Model</th>
                                    <th class="py-3 px-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Color</th>
                                    <th class="py-3 px-4 text-right pr-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50/50 transition-colors group">
                                    <td class="py-3 px-4 pl-6">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-lg bg-white border border-gray-100 shadow-sm flex items-center justify-center text-gray-400 group-hover:text-teal-600 transition-colors">
                                                <i :class="['pi text-xs', getIconForType(item.type)]"></i>
                                            </div>
                                            <span class="text-[10px] font-bold text-gray-600 uppercase tracking-tight">{{ item.type }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex flex-col">
                                            <span class="text-xs font-black text-gray-900 group-hover:text-teal-600 transition-colors leading-tight">{{ item.department || '--' }}</span>
                                            <span class="text-[10px] text-gray-400 font-medium">{{ item.location || '--' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="text-[10px] font-mono font-black text-teal-700 bg-teal-50 px-2 py-1 rounded-md border border-teal-100">{{ item.asset_tag || '--' }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="text-[10px] font-mono font-bold text-gray-500 bg-gray-50 px-2 py-1 rounded-md border border-gray-100">{{ item.serial_number || '--' }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="text-xs font-bold text-gray-800">{{ item.name }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                         <div class="flex flex-col">
                                            <span class="text-xs font-bold text-gray-800 leading-tight">{{ item.brand || '--' }}</span>
                                            <span class="text-[10px] text-gray-400 font-medium">{{ item.model_name || '--' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-1.5">
                                            <div v-if="item.color" class="w-2.5 h-2.5 rounded-full border border-gray-200" :style="{ backgroundColor: item.color.toLowerCase() === 'white' ? '#f8fafc' : (item.color.toLowerCase() === 'black' ? '#0f172a' : (item.color.toLowerCase() === 'gray' ? '#94a3b8' : item.color)) }"></div>
                                            <span class="text-[10px] font-bold text-gray-500">{{ item.color || '--' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 pr-6 text-right">
                                        <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-all">
                                            <button @click="editItem(item)" class="w-8 h-8 rounded-lg bg-white border border-gray-200 text-teal-600 hover:bg-teal-50 hover:border-teal-200 transition-all flex items-center justify-center cursor-pointer shadow-sm" title="Edit Asset">
                                                <i class="pi pi-pencil text-xs"></i>
                                            </button>
                                            <button @click="confirmDelete(item)" class="w-8 h-8 rounded-lg bg-white border border-gray-200 text-rose-600 hover:bg-rose-50 hover:border-rose-200 transition-all flex items-center justify-center cursor-pointer shadow-sm" title="Delete Asset">
                                                <i class="pi pi-trash text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Controls -->
                    <div v-if="lastPage > 1" class="px-8 py-5 bg-gray-50/50 border-t border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Page {{ currentPage }} of {{ lastPage }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button 
                                @click="fetchItems(currentPage - 1)" 
                                :disabled="currentPage === 1"
                                class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-teal-50 hover:text-teal-600 hover:border-teal-200 disabled:opacity-30 disabled:hover:bg-white disabled:hover:text-gray-600 disabled:hover:border-gray-200 transition-all cursor-pointer shadow-sm"
                            >
                                <i class="pi pi-chevron-left text-xs"></i>
                            </button>
                            <div class="flex items-center gap-1">
                                <button 
                                    v-for="p in paginatedPages" 
                                    :key="p"
                                    @click="p !== '...' ? fetchItems(p) : null"
                                    :class="[
                                        'w-10 h-10 rounded-xl flex items-center justify-center text-xs font-black transition-all cursor-pointer border',
                                        currentPage === p ? 'bg-teal-600 text-white border-teal-500 shadow-lg shadow-teal-500/20' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'
                                    ]"
                                >
                                    {{ p }}
                                </button>
                            </div>
                            <button 
                                @click="fetchItems(currentPage + 1)" 
                                :disabled="currentPage === lastPage"
                                class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-teal-50 hover:text-teal-600 hover:border-teal-200 disabled:opacity-30 disabled:hover:bg-white disabled:hover:text-gray-600 disabled:hover:border-gray-200 transition-all cursor-pointer shadow-sm"
                            >
                                <i class="pi pi-chevron-right text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else-if="!loading" class="flex flex-col items-center justify-center py-20 bg-white rounded-[32px] border border-dashed border-gray-200">
                    <div class="w-20 h-20 rounded-3xl bg-gray-50 flex items-center justify-center text-gray-300 mb-6 shadow-inner">
                        <i class="pi pi-search text-4xl"></i>
                    </div>
                    <h3 class="text-xl font-black text-gray-900">No matching assets found</h3>
                    <p class="text-gray-500 mt-2 font-medium">Try adjusting your filters or search terms.</p>
                    <button @click="resetFilters" class="mt-6 px-6 py-3 bg-teal-600 text-white rounded-2xl font-bold text-sm shadow-lg shadow-teal-500/20 cursor-pointer hover:bg-teal-700 transition-colors">
                        Reset All Filters
                    </button>
                </div>

                <!-- Add/Edit Modal (Compact Version) -->
                <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-md animate-in fade-in duration-300">
                    <div class="bg-white rounded-[32px] shadow-2xl w-full max-w-md flex flex-col max-h-[95vh] overflow-hidden border border-white/20">
                        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-teal-600 flex items-center justify-center text-white shadow-lg shadow-teal-500/20">
                                    <i class="pi pi-file-edit text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-black text-gray-900 leading-tight">{{ editingItem ? 'Modify Asset' : 'Register Asset' }}</h3>
                                    <p class="text-[9px] text-gray-400 font-black uppercase tracking-[0.2em]">Audit ID: HQA-{{ Math.floor(1000 + Math.random() * 9000) }}</p>
                                </div>
                            </div>
                            <button @click="showModal = false" class="w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-400 cursor-pointer transition-colors">
                                <i class="pi pi-times text-xs"></i>
                            </button>
                        </div>

                        <form @submit.prevent="saveItem" class="p-6 overflow-y-auto custom-scrollbar space-y-5">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Asset Nomenclature</label>
                                    <input v-model="form.name" type="text" required placeholder="e.g. Dell Latitude 5420" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all font-bold text-gray-800 text-sm">
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Asset Type</label>
                                        <select v-model="form.type" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all font-bold text-gray-800 text-sm cursor-pointer">
                                            <option v-for="t in assetTypes" :key="t" :value="t">{{ t }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Units</label>
                                        <input v-model="form.quantity" type="number" required min="1" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all font-bold text-gray-800 text-sm font-mono">
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Asset Tag</label>
                                        <input v-model="form.asset_tag" type="text" placeholder="HQ-XXX" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all font-mono text-xs font-bold text-teal-700">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Serial Number</label>
                                        <input v-model="form.serial_number" type="text" placeholder="SN-0000X" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all font-mono text-xs font-bold text-gray-700">
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Brand</label>
                                        <input v-model="form.brand" type="text" placeholder="e.g. Dell" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all font-bold text-gray-800 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Model Name</label>
                                        <input v-model="form.model_name" type="text" placeholder="e.g. XPS 15" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all font-bold text-gray-800 text-sm">
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Department</label>
                                        <input v-model="form.department" type="text" placeholder="e.g. IT Room" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all font-bold text-gray-800 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Location Floor</label>
                                        <input v-model="form.location" type="text" placeholder="e.g. 1F" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all font-bold text-gray-800 text-sm">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Color</label>
                                    <input v-model="form.color" type="text" placeholder="e.g. Black" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all font-bold text-gray-800 text-sm">
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Condition</label>
                                    <select v-model="form.status" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all font-bold text-gray-800 text-sm cursor-pointer">
                                        <option value="Good">Good Condition</option>
                                        <option value="Needs Repair">Needs Repair</option>
                                        <option value="Replacement">For Replacement</option>
                                    </select>
                                </div>

                            </div>

                            <div class="flex gap-3 pt-2">
                                <button type="button" @click="showModal = false" class="flex-1 py-3 text-gray-500 font-black text-xs uppercase tracking-widest hover:bg-gray-100 rounded-xl transition-all cursor-pointer">Cancel</button>
                                <button 
                                    type="submit" 
                                    :disabled="saving"
                                    class="flex-[2] py-3 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-black text-xs uppercase tracking-widest transition-all shadow-lg shadow-teal-500/20 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
                                >
                                    <i v-if="saving" class="pi pi-spin pi-spinner"></i>
                                    {{ editingItem ? 'Save Changes' : 'Register Asset' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </MainLayout>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../../stores/auth';
import { storeToRefs } from 'pinia';
import MainLayout from '../../layouts/MainLayout.vue';

const authStore = useAuthStore();
const { user } = storeToRefs(authStore);

const items = ref([]);
const loading = ref(true);
const saving = ref(false);
const importing = ref(false);
const showModal = ref(false);
const editingItem = ref(null);

// Pagination State
const currentPage = ref(1);
const lastPage = ref(1);
const totalItems = ref(0);
const pageSize = ref(15);

// Search & Filter State
const searchQuery = ref('');
const typeFilter = ref('');
let debounceTimer = null;

const assetTypes = ['Laptop', 'Desktop', 'Monitor', 'Mouse', 'Keyboard', 'Printer', 'Webcam', 'Router', 'Furniture', 'Others'];

const paginatedPages = computed(() => {
    const range = [];
    const delta = 2;
    for (let i = Math.max(2, currentPage.value - delta); i <= Math.min(lastPage.value - 1, currentPage.value + delta); i++) {
        range.push(i);
    }
    if (currentPage.value - delta > 2) range.unshift('...');
    range.unshift(1);
    if (currentPage.value + delta < lastPage.value - 1) range.push('...');
    if (lastPage.value > 1) range.push(lastPage.value);
    return range;
});

const resetFilters = () => {
    searchQuery.value = '';
    typeFilter.value = '';
};

const form = ref({
    name: '',
    type: 'Laptop',
    quantity: 1,
    serial_number: '',
    asset_tag: '',
    status: 'Good',
    department: '',
    location: '',
    brand: '',
    model_name: '',
    color: ''
});

const getIconForType = (type) => {
    const map = {
        'Laptop': 'pi-desktop',
        'Desktop': 'pi-box',
        'Monitor': 'pi-image',
        'Mouse': 'pi-mouse',
        'Keyboard': 'pi-tablet',
        'Printer': 'pi-print',
        'Webcam': 'pi-camera',
        'Router': 'pi-wifi',
        'Furniture': 'pi-home',
        'Others': 'pi-ellipsis-h'
    };
    return map[type] || 'pi-box';
};

const fetchItems = async (page = 1) => {
    loading.value = true;
    try {
        const response = await axios.get('/api/inventory', {
            params: {
                page,
                search: searchQuery.value,
                type: typeFilter.value,
                limit: pageSize.value
            }
        });
        
        items.value = response.data.data;
        currentPage.value = response.data.current_page;
        lastPage.value = response.data.last_page;
        totalItems.value = response.data.total;
    } catch (e) {
        console.error("Failed to fetch inventory", e);
    } finally {
        loading.value = false;
    }
};

// Lifecycle & Watches
onMounted(() => {
    authStore.fetchUser();
    fetchItems();
});

watch([searchQuery, typeFilter], () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        fetchItems(1);
    }, 400);
});

const openAddModal = () => {
    editingItem.value = null;
    form.value = {
        name: '',
        type: 'Laptop',
        quantity: 1,
        serial_number: '',
        asset_tag: '',
        status: 'Good',
        department: '',
        location: '',
        brand: '',
        model_name: '',
        color: ''
    };
    showModal.value = true;
};

const editItem = (item) => {
    editingItem.value = item;
    form.value = { ...item };
    showModal.value = true;
};

const saveItem = async () => {
    saving.value = true;
    try {
        if (editingItem.value) {
            await axios.put(`/api/inventory/${editingItem.value.id}`, form.value);
        } else {
            await axios.post('/api/inventory', form.value);
        }
        await fetchItems();
        showModal.value = false;
    } catch (e) {
        console.error("Save failed", e);
        alert('Failed to save asset: ' + (e.response?.data?.message || 'Server error'));
    } finally {
        saving.value = false;
    }
};

const confirmDelete = async (item) => {
    if (confirm(`Are you sure you want to remove this ${item.type}: ${item.name}?`)) {
        try {
            await axios.delete(`/api/inventory/${item.id}`);
            await fetchItems();
        } catch (e) {
            console.error("Delete failed", e);
        }
    }
};

const handleImport = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('file', file);

    importing.value = true;
    try {
        await axios.post('/api/inventory/import', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        alert('Inventory imported successfully!');
        await fetchItems();
    } catch (error) {
        console.error("Import failed", error.response?.data || error.message);
        alert('Import failed: ' + (error.response?.data?.message || 'Server error'));
    } finally {
        importing.value = false;
        event.target.value = ''; // Reset input
    }
};

onMounted(() => {
    authStore.fetchUser();
    fetchItems();
});
</script>

<style scoped>
/* Scoped styles */
</style>
