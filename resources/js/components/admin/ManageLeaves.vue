<template>
    <div v-if="user">
        <MainLayout :user="user">
            <div class="p-6 max-w-7xl mx-auto">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Leave Management</h1>
                        <p class="text-gray-500 text-sm mt-1">Track, manage, and report employee leave requests.</p>
                    </div>
                    <button 
                        @click="exportReport" 
                        class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors shadow-sm"
                    >
                        <i class="pi pi-download"></i>
                        <span>Export Report</span>
                    </button>
                </div>

                <!-- Metric Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-orange-50 flex items-center justify-center text-orange-500 text-xl font-bold">
                            {{ pendingCount }}
                        </div>
                        <div>
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pending</h3>
                            <p class="text-lg font-bold text-gray-800">Requests</p>
                        </div>
                    </div>
                     <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center text-green-500 text-xl font-bold">
                            {{ approvedCount }}
                        </div>
                        <div>
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Approved</h3>
                            <p class="text-lg font-bold text-gray-800">This Month</p>
                        </div>
                    </div>
                     <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 text-xl font-bold">
                            {{ scheduledCount }}
                        </div>
                        <div>
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Scheduled</h3>
                            <p class="text-lg font-bold text-gray-800">Upcoming</p>
                        </div>
                    </div>
                     <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-gray-50 flex items-center justify-center text-gray-500 text-xl font-bold">
                            {{ totalCount }}
                        </div>
                        <div>
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</h3>
                            <p class="text-lg font-bold text-gray-800">All Time</p>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6 flex flex-col md:flex-row gap-4 items-center">
                    <div class="relative flex-1 w-full">
                        <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input 
                            v-model="filters.search" 
                            type="text" 
                            placeholder="Search by employee name or ID..." 
                            class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all"
                        >
                    </div>
                    <select v-model="filters.status" class="px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 bg-white min-w-[150px]">
                        <option value="">All Statuses</option>
                        <option value="Pending">Pending</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                    <select v-model="filters.type" class="px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 bg-white min-w-[150px]">
                        <option value="">All Leave Types</option>
                         <option value="SIL">SIL (Service Incentive)</option>
                        <option value="Sick">Sick Leave</option>
                        <option value="Vacation">Vacation Leave</option>
                        <option value="Emergency">Emergency Leave</option>
                        <option value="Maternity">Maternity Leave</option>
                        <option value="Paternity">Paternity Leave</option>
                    </select>
                </div>

                <!-- Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/50 border-b border-gray-100 text-xs uppercase tracking-wider text-gray-500 font-semibold">
                                    <th class="p-4">Employee</th>
                                    <th class="p-4">Leave Type</th>
                                    <th class="p-4">Dates</th>
                                    <th class="p-4">Duration</th>
                                    <th class="p-4">Status</th>
                                    <th class="p-4">Payment</th>
                                    <th class="p-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-if="loading" class="animate-pulse">
                                    <td colspan="7" class="p-8 text-center text-gray-400">Loading leave records...</td>
                                </tr>
                                <tr v-else-if="requests.length === 0">
                                    <td colspan="7" class="p-8 text-center text-gray-400 flex flex-col items-center">
                                        <i class="pi pi-inbox text-4xl mb-2 opacity-50"></i>
                                        <span>No leave requests found matching your filters.</span>
                                    </td>
                                </tr>
                                <tr 
                                    v-for="request in requests" 
                                    :key="request.id" 
                                    class="hover:bg-gray-50/80 transition-colors group cursor-pointer"
                                    @click="viewDetails(request)"
                                >
                                    <td class="p-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-gray-100 overflow-hidden ring-2 ring-transparent group-hover:ring-teal-500/20 transition-all">
                                                <img v-if="request.user?.avatar" :src="request.user.avatar" class="w-full h-full object-cover">
                                                <div v-else class="w-full h-full flex items-center justify-center bg-teal-100 text-teal-600 font-bold text-sm">
                                                    {{ getInitials(request.user?.name) }}
                                                </div>
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-800 text-sm">{{ request.user?.name }}</p>
                                                <p class="text-xs text-gray-400">{{ request.user?.department }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <span class="font-medium text-gray-700 text-sm block">{{ request.leave_type }}</span>
                                        <span class="text-xs text-gray-400">{{ request.request_type }}</span>
                                    </td>
                                    <td class="p-4">
                                        <div class="text-sm text-gray-600">
                                            <p class="font-medium">{{ formatDate(request.from_date) }}</p>
                                            <p v-if="request.to_date && request.to_date !== request.from_date" class="text-xs text-gray-400">to {{ formatDate(request.to_date) }}</p>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ request.days_taken }} Day(s)
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <span :class="[
                                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wide',
                                            request.status === 'Approved' ? 'bg-green-100 text-green-700' :
                                            request.status === 'Pending' ? 'bg-orange-100 text-orange-700' :
                                            'bg-red-100 text-red-700'
                                        ]">
                                            {{ request.status }}
                                        </span>
                                    </td>
                                     <td class="p-4">
                                        <span v-if="request.is_paid" class="text-green-600 text-xs font-bold flex items-center gap-1">
                                            <i class="pi pi-check-circle"></i> With Pay
                                        </span>
                                        <span v-else class="text-gray-400 text-xs flex items-center gap-1">
                                            <i class="pi pi-circle"></i> Unpaid
                                        </span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <button class="p-2 hover:bg-white rounded-full text-gray-400 hover:text-teal-600 transition-colors shadow-sm border border-transparent hover:border-gray-200">
                                            <i class="pi pi-chevron-right"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="bg-gray-50 p-4 border-t border-gray-100 flex justify-between items-center text-sm">
                        <span class="text-gray-500">Showing {{ requests.length }} records</span>
                        <div class="flex gap-2">
                            <button class="px-3 py-1 border rounded bg-white disabled:opacity-50" :disabled="page === 1" @click="page--">Prev</button>
                            <button class="px-3 py-1 border rounded bg-white disabled:opacity-50" @click="page++">Next</button>
                        </div>
                    </div>
                </div>

                <!-- Detail Modal -->
                <div v-if="selectedRequest" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="closeModal">
                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col">
                        <!-- Modal Header -->
                        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                            <div>
                                <h2 class="text-xl font-bold text-gray-800">Leave Request Details</h2>
                                <p class="text-sm text-gray-500">RID-{{ String(selectedRequest.id).padStart(6, '0') }}</p>
                            </div>
                            <button @click="closeModal" class="p-2 hover:bg-gray-200 rounded-full text-gray-500 transition-colors"><i class="pi pi-times"></i></button>
                        </div>

                        <!-- Modal Content -->
                        <div class="flex-1 overflow-y-auto p-6">
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                                <!-- Left Col: Employee Profile -->
                                <div class="lg:col-span-1 space-y-6">
                                    <div class="text-center p-6 bg-gray-50 rounded-xl border border-gray-200">
                                        <div class="w-20 h-20 mx-auto rounded-full bg-white p-1 shadow-sm mb-3">
                                            <img v-if="selectedRequest.user?.avatar" :src="selectedRequest.user.avatar" class="w-full h-full rounded-full object-cover">
                                            <div v-else class="w-full h-full rounded-full bg-teal-100 flex items-center justify-center text-teal-600 text-2xl font-bold">
                                                {{ getInitials(selectedRequest.user?.name) }}
                                            </div>
                                        </div>
                                        <h3 class="font-bold text-gray-800">{{ selectedRequest.user?.name }}</h3>
                                        <p class="text-sm text-gray-500">{{ selectedRequest.user?.position || 'Employee' }}</p>
                                        <span class="inline-block mt-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-bold">
                                            {{ selectedRequest.user?.employment_status || 'Probationary' }}
                                        </span>
                                    </div>

                                    <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100">
                                        <h4 class="text-xs font-bold text-blue-800 uppercase tracking-wider mb-2">Leave Credits (SIL)</h4>
                                        <div class="flex justify-between items-end">
                                            <div>
                                                <span class="text-3xl font-bold text-blue-600">{{ selectedRequest.user?.sil_credits || 0 }}</span>
                                                <span class="text-sm text-blue-400 font-medium ml-1">days available</span>
                                            </div>
                                            <i class="pi pi-wallet text-blue-200 text-4xl"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Col: Request Details -->
                                <div class="lg:col-span-2 space-y-6">
                                    <!-- Status Banner -->
                                    <div :class="[
                                        'p-4 rounded-lg flex items-center gap-3 border',
                                        selectedRequest.status === 'Approved' ? 'bg-green-50 border-green-200 text-green-700' :
                                        selectedRequest.status === 'Pending' ? 'bg-orange-50 border-orange-200 text-orange-700' :
                                        'bg-red-50 border-red-200 text-red-700'
                                    ]">
                                        <i :class="[
                                            'pi text-xl',
                                            selectedRequest.status === 'Approved' ? 'pi-check-circle' :
                                            selectedRequest.status === 'Pending' ? 'pi-clock' :
                                            'pi-times-circle'
                                        ]"></i>
                                        <div>
                                            <p class="font-bold">Status: {{ selectedRequest.status }}</p>
                                            <p class="text-xs opacity-80" v-if="selectedRequest.status !== 'Pending'">
                                                Processed on {{ formatDate(selectedRequest.updated_at) }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Details Grid -->
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                                            <p class="text-xs text-gray-500 uppercase font-bold mb-1">Leave Type</p>
                                            <p class="font-medium text-gray-800">{{ selectedRequest.leave_type }}</p>
                                        </div>
                                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                                            <p class="text-xs text-gray-500 uppercase font-bold mb-1">Duration</p>
                                            <p class="font-medium text-gray-800">{{ selectedRequest.days_taken }} Days</p>
                                        </div>
                                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 col-span-2">
                                            <p class="text-xs text-gray-500 uppercase font-bold mb-1">Date Coverage</p>
                                            <p class="font-medium text-gray-800">
                                                {{ formatDate(selectedRequest.from_date) }} 
                                                <span v-if="selectedRequest.to_date"> - {{ formatDate(selectedRequest.to_date) }}</span>
                                            </p>
                                            <p v-if="selectedRequest.start_time" class="text-sm text-gray-500 mt-1">
                                                Time: {{ formatTime(selectedRequest.start_time) }} - {{ formatTime(selectedRequest.end_time) }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Reason -->
                                    <div>
                                        <h4 class="font-bold text-gray-700 mb-2">Reason for Leave</h4>
                                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 text-gray-700 italic border-l-4 border-l-teal-500">
                                            "{{ selectedRequest.reason }}"
                                        </div>
                                    </div>

                                    <!-- Admin Actions Area -->
                                    <div class="border-t border-gray-100 pt-6">
                                        <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                                            <i class="pi pi-shield text-teal-600"></i> Admin Actions
                                        </h4>
                                        
                                        <div class="space-y-4">
                                            <!-- Payment Toggle -->
                                            <div class="flex items-center justify-between p-3 rounded-lg border border-gray-200 hover:border-teal-400 transition-colors cursor-pointer" @click="togglePaid">
                                                <div class="flex items-center gap-3">
                                                    <div :class="['w-5 h-5 rounded border flex items-center justify-center transition-colors', selectedRequest.is_paid ? 'bg-teal-500 border-teal-500' : 'border-gray-300 bg-white']">
                                                        <i class="pi pi-check text-white text-xs" v-if="selectedRequest.is_paid"></i>
                                                    </div>
                                                    <div>
                                                        <p class="font-medium text-gray-800">Leave with Pay</p>
                                                        <p class="text-xs text-gray-500">Mark this leave request as paid</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Remarks -->
                                            <div>
                                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Admin Remarks</label>
                                                <textarea 
                                                    v-model="adminRemarks" 
                                                    rows="2" 
                                                    class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none text-sm"
                                                    placeholder="Add internal notes or remarks..."
                                                ></textarea>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="flex gap-3 pt-2">
                                                <button 
                                                    @click="updateStatus('Approved')" 
                                                    class="flex-1 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg font-bold shadow-sm shadow-green-200 transition-all active:scale-95"
                                                    v-if="selectedRequest.status !== 'Approved'"
                                                >
                                                    Approve Request
                                                </button>
                                                <button 
                                                    @click="updateStatus('Rejected')"
                                                    class="flex-1 py-2.5 bg-white border border-red-200 text-red-600 hover:bg-red-50 rounded-lg font-bold transition-all active:scale-95"
                                                    v-if="selectedRequest.status !== 'Rejected'"
                                                >
                                                    Reject Request
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </MainLayout>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../../stores/auth';
import { storeToRefs } from 'pinia';
import MainLayout from '../../layouts/MainLayout.vue';

const authStore = useAuthStore();
const { user } = storeToRefs(authStore);

onMounted(() => {
    authStore.fetchUser();
    fetchRequests();
});

const requests = ref([]);
const loading = ref(false);
const page = ref(1);
const selectedRequest = ref(null);
const adminRemarks = ref('');
const filters = ref({
    search: '',
    status: '',
    type: ''
});

// Metrics Mockup (Real apps would compute this from API)
const pendingCount = computed(() => requests.value.filter(r => r.status === 'Pending').length);
const approvedCount = computed(() => requests.value.filter(r => r.status === 'Approved').length);
const scheduledCount = ref(0); // This would come from backend logic
const totalCount = computed(() => requests.value.length);

const fetchRequests = async () => {
    loading.value = true;
    try {
        const params = {
            page: page.value,
            status: filters.value.status,
            // Add other filters as query params
        };
        const response = await axios.get('/api/leave-requests', { params });
        requests.value = response.data.data;
    } catch (e) {
        console.error("Fetch failed", e);
    } finally {
        loading.value = false;
    }
};

watch(filters, () => {
    page.value = 1;
    fetchRequests();
}, { deep: true });

// Helpers
const getInitials = (name) => name ? name.match(/(\b\S)?/g).join("").match(/(^\S|\S$)?/g).join("").toUpperCase() : 'U';
const formatDate = (d) => new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
const formatTime = (t) => {
    if (!t) return '';
    const [h, m] = t.split(':');
    const date = new Date();
    date.setHours(h, m);
    return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
};

// Modal Logic
const viewDetails = (req) => {
    selectedRequest.value = { ...req }; // clone
    adminRemarks.value = req.admin_remarks || '';
};

const closeModal = () => {
    selectedRequest.value = null;
    adminRemarks.value = '';
};

const togglePaid = () => {
    selectedRequest.value.is_paid = !selectedRequest.value.is_paid;
};

const updateStatus = async (newStatus) => {
    if (!confirm(`Are you sure you want to mark this as ${newStatus}?`)) return;
    
    try {
        const payload = {
            status: newStatus,
            is_paid: selectedRequest.value.is_paid,
            admin_remarks: adminRemarks.value
        };
        
        await axios.put(`/api/leave-requests/${selectedRequest.value.id}`, payload);
        
        // Update local list
        const idx = requests.value.findIndex(r => r.id === selectedRequest.value.id);
        if (idx !== -1) {
            requests.value[idx] = { ...requests.value[idx], ...payload, updated_at: new Date() };
        }
        
        closeModal();
        // Show Toast Success
    } catch (e) {
        console.error("Update failed", e);
        alert('Failed to update request');
    }
};

const exportReport = () => {
    alert("Export functionality coming soon! (Backend CSV generation)");
};
</script>
