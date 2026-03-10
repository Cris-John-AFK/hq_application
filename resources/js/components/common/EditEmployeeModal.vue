<template>
    <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden animate-in fade-in zoom-in-95 duration-200 flex flex-col">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50 shrink-0">
                <div>
                    <h3 class="font-bold text-gray-800 text-lg">{{ isEditMode ? 'Edit Employee Details' : 'Employee Details' }}</h3>
                    <p class="text-xs text-gray-500">{{ isEditMode ? 'Update masterlist information.' : 'Detailed view of employee records.' }}</p>
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
                    <fieldset :disabled="!isEditMode" class="space-y-8">
                    
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
                            <!-- Working Hours Display Only (Updated via Import) -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Assigned Shift (Working Hours) <span class="text-xs text-gray-400 font-normal">(Via Import)</span></label>
                                <input v-model="form.working_hours" type="text" class="w-full px-3 py-2 border border-slate-200 bg-slate-50 text-slate-500 cursor-not-allowed rounded-lg text-sm outline-none font-mono" readonly placeholder="e.g. 7:00 AM - 3:00 PM">
                            </div>
                        </div>
                    </div>

                    <!-- NEW: Section 1.5: Leave Balances & Adjustments (outside fieldset so Adjust always works) -->
                    </fieldset>

                    <div>
                        <div class="flex justify-between items-center mb-4 border-b border-purple-100 pb-2">
                            <h4 class="text-sm font-bold text-purple-700 uppercase tracking-widest flex items-center gap-2">
                                <i class="pi pi-calendar-plus"></i> Leave Balances
                            </h4>
                            <span class="text-[10px] text-purple-500 font-bold bg-purple-50 px-2 py-1 rounded">Live Data · Hover to Adjust</span>
                        </div>
                        
                        <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3">
                            <div v-for="(label, key) in leaveTypes" :key="key" class="p-3 bg-white border border-gray-100 rounded-xl shadow-sm text-center relative group">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">{{ label }}</p>
                                <p class="text-2xl font-black text-gray-800">{{ form[key] ?? 0 }}</p>
                                
                                <!-- Hover Actions -->
                                <div class="absolute inset-x-0 bottom-0 top-0 bg-white/95 backdrop-blur-sm rounded-xl flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity p-2 hidden md:flex border border-purple-200 border-dashed">
                                    <button type="button" @click="openAdjustModal(key, label)" class="w-full h-full text-xs font-bold text-purple-600 bg-purple-50 hover:bg-purple-100 rounded-lg flex items-center justify-center gap-1 transition-colors border border-purple-100 cursor-pointer">
                                        <i class="pi pi-sliders-v"></i> Adjust
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile Adjust Hint -->
                        <div class="mt-3 block md:hidden text-center">
                            <p class="text-xs text-purple-600 font-bold mb-2">Tap a type to adjust:</p>
                            <div class="flex flex-wrap gap-2 justify-center">
                                <button v-for="(label, key) in leaveTypes" :key="key" type="button" @click="openAdjustModal(key, label)" class="text-[10px] font-bold text-purple-600 underline">{{ label }}</button>
                            </div>
                        </div>
                    </div>

                    <fieldset :disabled="!isEditMode" class="space-y-8">

                    <!-- Section 2: System Access (Promote/Demote) -->
                    <div v-if="form.role">
                        <h4 class="text-sm font-bold text-rose-700 uppercase tracking-widest mb-4 border-b border-rose-100 pb-2 flex items-center gap-2">
                             <i class="pi pi-shield"></i>
                             System Access & Permissions
                        </h4>
                        <div class="p-4 bg-rose-50 rounded-2xl border border-rose-100">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                                <div>
                                    <label class="block text-xs font-bold text-rose-900 uppercase tracking-wider mb-2">System Role (Privilege)</label>
                                    <select v-model="form.role" class="w-full px-4 py-2 bg-white border border-rose-200 rounded-xl text-sm font-bold text-gray-700 focus:ring-2 focus:ring-rose-500 outline-none appearance-none cursor-pointer">
                                        <option value="user">Regular Employee (Self Service)</option>
                                        <option value="dept_head">Department Head (Manager)</option>
                                        <option value="admin">System Administrator (Full Access)</option>
                                    </select>
                                    <p class="text-[10px] text-rose-600 mt-2 font-medium">
                                        Changing this will immediately update their access level. Promote to "Dept Head" if they need to manage leave for their department.
                                    </p>
                                </div>
                                <div class="hidden md:flex items-center gap-3 text-rose-700">
                                    <i class="pi pi-info-circle text-2xl"></i>
                                    <p class="text-[11px] font-medium leading-relaxed">
                                        Promoting an employee to **Admin** gives them full control over the system. 
                                        **Dept Heads** can only approve/reject leaves within their own department.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else>
                         <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 flex items-center gap-3">
                             <i class="pi pi-user-plus text-gray-400"></i>
                             <p class="text-[11px] text-gray-500 font-medium">This employee does not have a system account yet. Create one from the masterlist to manage their access roles.</p>
                         </div>
                    </div>



                    <!-- Section 3: Personal Information -->
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

                    </fieldset>
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
                    {{ isEditMode ? 'Cancel Edit' : 'Close' }}
                </button>
                <button 
                    v-if="!isEditMode"
                    type="button" 
                    @click="isEditMode = true"
                    class="cursor-pointer px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-md shadow-blue-200 transition-all flex items-center gap-2"
                >
                    <i class="pi pi-pencil"></i>
                    <span>Edit Details</span>
                </button>
                <button 
                    v-if="isEditMode"
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

        <!-- Adjust Balance Custom Sub-Modal -->
        <div v-if="adjustingLeave" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm shadow-2xl" @click.self="adjustingLeave = false">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden animate-in fade-in zoom-in duration-200">
                <div class="p-6 border-b border-purple-100 bg-purple-50/50">
                    <h3 class="text-lg font-black text-gray-800 tracking-tight">Adjust Balance</h3>
                    <p class="text-[11px] text-purple-600 font-bold uppercase mt-1">Manual specific adjustment for {{ adjustingContext.label }}</p>
                </div>
                
                <div class="p-6 space-y-4">
                    <div class="flex items-center gap-3 p-3 bg-purple-50 rounded-xl border border-purple-100 text-purple-700 justify-center">
                        <div class="text-center">
                            <p class="text-[10px] uppercase font-bold text-purple-400">Current Balance</p>
                            <span class="text-3xl font-black">{{ form[adjustingContext.key] || 0 }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="adjustingForm.action = 'add'" :class="adjustingForm.action === 'add' ? 'bg-teal-50 border-teal-500 text-teal-700 ring-1 ring-teal-500' : 'bg-gray-50 border-gray-200 text-gray-500'" class="p-3 rounded-lg border font-bold text-xs uppercase cursor-pointer transition-all">Add (+)</button>
                        <button type="button" @click="adjustingForm.action = 'deduct'" :class="adjustingForm.action === 'deduct' ? 'bg-rose-50 border-rose-500 text-rose-700 ring-1 ring-rose-500' : 'bg-gray-50 border-gray-200 text-gray-500'" class="p-3 rounded-lg border font-bold text-xs uppercase cursor-pointer transition-all">Deduct (-)</button>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Amount</label>
                        <input v-model.number="adjustingForm.amount" type="number" step="0.5" min="0.5" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-purple-500 bg-white font-mono" placeholder="0.5">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Justification / Reason (Required)</label>
                        <textarea v-model="adjustingForm.justification" rows="2" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-purple-500 bg-white resize-none" placeholder="Reason for adjustment..."></textarea>
                        <p class="text-[9px] text-gray-400 mt-1 italic">This action will be publicly logged in the system audit trail.</p>
                    </div>
                </div>

                <div class="p-4 bg-gray-50 border-t border-gray-100 flex gap-2">
                    <button type="button" @click="adjustingLeave = false" class="flex-1 px-4 py-2 text-xs font-black uppercase text-gray-500 hover:text-gray-700 transition-colors cursor-pointer bg-white border border-gray-200 rounded-xl">Cancel</button>
                    <button 
                        type="button"
                        @click="submitAdjustment" 
                        :disabled="isSubmittingAdjustment || !adjustingForm.amount || !adjustingForm.justification || adjustingForm.justification.length < 5"
                        class="flex-1 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <i v-if="isSubmittingAdjustment" class="pi pi-spin pi-spinner"></i>
                        <span>Confirm</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, watch, ref } from 'vue';
