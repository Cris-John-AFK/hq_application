<template>
    <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden animate-in fade-in zoom-in-95 duration-200 flex flex-col">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50 shrink-0">
                <div>
                    <h3 class="font-bold text-gray-800 text-lg">Edit Employee Details</h3>
                    <p class="text-xs text-gray-500">Update masterlist information.</p>
                </div>
                <button 
                    @click="closeModal"
                    class="p-2 hover:bg-gray-200 rounded-full transition-colors text-gray-400 hover:text-gray-600 cursor-pointer"
                >
                    <i class="pi pi-times"></i>
                </button>
            </div>

            <!-- Scrollable Content -->
            <div class="overflow-y-auto p-6 space-y-8 flex-1" v-if="!fetchingDetails">
                <form id="editEmployeeForm" @submit.prevent="handleSubmit">
                    
                    <!-- Section 1: Employment Details -->
                    <div>
                        <h4 class="text-sm font-bold text-teal-700 uppercase tracking-widest mb-4 border-b border-teal-100 pb-2">Employment Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Employee ID <span class="text-red-500">*</span></label>
                                <input v-model="form.employee_id" type="text" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-gray-50 cursor-not-allowed" readonly>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Department <span class="text-red-500">*</span></label>
                                <select v-model="form.department_id" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 outline-none bg-white" required>
                                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Position <span class="text-red-500">*</span></label>
                                <input v-model="form.position" type="text" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 outline-none" required>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Employment Status <span class="text-red-500">*</span></label>
                                <select v-model="form.employment_status" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 outline-none bg-white" required>
                                    <option value="Regular">Regular</option>
                                    <option value="Probationary">Probationary</option>
                                    <option value="Contractual">Contractual</option>
                                    <option value="Resigned">Resigned</option>
                                    <option value="Terminated">Terminated</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Date Hired <span class="text-red-500">*</span></label>
                                <input v-model="form.date_hired" type="date" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 outline-none" required>
                            </div>
                             <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Company Email</label>
                                <input v-model="form.email" type="email" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 outline-none">
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
            
            <!-- Loading State -->
            <div v-else class="flex-1 flex items-center justify-center">
                 <div class="flex flex-col items-center">
                    <i class="pi pi-spin pi-spinner text-3xl text-teal-600 mb-2"></i>
                    <p class="text-gray-500 text-sm">Loading details...</p>
                 </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex justify-end gap-3 shrink-0">
                <button 
                    type="button" 
                    @click="closeModal"
                    class="cursor-pointer px-5 py-2.5 text-gray-600 hover:bg-gray-100 rounded-xl font-medium transition-colors"
                >
                    Cancel
                </button>
                <button 
                    type="submit" 
                    form="editEmployeeForm"
                    :disabled="loading || fetchingDetails"
                    class="cursor-pointer px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-bold shadow-md shadow-teal-200 transition-all flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed"
                >
                    <i v-if="loading" class="pi pi-spin pi-spinner"></i>
                    <span>{{ loading ? 'Saving...' : 'Save Changes' }}</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, watch, ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    modelValue: Boolean,
    loading: Boolean,
    employee: Object, // The summary object from list
    departments: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['update:modelValue', 'submit']);
const fetchingDetails = ref(false);

const form = reactive({
    // Core
    employee_id: '',
    department_id: '',
    position: '',
    employment_status: '',
    date_hired: '',
    email: '',
    
    // Personal
    last_name: '',
    first_name: '',
    middle_name: '',
    birthdate: '',
    gender: '',
    civil_status: '',
    
    // Gov
    sss_number: '',
    philhealth_number: '',
    pagibig_number: '',
    tin_number: '',
});

const fetchDetails = async (id) => {
    fetchingDetails.value = true;
    try {
        const response = await axios.get(`/api/employees/${id}`);
        const data = response.data;
        const details = data.details || {};
        
        // Populate core
        form.employee_id = data.employee_id;
        form.department_id = data.department_id;
        form.position = data.position;
        form.employment_status = data.employment_status;
        form.date_hired = data.date_hired;
        form.email = data.email;
        form.last_name = data.last_name;
        form.first_name = data.first_name;
        form.middle_name = data.middle_name;

        // Populate details
        form.birthdate = details.birthdate;
        form.gender = details.gender;
        form.civil_status = details.civil_status;
        form.sss_number = details.sss_number;
        form.philhealth_number = details.philhealth_number;
        form.pagibig_number = details.pagibig_number;
        form.tin_number = details.tin_number;

    } catch (error) {
        console.error("Failed to fetch details", error);
        alert("Failed to load employee details.");
        closeModal();
    } finally {
        fetchingDetails.value = false;
    }
};

watch(() => props.modelValue, (isOpen) => {
    if (isOpen && props.employee) {
        fetchDetails(props.employee.id);
    }
});

const closeModal = () => {
    emit('update:modelValue', false);
};

const handleSubmit = () => {
    const payload = {
        id: props.employee.id,
        first_name: form.first_name,
        last_name: form.last_name,
        middle_name: form.middle_name,
        department_id: form.department_id,
        position: form.position,
        employment_status: form.employment_status,
        date_hired: form.date_hired,
        email: form.email,
        
        details: {
            birthdate: form.birthdate,
            gender: form.gender,
            civil_status: form.civil_status,
            sss_number: form.sss_number,
            philhealth_number: form.philhealth_number,
            pagibig_number: form.pagibig_number,
            tin_number: form.tin_number,
        }
    };
    
    emit('submit', payload);
};
</script>
