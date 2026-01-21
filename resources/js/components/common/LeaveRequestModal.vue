<template>
    <div v-if="modelValue" class="fixed inset-0 z-[100] flex items-center justify-center p-4 animate-fade-in" @click.self="closeModal">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
        
        <!-- Modal Container -->
        <div class="wrapper relative z-10 w-full max-w-lg max-h-[90vh] flex flex-col bg-[#f0f2f5] rounded-xl overflow-hidden shadow-2xl" @click.stop>
            <!-- Purple Top Bar -->
            <div class="h-2.5 bg-[#673ab7] w-full shrink-0"></div>
            
            <!-- Scrollable Content -->
            <div class="overflow-y-auto p-6 space-y-4">
                <!-- Header Section -->
                <div class="bg-white p-6 rounded-lg border border-gray-300 shadow-sm relative">
                    <div class="heading text-2xl text-gray-800">{{ leaveType }} Request</div>
                    <p class="text-sm text-gray-600 mt-2" v-if="employeeName">For <span class="font-medium">{{ employeeName }}</span></p>
                    <hr class="my-4 border-gray-200"/>
                    <div class="text-xs text-[#d93025]" v-if="formError">* {{ formError }}</div>
                    <div class="text-xs text-gray-500" v-else>* Indicates required question</div>
                </div>

                <!-- Question Section -->
                <div class="bg-white p-6 rounded-lg border border-gray-300 shadow-sm">
                    <div class="mb-4">
                        <span class="text-base font-medium text-gray-800">Reason for Leave</span>
                        <span class="text-[#d93025] ml-1">*</span>
                    </div>
                    
                    <div class="space-y-3">
                        <div v-for="reason in leaveReasons" :key="reason">
                            <label class="flex items-center cursor-pointer py-2 group relative">
                                <input type="radio" v-model="form.selectedReason" :value="reason" name="leaveReason" class="peer absolute opacity-0">
                                <div class="relative w-5 h-5 border-2 border-[#5f6368] rounded-full peer-checked:border-[#673ab7] transition-all flex-shrink-0">
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="w-3 h-3 bg-[#673ab7] rounded-full opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></div>
                                    </div>
                                    <div class="absolute -inset-3 rounded-full bg-[#673ab7] opacity-0 group-hover:opacity-[0.04] transition-opacity"></div>
                                </div>
                                <span class="ml-3 text-sm text-gray-700 select-none">{{ reason }}</span>
                            </label>
                        </div>

                        <!-- Others Option -->
                        <div>
                            <label class="flex items-center cursor-pointer py-2 group relative">
                                <input type="radio" v-model="form.selectedReason" value="Others" name="leaveReason" class="peer absolute opacity-0">
                                <div class="relative w-5 h-5 border-2 border-[#5f6368] rounded-full peer-checked:border-[#673ab7] transition-all flex-shrink-0">
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="w-3 h-3 bg-[#673ab7] rounded-full opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></div>
                                    </div>
                                    <div class="absolute -inset-3 rounded-full bg-[#673ab7] opacity-0 group-hover:opacity-[0.04] transition-opacity"></div>
                                </div>
                                <span class="ml-3 text-sm text-gray-700 select-none">Others</span>
                            </label>
                            <div v-if="form.selectedReason === 'Others'" class="ml-8 mt-2">
                                <input 
                                    type="text" 
                                    v-model="form.otherReasonText" 
                                    class="w-full border-b border-gray-300 outline-none py-1 text-sm focus:border-[#673ab7] transition-colors bg-transparent placeholder-gray-400" 
                                    placeholder="Please specify reason..."
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Evidence Section -->
                <div class="bg-white p-6 rounded-lg border border-gray-300 shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-base font-medium text-gray-800">Evidence / Attachment</span>
                        <span class="text-xs text-gray-500">(Optional)</span>
                    </div>
                    <div class="border-2 border-dashed border-gray-200 rounded-lg p-6 hover:bg-gray-50 transition-colors text-center cursor-pointer relative group">
                        <input type="file" @change="handleFileUpload" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
                        <div class="group-hover:scale-105 transition-transform duration-200">
                            <i class="pi pi-cloud-upload text-3xl text-[#673ab7] mb-3 block"></i>
                            <span class="text-sm text-gray-600 block" v-if="!form.file">Drag file here or click to upload</span>
                            <span class="text-sm text-[#673ab7] font-medium block truncate px-4" v-else>{{ form.file.name }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="bg-[#f0f2f5] p-4 flex justify-between items-center shrink-0 border-t border-gray-200">
                <div @click="closeModal" class="btn-text cursor-pointer text-[#673ab7] text-sm font-medium hover:bg-[#673ab7]/10 px-4 py-2 rounded transition-colors">
                    Cancel
                </div>
                <button @click="submitForm" class="cursor-pointer bg-[#673ab7] text-white px-6 py-2 rounded text-sm font-medium hover:bg-[#5e35b1] shadow-sm transition-all active:scale-95">
                    Submit
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false
    },
    leaveType: {
        type: String,
        default: 'On Leave'
    },
    employeeName: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['update:modelValue', 'submit']);

const formError = ref('');
const form = ref({
    selectedReason: '',
    otherReasonText: '',
    file: null
});

const leaveReasons = [
    'Sick Leave',
    'Vacation Leave',
    'Personal Emergency',
    'Medical Appointment'
];

const closeModal = () => {
    emit('update:modelValue', false);
    resetForm();
};

const handleFileUpload = (event) => {
    form.value.file = event.target.files[0];
};

const submitForm = () => {
    if (!form.value.selectedReason) {
        formError.value = 'Please select a reason for leave.';
        return;
    }
    
    if (form.value.selectedReason === 'Others' && !form.value.otherReasonText) {
        formError.value = 'Please specify the reason.';
        return;
    }

    emit('submit', {
        reason: form.value.selectedReason === 'Others' ? form.value.otherReasonText : form.value.selectedReason,
        file: form.value.file
    });
    
    closeModal();
};

const resetForm = () => {
    form.value = {
        selectedReason: '',
        otherReasonText: '',
        file: null
    };
    formError.value = '';
};

// Reset form when modal opens
watch(() => props.modelValue, (newVal) => {
    if (newVal) {
        resetForm();
    }
});
</script>
