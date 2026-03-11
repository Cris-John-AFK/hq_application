<template>
    <div v-if="modelValue" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" @click.self="$emit('update:modelValue', false)">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden animate-in fade-in zoom-in duration-200">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-teal-50/30">
                <div>
                    <h3 class="text-lg font-black text-teal-600 uppercase tracking-tight">Security Settings</h3>
                    <p class="text-[10px] text-gray-400 font-bold uppercase mt-1">Change your account password</p>
                </div>
                <button @click="$emit('update:modelValue', false)" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="pi pi-times"></i>
                </button>
            </div>
            
            <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Current Password</label>
                        <div class="relative">
                            <i class="pi pi-shield absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input 
                                v-model="form.current_password" 
                                type="password" 
                                required
                                placeholder="Verify current password"
                                class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all font-bold text-gray-700 text-sm"
                            >
                        </div>
                    </div>

                    <div class="border-t border-gray-50 pt-4">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">New Password</label>
                        <div class="relative">
                            <i class="pi pi-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input 
                                v-model="form.password" 
                                type="password" 
                                required
                                minlength="8"
                                placeholder="Minimum 8 characters"
                                class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all font-bold text-gray-700 text-sm"
                            >
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Confirm New Password</label>
                        <div class="relative">
                            <i class="pi pi-check-circle absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input 
                                v-model="form.password_confirmation" 
                                type="password" 
                                required
                                placeholder="Repeat new password"
                                class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all font-bold text-gray-700 text-sm"
                            >
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 rounded-xl p-3 border border-blue-100 mt-2">
                    <p class="text-[9px] text-blue-600 font-bold leading-relaxed">
                        <i class="pi pi-info-circle mr-1"></i>
                        Strong passwords use a mix of letters, numbers, and symbols. Your session will remain active after change.
                    </p>
                </div>

                <div class="pt-2 flex gap-3">
                    <button type="button" @click="$emit('update:modelValue', false)" class="flex-1 px-4 py-2 text-xs font-black uppercase text-gray-400 hover:text-gray-600 transition-colors">Cancel</button>
                    <button 
                        type="submit" 
                        :disabled="loading || !form.password"
                        class="flex-1 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-teal-200 transition-all flex items-center justify-center gap-2 disabled:opacity-50"
                    >
                        <i v-if="loading" class="pi pi-spin pi-spinner"></i>
                        <span>Update Password</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import axios from 'axios';

const props = defineProps({
    modelValue: Boolean
});

const emit = defineEmits(['update:modelValue', 'success']);

const loading = ref(false);
const form = reactive({
    current_password: '',
    password: '',
    password_confirmation: ''
});

const handleSubmit = async () => {
    if (form.password !== form.password_confirmation) {
        alert('New passwords do not match');
        return;
    }

    loading.value = true;
    try {
        await axios.post('/api/user/change-password', form);
        alert('Your password has been changed successfully.');
        form.current_password = '';
        form.password = '';
        form.password_confirmation = '';
        emit('success');
        emit('update:modelValue', false);
    } catch (e) {
        console.error(e);
        alert(e.response?.data?.message || 'Failed to change password. Validate your current password.');
    } finally {
        loading.value = false;
    }
};
</script>
