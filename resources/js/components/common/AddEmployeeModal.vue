<template>
    <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden animate-in fade-in zoom-in-95 duration-200 flex flex-col">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50 shrink-0">
                <div>
                    <h3 class="font-bold text-gray-800 text-lg">Add New Employee to Masterlist</h3>
                    <p class="text-xs text-gray-500">All fields are required unless optional.</p>
                </div>
                <button 
                    @click="$emit('update:modelValue', false)"
                    class="p-2 hover:bg-gray-200 rounded-full transition-colors text-gray-400 hover:text-gray-600 cursor-pointer"
                >
                    <i class="pi pi-times"></i>
                </button>
            </div>

            <!-- Scrollable Content -->
            <div class="overflow-y-auto p-6 space-y-8 flex-1">
                <form id="addEmployeeForm" @submit.prevent="handleSubmit">
                    
                    <!-- Section 1: Employment Details -->
                    <div>
                        <h4 class="text-sm font-bold text-teal-700 uppercase tracking-widest mb-4 border-b border-teal-100 pb-2">Employment Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Employee ID <span class="text-red-500">*</span></label>
                                <input 
                                    v-model.number="form.employee_id" 
                                    type="number" 
                                    placeholder="Enter digits only" 
                                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 outline-none" 
                                    required
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Department <span class="text-red-500">*</span></label>
                                <select v-model="form.department_id" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 outline-none bg-white" required>
                                    <option value="" disabled>Select Department</option>
                                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Position <span class="text-red-500">*</span></label>
                                <input v-model="form.position" type="text" placeholder="Job Title" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 outline-none" required>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Employment Status <span class="text-red-500">*</span></label>
                                <select v-model="form.employment_status" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 outline-none bg-white" required>
                                    <option value="Regular">Regular</option>
                                    <option value="Probationary">Probationary</option>
                                    <option value="Contractual">Contractual</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Date Hired <span class="text-red-500">*</span></label>
                                <input v-model="form.date_hired" type="date" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 outline-none" required>
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-widest mb-3 flex items-center gap-2">
                                    <i class="pi pi-clock text-teal-600"></i>
                                    Assigned Shift (Working Hours) <span class="text-red-500">*</span>
                                </label>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <button 
                                        v-for="shift in shifts" 
                                        :key="shift.code"
                                        type="button"
                                        @click="form.working_hours = shift.time"
                                        :class="form.working_hours === shift.time ? 'border-teal-600 bg-teal-600 text-white shadow-xl shadow-teal-100 scale-[1.05] z-10' : 'border-gray-200 bg-white text-gray-600 hover:border-teal-400 hover:text-teal-700'"
                                        class="flex items-center gap-4 p-4 rounded-2xl border transition-all cursor-pointer group relative overflow-hidden"
                                    >
                                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-lg font-black shrink-0 transition-all shadow-sm" :class="form.working_hours === shift.time ? 'bg-white text-teal-600' : 'bg-teal-50 text-teal-700 group-hover:scale-110'">
                                            {{ shift.code }}
                                        </div>
                                        <div class="flex flex-col items-start min-w-0">
                                            <p class="text-[10px] font-black uppercase tracking-widest opacity-60 mb-0.5" :class="form.working_hours === shift.time ? 'text-white' : 'text-gray-400'">Assigned Time</p>
                                            <span class="text-sm font-black tracking-tight whitespace-nowrap">{{ shift.time }}</span>
                                        </div>
                                    </button>
                                    
                                    <!-- Custom / Other Option -->
                                    <div class="relative group-focus-within:ring-2 ring-teal-500 rounded-2xl bg-slate-50 border border-slate-200 p-1 flex items-center">
                                        <div class="w-10 h-10 flex items-center justify-center shrink-0 text-slate-400">
                                            <i class="pi pi-pencil"></i>
                                        </div>
                                        <input 
                                            v-model="form.working_hours" 
                                            type="text" 
                                            placeholder="Manual Edit"
                                            class="w-full h-12 bg-transparent text-xs font-bold font-mono outline-none pr-4"
                                        >
                                    </div>
                                </div>
                            </div>


                        </div>
                    </div>

                    <!-- Section 2: Personal Information -->
                    <div>
                        <h4 class="text-sm font-bold text-teal-700 uppercase tracking-widest mb-4 border-b border-teal-100 pb-2">Personal Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="md:col-span-1">
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Last Name <span class="text-red-500">*</span></label>
                                <input v-model="form.last_name" type="text" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 outline-none uppercase" required>
                            </div>
                            <div class="md:col-span-1">
                                <label class="block text-xs font-semibold text-gray-600 mb-1">First Name <span class="text-red-500">*</span></label>
                                <input v-model="form.first_name" type="text" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 outline-none uppercase" required>
                            </div>
                            <div class="md:col-span-1">
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Middle Name</label>
                                <input v-model="form.middle_name" type="text" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 outline-none uppercase">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Birthdate <span class="text-red-500">*</span></label>
                                <input v-model="form.birthdate" type="date" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 outline-none" required>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Gender <span class="text-red-500">*</span></label>
                                <select v-model="form.gender" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 outline-none bg-white" required>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Civil Status</label>
                                <select v-model="form.civil_status" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 outline-none bg-white">
                                    <option value="Single">Single</option>
                                    <option value="Married">Married</option>
                                    <option value="Widowed">Widowed</option>
                                    <option value="Separated">Separated</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Government Numbers -->
                    <div>
                        <h4 class="text-sm font-bold text-teal-700 uppercase tracking-widest mb-4 border-b border-teal-100 pb-2">Government ID Numbers</h4>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">SSS No.</label>
                                <input v-model="form.sss_number" type="text" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">PhilHealth No.</label>
                                <input v-model="form.philhealth_number" type="text" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Pag-IBIG No.</label>
                                <input v-model="form.pagibig_number" type="text" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">TIN</label>
                                <input v-model="form.tin_number" type="text" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 outline-none">
                            </div>
                        </div>
                    </div>

                </form>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex justify-end gap-3 shrink-0">
                <button 
                    type="button" 
                    @click="$emit('update:modelValue', false)"
                    class="cursor-pointer px-5 py-2.5 text-gray-600 hover:bg-gray-100 rounded-xl font-medium transition-colors"
                >
                    Cancel
                </button>
                <button 
                    type="submit" 
                    form="addEmployeeForm"
                    :disabled="loading"
                    class="cursor-pointer px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-bold shadow-md shadow-teal-200 transition-all flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed"
                >
                    <i v-if="loading" class="pi pi-spin pi-spinner"></i>
                    <span>{{ loading ? 'Saving...' : 'Save Employee' }}</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, watch, ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    modelValue: Boolean,
    loading: Boolean,
    departments: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['update:modelValue', 'submit']);

