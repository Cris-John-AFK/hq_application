<template>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-gray-800">Employee Masterlist</h3>
                <p class="text-sm text-gray-500">Centralized database of all employees.</p>
            </div>
            
            <div class="flex flex-col md:flex-row gap-3 items-center">
                <button 
                    @click="showAddModal = true"
                    class="cursor-pointer h-10 px-4 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-sm font-bold transition-colors flex items-center justify-center gap-2 shadow-sm whitespace-nowrap"
                >
                    <i class="pi pi-plus"></i>
                    Add Employee
                </button>
                
                <button 
                    @click="triggerImport"
                    class="cursor-pointer h-10 px-4 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-lg text-sm font-bold transition-colors flex items-center justify-center gap-2 shadow-sm whitespace-nowrap relative"
                    :disabled="isImporting"
                >
                    <i v-if="isImporting" class="pi pi-spin pi-spinner text-teal-600"></i>
                    <i v-else class="pi pi-upload text-teal-600"></i>
                    {{ isImporting ? 'Importing...' : 'Import Masterlist' }}
                    
                    <input 
                        ref="fileInput" 
                        type="file" 
                        class="hidden" 
                        accept=".xls,.xlsx,.csv" 
                        @change="handleImport"
                    >
                </button>
                
                <!-- Department Filter -->
                <div class="relative w-full md:w-auto">
                    <i class="pi pi-filter absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                    <select 
                        v-model="selectedDepartment" 
                        class="h-10 pl-10 pr-8 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 outline-none bg-white appearance-none cursor-pointer w-full"
                    >
                        <option value="All">All Departments</option>
                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                    </select>
                </div>

                <!-- Search -->
                <div class="relative w-full md:w-auto">
                    <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                    <input 
                        v-model="searchQuery" 
                        type="text" 
                        placeholder="Search name or ID..." 
                        class="h-10 pl-10 pr-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 outline-none w-full md:w-64"
                    >
                </div>

                <!-- Filter Toggle -->
                <button 
                    @click="showFilters = !showFilters"
                    :class="showFilters ? 'bg-teal-50 text-teal-600 border-teal-200' : 'bg-white text-gray-600 border-gray-200'"
                    class="cursor-pointer h-10 px-4 border rounded-lg text-sm font-bold transition-all flex items-center justify-center gap-2 shadow-sm whitespace-nowrap"
                >
                    <i class="pi pi-filter"></i>
                    <span>Smart Filters</span>
                    <i :class="showFilters ? 'pi-chevron-up' : 'pi-chevron-down'" class="pi text-[10px] ml-1"></i>
                </button>
            </div>
        </div>

        <!-- Expanded Filters -->
        <div v-show="showFilters" class="px-6 py-4 bg-gray-50 border-b border-gray-100 animate-in slide-in-from-top-2 duration-200">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <!-- Status Filter -->
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Emp. Status</label>
                    <select v-model="filterStatus" class="w-full h-9 px-3 border border-gray-200 rounded-lg text-xs bg-white outline-none focus:ring-2 focus:ring-teal-500">
                        <option value="All">All Statuses</option>
                        <option value="Regular">Regular</option>
                        <option value="Probationary">Probationary</option>
                        <option value="Contractual">Contractual</option>
                        <option value="Resigned">Resigned</option>
                        <option value="Terminated">Terminated</option>
                    </select>
                </div>

                <!-- Gender Filter -->
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Gender</label>
                    <select v-model="filterGender" class="w-full h-9 px-3 border border-gray-200 rounded-lg text-xs bg-white outline-none focus:ring-2 focus:ring-teal-500">
                        <option value="All">All Genders</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>

                <!-- Civil Status -->
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Civil Status</label>
                    <select v-model="filterCivilStatus" class="w-full h-9 px-3 border border-gray-200 rounded-lg text-xs bg-white outline-none focus:ring-2 focus:ring-teal-500">
                        <option value="All">All Status</option>
                        <option value="Single">Single</option>
                        <option value="Married">Married</option>
                        <option value="Widowed">Widowed</option>
                        <option value="Separated">Separated</option>
                    </select>
                </div>

                <!-- ID Compliance -->
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Compliance Check</label>
                    <select v-model="filterMissingId" class="w-full h-9 px-3 border border-gray-200 rounded-lg text-xs bg-white outline-none focus:ring-2 focus:ring-teal-500">
                        <option value="All">Full Compliance</option>
                        <option value="sss">Missing SSS</option>
                        <option value="philhealth">Missing PhilHealth</option>
                        <option value="pagibig">Missing Pag-IBIG</option>
                        <option value="tin">Missing TIN</option>
                    </select>
                </div>

                <!-- Hired Year -->
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Hired Year</label>
                    <select v-model="filterHiredYear" class="w-full h-9 px-3 border border-gray-200 rounded-lg text-xs bg-white outline-none focus:ring-2 focus:ring-teal-500">
                        <option value="All">All Years</option>
                        <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
                    </select>
                </div>
            </div>
            
            <div class="mt-3 flex justify-end">
                <button @click="resetFilters" class="text-[10px] font-bold text-teal-600 hover:text-teal-700 uppercase tracking-widest flex items-center gap-1 cursor-pointer">
                    <i class="pi pi-refresh text-[8px]"></i>
                    Reset All Filters
                </button>
            </div>
        </div>

        <div class="overflow-x-auto min-h-[400px]">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-semibold">Employee</th>
                        <th class="px-6 py-4 font-semibold">ID</th>
                        <th class="px-6 py-4 font-semibold">Department</th>
                        <th class="px-6 py-4 font-semibold">Position</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold">Date Hired</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-if="isLoading">
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <i class="pi pi-spin pi-spinner text-2xl text-teal-600 mb-2"></i>
                            <p>Loading masterlist...</p>
                        </td>
                    </tr>
                    <tr v-else-if="employees.length === 0">
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            No employees found. Add one to get started.
                        </td>
                    </tr>
                    <tr v-else v-for="employee in employees" :key="employee.id" class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 font-bold text-sm bg-cover bg-center shrink-0 border border-slate-200"
                                    :style="employee.avatar ? { backgroundImage: `url(${employee.avatar})` } : {}">
                                    <span v-if="!employee.avatar">{{ getInitials(employee.first_name, employee.last_name) }}</span>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-800 text-sm">{{ employee.last_name }}, {{ employee.first_name }} {{ employee.suffix || '' }}</p>
                                    <p class="text-xs text-gray-500">{{ employee.email || 'No email' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-xs font-mono font-semibold text-gray-600 bg-gray-50 rounded w-fit my-auto h-fit">{{ employee.employee_id }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ employee.department?.name || 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ employee.position }}</td>
                        <td class="px-6 py-4">
                            <span :class="getStatusClass(employee.employment_status)" class="px-2 py-1 rounded-full text-[10px] uppercase font-bold tracking-wider">
                                {{ employee.employment_status }}
                            </span>
                        </td>
                         <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">{{ formatDate(employee.date_hired) }}</td>
                        <td class="px-6 py-4 text-right">
                             <div class="flex items-center justify-end gap-2">
                                <button 
                                    @click="openEditModal(employee)" 
                                    class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition-colors cursor-pointer"
                                    title="Edit Details"
                                >
                                    <i class="pi pi-pencil text-xs"></i>
                                </button>
                                <button 
                                    @click="goToLeaveManagement(employee.employee_id)" 
                                    class="w-8 h-8 rounded-full bg-teal-50 text-teal-600 hover:bg-teal-100 flex items-center justify-center transition-colors cursor-pointer"
                                    title="View Leave History"
                                >
                                    <i class="pi pi-calendar-plus text-xs"></i>
                                </button>
                                <button 
                                    @click="adminFileLeave(employee.employee_id)" 
                                    class="w-8 h-8 rounded-full bg-purple-50 text-purple-600 hover:bg-purple-100 flex items-center justify-center transition-colors cursor-pointer"
                                    title="File Leave for Employee"
                                >
                                    <i class="pi pi-plus text-xs"></i>
                                </button>
                                <button 
                                    @click="deleteEmployee(employee.id)" 
                                    class="w-8 h-8 rounded-full bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition-colors cursor-pointer"
                                    title="Delete Record"
                                >
                                    <i class="pi pi-trash text-xs"></i>
                                </button>
                             </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="p-4 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
             <span>Page {{ currentPage }} of {{ lastPage }}</span>
             <div class="flex gap-2">
                 <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1" class="px-3 py-1 border rounded hover:bg-gray-50 disabled:opacity-50">Prev</button>
                 <button @click="changePage(currentPage + 1)" :disabled="currentPage === lastPage" class="px-3 py-1 border rounded hover:bg-gray-50 disabled:opacity-50">Next</button>
             </div>
        </div>

        <!-- Modals -->
        <AddEmployeeModal 
            v-model="showAddModal" 
            :departments="departments"
            :loading="isCreating"
            @submit="handleCreate"
        />

        <EditEmployeeModal 
            v-model="showEditModal"
            :employee="selectedEmployee"
            :departments="departments"
            :loading="isEditing"
            @submit="handleUpdate"
        />
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import AddEmployeeModal from './AddEmployeeModal.vue';
import EditEmployeeModal from './EditEmployeeModal.vue';

// State
const employees = ref([]);
const departments = ref([]);
const isLoading = ref(false);
const isImporting = ref(false);
const fileInput = ref(null);
const currentPage = ref(1);
const lastPage = ref(1);

// Filters
const searchQuery = ref('');
const selectedDepartment = ref('All');
const showFilters = ref(false);
const filterStatus = ref('All');
const filterGender = ref('All');
const filterCivilStatus = ref('All');
const filterMissingId = ref('All');
const filterHiredYear = ref('All');
const availableYears = ref([]);

let debounceTimer = null;

// Modals
const showAddModal = ref(false);
const showEditModal = ref(false);
const isCreating = ref(false);
const isEditing = ref(false);
const selectedEmployee = ref(null);

// Fetch Data
const fetchDepartments = async () => {
    try {
        const response = await axios.get('/api/departments'); // Returns [{id, name, ...}]
        departments.value = response.data;
    } catch (e) {
        console.error("Dept fetch error", e);
    }
};

const resetFilters = () => {
    selectedDepartment.value = 'All';
    filterStatus.value = 'All';
    filterGender.value = 'All';
    filterCivilStatus.value = 'All';
    filterMissingId.value = 'All';
    filterHiredYear.value = 'All';
    searchQuery.value = '';
};

const fetchEmployees = async (page = 1) => {
    isLoading.value = true;
    try {
        const params = {
            page: page,
            search: searchQuery.value,
            department_id: selectedDepartment.value === 'All' ? null : selectedDepartment.value,
            employment_status: filterStatus.value === 'All' ? null : filterStatus.value,
            gender: filterGender.value === 'All' ? null : filterGender.value,
            civil_status: filterCivilStatus.value === 'All' ? null : filterCivilStatus.value,
            missing_id: filterMissingId.value === 'All' ? null : filterMissingId.value,
            hired_year: filterHiredYear.value === 'All' ? null : filterHiredYear.value,
        };
        const response = await axios.get('/api/employees', { params });
        employees.value = response.data.data;
        currentPage.value = response.data.current_page;
        lastPage.value = response.data.last_page;
        
        // Generate years from data if we don't have them (just a helper)
        if (availableYears.value.length === 0) {
            const years = new Set();
            const currentYear = new Date().getFullYear();
            for (let i = 0; i < 20; i++) years.add(currentYear - i);
            availableYears.value = Array.from(years);
        }
    } catch (e) {
        console.error("Employee fetch error", e);
    } finally {
        isLoading.value = false;
    }
};

// Actions
const getInitials = (first, last) => {
    return (first[0] + last[0]).toUpperCase();
};

const getStatusClass = (status) => {
    switch(status) {
        case 'Regular': return 'bg-green-100 text-green-700';
        case 'Probationary': return 'bg-orange-100 text-orange-700';
        case 'Contractual': return 'bg-blue-100 text-blue-700';
        case 'Resigned': return 'bg-gray-100 text-gray-500';
        case 'Terminated': return 'bg-red-100 text-red-700';
        default: return 'bg-slate-100 text-slate-700';
    }
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString();
};

const changePage = (page) => {
    if (page >= 1 && page <= lastPage.value) {
        fetchEmployees(page);
    }
};

// Create
const handleCreate = async (formData) => {
    isCreating.value = true;
    try {
        // formData is already structured by the modal
        await axios.post('/api/employees', formData);
        showAddModal.value = false;
        fetchEmployees(1); // Reset to first page
        alert('Employee added successfully!');
    } catch (e) {
        console.error(e);
        alert('Failed to add employee: ' + (e.response?.data?.message || 'Unknown error'));
    } finally {
        isCreating.value = false;
    }
};

// Edit
const openEditModal = (emp) => {
    selectedEmployee.value = emp;
    showEditModal.value = true;
};

const handleUpdate = async (payload) => {
    isEditing.value = true;
    try {
        // payload contains { id, ...data, details: {...} }
        await axios.put(`/api/employees/${payload.id}`, payload);
        showEditModal.value = false;
        fetchEmployees(currentPage.value);
        alert('Employee updated successfully!');
    } catch (e) {
        console.error(e);
        alert('Failed to update employee: ' + (e.response?.data?.message || 'Unknown error'));
    } finally {
        isEditing.value = false;
    }
};

// Delete
const goToLeaveManagement = (employeeId) => {
    // Redirect to manage-leaves with the specific employee search
    window.location.href = `/manage-leaves?search=${employeeId}`;
};

const adminFileLeave = (employeeId) => {
    // Redirect to manage-leaves and trigger the filing modal
    window.location.href = `/manage-leaves?admin_file_target=${employeeId}`;
};

const deleteEmployee = async (id) => {
    if (!confirm('Are you sure you want to delete this record?')) return;
    try {
        await axios.delete(`/api/employees/${id}`);
        fetchEmployees(currentPage.value);
    } catch (e) {
        alert('Failed to delete');
    }
};

// Import Logic
const triggerImport = () => {
    fileInput.value.click();
};

const handleImport = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    // Reset input so same file can be selected again if needed
    event.target.value = '';

    if (!confirm(`Import ${file.name}? This might take a moment.`)) return;

    isImporting.value = true;
    const formData = new FormData();
    formData.append('file', file);

    try {
        await axios.post('/api/employees/import', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        alert('Masterlist imported successfully!');
        fetchEmployees(1);
    } catch (e) {
        console.error(e);
        alert('Import failed: ' + (e.response?.data?.message || 'Unknown error'));
    } finally {
        isImporting.value = false;
    }
};

// Watchers for filtering
watch([
    searchQuery, 
    selectedDepartment, 
    filterStatus, 
    filterGender, 
    filterCivilStatus, 
    filterMissingId, 
    filterHiredYear
], () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        fetchEmployees(1);
    }, 300);
});

onMounted(() => {
    fetchDepartments();
    fetchEmployees();
});
</script>