import axios from 'axios';

const isEditMode = ref(false);

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
    role: '',
    working_hours: '',
    
    // Leaves
    vacation_leave: 0,
    sick_leave: 0,
    paternity_leave: 0,
    solo_parent_leave: 0,
    bereavement_leave: 0,
    vawc_leave: 0,
    
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
        
        // Helper to format date for input type="date"
        const formatForInput = (dateStr) => {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            return d.toISOString().split('T')[0];
        };
        
        // Populate core
        form.employee_id = data.employee_id;
        form.department_id = data.department_id;
        form.position = data.position;
        form.employment_status = data.employment_status;
        form.date_hired = formatForInput(data.date_hired);
        form.email = data.email;
        form.working_hours = data.working_hours;
        
        // Clear existing dynamic leaves
        Object.keys(form).forEach(key => {
            if (key.endsWith('_leave')) delete form[key];
        });

        // Leaves based on active leaveTypes
        for (const typeKey of Object.keys(leaveTypes.value)) {
            form[typeKey] = data[typeKey] ?? 0;
        }

        form.last_name = data.last_name;
        form.first_name = data.first_name;
        form.middle_name = data.middle_name;
        form.role = data.user ? data.user.role : '';

        // Populate details
        form.birthdate = formatForInput(details.birthdate);
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