const shifts = ref([]);

const loadShifts = async () => {
    try {
        const res = await axios.get('/api/settings/working_hours');
        shifts.value = res.data || [];
    } catch (e) {
        console.error("Failed to load shifts", e);
    }
};

onMounted(() => {
    loadShifts();
});

const form = reactive({
    // Core
    employee_id: '',
    department_id: '',
    position: '',
    employment_status: 'Probationary',
    date_hired: '',
    working_hours: '08:00 AM - 04:00 PM',
    email: '',
    leave_credits: 5,
    
    // Personal
    last_name: '',
    first_name: '',
    middle_name: '',
    birthdate: '',
    gender: 'Male',
    civil_status: 'Single',
    
    // Gov
    sss_number: '',
    philhealth_number: '',
    pagibig_number: '',
    tin_number: '',
});

// Reset form when modal opens
watch(() => props.modelValue, (newVal) => {
    if (newVal) {
        Object.keys(form).forEach(key => form[key] = '');
        form.employment_status = 'Probationary';
        form.gender = 'Male';
        form.civil_status = 'Single';
        form.leave_credits = 5;
        if(shifts.value.length > 0) {
            form.working_hours = shifts.value[0].time;
        } else {
            form.working_hours = '08:00 AM - 04:00 PM';
        }
        loadShifts(); // Refresh in case it was modified
    }
});

const handleSubmit = () => {
    emit('submit', { ...form });
};
</script>
