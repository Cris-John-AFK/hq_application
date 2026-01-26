<template>
    <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="font-bold text-gray-800 text-lg">Add New Employee</h3>
                <button 
                    @click="$emit('update:modelValue', false)"
                    class="p-2 hover:bg-gray-200 rounded-full transition-colors text-gray-400 hover:text-gray-600 cursor-pointer"
                >
                    <i class="pi pi-times"></i>
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input 
                        v-model="form.name" 
                        type="text" 
                        placeholder="e.g. John Doe"
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none transition-all"
                        required
                    >
                </div>

                <!-- Department -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                    <select 
                        v-model="form.department" 
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none transition-all bg-white appearance-none cursor-pointer"
                        required
                    >
                        <option value="" disabled>Select Department</option>
                        <option v-for="dept in departments" :key="dept" :value="dept">{{ dept }}</option>
                    </select>
                </div>

                <!-- Position & Access -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Position / Job Title</label>
                        <input 
                            v-model="form.position" 
                            type="text" 
                            placeholder="e.g. Software Engineer"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none transition-all"
                            required
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Account Type (Role)</label>
                        <select 
                            v-model="form.role" 
                            class="w-full px-3 py-2 border border-blue-200 bg-blue-50/50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white transition-all font-semibold text-blue-800"
                            required
                        >
                            <option value="user">Standard User</option>
                            <option value="admin">System Admin</option>
                        </select>
                        <p class="text-[10px] text-gray-400 mt-1">Standard = Employee Dashboard access</p>
                    </div>
                </div>

                <!-- ID & Status -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Employee ID</label>
                        <input 
                            v-model="form.id_number" 
                            type="text" 
                            placeholder="HQI-XXXX"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none transition-all uppercase"
                            required
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Employment Status</label>
                        <select 
                            v-model="form.employment_status" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none bg-white transition-all"
                            required
                        >
                            <option value="Probationary">Probationary</option>
                            <option value="Regular">Regular</option>
                        </select>
                    </div>
                </div>

                <!-- Info Message -->
                <div class="bg-blue-50 text-blue-700 p-4 rounded-xl text-sm">
                    <p class="font-medium">Auto-generated Credentials:</p>
                    <ul class="list-disc list-inside text-xs mt-1 opacity-80">
                        <li>Email: firstname@hq.app</li>
                        <li>Default Password: password</li>
                    </ul>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 mt-6">
                    <button 
                        type="button" 
                        @click="$emit('update:modelValue', false)"
                        class="cursor-pointer px-5 py-2.5 text-gray-600 hover:bg-gray-100 rounded-xl font-medium transition-colors"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        :disabled="loading"
                        class="cursor-pointer px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-medium shadow-lg shadow-teal-200 transition-all flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed"
                    >
                        <i v-if="loading" class="pi pi-spin pi-spinner"></i>
                        <span>{{ loading ? 'Creating...' : 'Create Employee' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive, watch } from 'vue';

const props = defineProps({
    modelValue: Boolean,
    loading: Boolean,
    departments: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['update:modelValue', 'submit']);

const form = reactive({
    name: '',
    department: 'Engineering',
    role: 'user',
    position: '',
    id_number: '',
    employment_status: 'Probationary'
});

// Reset form when modal opens
watch(() => props.modelValue, (newVal) => {
    if (newVal) {
        form.name = '';
        form.department = '';
        form.role = 'user';
        form.position = '';
        form.employment_status = 'Probationary';
    }
});

const handleSubmit = () => {
    emit('submit', { ...form });
};
</script>
