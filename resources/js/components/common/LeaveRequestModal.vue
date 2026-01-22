<template>
    <div v-if="modelValue" class="fixed inset-0 z-[100] flex items-center justify-center p-4 animate-fade-in" @click.self="closeModal">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
        
        <!-- Modal Container -->
        <div class="wrapper relative z-10 w-full max-w-2xl max-h-[90vh] flex flex-col bg-[#f0f2f5] rounded-xl overflow-hidden shadow-2xl" @click.stop>
            <!-- Purple Top Bar -->
            <div class="h-2.5 bg-[#673ab7] w-full shrink-0"></div>
            
            <!-- Scrollable Content -->
            <div class="overflow-y-auto p-6 space-y-4">
                <!-- Header Section -->
                <div class="bg-white p-6 rounded-lg border border-gray-300 shadow-sm relative">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">Personnel Leave Authorization Form</h2>
                            <p class="text-sm text-gray-600 mt-1">Please fill out all required details accurately.</p>
                        </div>
                        <div class="text-right">
                             <label class="block text-xs font-bold text-gray-500 uppercase">Date Filed</label>
                             <input type="date" v-model="form.dateFiled" class="text-sm border-b border-gray-300 focus:border-purple-600 outline-none py-1 text-gray-800 font-medium text-right">
                        </div>
                    </div>
                    
                    <hr class="my-4 border-gray-200"/>
                    
                    <!-- Employee Info Preview (Read Only) -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-2" v-if="user">
                        <div>
                            <span class="block text-xs text-gray-500 uppercase">Name</span>
                            <span class="font-bold text-gray-800">{{ user.name }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-gray-500 uppercase">Employee No.</span>
                            <span class="font-bold text-gray-800">HQI-{{ String(user.id).padStart(4, '0') }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-gray-500 uppercase">Position</span>
                            <span class="font-bold text-gray-800">{{ user.position || 'N/A' }}</span>
                        </div>
                         <div>
                            <span class="block text-xs text-gray-500 uppercase">Status</span>
                            <span class="inline-block px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 text-xs font-bold">{{ user.employment_status || 'Probationary' }}</span>
                        </div>
                    </div>

                    <div class="text-xs text-[#d93025]" v-if="formError">* {{ formError }}</div>
                    <div class="text-xs text-gray-500" v-else>* Indicates required fields</div>
                </div>

                <!-- 1. Request For -->
                <div class="bg-white p-6 rounded-lg border border-gray-300 shadow-sm">
                    <div class="mb-3">
                        <span class="text-base font-medium text-gray-800">Leave Request For</span>
                        <span class="text-[#d93025] ml-1">*</span>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <label v-for="type in requestTypes" :key="type" class="flex items-center cursor-pointer p-2 rounded hover:bg-purple-50 transition-colors border border-transparent hover:border-purple-100">
                             <div class="relative flex items-center">
                                <input type="radio" v-model="form.requestType" :value="type" name="requestType" class="peer h-4 w-4 cursor-pointer text-purple-600 focus:ring-purple-500 border-gray-300">
                            </div>
                            <span class="ml-2 text-sm text-gray-700 font-medium">{{ type }}</span>
                        </label>
                    </div>
                </div>

                <!-- 2. Leave Type -->
                <div class="bg-white p-6 rounded-lg border border-gray-300 shadow-sm">
                    <div class="mb-3">
                        <span class="text-base font-medium text-gray-800">Leave Type</span>
                        <span class="text-[#d93025] ml-1">*</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3">
                        <label v-for="lType in leaveTypes" :key="lType" class="flex items-center cursor-pointer group">
                             <input type="radio" v-model="form.leaveType" :value="lType" name="leaveType" class="peer h-4 w-4 cursor-pointer text-purple-600 focus:ring-purple-500 border-gray-300">
                             <span class="ml-2 text-sm text-gray-700 group-hover:text-purple-700 transition-colors">{{ lType }}</span>
                        </label>
                        
                        <!-- Others -->
                        <div class="col-span-1 md:col-span-2 mt-2">
                             <label class="flex items-center cursor-pointer group mb-2">
                                 <input type="radio" v-model="form.leaveType" value="Others" name="leaveType" class="peer h-4 w-4 cursor-pointer text-purple-600 focus:ring-purple-500 border-gray-300">
                                 <span class="ml-2 text-sm text-gray-700 group-hover:text-purple-700 font-medium">Others (Please Specify)</span>
                            </label>
                            <transition name="fade">
                                <input 
                                    v-if="form.leaveType === 'Others'"
                                    type="text" 
                                    v-model="form.otherLeaveType" 
                                    class="w-full border-b-2 border-gray-200 outline-none py-1.5 text-sm focus:border-[#673ab7] transition-colors bg-gray-50 px-2 rounded-t" 
                                    placeholder="Specify leave type..." 
                                    ref="otherInput"
                                >
                            </transition>
                        </div>
                    </div>
                </div>

                <!-- 3. Details of Leave -->
                <div class="bg-white p-6 rounded-lg border border-gray-300 shadow-sm">
                    <div class="mb-4 text-base font-medium text-gray-800">Details of Leave / Halfday / Undertime</div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Date Range -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">From Date <span class="text-red-500">*</span></label>
                                <input type="date" v-model="form.fromDate" class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">To Date <span class="text-red-500">*</span></label>
                                <input type="date" v-model="form.toDate" :min="form.fromDate" class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition-all">
                            </div>
                        </div>

                        <!-- Duration Metrics -->
                        <div class="space-y-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
                             <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">No. of Days</label>
                                <div class="flex items-center gap-2">
                                    <input type="number" step="0.5" v-model="form.numberOfDays" class="w-24 p-2 border border-gray-300 rounded font-bold text-gray-800 text-center focus:ring-purple-500 outline-none">
                                    <span class="text-sm text-gray-500">days</span>
                                </div>
                            </div>
                            
                            <!-- Conditional Hours input for Undertime/Halfday -->
                            <transition name="slide-fade">
                                <div v-if="['Undertime', 'Halfday'].includes(form.requestType)">
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1 mt-3">No. of Hours</label>
                                    <div class="flex items-center gap-2">
                                        <input type="number" step="0.5" v-model="form.numberOfHours" class="w-24 p-2 border border-gray-300 rounded font-bold text-gray-800 text-center focus:ring-purple-500 outline-none">
                                        <span class="text-sm text-gray-500">hours</span>
                                    </div>
                                    
                                    <div class="mt-3 grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="text-[10px] uppercase text-gray-400 font-bold">Start Time</label>
                                            <input type="time" v-model="form.startTime" class="w-full p-1.5 text-sm border rounded">
                                        </div>
                                         <div>
                                            <label class="text-[10px] uppercase text-gray-400 font-bold">End Time</label>
                                            <input type="time" v-model="form.endTime" class="w-full p-1.5 text-sm border rounded">
                                        </div>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </div>
                </div>

                <!-- 4. Reason -->
                <div class="bg-white p-6 rounded-lg border border-gray-300 shadow-sm">
                    <div class="mb-3">
                        <span class="text-base font-medium text-gray-800">Reason for Leave / Halfday / Undertime</span>
                        <span class="text-[#d93025] ml-1">*</span>
                    </div>
                    <textarea 
                        v-model="form.reason" 
                        rows="4" 
                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition-shadow resize-none placeholder-gray-400 text-sm"
                        placeholder="Please state the specific reason for your request..."
                    ></textarea>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="bg-[#f0f2f5] p-4 flex justify-between items-center shrink-0 border-t border-gray-200">
                <div @click="closeModal" class="btn-text cursor-pointer text-[#673ab7] text-sm font-bold uppercase hover:bg-[#673ab7]/10 px-4 py-2 rounded transition-colors tracking-wide">
                    Cancel
                </div>
                <button 
                    @click="submitForm" 
                    :disabled="loading"
                    class="cursor-pointer bg-[#673ab7] text-white px-8 py-2.5 rounded shadow-md text-sm font-bold uppercase hover:bg-[#5e35b1] hover:shadow-lg transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                >
                    <i v-if="loading" class="pi pi-spin pi-spinner"></i>
                    <span>Submit Request</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, computed, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { storeToRefs } from 'pinia';

const props = defineProps({
    modelValue: { type: Boolean, default: false }
});

const emit = defineEmits(['update:modelValue', 'submit']);
const authStore = useAuthStore();
const { user } = storeToRefs(authStore);

onMounted(() => {
    if (!user.value) authStore.fetchUser();
});

const formError = ref('');
const loading = ref(false);

const form = ref({
    dateFiled: new Date().toISOString().split('T')[0],
    requestType: 'Leave',
    leaveType: '',
    otherLeaveType: '',
    fromDate: '',
    toDate: '',
    numberOfDays: 1,
    numberOfHours: 0,
    startTime: '',
    endTime: '',
    reason: ''
});

const requestTypes = ['Leave', 'Halfday', 'Undertime', 'Official Business'];
const leaveTypes = ['SIL', 'Solo Parent', 'Maternity', 'VAWS', 'Paternity', 'Magna Carta', 'Emergency'];

// Auto-calc number of days
watch([() => form.value.fromDate, () => form.value.toDate], ([start, end]) => {
    if (start && end) {
        const s = new Date(start);
        const e = new Date(end);
        const diffTime = Math.abs(e - s);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; 
        if (!isNaN(diffDays) && diffDays > 0) {
            form.value.numberOfDays = diffDays;
        }
    }
});

const closeModal = () => {
    emit('update:modelValue', false);
    resetForm();
};

const submitForm = () => {
    formError.value = '';
    
    // Validation
    if (!form.value.leaveType) return setError('Please select a leave type.');
    if (form.value.leaveType === 'Others' && !form.value.otherLeaveType) return setError('Please specify the "Others" leave type.');
    if (!form.value.fromDate || !form.value.toDate) return setError('Please select the date range.');
    if (!form.value.reason) return setError('Please provide a reason.');

    loading.value = true;

    // Simulate Backend Delay or emit immediately
    setTimeout(() => {
        emit('submit', {
            ...form.value,
            // Consolidate 'Others' input
            leaveType: form.value.leaveType === 'Others' ? form.value.otherLeaveType : form.value.leaveType,
            date_filed: form.value.dateFiled // Ensure snake_case for backend if needed
        });
        loading.value = false;
        closeModal();
    }, 500);
};

const setError = (msg) => {
    formError.value = msg;
    // Scroll to top or highlight?
};

const resetForm = () => {
    form.value = {
        dateFiled: new Date().toISOString().split('T')[0],
        requestType: 'Leave',
        leaveType: '',
        otherLeaveType: '',
        fromDate: '',
        toDate: '',
        numberOfDays: 1,
        numberOfHours: 0,
        startTime: '',
        endTime: '',
        reason: ''
    };
    formError.value = '';
};
</script>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.2s ease-out;
}
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style>
