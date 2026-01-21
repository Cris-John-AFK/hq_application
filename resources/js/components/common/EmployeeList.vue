<template>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-gray-800">Employee List</h3>
                <p class="text-sm text-gray-500">Manage and view all registered employees</p>
            </div>
            
            <div class="flex flex-col md:flex-row gap-3 items-center">
                <button 
                    @click="showAddModal = true"
                    class="cursor-pointer h-10 px-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2 shadow-sm whitespace-nowrap"
                >
                    <i class="pi pi-plus"></i>
                    Add Employee
                </button>
                <!-- Department Filter -->
                <div class="relative w-full md:w-auto">
                    <i class="pi pi-filter absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                    <select 
                        v-model="selectedDepartment" 
                        class="h-10 pl-10 pr-8 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none bg-white appearance-none cursor-pointer w-full"
                    >
                        <option value="All">All Departments</option>
                        <option value="Engineering">Engineering</option>
                        <option value="Design">Design</option>
                        <option value="Marketing">Marketing</option>
                        <option value="HR">Human Resources</option>
                    </select>
                </div>
                <!-- Status Filter -->
                <div class="relative w-full md:w-auto">
                    <i class="pi pi-filter absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                    <select 
                        v-model="selectedStatus" 
                        class="h-10 pl-10 pr-8 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none bg-white appearance-none cursor-pointer w-full"
                    >
                        <option value="All">All Status</option>
                        <option value="Available">Available</option>
                        <option value="Working">Working</option>
                        <option value="Not Yet Arrived">Not Yet Arrived</option>
                        <option value="On Leave">On Leave</option>
                        <option value="Absent">Absent</option>
                    </select>
                </div>

                <!-- Search -->
                <div class="relative w-full md:w-auto">
                    <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                    <input 
                        v-model="searchQuery" 
                        type="text" 
                        placeholder="Search employees..." 
                        class="h-10 pl-10 pr-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none w-full md:w-45"
                    >
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-semibold">Employee</th>
                        <th class="px-6 py-4 font-semibold">ID</th>
                        <th class="px-6 py-4 font-semibold">Department</th>
                        <th class="px-6 py-4 font-semibold">Role</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="employee in paginatedEmployees" :key="employee.id" class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div
                                class="w-10 h-10 rounded-full bg-teal-100 flex items-center justify-center text-teal-600 font-bold text-sm bg-cover bg-center"
                                :style="employee.avatar ? { backgroundImage: `url(${employee.avatar})` } : {}"
                                >
                                <span v-if="!employee.avatar">
                                    {{ getInitials(employee.name) }}
                                </span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800">{{ employee.name }}</p>
                                    <p class="text-xs text-gray-500">{{ employee.email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 font-mono">{{ employee.empId }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ employee.department }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ employee.role }}</td>
                        <td class="px-4 py-4">
                            <span 
                                :class="{
                                    'bg-green-100 text-green-700': employee.status === 'Available',
                                    'bg-gray-100 text-gray-600': employee.status === 'On Leave',
                                }"
                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium"
                            >
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                {{ employee.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right relative">
                            <!-- Action Button -->
                            <button 
                                @click.stop="toggleActionMenu(employee.id)" 
                                class="text-gray-400 hover:text-teal-600 transition-colors cursor-pointer w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100"
                            >
                                <i class="pi pi-ellipsis-v"></i>
                            </button>

                            <!-- Action Menu -->
                            <div 
                                v-if="openActionMenuId === employee.id" 
                                class="absolute right-0 top-full mt-2 z-50 mr-6 origin-top-right"
                                @click.stop
                            >
                                <div class="custom-radio-group">
                                    <button 
                                        @click="handleViewLeaves(employee)"
                                        class="custom-radio-container w-full text-left hover:text-teal-400 group"
                                    >
                                        <i class="pi pi-calendar-times mr-3"></i>
                                        <span>View Leaves</span>
                                    </button>

                                    <button 
                                        @click="handleChangePassword(employee)"
                                        class="custom-radio-container w-full text-left hover:text-teal-400 group"
                                    >
                                        <i class="pi pi-key mr-3"></i>
                                        <span>Change Password</span>
                                    </button>

                                    <div class="h-px bg-gray-600 my-1 mx-2 opacity-30"></div>

                                    <label class="custom-radio-container" @click="openLeaveModal(employee.id, 'On Leave')">
                                        <input type="radio" name="custom-radio" :checked="employee.status === 'On Leave'" />
                                        <span class="custom-radio-checkmark"></span>
                                        Mark On Leave
                                    </label>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="filteredEmployees.length === 0">
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            No employees found matching your criteria.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination Controls -->
        <div class="p-4 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
            <span>Showing {{ startEntry }} to {{ endEntry }} of {{ filteredEmployees.length }} entries</span>
            <div class="flex gap-2">
                <button 
                    @click="prevPage" 
                    :disabled="currentPage === 1"
                    class="px-3 py-1 border border-gray-200 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer transition-colors"
                >
                    Previous
                </button>
                <div class="flex items-center gap-1 px-2">
                    <span class="font-medium text-gray-700">{{ currentPage }}</span>
                    <span class="text-gray-400">/</span>
                    <span>{{ totalPages || 1 }}</span>
                </div>
                <button 
                    @click="nextPage" 
                    :disabled="currentPage === totalPages || totalPages === 0"
                    class="px-3 py-1 border border-gray-200 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer transition-colors"
                >
                    Next
                </button>
            </div>
        </div>
        

        <!-- Add Employee Modal -->
        <AddEmployeeModal 
            v-model="showAddModal" 
            :loading="isCreating"
            @submit="handleCreateEmployee"
        />

        <!-- Leave Form Modal (Reusable Component) -->
        <LeaveRequestModal 
            v-model="showLeaveModal" 
            :leave-type="leaveForm.type"
            :employee-name="getEmployeeName(leaveForm.employeeId)"
            @submit="handleLeaveSubmit"
        />
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import LeaveRequestModal from './LeaveRequestModal.vue';
import AddEmployeeModal from './AddEmployeeModal.vue';
import axios from 'axios';

const selectedDepartment = ref('All');
const selectedStatus = ref('All');
const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = 10;
const openActionMenuId = ref(null);
const isLoading = ref(false);

// Modal Logic
const showLeaveModal = ref(false);
const showAddModal = ref(false);
const isCreating = ref(false);
const leaveForm = ref({
    employeeId: null,
    type: ''
});

// Employee Data
const employees = ref([]);

const fetchEmployees = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/api/users');
        employees.value = response.data.map(user => ({
            id: user.id,
            name: user.name,
            email: user.email,
            avatar: user.avatar_url, // Use the full URL provided by the backend accessor
            empId: user.id_number || 'N/A',
            department: user.department || 'N/A',
            role: user.position || (user.role === 'admin' ? 'Administrator' : 'Employee'),
            status: user.status || 'Available',
            position: user.position
        }));
    } catch (error) {
        console.error('Failed to fetch employees:', error);
    } finally {
        isLoading.value = false;
    }
};

const getInitials = (name) => {
    return name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
};

const handleCreateEmployee = async (formData) => {
    isCreating.value = true;
    try {
        await axios.post('/api/users', formData);
        showAddModal.value = false;
        await fetchEmployees(); // Refresh list
        // Could show success toast here
    } catch (error) {
        console.error('Failed to create employee:', error);
        alert('Failed to create employee. Please try again.');
    } finally {
        isCreating.value = false;
    }
};

const filteredEmployees = computed(() => {
    return employees.value.filter(employee => {
        const matchesDepartment = selectedDepartment.value === 'All' || employee.department === selectedDepartment.value;
        const matchesSearch = employee.name.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
                              employee.email.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                              employee.empId.toLowerCase().includes(searchQuery.value.toLowerCase());
        return matchesDepartment && matchesSearch;
    });
});

const filteredByStatus = computed(() => {
    return filteredEmployees.value.filter(employee => {
        return employee.status === selectedStatus.value || selectedStatus.value === 'All';
    });
});

// Pagination Logic
const totalPages = computed(() => Math.ceil(filteredByStatus.value.length / itemsPerPage));

const paginatedEmployees = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return filteredByStatus.value.slice(start, end);
});

