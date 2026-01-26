<template>
    <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="font-bold text-gray-800 text-lg">Edit Employee Details</h3>
                <button 
                    @click="closeModal"
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
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none transition-all"
                            placeholder="e.g. Software Engineer"
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
                        <p class="text-[10px] text-gray-400 mt-1">Admin has full dashboard access</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Employee ID</label>
                        <input 
                            v-model="form.id_number" 
                            type="text" 
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

                <!-- Leave Credits Section -->
                <div class="border-t border-gray-200 pt-4 mt-2">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Leave Credits</h4>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Total Leave Credits (Days)</label>
                        <input 
                            v-model.number="form.leave_credits" 
                            type="number" 
                            step="0.5"
                            min="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none transition-all"
                            placeholder="e.g., 15"
                        >
                        <p class="text-xs text-gray-500 mt-1">Available leave days for this employee</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-between items-center gap-3 mt-6">
                    <button 
                        type="button" 
                        @click="handleChangePassword"
                        class="cursor-pointer px-4 py-2.5 text-gray-600 hover:bg-gray-100 rounded-xl font-medium transition-colors flex items-center gap-2 border border-gray-200"
                    >
                        <i class="pi pi-key text-sm"></i>
                        Change Password
                    </button>
                    <div class="flex gap-3">
                        <button 
                            type="button" 
                            @click="closeModal"
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
                            <span>{{ loading ? 'Updating...' : 'Save Changes' }}</span>
                        </button>
                    </div>
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
    modelValue: Boolean,
    loading: Boolean,
    employee: Object,
    departments: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['update:modelValue', 'submit', 'changePassword']);

const form = reactive({
    name: '',
    department: '',
    role: '',
    position: '',
    id_number: '',
    employment_status: '',
    leave_credits: 0
});

watch(() => props.employee, (emp) => {
    if (emp) {
        form.name = emp.name;
        form.department = emp.department;
        form.role = emp.role;
        form.position = emp.position;
        form.id_number = emp.id_number || emp.empId;
        form.employment_status = emp.employment_status || 'Probationary';
        form.leave_credits = emp.leave_credits || 0;
    }
}, { immediate: true });

const closeModal = () => {
    emit('update:modelValue', false);
};

const handleSubmit = () => {
    emit('submit', { id: props.employee.id, ...form });
};

const handleChangePassword = () => {
    emit('changePassword', props.employee);
};
</script>
