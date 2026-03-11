<template>
    <div v-if="user">
        <MainLayout :user="user">
            <div class="max-w-4xl mx-auto space-y-6">
                <!-- Header -->
                <div class="bg-white p-8 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-3xl font-black text-slate-800 tracking-tight flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-teal-50 border border-teal-100 flex items-center justify-center text-teal-600">
                                <i class="pi pi-shield text-xl"></i>
                            </div>
                            Security Settings
                        </h2>
                        <p class="text-slate-500 font-medium mt-2 pl-[3.75rem]">Manage your account password and security preferences.</p>
                    </div>
                </div>

                <!-- Password Change Form -->
                <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 overflow-hidden">
                    <div class="p-8 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="text-lg font-bold text-slate-800">Change Password</h3>
                        <p class="text-sm text-slate-500 mt-1">Ensure your account is using a long, random password to stay secure.</p>
                    </div>
                    
                    <form @submit.prevent="handleSubmit" class="p-8 space-y-6">
                        <div class="space-y-5 max-w-xl">
                            <!-- Target User (Admin Only) -->
                            <div v-if="user?.role === 'admin'">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-widest mb-2 ml-1">Account Target</label>
                                
                                <!-- Search Bar for Accounts -->
                                <div class="relative mb-3">
                                    <i class="pi pi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                    <input 
                                        v-model="userSearch"
                                        type="text"
                                        placeholder="Search by name or email..."
                                        class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all font-medium text-slate-700 text-sm"
                                    >
                                </div>

                                <div class="relative">
                                    <i class="pi pi-users absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    <select 
                                        v-model="targetUser"
                                        class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all font-medium text-slate-700 appearance-none"
                                    >
                                        <option value="myself">Myself ({{ user?.name }})</option>
                                        <option v-for="u in filteredOtherUsers" :key="u.id" :value="u.id">{{ u.name }} ({{ u.email }})</option>
                                    </select>
                                    <i class="pi pi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                                </div>
                                <p v-if="userSearch && filteredOtherUsers.length === 0" class="text-[10px] text-rose-500 font-bold mt-2 ml-1 uppercase">No matching accounts found for "{{ userSearch }}"</p>
                            </div>

                            <hr v-if="user?.role === 'admin'" class="border-slate-100 my-6">

                            <!-- Current Password -->
                            <div v-if="targetUser === 'myself'">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-widest mb-2 ml-1">Current Password</label>
                                <div class="relative">
                                    <i class="pi pi-lock-open absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    <input 
                                        v-model="form.current_password" 
                                        type="password" 
                                        required
                                        placeholder="Enter your current password"
                                        class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all font-medium text-slate-700"
                                    >
                                </div>
                            </div>

                            <hr v-if="targetUser === 'myself'" class="border-slate-100 my-6">

                            <!-- New Password -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-widest mb-2 ml-1">New Password</label>
                                <div class="relative">
                                    <i class="pi pi-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    <input 
                                        v-model="form.password" 
                                        type="password" 
                                        required
                                        minlength="8"
                                        placeholder="Minimum 8 characters"
                                        class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all font-medium text-slate-700"
                                    >
                                </div>
                            </div>

                            <!-- Confirm Password -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-widest mb-2 ml-1">Confirm New Password</label>
                                <div class="relative">
                                    <i class="pi pi-check-circle absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    <input 
                                        v-model="form.password_confirmation" 
                                        type="password" 
                                        required
                                        placeholder="Repeat your new password"
                                        class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none transition-all font-medium text-slate-700"
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Info Banner -->
                        <div class="bg-blue-50/50 rounded-2xl p-4 border border-blue-100 mt-6 max-w-xl flex gap-3 items-start">
                            <i class="pi pi-info-circle text-blue-500 mt-0.5"></i>
                            <p class="text-sm text-blue-700 font-medium leading-relaxed">
                                Strong passwords use a mix of uppercase and lowercase letters, numbers, and symbols. Your session will remain active after you change your password.
                            </p>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="pt-6 border-t border-slate-100 flex gap-4 max-w-xl justify-end">
                            <button 
                                type="button" 
                                @click="resetForm" 
                                class="px-6 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors"
                            >
                                Clear
                            </button>
                            <button 
                                type="submit" 
                                :disabled="loading || !form.password"
                                class="px-8 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-sm font-bold tracking-wide shadow-lg shadow-teal-500/30 transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <i v-if="loading" class="pi pi-spin pi-spinner"></i>
                                <span>Save Changes</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </MainLayout>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import { storeToRefs } from 'pinia';
import MainLayout from '../layouts/MainLayout.vue';
import axios from 'axios';

const authStore = useAuthStore();
const { user } = storeToRefs(authStore);

const loading = ref(false);
const users = ref([]);
const targetUser = ref('myself');
const userSearch = ref('');

const form = reactive({
    current_password: '',
    password: '',
    password_confirmation: ''
});

const filteredOtherUsers = computed(() => {
    const search = userSearch.value.toLowerCase().trim();
    return users.value.filter(u => {
        const isNotMe = u.id !== user.value?.id;
        if (!search) return isNotMe;
        
        const matchesName = u.name?.toLowerCase().includes(search);
        const matchesEmail = u.email?.toLowerCase().includes(search);
        return isNotMe && (matchesName || matchesEmail);
    });
});

const fetchUsers = async () => {
    if (user.value?.role !== 'admin') return;
    try {
        const res = await axios.get('/api/users?all=true');
        users.value = res.data.data || res.data;
    } catch (e) {
        console.error("Failed to load users for security settings.");
    }
};

onMounted(async () => {
    await authStore.fetchUser();
    fetchUsers();
});

const resetForm = () => {
    form.current_password = '';
    form.password = '';
    form.password_confirmation = '';
    targetUser.value = 'myself';
    userSearch.value = '';
};

const handleSubmit = async () => {
    if (form.password !== form.password_confirmation) {
        alert('New passwords do not match');
        return;
    }

    loading.value = true;
    try {
        if (targetUser.value === 'myself') {
            await axios.post('/api/user/change-password', form);
            alert('Your password has been changed successfully.');
        } else {
            await axios.put(`/api/users/${targetUser.value}/password`, {
                password: form.password,
                password_confirmation: form.password_confirmation
            });
            alert('Account password has been changed successfully.');
        }
        resetForm();
    } catch (e) {
        console.error(e);
        alert(e.response?.data?.message || 'Failed to change password. Please validate your inputs.');
    } finally {
        loading.value = false;
    }
};
</script>