const startEntry = computed(() => filteredByStatus.value.length === 0 ? 0 : (currentPage.value - 1) * itemsPerPage + 1);
const endEntry = computed(() => Math.min(currentPage.value * itemsPerPage, filteredByStatus.value.length));

const nextPage = () => {
    if (currentPage.value < totalPages.value) currentPage.value++;
};

const prevPage = () => {
    if (currentPage.value > 1) currentPage.value--;
};

// Reset to page 1 when filters change
watch([selectedDepartment, searchQuery], () => {
    currentPage.value = 1;
});

// Action Menu Logic
const toggleActionMenu = (id) => {
    if (openActionMenuId.value === id) {
        openActionMenuId.value = null;
    } else {
        openActionMenuId.value = id;
    }
};

const handleViewLeaves = (employee) => {
    openActionMenuId.value = null;
    alert(`View Leave Requests for ${employee.name} - Feature coming soon!`);
    // Navigate to leaves page or open modal
};

const handleChangePassword = (employee) => {
    openActionMenuId.value = null;
    alert(`Change Password for ${employee.name} - Feature coming soon!`);
    // Open password change modal
};

const updateStatus = (id, newStatus) => {
    // Optimistic update
    const employee = employees.value.find(e => e.id === id);
    if (employee) {
        employee.status = newStatus;
        openActionMenuId.value = null; // Close menu after selection
    }
    // In real app, call API here to persist status
};

