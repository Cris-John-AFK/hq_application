<template>
    <div class="min-h-screen bg-[#f0f2f5] py-10 px-4 flex justify-center w-full">
        
        <div class="flex flex-col md:flex-row gap-8 max-w-5xl w-full">
            
            <!-- Left Side: Main Content (The Form or History) -->
            <div class="flex-1 w-full min-w-0 max-w-3xl">
                
                <div class="flex items-center gap-4 mb-6 px-2">
                    <img src="/logo.png" alt="Logo" class="w-14 h-14 drop-shadow-sm">
                    <h1 class="text-2xl font-black text-gray-800 tracking-tight">Employee Portal</h1>
                </div>

                <transition name="fade" mode="out-in">
                    <div v-if="activeTab === 'form'" key="form">
                        <!-- Reusable Leave Request Component in "Portal Mode" -->
                        <div v-if="successMsg" class="mb-6 bg-emerald-50 border-emerald-200 border-2 text-emerald-700 px-4 py-3 rounded-xl flex shadow-sm items-center gap-3">
                            <i class="pi pi-check-circle text-lg shrink-0"></i>
                            <div>
                                <p class="font-bold text-sm">Successfully Submitted</p>
                                <p class="text-xs opacity-90">{{ successMsg }}</p>
                            </div>
                        </div>

                        <LeaveRequestModal 
                            :modelValue="true" 
                            :isAdminMode="false" 
                            :isPortalMode="true"
                            :employeeData="employee"
                            :initialData="editData"
                            :isEdit="!!editData"
                            @submit="handleFormSubmit"
                            @update="handleFormUpdate"
                        />
                    </div>

                    <div v-else-if="activeTab === 'history'" key="history" class="w-full bg-white shadow-2xl rounded-xl border border-gray-200 p-6 md:p-8 animate-in fade-in zoom-in-95 duration-200">
                        <h3 class="text-xl font-black text-gray-800 mb-6 pb-4 border-b border-gray-100 flex items-center gap-3">
                            <i class="pi pi-history text-[#673ab7] text-2xl"></i> My Leave History
                        </h3>

                        <div class="space-y-4">
                            <div v-if="leaveHistory.length === 0" class="text-center py-10 bg-gray-50 rounded-lg border border-gray-200">
                                <p class="text-sm font-bold text-gray-500">No past leave forms found.</p>
                            </div>

                            <div v-for="leave in leaveHistory" :key="leave.id" 
                                @click="leave.status === 'Pending' ? startEdit(leave) : null"
                                :class="[
                                    'p-5 border border-gray-200 rounded-xl transition-all group bg-white flex flex-col md:flex-row justify-between gap-4',
                                    leave.status === 'Pending' ? 'cursor-pointer hover:border-[#673ab7] hover:shadow-md hover:bg-purple-50/20 shadow-sm' : 'cursor-default'
                                ]">
                                <div>
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest"
                                            :class="leave.status === 'Approved' ? 'bg-emerald-100 text-emerald-700' :
                                                    leave.status === 'Pending' ? 'bg-amber-100 text-amber-700' :
                                                    leave.status === 'Rejected' ? 'bg-rose-100 text-rose-700' :
                                                    'bg-gray-100 text-gray-700'">
                                            {{ leave.status }}
                                        </span>
                                        <span class="text-xs font-bold text-gray-400">Filed {{ formatDate(leave.date_filed) }}</span>
                                    </div>
                                    <h4 class="text-base font-bold text-gray-800">{{ leave.leave_type }}</h4>
                                    <p class="text-sm text-gray-600 truncate max-w-sm">{{ leave.reason }}</p>
                                    
                                    <p v-if="leave.status === 'Pending'" class="mt-3 text-[10px] font-black uppercase tracking-widest text-[#673ab7] flex items-center gap-1.5 opacity-60 group-hover:opacity-100 transition-opacity">
                                        <i class="pi pi-pencil"></i> Click anywhere to edit this form
                                    </p>
                                </div>
                                <div class="text-left md:text-right overflow-hidden mt-1 md:mt-0 pt-3 md:pt-0 border-t border-gray-50 md:border-0">
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Duration</p>
                                    <p class="text-sm font-bold text-gray-800">{{ formatDate(leave.from_date) }}</p>
                                    <p class="text-xs font-bold text-[#673ab7] mt-1">{{ leave.days_taken }} Day(s)</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </transition>
            </div>
            
            <!-- Right Side: Sidebar Navigation Tabs -->
            <div class="w-full md:w-56 shrink-0 mt-[80px] flex flex-col gap-3">
                
                <!-- Profile Box -->
                <div v-if="employee" class="bg-white rounded-xl shadow-md p-4 flex flex-col items-center text-center border border-gray-100 mb-2">
                    <div class="w-16 h-16 rounded-full bg-purple-50 flex items-center justify-center text-purple-600 shadow-inner mb-3">
                        <i class="pi pi-user text-2xl"></i>
                    </div>
                    <h2 class="text-sm font-black text-gray-800 uppercase tracking-tight">{{ employee.first_name }} {{ employee.last_name }}</h2>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">{{ employee.position || 'Employee' }}</p>
                    <div class="mt-3 px-3 py-1 bg-gray-50 rounded-lg border border-gray-100 w-full">
                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest block">ID Number</span>
                        <span class="text-xs font-black text-purple-600 block">{{ employee.employee_id }}</span>
                    </div>
                </div>

                <button @click="openNewForm" :class="['w-full py-4 px-6 text-sm font-black uppercase tracking-widest rounded-xl transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer', activeTab === 'form' && !editData ? 'bg-[#673ab7] hover:bg-[#5e35b1] text-white active:scale-95' : 'bg-white text-[#673ab7] hover:bg-purple-50']">
                    NEW LEAVE FORM
                </button>
                <button @click="activeTab = 'history'" :class="['w-full py-4 px-6 text-sm font-black uppercase tracking-widest rounded-xl transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer', activeTab === 'history' ? 'bg-[#673ab7] hover:bg-[#5e35b1] text-white active:scale-95' : 'bg-white text-[#673ab7] hover:bg-purple-50']">
                    FORM HISTORY
                </button>
                
                <div class="h-4"></div> <!-- visual separator -->
                
                <button @click="logout" class="w-full py-3 px-6 text-sm font-black uppercase tracking-widest rounded-xl transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer bg-white text-rose-500 hover:bg-rose-50 hover:text-rose-600 active:scale-95 border border-rose-100 mb-2">
                    LOGOUT
                </button>
                
                <div class="text-center">
                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-1">Session Auto-Logout In:</p>
                    <p class="text-sm font-black text-rose-400 tabular-nums">{{ formatTimeLeft(timeLeft) }}</p>
                </div>
            </div>
            
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import LeaveRequestModal from '../components/common/LeaveRequestModal.vue';

