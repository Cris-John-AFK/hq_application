<template>
    <div v-if="modelValue" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" @click.self="$emit('update:modelValue', false)">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden animate-in fade-in zoom-in duration-200">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-rose-50/30">
                <div>
                    <h3 class="text-lg font-black text-rose-600 uppercase tracking-tight">Force Password Reset</h3>
                    <p class="text-[10px] text-gray-400 font-bold uppercase mt-1">Admin Security Override</p>
                </div>
                <button @click="$emit('update:modelValue', false)" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="pi pi-times"></i>
                </button>
            </div>
            
            <div class="p-6 space-y-4">
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-100">
                    <div class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold uppercase">
                        {{ getInitials(employee?.first_name, employee?.last_name) }}
                    </div>
                    <div>
                        <p class="text-xs font-black uppercase text-gray-800">{{ employee?.first_name }} {{ employee?.last_name }}</p>
                        <p class="text-[10px] text-gray-400 font-bold uppercase">{{ employee?.employee_id }} — {{ employee?.user?.role }}</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">New Password</label>
                        <div class="relative">
                            <i class="pi pi-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input 
                                v-model="form.password" 
                                type="password" 
                                placeholder="Enter new password"
                                class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-rose-500/10 focus:border-rose-500 outline-none transition-all font-bold text-gray-700 text-sm"
                            >
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Confirm New Password</label>
                        <div class="relative">
                            <i class="pi pi-shield absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input 
                                v-model="form.password_confirmation" 
                                type="password" 
                                placeholder="Repeat new password"
                                class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-rose-500/10 focus:border-rose-500 outline-none transition-all font-bold text-gray-700 text-sm"
                            >
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-amber-50 rounded-xl border border-amber-100">
                    <p class="text-[10px] text-amber-700 font-bold leading-relaxed">
                        <i class="pi pi-exclamation-triangle mr-1"></i>
                        Caution: This will instantly lock the user out of their account until they use the new password.
                    </p>
                </div>
            </div>

            <div class="p-6 bg-gray-50 border-t border-gray-100 flex gap-3">
                <button @click="$emit('update:modelValue', false)" class="flex-1 px-4 py-2 text-xs font-black uppercase text-gray-400 hover:text-gray-600 transition-colors">Cancel</button>
                <button 
                    @click="handleSubmit" 
                    :disabled="loading || !form.password"
                    class="flex-1 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-rose-200 transition-all flex items-center justify-center gap-2 disabled:opacity-50"
                >
                    <i v-if="loading" class="pi pi-spin pi-spinner"></i>
                    <span>Forced Update</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import axios from 'axios';

const props = defineProps({
    modelValue: Boolean,
    employee: Object
});

const emit = defineEmits(['update:modelValue', 'success']);

const loading = ref(false);
const form = reactive({
    password: '',
    password_confirmation: ''
});

const getInitials = (first, last) => {
    if (!first || !last) return '??';
    return (first[0] + last[0]).toUpperCase();
};

const handleSubmit = async () => {
    if (form.password !== form.password_confirmation) {
        alert('Passwords do not match');
        return;
    }

    if (!props.employee?.user?.id) return;

    loading.value = true;
    try {
        await axios.put(`/api/users/${props.employee.user.id}/password`, form);
        alert('Password has been forcibly reset successfully.');
        form.password = '';
        form.password_confirmation = '';
        emit('success');
        emit('update:modelValue', false);
    } catch (e) {
        console.error(e);
        alert(e.response?.data?.message || 'Failed to reset password');
    } finally {
        loading.value = false;
    }
};
</script>