const openLeaveModal = (id, type) => {
    openActionMenuId.value = null;
    leaveForm.value = {
        employeeId: id,
        type: type
    };
    showLeaveModal.value = true;
};

const handleLeaveSubmit = (formData) => {
    // Update employee status
    updateStatus(leaveForm.value.employeeId, leaveForm.value.type);
    // In a real app, send formData to backend
    console.log('Leave request submitted:', formData);
};

const getEmployeeName = (id) => {
    const emp = employees.value.find(e => e.id === id);
    return emp ? emp.name : 'Employee';
};

const closeActionMenu = () => {
    openActionMenuId.value = null;
};

onMounted(() => {
    fetchEmployees();
    document.addEventListener('click', closeActionMenu);
});

onUnmounted(() => {
    document.removeEventListener('click', closeActionMenu);
});
</script>

<style scoped>
/* From Uiverse.io by kyle1dev */ 
.custom-radio-group {
  display: flex;
  flex-direction: column;
  gap: 12px;
  width: 280px; 
  border-radius: 12px;
  background: rgba(30, 41, 59, 0.95); 
  padding: 16px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
  backdrop-filter: blur(8px);
}

.custom-radio-container {
  position: relative;
  display: flex;
  align-items: center;
  cursor: pointer;
  padding: 12px 20px;
  border-radius: 8px;
  background-color: rgba(255, 255, 255, 0.1);
  transition:
    background-color 0.3s ease,
    transform 0.3s ease,
    box-shadow 0.3s ease;
  font-size: 14px;
  font-weight: 500;
  color: #f1f5f9; 
  user-select: none;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.custom-radio-container:hover {
  background-color: rgba(255, 255, 255, 0.15);
  transform: scale(1.02);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.custom-radio-container input[type="radio"] {
  opacity: 0;
  position: absolute;
}

.custom-radio-checkmark {
  position: relative;
  height: 20px;
  width: 20px;
  border: 2px solid #cbd5e1; 
  border-radius: 50%;
  background-color: transparent;
  transition:
    background-color 0.4s ease,
    transform 0.4s ease,
    border-color 0.4s ease;
  margin-right: 12px;
  display: inline-block;
  vertical-align: middle;
}

.custom-radio-container input[type="radio"]:checked + .custom-radio-checkmark {
  background-color: #14b8a6; 
  border-color: #14b8a6;
  box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.2);
  transform: scale(1.1);
  animation: pulse 0.6s forwards;
}

.custom-radio-checkmark::after {
  content: "";
  position: absolute;
  display: none;
}

.custom-radio-container input[type="radio"]:checked + .custom-radio-checkmark::after {
  display: block;
  left: 50%;
  top: 50%;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #ffffff;
  transform: translate(-50%, -50%);
}

@keyframes pulse {
  0% { transform: scale(1.1); }
  50% { transform: scale(1.2); }
  100% { transform: scale(1.1); }
}
</style>
