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
                        @click="openNewRequest" 
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
                                <p class="text-sm text-gray-500">Leave Credits</p>
                                <p class="text-3xl font-bold text-gray-800 mt-1">{{ Number(user.leave_credits || 0) }}</p>
                                <p class="text-xs text-gray-400 mt-1">days remaining</p>
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
                                    <th class="px-6 py-4 font-semibold text-right">Actions</th>
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
                                                'bg-yellow-100 text-yellow-700': (request.status || 'Pending') === 'Pending',
                                                'bg-green-100 text-green-700': request.status === 'Approved',
                                                'bg-red-100 text-red-700': request.status === 'Rejected',
                                                'bg-gray-100 text-gray-700': request.status === 'Cancelled',
                                                'bg-gray-100 text-gray-700': !['Pending', 'Approved', 'Rejected', 'Cancelled'].includes(request.status) && request.status
                                            }"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        >
                                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                            {{ request.status || 'Pending' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <button 
                                                v-if="(request.status || 'Pending') === 'Pending'"
                                                @click="editRequest(request)"
                                                class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                                title="Edit Request"
                                            >
                                                <i class="pi pi-pencil"></i>
                                            </button>
                                            <button 
                                                v-if="['Pending', 'Approved'].includes(request.status || 'Pending')"
                                                @click="cancelRequest(request)"
                                                class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                                                title="Cancel Request"
                                            >
                                                <i class="pi pi-times"></i>
                                            </button>
                                        </div>
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
                    :is-edit="isEditMode"
                    :initial-data="editingRequest"
                    @submit="handleLeaveSubmit"
                    @update="handleLeaveUpdate"
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
const isEditMode = ref(false);
const editingRequest = ref(null);
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

const openNewRequest = () => {
    isEditMode.value = false;
    editingRequest.value = null;
    showLeaveModal.value = true;
};

const editRequest = (request) => {
    isEditMode.value = true;
    editingRequest.value = request;
    showLeaveModal.value = true;
};

const cancelRequest = async (request) => {
    if (!confirm('Are you sure you want to cancel this leave request? This action will be logged.')) return;
    
    try {
        await axios.put(`/api/leave-requests/${request.id}`, { status: 'Cancelled' });
        fetchRequests();
        alert('Request cancelled successfully.');
    } catch (error) {
        console.error('Cancel failed:', error);
        alert('Failed to cancel request.');
    }
};

const handleLeaveSubmit = async (formData) => {
    try {
        const payload = new FormData();
        payload.append('date_filed', formData.date_filed);
        payload.append('request_type', formData.requestType);
        payload.append('leave_type', formData.leaveType);
        payload.append('from_date', formData.fromDate);
        payload.append('to_date', formData.toDate);
        payload.append('days_taken', formData.numberOfDays);
        if (formData.startTime) payload.append('start_time', formData.startTime);
        if (formData.endTime) payload.append('end_time', formData.endTime);
        payload.append('reason', formData.reason);
        
        if (formData.attachment) {
            payload.append('attachment', formData.attachment);
        }

        const response = await axios.post('/api/leave-requests', payload, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        
        fetchRequests();
        alert('Leave request submitted successfully!');
    } catch (error) {
        console.error('Submission failed:', error);
        alert('Failed to submit request. ' + (error.response?.data?.message || 'Please try again.'));
    }
};

const handleLeaveUpdate = async (formData) => {
    try {
        // Use PUT for updates. Note: Laravel might need _method for FormData if using PUT, 
        // but here we are not using FormData for update yet (optional).
        // Let's use simple JSON for update if no new file is attached.
        const payload = {
            date_filed: formData.date_filed,
            request_type: formData.requestType,
            leave_type: formData.leaveType,
            from_date: formData.fromDate,
            to_date: formData.toDate,
            days_taken: formData.numberOfDays,
            start_time: formData.startTime,
            end_time: formData.endTime,
            reason: formData.reason
        };

        await axios.put(`/api/leave-requests/${editingRequest.value.id}`, payload);
        
        fetchRequests();
        alert('Leave request updated successfully!');
    } catch (error) {
        console.error('Update failed:', error);
        alert('Failed to update request. ' + (error.response?.data?.message || 'Please try again.'));
    }
};

onMounted(() => {
    authStore.fetchUser();
    fetchRequests();
});
</script>