const activeTab = ref('form');
const employee = ref(null);
const leaveHistory = ref([]);
const birthdateCred = ref('');
const successMsg = ref('');
const editData = ref(null);

const SESSION_TIMEOUT = 5 * 60; // 5 minutes in seconds
const timeLeft = ref(SESSION_TIMEOUT);
let countdownInterval = null;

const resetTimer = () => {
    timeLeft.value = SESSION_TIMEOUT;
};

onMounted(async () => {
    const activeEmp = localStorage.getItem('hq_employee_portal_id');
    const bdCheck = localStorage.getItem('hq_employee_portal_bd');
    
    if (!activeEmp || !bdCheck) {
        logout();
        return;
    }
    
    birthdateCred.value = bdCheck;

    try {
        // Re-verify and fetch history
        const { data } = await axios.post('/api/employee-portal/login', {
            employee_id: activeEmp,
            birthdate: bdCheck
        });

        employee.value = data.employee;
        leaveHistory.value = data.leave_history || [];
        
        // Start session countdown
        startCountdown();
        
        // Setup activity listeners
        document.addEventListener('mousemove', resetTimer);
        document.addEventListener('keydown', resetTimer);
        document.addEventListener('click', resetTimer);
        document.addEventListener('scroll', resetTimer);
    } catch (e) {
        logout(); // Kick out if tampering
    }
});

onUnmounted(() => {
    stopCountdown();
    document.removeEventListener('mousemove', resetTimer);
    document.removeEventListener('keydown', resetTimer);
    document.removeEventListener('click', resetTimer);
    document.removeEventListener('scroll', resetTimer);
});

const startCountdown = () => {
    stopCountdown();
    countdownInterval = setInterval(() => {
        timeLeft.value--;
        if (timeLeft.value <= 0) {
            stopCountdown();
            logout();
        }
    }, 1000);
};

const stopCountdown = () => {
    if (countdownInterval) {
        clearInterval(countdownInterval);
        countdownInterval = null;
    }
};

const formatTimeLeft = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
};

const logout = () => {
    localStorage.removeItem('hq_employee_portal_id');
    localStorage.removeItem('hq_employee_portal_bd');
    window.location.href = '/login';
};

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return isNaN(d) ? dateStr : d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const openNewForm = () => {
    editData.value = null;
    activeTab.value = 'form';
    successMsg.value = '';
};

const startEdit = (leave) => {
    editData.value = leave;
    activeTab.value = 'form';
    successMsg.value = '';
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const handleFormSubmit = async (payload, resolve, reject) => {
    successMsg.value = '';
    
    // Supplement payload with auth info
    const finalPayload = {
        ...payload,
        employee_id: employee.value?.id,
        birthdate: birthdateCred.value,
    };

    try {
        const { data } = await axios.post('/api/employee-portal/submit-leave', finalPayload);
        successMsg.value = 'Your leave request has been sent to the HR successfully.';
        
        leaveHistory.value.unshift(data.leave); // Update history live
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
        setTimeout(() => { successMsg.value = ''; }, 5000);
        
        resolve(); // Tells the modal to clear its loading state and reset
    } catch (error) {
        reject(error.response?.data?.message || 'Failed to submit form.');
    }
};

const handleFormUpdate = async (payload, resolve, reject) => {
    successMsg.value = '';
    
    // Supplement payload with auth info
    const finalPayload = {
        ...payload,
        employee_id: employee.value?.id,
        birthdate: birthdateCred.value,
    };

    try {
        const { data } = await axios.put(`/api/employee-portal/update-leave/${editData.value.id}`, finalPayload);
        successMsg.value = 'Your leave request has been successfully updated.';
        
        // Update history live
        const idx = leaveHistory.value.findIndex(l => l.id === editData.value.id);
        if (idx !== -1) {
            leaveHistory.value[idx] = data.leave;
        }
        
        editData.value = null;
        window.scrollTo({ top: 0, behavior: 'smooth' });
        setTimeout(() => { successMsg.value = ''; }, 5000);
        
        resolve(); // Tells the modal to clear its loading state and reset
    } catch (error) {
        reject(error.response?.data?.message || 'Failed to update form.');
    }
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
    transform: translateY(5px);
}
</style>
