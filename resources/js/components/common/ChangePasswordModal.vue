<template>
    <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="close">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md transform transition-all">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Change Password</h2>
                    <p class="text-sm text-gray-500 mt-1">{{ employeeName }}</p>
                </div>
                <button 
                    @click="close" 
                    class="p-2 hover:bg-gray-100 rounded-full transition-colors"
                >
                    <i class="pi pi-times text-gray-400"></i>
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <input 
                        v-model="form.password" 
                        type="password" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none transition-all"
                        required
                        minlength="8"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input 
                        v-model="form.password_confirmation" 
                        type="password" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none transition-all"
                        required
                        minlength="8"
                    >
                </div>

                <div v-if="error" class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600">
                    {{ error }}
                </div>

                <!-- Actions -->
                <div class="flex gap-3 pt-4">
                    <button 
                        type="button"
                        @click="close"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit"
                        :disabled="loading"
                        class="flex-1 px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ loading ? 'Updating...' : 'Update Password' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    modelValue: Boolean,
    employeeId: Number,
    employeeName: String
});

const emit = defineEmits(['update:modelValue', 'success']);

const form = ref({
    password: '',
    password_confirmation: ''
});

const loading = ref(false);
const error = ref('');

const close = () => {
    emit('update:modelValue', false);
    resetForm();
};

const resetForm = () => {
    form.value = {
        password: '',
        password_confirmation: ''
    };
    error.value = '';
};

const handleSubmit = async () => {
    error.value = '';
    
    if (form.value.password !== form.value.password_confirmation) {
        error.value = 'Passwords do not match';
        return;
    }

    if (form.value.password.length < 8) {
        error.value = 'Password must be at least 8 characters';
        return;
    }

    loading.value = true;

    try {
        await axios.put(`/api/users/${props.employeeId}/password`, form.value);
        emit('success');
        close();
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to update password';
    } finally {
        loading.value = false;
    }
};

watch(() => props.modelValue, (newVal) => {
    if (newVal) {
        resetForm();
    }
});
</script>
