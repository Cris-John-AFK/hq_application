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
                            <span class="text-2xl font-black text-gray-900">{{ items.length }}</span>
                        </div>
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
                            placeholder="Search by name, serial number, or description..." 
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

                        <div class="relative min-w-[160px]">
                            <select v-model="statusFilter" class="w-full pl-5 pr-10 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-2 focus:ring-teal-500/10 focus:border-teal-500 outline-none appearance-none transition-all font-bold text-gray-700 text-sm cursor-pointer shadow-inner">
                                <option value="">All Statuses</option>
                                <option value="Good">Good Condition</option>
                                <option value="Needs Repair">Needs Repair</option>
                                <option value="Replacement">For Replacement</option>
                            </select>
                            <i class="pi pi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                        </div>

                        <button 
                            @click="resetFilters"
                            class="p-4 text-gray-400 hover:text-rose-500 transition-colors cursor-pointer"
                            v-if="searchQuery || typeFilter || statusFilter"
                            title="Reset Filters"
                        >
                            <i class="pi pi-refresh"></i>
                        </button>
                    </div>
                </div>

                <!-- 3D Asset Showcase Grid -->
                <div v-if="filteredItems.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                    <div 
                        v-for="item in filteredItems" 
                        :key="item.id"
                        class="perspective-group"
                    >
                        <div class="inventory-card bg-white rounded-[40px] border border-gray-100 shadow-2xl relative overflow-hidden group/card hover:border-teal-500/30 transition-all duration-700 cursor-pointer p-0 flex flex-col items-center">
                            
                            <!-- Premium Image Header -->
                            <div class="w-full h-56 relative overflow-hidden bg-gray-50 flex items-center justify-center">
                                <img 
                                    v-if="!imageErrors[item.id]"
                                    :src="getImageForType(item.type)" 
                                    :alt="item.name"
                                    @error="handleImageError(item.id)"
                                    class="w-full h-full object-cover transform scale-110 group-hover/card:scale-100 transition-transform duration-1000 ease-out grayscale-[20%] group-hover/card:grayscale-0 opacity-90 group-hover/card:opacity-100"
                                >
                                <!-- High-end Fallback Icon -->
                                <div v-else class="w-full h-full bg-gradient-to-br from-gray-50 to-white flex items-center justify-center">
                                    <i :class="['pi text-6xl text-teal-600/20 animate-pulse', getIconForType(item.type)]"></i>
                                </div>
                                <div class="absolute inset-0 bg-gradient-to-t from-white via-transparent to-black/5"></div>
                                
                                <!-- Status Float Badge -->
                                <div :class="[
                                    'absolute top-6 right-6 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border backdrop-blur-md shadow-lg',
                                    item.status === 'Good' ? 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20' : 
                                    item.status === 'Needs Repair' ? 'bg-amber-500/10 text-amber-600 border-amber-500/20' : 
                                    'bg-rose-500/10 text-rose-600 border-rose-500/20'
                                ]">
                                    {{ item.status }}
                                </div>
                            </div>

                            <!-- Content Overlay Area -->
                            <div class="p-8 w-full relative z-10 -mt-8 bg-white rounded-t-[40px] flex flex-col items-center text-center">
                                <span class="text-[10px] font-black text-teal-600 uppercase tracking-[0.3em] bg-teal-50 px-4 py-1.5 rounded-full border border-teal-100 shadow-sm mb-4">{{ item.type }}</span>
                                <h3 class="text-2xl font-black text-gray-900 tracking-tight group-hover/card:text-teal-600 transition-colors duration-300">{{ item.name }}</h3>
                                <p class="text-xs text-gray-500 mt-3 font-medium leading-relaxed max-w-[240px] italic">"{{ item.description || 'No specialized audit notes provided for this unit.' }}"</p>

                                <div class="w-full mt-8 pt-8 border-t border-gray-50 grid grid-cols-2 gap-6">
                                    <div class="flex flex-col items-start">
                                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Inventory Level</span>
                                        <div class="flex items-center gap-2">
                                            <div class="w-2 h-2 rounded-full bg-teal-500 animate-pulse"></div>
                                            <span class="text-lg font-black text-gray-800">{{ item.quantity }} <span class="text-xs text-gray-400 font-bold ml-1 uppercase">Units</span></span>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-end">
                                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Asset Tag</span>
                                        <span class="text-xs font-mono font-black text-gray-700 bg-gray-50 px-3 py-1 rounded-lg border border-gray-100">{{ item.serial_number || 'UNTAGGED' }}</span>
                                    </div>
                                </div>

                                <!-- Floating Action Menu -->
                                <div class="absolute -top-6 left-1/2 -translate-x-1/2 flex gap-3 opacity-0 translate-y-4 group-hover/card:opacity-100 group-hover/card:translate-y-0 transition-all duration-500 ease-out pointer-events-none group-hover/card:pointer-events-auto">
                                    <button @click="editItem(item)" class="w-12 h-12 bg-white rounded-2xl shadow-xl text-teal-600 hover:bg-teal-600 hover:text-white transition-all cursor-pointer flex items-center justify-center border border-gray-100 scale-90 hover:scale-100">
                                        <i class="pi pi-pencil font-bold"></i>
                                    </button>
                                    <button @click="confirmDelete(item)" class="w-12 h-12 bg-white rounded-2xl shadow-xl text-rose-600 hover:bg-rose-600 hover:text-white transition-all cursor-pointer flex items-center justify-center border border-gray-100 scale-90 hover:scale-100">
                                        <i class="pi pi-trash font-bold"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Interactive Reflection -->
                            <div class="absolute inset-0 bg-gradient-to-tr from-teal-500/5 to-transparent opacity-0 group-hover/card:opacity-100 transition-opacity duration-1000 pointer-events-none"></div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="flex flex-col items-center justify-center py-20 bg-white rounded-[40px] border border-dashed border-gray-200">
                    <div class="w-20 h-20 rounded-3xl bg-gray-50 flex items-center justify-center text-gray-300 mb-6">
                        <i class="pi pi-search text-4xl"></i>
                    </div>
                    <h3 class="text-xl font-black text-gray-900">No matching assets found</h3>
                    <p class="text-gray-500 mt-2 font-medium">Try adjusting your filters or search terms.</p>
                    <button @click="resetFilters" class="mt-6 px-6 py-3 bg-teal-600 text-white rounded-2xl font-bold text-sm shadow-lg shadow-teal-500/20 cursor-pointer">
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
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Serial Number</label>
                                        <input v-model="form.serial_number" type="text" placeholder="SN-0000X" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all font-mono text-xs font-bold text-gray-700">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Condition</label>
                                        <select v-model="form.status" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all font-bold text-gray-800 text-sm cursor-pointer">
                                            <option value="Good">Good</option>
                                            <option value="Needs Repair">Repair</option>
                                            <option value="Replacement">Replace</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Audit Notes</label>
                                    <textarea v-model="form.description" rows="2" placeholder="Technical specifications or physical condition notes..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all font-medium text-gray-700 text-xs leading-relaxed resize-none"></textarea>
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
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../../stores/auth';
import { storeToRefs } from 'pinia';
import MainLayout from '../../layouts/MainLayout.vue';

