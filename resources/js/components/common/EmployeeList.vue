<template>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-gray-800">Employee List</h3>
                <p class="text-sm text-gray-500">Manage and view all registered employees</p>
            </div>
            
            <div class="flex flex-col md:flex-row gap-3">
                <!-- Department Filter -->
                <div class="relative">
                    <i class="pi pi-filter absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <select 
                        v-model="selectedDepartment" 
                        class="pl-10 pr-8 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none bg-white appearance-none cursor-pointer"
                    >
                        <option value="All">All Departments</option>
                        <option value="Engineering">Engineering</option>
                        <option value="Design">Design</option>
                        <option value="Marketing">Marketing</option>
                        <option value="HR">Human Resources</option>
                    </select>
                </div>
                <!-- Status Filter -->
                <div class="relative">
                    <i class="pi pi-filter absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <select 
                        v-model="selectedStatus" 
                        class="pl-10 pr-8 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none bg-white appearance-none cursor-pointer"
                    >
                        <option value="All">All Status</option>
                        <option value="Working">Working</option>
                        <option value="Not Yet Arrived">Not Yet Arrived</option>
                        <option value="On Leave">On Leave</option>
                    </select>
                </div>

                <!-- Search -->
                <div class="relative">
                    <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input 
                        v-model="searchQuery" 
                        type="text" 
                        placeholder="Search employees..." 
                        class="pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none w-full md:w-64"
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
                                <div class="w-10 h-10 rounded-full bg-teal-100 flex items-center justify-center text-teal-600 font-bold text-sm">
                                    {{ employee.initials }}
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
                        <td class="px-6 py-4">
                            <span 
                                :class="{
                                    'bg-green-100 text-green-700': employee.status === 'Working',
                                    'bg-yellow-100 text-yellow-700': employee.status === 'Not Yet Arrived',
                                    'bg-gray-100 text-gray-600': employee.status === 'On Leave',
                                    'bg-red-100 text-red-600': employee.status === 'Absent',
                                    'bg-red-200 text-red-600': employee.status === 'Emergency Leave'
                                }"
                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium"
                            >
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                {{ employee.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right relative">
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
                                    <label class="custom-radio-container" @click="openLeaveModal(employee.id, 'On Leave')">
                                        <input type="radio" name="custom-radio" :checked="employee.status === 'On Leave'" />
                                        <span class="custom-radio-checkmark"></span>
                                        On Leave
                                    </label>
                                    <label class="custom-radio-container" @click="updateStatus(employee.id, 'Absent')">
                                        <input type="radio" name="custom-radio" :checked="employee.status === 'Absent'" />
                                        <span class="custom-radio-checkmark"></span>
                                        Absent
                                    </label>
                                    <label class="custom-radio-container" @click="openLeaveModal(employee.id, 'Emergency Leave')">
                                        <input type="radio" name="custom-radio" :checked="employee.status === 'Emergency Leave'" />
                                        <span class="custom-radio-checkmark"></span>
                                        Emergency Leave
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
        
        <!-- Leave Form Modal (Google Forms Style) -->
        <div v-if="showLeaveModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 animate-fade-in" @click.self="closeLeaveModal">
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
                        <div class="heading text-2xl text-gray-800">{{ leaveForm.type }} Request</div>
                        <p class="text-sm text-gray-600 mt-2">For <span class="font-medium">{{ getEmployeeName(leaveForm.employeeId) }}</span></p>
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
                                    <input type="radio" v-model="leaveForm.selectedReason" :value="reason" name="leaveReason" class="peer absolute opacity-0">
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
                                    <input type="radio" v-model="leaveForm.selectedReason" value="Others" name="leaveReason" class="peer absolute opacity-0">
                                    <div class="relative w-5 h-5 border-2 border-[#5f6368] rounded-full peer-checked:border-[#673ab7] transition-all flex-shrink-0">
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <div class="w-3 h-3 bg-[#673ab7] rounded-full opacity-0 peer-checked:opacity-100 transition-opacity duration-200"></div>
                                        </div>
                                        <div class="absolute -inset-3 rounded-full bg-[#673ab7] opacity-0 group-hover:opacity-[0.04] transition-opacity"></div>
                                    </div>
                                    <span class="ml-3 text-sm text-gray-700 select-none">Others</span>
                                </label>
                                <div v-if="leaveForm.selectedReason === 'Others'" class="ml-8 mt-2">
                                    <input 
                                        type="text" 
                                        v-model="leaveForm.otherReasonText" 
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
                                <span class="text-sm text-gray-600 block" v-if="!leaveForm.file">Drag file here or click to upload</span>
                                <span class="text-sm text-[#673ab7] font-medium block truncate px-4" v-else>{{ leaveForm.file.name }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="bg-[#f0f2f5] p-4 flex justify-between items-center shrink-0 border-t border-gray-200">
                    <div @click="closeLeaveModal" class="btn-text cursor-pointer text-[#673ab7] text-sm font-medium hover:bg-[#673ab7]/10 px-4 py-2 rounded transition-colors">
                        Cancel
                    </div>
                    <button @click="submitLeaveForm" class="bg-[#673ab7] text-white px-6 py-2 rounded text-sm font-medium hover:bg-[#5e35b1] shadow-sm transition-all active:scale-95">
                        Submit
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

const selectedDepartment = ref('All');
const selectedStatus = ref('All');
const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = 10;
const openActionMenuId = ref(null);

// Modal Logic
const showLeaveModal = ref(false);
const formError = ref('');
const leaveForm = ref({
    employeeId: null,
    type: '',
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

// Mock Data - Expanded for Pagination Demo
const employees = ref([
    { id: 1, name: 'John Doe', email: 'john@hq.app', initials: 'JD', empId: 'EMP-001', department: 'Engineering', role: 'Senior Developer', status: 'Working' },
    { id: 2, name: 'Jane Smith', email: 'jane@hq.app', initials: 'JS', empId: 'EMP-002', department: 'Design', role: 'UI/UX Designer', status: 'Not Yet Arrived' },
    { id: 3, name: 'Mike Johnson', email: 'mike@hq.app', initials: 'MJ', empId: 'EMP-003', department: 'Sales', role: 'Sales Manager', status: 'Working' },
    { id: 4, name: 'Sarah Williams', email: 'sarah@hq.app', initials: 'SW', empId: 'EMP-004', department: 'HR', role: 'HR Specialist', status: 'Not Yet Arrived' },
    { id: 5, name: 'Robert Brown', email: 'robert@hq.app', initials: 'RB', empId: 'EMP-005', department: 'Engineering', role: 'QA Engineer', status: 'Working' },
    { id: 6, name: 'Emily Davis', email: 'emily@hq.app', initials: 'ED', empId: 'EMP-006', department: 'Marketing', role: 'Content Writer', status: 'Working' },
    { id: 7, name: 'David Wilson', email: 'david@hq.app', initials: 'DW', empId: 'EMP-007', department: 'Engineering', role: 'DevOps Engineer', status: 'On Leave' },
    { id: 8, name: 'Lisa Miller', email: 'lisa@hq.app', initials: 'LM', empId: 'EMP-008', department: 'Marketing', role: 'Marketing Manager', status: 'Not Yet Arrived' },
    { id: 9, name: 'James Taylor', email: 'james@hq.app', initials: 'JT', empId: 'EMP-009', department: 'Sales', role: 'Sales Executive', status: 'Working' },
    { id: 10, name: 'Patricia Anderson', email: 'patricia@hq.app', initials: 'PA', empId: 'EMP-010', department: 'HR', role: 'Recruiter', status: 'Working' },
    { id: 11, name: 'Jennifer Thomas', email: 'jennifer@hq.app', initials: 'JT', empId: 'EMP-011', department: 'Design', role: 'Graphic Designer', status: 'Working' },
    { id: 12, name: 'Charles Jackson', email: 'charles@hq.app', initials: 'CJ', empId: 'EMP-012', department: 'Engineering', role: 'Frontend Developer', status: 'Working' },
    { id: 13, name: 'Linda White', email: 'linda@hq.app', initials: 'LW', empId: 'EMP-013', department: 'Sales', role: 'Account Manager', status: 'Not Yet Arrived' },
    { id: 14, name: 'Barbara Harris', email: 'barbara@hq.app', initials: 'BH', empId: 'EMP-014', department: 'Engineering', role: 'Backend Developer', status: 'Working' },
    { id: 15, name: 'Paul Martin', email: 'paul@hq.app', initials: 'PM', empId: 'EMP-015', department: 'Marketing', role: 'SEO Specialist', status: 'On Leave' },
]);

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

const updateStatus = (id, newStatus) => {
    const employee = employees.value.find(e => e.id === id);
    if (employee) {
        employee.status = newStatus;
        openActionMenuId.value = null; // Close menu after selection
    }
};

const openLeaveModal = (id, type) => {
    openActionMenuId.value = null;
    leaveForm.value = {
        employeeId: id,
        type: type,
        selectedReason: '',
        otherReasonText: '',
        file: null
    };
    formError.value = '';
    showLeaveModal.value = true;
};

const closeLeaveModal = () => {
    showLeaveModal.value = false;
};

const handleFileUpload = (event) => {
    leaveForm.value.file = event.target.files[0];
};

const submitLeaveForm = () => {
    if (!leaveForm.value.selectedReason) {
        formError.value = 'Please select a reason for leave.';
        return;
    }
    
    if (leaveForm.value.selectedReason === 'Others' && !leaveForm.value.otherReasonText) {
        formError.value = 'Please specify the reason.';
        return;
    }

    // Update status
    updateStatus(leaveForm.value.employeeId, leaveForm.value.type);
    closeLeaveModal();
    // In a real app, you would send the file and reason to the backend here
};

const getEmployeeName = (id) => {
    const emp = employees.value.find(e => e.id === id);
    return emp ? emp.name : 'Employee';
};

const closeActionMenu = () => {
    openActionMenuId.value = null;
};

onMounted(() => {
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
