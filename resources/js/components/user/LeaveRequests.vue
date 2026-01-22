<template>
    <div v-if="user">
        <MainLayout :user="user">
            <div class="space-y-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Leave Requests</h1>
                        <p class="text-sm text-gray-500 mt-1">Submit and manage your leave requests</p>
                    </div>
                    <button 
                        @click="showLeaveModal = true" 
                        class="cursor-pointer bg-teal-600 text-white px-6 py-2.5 rounded-lg hover:bg-teal-700 transition-colors flex items-center gap-2 shadow-sm font-medium"
                    >
                        <i class="pi pi-plus"></i>
                        Request Leave
                    </button>
                </div>

                <!-- Leave Balance Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">Leave</p>
                                <p class="text-3xl font-bold text-gray-800 mt-1">{{ Number(user.sick_credits || 0) }}</p>
                                <p class="text-xs text-gray-400 mt-1">days remaining</p>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                                <i class="pi pi-heart text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leave Requests Table -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800">My Leave Requests</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                                    <th class="px-6 py-4 font-semibold">Date Requested</th>
                                    <th class="px-6 py-4 font-semibold">Leave Type</th>
                                    <th class="px-6 py-4 font-semibold">Reason</th>
                                    <th class="px-6 py-4 font-semibold">Duration</th>
                                    <th class="px-6 py-4 font-semibold">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="request in leaveRequests" :key="request.id" class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4 text-sm text-gray-800">{{ formatDate(request.date_filed || request.created_at) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <span class="font-medium block">{{ request.leave_type }}</span>
                                        <span class="text-xs text-gray-400">{{ request.request_type }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ request.reason }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ request.days_taken }} Day(s)</td>
                                    <td class="px-6 py-4">
                                        <span 
                                            :class="{
                                                'bg-yellow-100 text-yellow-700': request.status === 'Pending',
                                                'bg-green-100 text-green-700': request.status === 'Approved',
                                                'bg-red-100 text-red-700': request.status === 'Rejected'
                                            }"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        >
                                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                            {{ request.status }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="leaveRequests.length === 0 && !loading">
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                        No leave requests found. Click "Request Leave" to submit a new request.
                                    </td>
                                </tr>
                                 <tr v-if="loading">
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-400">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Leave Request Modal -->
                <LeaveRequestModal 
                    v-model="showLeaveModal" 
                    leave-type="Leave"
                    @submit="handleLeaveSubmit"
                />
            </div>
        </MainLayout>
    </div>
    <div v-else class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="text-center">
            <i class="pi pi-spin pi-spinner text-4xl mb-4 text-teal-600"></i>
            <p class="text-gray-500">Loading...</p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../../stores/auth';
import { storeToRefs } from 'pinia';
import MainLayout from '../../layouts/MainLayout.vue';
import LeaveRequestModal from '../common/LeaveRequestModal.vue';

const authStore = useAuthStore();
const { user } = storeToRefs(authStore);

const showLeaveModal = ref(false);
const leaveRequests = ref([]);
const loading = ref(false);

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
};

const fetchRequests = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/leave-requests');
        leaveRequests.value = response.data.data;
    } catch (error) {
        console.error('Error fetching requests:', error);
    } finally {
        loading.value = false;
    }
};

const handleLeaveSubmit = async (formData) => {
    try {
        // Map frontend fields back to backend expected snake_case if slightly different, 
        // though our modal now aligns mostly.
        // formData has: date_filed, requestType, leaveType, fromDate, toDate, numberOfDays, numberOfHours, startTime, endTime, reason
        
        const payload = {
            date_filed: formData.date_filed,
            request_type: formData.requestType,
            leave_type: formData.leaveType,
            from_date: formData.fromDate,
            to_date: formData.toDate,
            days_taken: formData.numberOfDays,
            start_time: formData.startTime || null,
            end_time: formData.endTime || null,
            reason: formData.reason
        };

        const response = await axios.post('/api/leave-requests', payload);
        
        // Add to list or refresh
        leaveRequests.value.unshift(response.data);
        alert('Leave request submitted successfully!');
    } catch (error) {
        console.error('Submission failed:', error);
        alert('Failed to submit request. Please try again.');
    }
};

onMounted(() => {
    authStore.fetchUser();
    fetchRequests();
});
</script>
