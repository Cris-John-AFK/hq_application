<template>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-gray-800">Employee Masterlist</h3>
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

                <button 
                    @click="handleExport"
                    class="cursor-pointer h-10 px-4 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-lg text-sm font-bold transition-colors flex items-center justify-center gap-2 shadow-sm whitespace-nowrap"
                >
                    <i class="pi pi-download text-teal-600"></i>
                    Export Masterlist
                </button>
                
                <!-- Department Filter -->
                <div class="relative w-full md:w-auto">
                    <i class="pi pi-filter absolute left-3 top-1/2 -translate-y-1/2 text-teal-600 text-xs pointer-events-none"></i>
                    <select 
                        v-model="selectedDepartment" 
                        class="h-10 pl-9 pr-8 border border-gray-200 rounded-lg text-sm bg-white hover:bg-gray-50 transition-colors focus:ring-2 focus:ring-teal-500 outline-none appearance-none cursor-pointer w-full md:w-48"
                    >
                        <option value="All">All Departments</option>
                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                    </select>
                    <i class="pi pi-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-gray-400 pointer-events-none"></i>
                </div>

                <!-- Search -->
                <div class="relative w-full md:w-auto">
                    <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                    <input 
                        v-model="searchQuery" 
                        type="text" 
                        placeholder="Search name or ID..." 
                        class="h-10 pl-10 pr-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 outline-none w-full md:w-48"
                    >
                </div>

                <!-- Filter Toggle -->
                <button 
                    @click="showFilters = !showFilters"
                    :class="showFilters ? 'bg-teal-50 text-teal-600 border-teal-200' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'"
                    class="cursor-pointer h-10 px-4 border rounded-lg text-sm font-bold transition-all flex items-center justify-center gap-2 shadow-sm"
                >
                    <i class="pi pi-filter"></i>
                    <span>Filters</span>
                    <i :class="showFilters ? 'pi-chevron-up' : 'pi-chevron-down'" class="pi text-[10px]"></i>
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

                <!-- Working Hours Filter -->
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Working Hours (Shift)</label>
                    <select v-model="filterWorkingHours" class="w-full h-9 px-3 border border-gray-200 rounded-lg text-xs bg-white outline-none focus:ring-2 focus:ring-teal-500">
                        <option value="All">All Shifts</option>
                        <option value="7:00 AM - 3:00 PM">Shift A (7 AM - 3 PM)</option>
                        <option value="7:00 PM - 4:00 AM">Shift B (7 PM - 4 AM)</option>
                        <option value="6:00 AM - 2:00 PM">Shift C (6 AM - 2 PM)</option>
                        <option value="2:00 PM - 10:00 PM">Shift D (2 PM - 10 PM)</option>
                        <option value="8:00 AM - 4:00 PM">Shift E (8 AM - 4 PM)</option>
                    </select>
                </div>

                <!-- Leave Type Filter -->
                 <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">With Balance In</label>
                    <select v-model="filterHasBalance" class="w-full h-9 px-3 border border-gray-200 rounded-lg text-xs bg-white outline-none focus:ring-2 focus:ring-teal-500">
                        <option value="All">Any / All</option>
                        <option v-for="col in activeLeaveColumns" :key="col.col" :value="col.col">{{ col.label }}</option>
                    </select>
                </div>

                <!-- Account Status Filter -->
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">System Access</label>
                    <div class="flex items-center h-9">
                        <button 
                            @click="filterHasAccount = !filterHasAccount"
                            :class="filterHasAccount ? 'bg-rose-600 text-white border-rose-600' : 'bg-white text-gray-400 border-gray-200'"
                            class="w-full h-full border rounded-lg text-[9px] font-black uppercase tracking-tight transition-all flex items-center justify-center gap-2 px-3 shadow-inner"
                        >
                            <i :class="filterHasAccount ? 'pi-users' : 'pi-id-card'" class="pi text-[10px]"></i>
                            {{ filterHasAccount ? 'Hide Non-Users' : 'Show All Staff' }}
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="mt-3 flex justify-end">
                <button @click="resetFilters" class="text-[10px] font-bold text-teal-600 hover:text-teal-700 uppercase tracking-widest flex items-center gap-1 cursor-pointer">
                    <i class="pi pi-refresh text-[8px]"></i>
                    Reset All Filters
                </button>
            </div>
        </div>

        <!-- Shift Legend Section -->
        <div class="px-6 py-4 bg-slate-50 border-b border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-[10px] font-black tracking-widest text-slate-400 uppercase">Assigned Shift Deployment</h4>
                <button @click="showManageShiftsModal = true" class="text-[9px] font-black text-teal-600 hover:text-teal-700 uppercase tracking-widest flex items-center gap-1 cursor-pointer transition-colors border border-teal-100 bg-white px-2 py-1 rounded shadow-sm hover:shadow">
                    <i class="pi pi-cog text-[8px]"></i> Manage Shifts
                </button>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            <div v-for="stat in shiftStats" :key="stat.code" class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between group">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 bg-teal-600 text-white rounded-full flex items-center justify-center text-[10px] font-black">{{ stat.code }}</span>
                        <span class="text-[10px] font-black text-slate-800 tracking-tight">{{ stat.time }}</span>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-[14px] font-black text-teal-600">{{ stat.count }}</p>
                    <p class="text-[8px] font-bold text-slate-400 uppercase">Deployed</p>
                </div>
            </div>
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
                        <th class="px-6 py-4 font-semibold">Balances</th>
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
                    <tr v-else v-for="employee in employees" :key="employee.id" @click="openEditModal(employee)" class="hover:bg-gray-50/50 transition-colors cursor-pointer">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 font-bold text-sm bg-cover bg-center shrink-0 border border-slate-200"
                                    :style="employee.avatar ? { backgroundImage: `url(${employee.avatar})` } : {}">
                                    <span v-if="!employee.avatar">{{ getInitials(employee.first_name, employee.last_name) }}</span>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-800 text-sm">{{ employee.last_name }}, {{ employee.first_name }} {{ employee.suffix || '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-xs font-mono font-semibold text-gray-600 bg-gray-50 rounded w-fit my-auto h-fit">{{ employee.employee_id }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ employee.department?.name || 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ employee.position }}</td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1 w-[160px]">
                                <div v-for="col in activeLeaveColumns" :key="col.col" class="flex flex-col border border-gray-100 rounded p-1 bg-white min-w-[28px] text-center shrink-0" :title="col.label">
                                    <span class="text-[8px] font-bold text-gray-400">{{ col.abbr }}</span>
                                    <span class="text-[10px] font-black" :class="Number(employee[col.col]) > 0 ? 'text-teal-600' : 'text-gray-300'">{{ Number(employee[col.col]) || 0 }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-1">
                                <span :class="getStatusClass(employee.employment_status)" class="px-2 py-0.5 rounded-full text-[9px] uppercase font-bold tracking-wider w-fit">
                                    {{ employee.employment_status }}
                                </span>
                                <div v-if="employee.user" class="flex items-center gap-1 mt-1">
                                    <span :class="getRoleClass(employee.user.role)" class="px-2 py-0.5 rounded-full text-[8px] uppercase font-black tracking-widest border border-current shadow-sm">
                                        {{ employee.user.role }}
                                    </span>
                                </div>
                                <button 
                                    v-else 
                                    @click.stop="openAccountModal(employee)"
                                    class="mt-1 text-[9px] font-black uppercase text-teal-600 hover:text-teal-700 flex items-center gap-1 cursor-pointer transition-colors"
                                >
                                    <i class="pi pi-user-plus"></i>
                                    Create Account
                                </button>
                            </div>
                        </td>
                         <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">{{ formatDate(employee.date_hired) }}</td>
                        <td class="px-6 py-4 text-right">
                             <div class="flex items-center justify-end gap-2">
                                <button 
                                    @click.stop="openEditModal(employee)" 
                                    class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition-colors cursor-pointer"
                                    title="Edit Details"
                                >
                                    <i class="pi pi-pencil text-xs"></i>
                                </button>
                                <button 
                                    @click.stop="goToLeaveManagement(employee.employee_id)" 
                                    class="w-8 h-8 rounded-full bg-teal-50 text-teal-600 hover:bg-teal-100 flex items-center justify-center transition-colors cursor-pointer"
                                    title="View Leave History"
                                >
                                    <i class="pi pi-calendar-plus text-xs"></i>
                                </button>
                                <button 
                                    @click.stop="adminFileLeave(employee.employee_id)" 
                                    class="w-8 h-8 rounded-full bg-purple-50 text-purple-600 hover:bg-purple-100 flex items-center justify-center transition-colors cursor-pointer"
                                    title="File Leave for Employee"
                                >
                                    <i class="pi pi-plus text-xs"></i>
                                </button>
                                <button 
                                    @click.stop="archiveEmployee(employee.id)" 
                                    class="w-8 h-8 rounded-full bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition-colors cursor-pointer"
                                    title="Archive Record"
                                >
                                    <i class="pi pi-folder text-xs"></i>
                                </button>
                                <button 
                                    v-if="employee.user"
                                    @click.stop="openResetPasswordModal(employee)" 
                                    class="w-8 h-8 rounded-full bg-slate-100 text-slate-800 hover:bg-slate-200 flex items-center justify-center transition-colors cursor-pointer border border-slate-200 shadow-sm"
                                    title="Force Password Reset"
                                >
                                    <i class="pi pi-lock text-xs"></i>
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
            ref="editEmployeeModalRef"
            v-model="showEditModal"
            :employee="selectedEmployee"
            :departments="departments"
            :loading="isEditing"
            @submit="handleUpdate"
        />

        <ManageShiftsModal v-model="showManageShiftsModal" @shifts-updated="fetchShiftStats" />

        <ResetPasswordModal 
            v-model="showResetModal"
            :employee="selectedEmployeeForReset"
        />

        <!-- Account Creation Modal -->
        <div v-if="showAccountModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" @click.self="showAccountModal = false">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden animate-in fade-in zoom-in duration-200">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-lg font-black text-gray-800 uppercase tracking-tight">System Account Access</h3>
                    <p class="text-[11px] text-gray-500 font-bold uppercase mt-1">Promote employee to system user</p>
                </div>
                
                <div class="p-6 space-y-4">
                    <div class="flex items-center gap-3 p-3 bg-teal-50 rounded-xl border border-teal-100 text-teal-700">
                        <i class="pi pi-user text-xl"></i>
                        <div>
                            <p class="text-xs font-black uppercase">{{ selectedEmployeeForAccount?.first_name }} {{ selectedEmployeeForAccount?.last_name }}</p>
                            <p class="text-[10px] opacity-75 font-bold uppercase">{{ selectedEmployeeForAccount?.employee_id }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Access Privilege / Role</label>
                        <div class="grid grid-cols-1 gap-2">
                            <button 
                                v-for="role in ['user', 'dept_head', 'admin']" 
                                :key="role"
                                @click="accountRole = role"
                                :class="accountRole === role ? 'border-teal-600 bg-teal-600 text-white shadow-lg shadow-teal-100 scale-[1.02]' : 'border-gray-100 bg-gray-50 text-gray-500 hover:bg-gray-100'"
                                class="flex items-center justify-between p-3 rounded-xl border transition-all cursor-pointer"
                            >
                                <span class="text-xs font-black uppercase tracking-wider">{{ role.replace('_', ' ') }}</span>
                                <i v-if="accountRole === role" class="pi pi-check-circle"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Department Assignment (for dept_head / admin) -->
                    <div v-show="accountRole === 'dept_head' || accountRole === 'admin'" class="animate-in slide-in-from-top-2 duration-300">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Managed Department</label>
                        <select 
                            v-model="accountDepartmentId" 
                            class="w-full text-xs font-bold text-gray-700 p-3 rounded-xl border border-gray-200 outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 cursor-pointer bg-white"
                        >
                            <option value="">-- No Specific Department --</option>
                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                {{ dept.name }}
                            </option>
                        </select>
                        <p class="text-[9px] text-gray-400 mt-1 mb-1 italic">Select the department this account will manage/head.</p>
                    </div>

                    <div class="bg-amber-50 rounded-xl p-3 border border-amber-100">
                        <p class="text-[9px] text-amber-600 font-bold leading-relaxed">
                            <i class="pi pi-info-circle mr-1"></i>
                            An initial email (name.surname@hq.app) and default password ("password") will be generated for the user.
                        </p>
                    </div>
                </div>

                <div class="p-6 bg-gray-50 border-t border-gray-100 flex gap-3">
                    <button @click="showAccountModal = false" class="flex-1 px-4 py-2 text-xs font-black uppercase text-gray-400 hover:text-gray-600 transition-colors">Cancel</button>
                    <button 
                        @click="handleCreateAccount" 
                        :disabled="isCreatingAccount"
                        class="flex-1 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-teal-200 transition-all flex items-center justify-center gap-2"
                    >
                        <i v-if="isCreatingAccount" class="pi pi-spin pi-spinner"></i>
                        <span>Confirm Access</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import AddEmployeeModal from './AddEmployeeModal.vue';
import EditEmployeeModal from './EditEmployeeModal.vue';
import ManageShiftsModal from './ManageShiftsModal.vue';
import ResetPasswordModal from './ResetPasswordModal.vue';

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
const filterWorkingHours = ref('All');
const filterHasBalance = ref('All');
const filterHasAccount = ref(false);
const availableYears = ref([]);
const shiftStats = ref([]);

let debounceTimer = null;

// Modals
const showAddModal = ref(false);
const showEditModal = ref(false);
const showManageShiftsModal = ref(false);
const isCreating = ref(false);
const isEditing = ref(false);
const selectedEmployee = ref(null);
const selectedEmployeeForAccount = ref(null);
const editEmployeeModalRef = ref(null);
const showAccountModal = ref(false);
const showResetModal = ref(false);
const accountRole = ref('user');
const accountDepartmentId = ref('');
const isCreatingAccount = ref(false);
const selectedEmployeeForReset = ref(null);

const openResetPasswordModal = (emp) => {
    selectedEmployeeForReset.value = emp;
    showResetModal.value = true;
};

const openAccountModal = (emp) => {
    selectedEmployeeForAccount.value = emp;
    accountRole.value = 'user';
    accountDepartmentId.value = emp.department_id || '';
    showAccountModal.value = true;
};

const handleCreateAccount = async () => {
    if (!selectedEmployeeForAccount.value) return;
    
    isCreatingAccount.value = true;
    try {
        const response = await axios.post('/api/users/create-from-employee', {
            employee_id: selectedEmployeeForAccount.value.id,
            role: accountRole.value,
            department_id: accountDepartmentId.value || null
        });
        
        alert(`Account created!\nEmail: ${response.data.email}\nPassword: ${response.data.password}`);
        showAccountModal.value = false;
        fetchEmployees(currentPage.value);
    } catch (e) {
        console.error(e);
        alert(e.response?.data?.message || 'Failed to create account');
    } finally {
        isCreatingAccount.value = false;
    }
};

// Fetch Data
const activeLeaveColumns = ref([]);

const loadLeaveSettings = async () => {
    try {
        const res = await axios.get('/api/settings/leave_types');
        const typesList = res.data;
        activeLeaveColumns.value = typesList.map(label => {
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
            
            return { label, col, abbr };
        });
    } catch (error) {
        console.error('Failed to load leave types settings', error);
    }
};

const fetchDepartments = async () => {
    try {
        const response = await axios.get('/api/departments'); // Returns [{id, name, ...}]
        departments.value = response.data.sort((a, b) => a.name.localeCompare(b.name, undefined, { numeric: true, sensitivity: 'base' }));
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
    filterWorkingHours.value = 'All';
    filterHasBalance.value = 'All';
    filterHasAccount.value = false;
    searchQuery.value = '';
};

const fetchShiftStats = async () => {
    try {
        const response = await axios.get('/api/employees/shift-stats');
        shiftStats.value = response.data;
    } catch (e) {
        console.error("Shift stats error", e);
    }
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
            working_hours: filterWorkingHours.value === 'All' ? null : filterWorkingHours.value,
            has_balance: filterHasBalance.value === 'All' ? null : filterHasBalance.value,
            has_account: filterHasAccount.value ? 1 : 0,
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

const getRoleClass = (role) => {
    switch(role) {
        case 'admin': return 'bg-rose-50 text-rose-600 border-rose-200';
        case 'dept_head': return 'bg-purple-50 text-purple-600 border-purple-200';
        default: return 'bg-slate-50 text-slate-500 border-slate-200';
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
        if (editEmployeeModalRef.value) {
            editEmployeeModalRef.value.setEditMode(false);
        }
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

const archiveEmployee = async (id) => {
    if (!confirm('Move this employee to the archive registry? They will no longer appear in the active masterlist.')) return;
    try {
        await axios.post(`/api/employees/${id}/archive`);
        fetchEmployees(currentPage.value);
        alert('Employee has been successfully archived.');
    } catch (e) {
        alert('Failed to archive employee.');
    }
};

// Export Logic
const handleExport = () => {
    window.open('/api/employees/export', '_blank');
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
        fetchDepartments();
        fetchShiftStats();
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
    filterHiredYear,
    filterWorkingHours,
    filterHasBalance,
    filterHasAccount
], () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        fetchEmployees(1);
    }, 300);
});

onMounted(() => {
    loadLeaveSettings().then(() => {
        fetchEmployees();
    });
    fetchDepartments();
    fetchShiftStats();
    
    // Deep Link from Omni-Search/External
    const params = new URLSearchParams(window.location.search);
    const search = params.get('search');
    if (search) {
        searchQuery.value = search;
    }
});
</script>
