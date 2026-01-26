<template>
    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="$emit('close')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col animate-in fade-in zoom-in-95 duration-200">
            <!-- Header -->
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Manage Bulletins</h2>
                    <p class="text-xs text-gray-500 mt-1 uppercase tracking-widest font-bold">Admin Control Panel</p>
                </div>
                <button @click="$emit('close')" class="p-2 hover:bg-gray-200 rounded-full transition-colors text-gray-500">
                    <i class="pi pi-times"></i>
                </button>
            </div>

            <div class="flex-1 overflow-hidden flex">
                <!-- Left: List -->
                <div class="w-1/2 border-r border-gray-100 overflow-y-auto p-4 bg-gray-50/30">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest pl-1">Active Announcements</h3>
                        <button @click="resetForm" class="text-[10px] font-black uppercase text-teal-600 hover:text-teal-700 bg-teal-50 px-2 py-1 rounded">New post</button>
                    </div>

                    <div class="space-y-3">
                        <div v-if="loading" v-for="i in 3" :key="i" class="h-20 bg-gray-100 rounded-xl animate-pulse"></div>
                        <div 
                            v-for="item in announcements" 
                            :key="item.id"
                            @click="editItem(item)"
                            class="p-4 rounded-xl border bg-white cursor-pointer transition-all hover:shadow-md group relative"
                            :class="[selectedId === item.id ? 'border-teal-500 ring-2 ring-teal-50 shadow-sm' : 'border-gray-100']"
                        >
                            <div class="flex gap-3">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" :class="getTypeBg(item.type)">
                                    <i class="pi text-white text-[10px]" :class="getTypeIcon(item.type)"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-sm font-bold text-gray-800 truncate">{{ item.title }}</h4>
                                    <p class="text-[10px] text-gray-400 line-clamp-1 italic">{{ item.content }}</p>
                                </div>
                                <div class="flex flex-col items-end gap-1">
                                    <span 
                                        :class="getStatusClass(item)" 
                                        class="text-[8px] font-black uppercase px-1.5 py-0.5 rounded shadow-sm border"
                                    >
                                        {{ getStatusText(item) }}
                                    </span>
                                    <button @click.stop="deleteItem(item.id)" class="text-gray-300 hover:text-rose-500 transition-colors">
                                        <i class="pi pi-trash text-[10px]"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Form -->
                <div class="w-1/2 overflow-y-auto p-6 bg-white">
                    <h3 class="text-sm font-black text-gray-800 mb-6">{{ selectedId ? 'Edit Announcement' : 'Post New Announcement' }}</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 pl-1">Title</label>
                            <input v-model="form.title" type="text" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none text-sm transition-all" placeholder="e.g. System Maintenance">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 pl-1">Type</label>
                                <select v-model="form.type" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none text-sm bg-white">
                                    <option value="info">Information (Blue)</option>
                                    <option value="success">Success (Green)</option>
                                    <option value="warning">Warning (Amber)</option>
                                    <option value="urgent">Urgent (Rose)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 pl-1">Priority</label>
                                <input v-model="form.priority" type="number" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none text-sm" placeholder="0">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 pl-1">Message Content</label>
                            <textarea v-model="form.content" rows="4" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none text-sm transition-all" placeholder="Detail your announcement here..."></textarea>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 pl-1">Expiry Date (Optional)</label>
                            <input v-model="form.expires_at" type="datetime-local" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 outline-none text-sm">
                        </div>

                        <div class="flex items-center gap-2 pt-2">
                            <input type="checkbox" v-model="form.is_active" id="active_toggle" class="w-4 h-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500">
                            <label for="active_toggle" class="text-xs font-bold text-gray-600">Active and visible for everyone</label>
                        </div>

                        <div class="pt-6 border-t border-gray-50 flex gap-3">
                            <button 
                                @click="save" 
                                :disabled="saving"
                                class="cursor-pointer flex-1 py-3 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-bold transition-all shadow-lg shadow-teal-100 flex items-center justify-center gap-2"
                            >
                                <i class="pi pi-check" v-if="!saving"></i>
                                <i class="pi pi-spin pi-spinner" v-else></i>
                                {{ selectedId ? 'Update Post' : 'Share Now' }}
                            </button>
                            <button v-if="selectedId" @click="resetForm" class="px-6 py-3 bg-gray-100 text-gray-500 rounded-xl font-bold hover:bg-gray-200 transition-colors">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const emit = defineEmits(['close', 'refresh']);

const announcements = ref([]);
const loading = ref(false);
const saving = ref(false);
const selectedId = ref(null);

const form = ref({
    title: '',
    content: '',
    type: 'info',
    priority: 0,
    expires_at: '',
    is_active: true
});

const fetchAll = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/announcements/all');
        announcements.value = response.data;
    } catch (e) {
        console.error("Failed to load list");
    } finally {
        loading.value = false;
    }
};

const editItem = (item) => {
    selectedId.ref = item.id; // Corrected typo in logic below
    selectedId.value = item.id;
    form.value = { 
        title: item.title,
        content: item.content,
        type: item.type,
        priority: item.priority,
        expires_at: item.expires_at ? new Date(item.expires_at).toISOString().slice(0, 16) : '',
        is_active: !!item.is_active
    };
};

const resetForm = () => {
    selectedId.value = null;
    form.value = {
        title: '',
        content: '',
        type: 'info',
        priority: 0,
        expires_at: '',
        is_active: true
    };
};

const save = async () => {
    if (!form.value.title || !form.value.content) {
        alert("Please fill in the title and content.");
        return;
    }

    saving.value = true;
    try {
        if (selectedId.value) {
            await axios.put(`/api/announcements/${selectedId.value}`, form.value);
        } else {
            await axios.post('/api/announcements', form.value);
        }
        fetchAll();
        resetForm();
        emit('refresh');
    } catch (e) {
        alert("Action failed. Check fields.");
    } finally {
        saving.value = false;
    }
};

const deleteItem = async (id) => {
    if (!confirm("Are you sure you want to delete this announcement?")) return;
    try {
        await axios.delete(`/api/announcements/${id}`);
        fetchAll();
        emit('refresh');
        if (selectedId.value === id) resetForm();
    } catch (e) {
        alert("Delete failed");
    }
};

const getTypeIcon = (type) => {
    switch(type) {
        case 'urgent': return 'pi-exclamation-triangle';
        case 'warning': return 'pi-info-circle';
        case 'success': return 'pi-megaphone';
        default: return 'pi-bell';
    }
};

const getTypeBg = (type) => {
    switch(type) {
        case 'urgent': return 'bg-rose-500';
        case 'warning': return 'bg-amber-500';
        case 'success': return 'bg-emerald-500';
        default: return 'bg-teal-500';
    }
};

const isExpired = (item) => {
    if (!item.expires_at) return false;
    return new Date(item.expires_at) < new Date();
};

const getStatusText = (item) => {
    if (!item.is_active) return 'Hidden';
    if (isExpired(item)) return 'Expired';
    return 'Visible';
};

const getStatusClass = (item) => {
    if (!item.is_active) return 'bg-gray-100 text-gray-400 border-gray-200';
    if (isExpired(item)) return 'bg-rose-50 text-rose-500 border-rose-100';
    return 'bg-emerald-50 text-emerald-600 border-emerald-100';
};

onMounted(fetchAll);
</script>
