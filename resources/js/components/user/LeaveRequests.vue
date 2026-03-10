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

                <!-- Leave Balance Grid -->
                <div class="grid grid-cols-2 md:grid-cols-5 lg:grid-cols-9 gap-3">
                    <div v-for="type in leaveTypesList" :key="type.key" 
                        :class="[type.bg, type.border, 'p-3 rounded-xl border shadow-sm text-center transition-all hover:shadow-md group flex flex-col justify-center min-h-[70px]']">
                        <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400 group-hover:text-gray-600 transition-colors">{{ type.label }}</p>
                        <p :class="[type.color, 'text-xl font-black mt-1']">
                            {{ user.employee?.[type.key] ?? '0' }}
                        </p>
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

const leaveTypesList = ref([]);

const colorThemes = [
    { color: 'text-purple-600', bg: 'bg-purple-50', border: 'border-purple-100' },
    { color: 'text-pink-600', bg: 'bg-pink-50', border: 'border-pink-100' },
    { color: 'text-blue-600', bg: 'bg-blue-50', border: 'border-blue-100' },
    { color: 'text-emerald-600', bg: 'bg-emerald-50', border: 'border-emerald-100' },
    { color: 'text-gray-600', bg: 'bg-gray-50', border: 'border-gray-200' },
    { color: 'text-rose-600', bg: 'bg-rose-50', border: 'border-rose-100' },
    { color: 'text-amber-600', bg: 'bg-amber-50', border: 'border-amber-100' },
    { color: 'text-indigo-600', bg: 'bg-indigo-50', border: 'border-indigo-100' },
    { color: 'text-orange-600', bg: 'bg-orange-50', border: 'border-orange-100' },
    { color: 'text-teal-600', bg: 'bg-teal-50', border: 'border-teal-100' },
];

const loadLeaveSettings = async () => {
    try {
        const res = await axios.get('/api/settings/leave_types');
        const typesList = res.data;
        leaveTypesList.value = typesList.map((label, idx) => {
            let abbr = '';
            const match = label.match(/\(([^)]+)\)/);
            if (match) {
                abbr = match[1];
            } else {
                const words = label.replace(/leave/i, '').trim().split(' ');
                if (words.length > 1) {
                    abbr = (words[0][0] + words[1][0]).toUpperCase();
                } else {
                    abbr = words[0].substring(0, 2).toUpperCase();
                }
            }
            
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
            
            const theme = colorThemes[idx % colorThemes.length];
            return { key: col, label: abbr, fullLabel: label, color: theme.color, bg: theme.bg, border: theme.border };
        });
    } catch (error) {
        console.error('Failed to load leave types settings', error);
    }
};

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
        const params = {};
        if (user.value?.id) {
            params.user_id = user.value.id;
        }
        const response = await axios.get('/api/leave-requests', { params });
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

onMounted(async () => {
    await loadLeaveSettings();
    authStore.fetchUser();
    fetchRequests();
});
</script>