watch(() => props.modelValue, async (isOpen) => {
    if (isOpen && props.employee) {
        isEditMode.value = false;
        if (Object.keys(leaveTypes.value).length === 0) {
            await loadLeaveSettings();
        }
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
        role: form.role,
        
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

// Leave Adjustment Logic
const leaveTypes = ref({});

const loadLeaveSettings = async () => {
    try {
        const res = await axios.get('/api/settings/leave_types');
        const typesList = res.data;
        const newLeaveTypes = {};
        
        typesList.forEach(label => {
            // Generate valid DB column
            const typeLower = label.toLowerCase();
            let col = '';
            if (typeLower.includes('paternity')) col = 'paternity_leave';
            else if (typeLower.includes('solo')) col = 'solo_parent_leave';
            else if (typeLower.includes('bereavement')) col = 'bereavement_leave';
            else if (typeLower.includes('vawc') || typeLower.includes('vaws')) col = 'vawc_leave';
            else if (typeLower.includes('maternity')) col = 'maternity_leave';
            else if (typeLower.includes('magna') || typeLower.includes('carta')) col = 'magna_carta_leave';
            else if (typeLower.includes('emergency')) col = 'emergency_leave';
            else if (typeLower.includes('sick') || typeLower === 'sl') col = 'sick_leave';
            else if (typeLower.includes('vacation') || typeLower === 'vl') col = 'vacation_leave';
            else {
                col = typeLower.replace(/ *\([^)]*\) */g, "").trim().replace(/ /g, '_');
                if (!col.endsWith('_leave')) col += '_leave';
            }
            
            newLeaveTypes[col] = label;
        });
        
        leaveTypes.value = newLeaveTypes;
    } catch (error) {
        console.error('Failed to load leave types settings', error);
    }
};

const adjustingLeave = ref(false);
const isSubmittingAdjustment = ref(false);
const adjustingContext = reactive({ key: '', label: '' });
const adjustingForm = reactive({ action: 'add', amount: null, justification: '' });

const openAdjustModal = (key, label) => {
    adjustingContext.key = key;
    adjustingContext.label = label;
    adjustingForm.action = 'add';
    adjustingForm.amount = null;
    adjustingForm.justification = '';
    adjustingLeave.value = true;
};

const submitAdjustment = async () => {
    if (!adjustingForm.amount || !adjustingForm.justification) return;

    isSubmittingAdjustment.value = true;
    try {
        const res = await axios.post(`/api/employees/${props.employee.id}/adjust-leave`, {
            leave_type: adjustingContext.key,
            action: adjustingForm.action,
            amount: Number(adjustingForm.amount),
            justification: adjustingForm.justification
        });
        
        // Optimistically update form to show without refetching immediately
        form[adjustingContext.key] = res.data.employee[adjustingContext.key];
        
        // Give success feedback
        alert(res.data.message);
        adjustingLeave.value = false;
        
    } catch (error) {
        console.error("Adjustment Failed", error);
        alert(error.response?.data?.message || 'Failed to adjust balance.');
    } finally {
        isSubmittingAdjustment.value = false;
    }
};

const setEditMode = (mode) => {
    isEditMode.value = mode;
};

defineExpose({
    setEditMode
});
</script>