const authStore = useAuthStore();
const { user } = storeToRefs(authStore);

const items = ref([]);
const loading = ref(true);
const saving = ref(false);
const showModal = ref(false);
const editingItem = ref(null);
const imageErrors = ref({});

// Search & Filter State
const searchQuery = ref('');
const typeFilter = ref('');
const statusFilter = ref('');

const assetTypes = ['Laptop', 'Desktop', 'Monitor', 'Mouse', 'Keyboard', 'Printer', 'Webcam', 'Router', 'Furniture', 'Others'];

const filteredItems = computed(() => {
    return items.value.filter(item => {
        const matchesSearch = !searchQuery.value || 
            item.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            (item.serial_number && item.serial_number.toLowerCase().includes(searchQuery.value.toLowerCase())) ||
            (item.description && item.description.toLowerCase().includes(searchQuery.value.toLowerCase()));
        
        const matchesType = !typeFilter.value || item.type === typeFilter.value;
        const matchesStatus = !statusFilter.value || item.status === statusFilter.value;

        return matchesSearch && matchesType && matchesStatus;
    });
});

const resetFilters = () => {
    searchQuery.value = '';
    typeFilter.value = '';
    statusFilter.value = '';
};

const form = ref({
    name: '',
    type: 'Laptop',
    quantity: 1,
    serial_number: '',
    status: 'Good',
    description: '',
    location: ''
});

const handleImageError = (id) => {
    imageErrors.value[id] = true;
};

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

const getImageForType = (type) => {
    const map = {
        'Laptop': 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=800&q=80',
        'Desktop': 'https://images.unsplash.com/photo-1547082299-de196ea013d6?auto=format&fit=crop&w=800&q=80',
        'Monitor': 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?auto=format&fit=crop&w=800&q=80',
        'Mouse': 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?auto=format&fit=crop&w=800&q=80',
        'Keyboard': 'https://images.unsplash.com/photo-1511467687858-23d96c32e4ae?auto=format&fit=crop&w=800&q=80',
        'Printer': 'https://images.unsplash.com/photo-1612815154858-60aa4c59eaa6?auto=format&fit=crop&w=800&q=80',
        'Webcam': 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=800&q=80',
        'Router': 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=800&q=80',
        'Furniture': 'https://images.unsplash.com/photo-1580480055273-228ff5388ef8?auto=format&fit=crop&w=800&q=80',
        'Others': 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=800&q=80'
    };
    return map[type] || map['Others'];
};

const fetchItems = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/inventory');
        items.value = response.data;
    } catch (e) {
        console.error("Failed to fetch inventory", e);
    } finally {
        loading.value = false;
    }
};

const openAddModal = () => {
    editingItem.value = null;
    form.value = {
        name: '',
        type: 'Laptop',
        quantity: 1,
        serial_number: '',
        status: 'Good',
        description: '',
        location: ''
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

onMounted(() => {
    authStore.fetchUser();
    fetchItems();
});
</script>

<style scoped>
.perspective-group {
    perspective: 1000px;
}

.inventory-card {
    transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
    transform-style: preserve-3d;
}

.perspective-group:hover .inventory-card {
    transform: rotateY(10deg) rotateX(5deg) translateY(-10px);
    box-shadow: -20px 20px 60px rgba(0,0,0,0.1), 0 0 20px rgba(20,184,166,0.1);
}

.transform-style-3d {
    transform-style: preserve-3d;
}

.animate-spin-slow {
    animation: spin-slow 12s linear infinite;
}

@keyframes spin-slow {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.rotate-y-180 {
    transform: rotateY(180deg);
}
</style>
