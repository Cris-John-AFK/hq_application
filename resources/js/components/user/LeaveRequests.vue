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
                                <p class="text-sm text-gray-500">Sick Leave</p>
                                <p class="text-3xl font-bold text-gray-800 mt-1">8</p>
                                <p class="text-xs text-gray-400 mt-1">days remaining</p>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                                <i class="pi pi-heart text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">Vacation Leave</p>
                                <p class="text-3xl font-bold text-gray-800 mt-1">12</p>
                                <p class="text-xs text-gray-400 mt-1">days remaining</p>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center">
                                <i class="pi pi-sun text-purple-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">Emergency Leave</p>
                                <p class="text-3xl font-bold text-gray-800 mt-1">3</p>
                                <p class="text-xs text-gray-400 mt-1">days remaining</p>
                            </div>
                            <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center">
                                <i class="pi pi-exclamation-triangle text-red-600 text-xl"></i>
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
                                    <td class="px-6 py-4 text-sm text-gray-800">{{ request.date }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ request.type }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ request.reason }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ request.duration }}</td>
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
                                <tr v-if="leaveRequests.length === 0">
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                        No leave requests found. Click "Request Leave" to submit a new request.
                                    </td>
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
import { useAuthStore } from '../../stores/auth';
import { storeToRefs } from 'pinia';
import MainLayout from '../../layouts/MainLayout.vue';
import LeaveRequestModal from '../common/LeaveRequestModal.vue';

const authStore = useAuthStore();
const { user } = storeToRefs(authStore);

const showLeaveModal = ref(false);

const leaveRequests = ref([
    { id: 1, date: 'Jan 15, 2026', type: 'Sick Leave', reason: 'Medical Appointment', duration: '1 day', status: 'Approved' },
    { id: 2, date: 'Jan 10, 2026', type: 'Vacation Leave', reason: 'Personal Emergency', duration: '3 days', status: 'Pending' },
]);

const handleLeaveSubmit = (formData) => {
    console.log('Leave request submitted:', formData);
    // In a real app, send this to the backend
    leaveRequests.value.unshift({
        id: Date.now(),
        date: new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }),
        type: 'Leave',
        reason: formData.reason,
        duration: '1 day',
        status: 'Pending'
    });
};

onMounted(() => {
    authStore.fetchUser();
});
</script>
