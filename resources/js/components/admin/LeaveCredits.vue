<template>
    <div v-if="user">
        <MainLayout :user="user">
            <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div>
                <h1 class="text-2xl font-black text-gray-800 tracking-tight uppercase">Leave Credit Setup</h1>
                <p class="text-sm text-gray-500 mt-1 uppercase font-bold tracking-widest opacity-70">Official Leave Balances & Bulk Adjustments</p>
            </div>
            <div class="flex items-center gap-3">
                <button 
                    v-if="selectedEmployees.length > 0"
                    @click="showBulkModal = true"
                    class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl hover:bg-indigo-700 transition-all flex items-center gap-2 shadow-lg shadow-indigo-200 font-black uppercase text-xs tracking-widest active:scale-95"
                >
                    <i class="pi pi-bolt"></i>
                    Bulk Adjust ({{ selectedEmployees.length }})
                </button>
                <div class="h-8 w-px bg-gray-200 mx-2" v-if="selectedEmployees.length > 0"></div>
                <button 
                    @click="fetchEmployees"
                    class="p-2.5 text-gray-400 hover:text-teal-600 hover:bg-teal-50 rounded-xl transition-all"
                    title="Refresh List"
                >
                    <i class="pi pi-refresh" :class="{ 'pi-spin': loading }"></i>
                </button>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="relative col-span-2">
                <i class="pi pi-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input 
                    v-model="filters.search" 
                    type="text" 
                    placeholder="Search by Name or Employee ID..." 
                    class="w-full pl-12 pr-4 py-3 bg-gray-50 border-2 border-transparent focus:border-teal-500 focus:bg-white rounded-xl outline-none transition-all font-bold text-gray-700 uppercase text-sm"
                    @input="debounceSearch"
                >
            </div>
            <div>
                <select 
                    v-model="filters.department_id" 
                    class="w-full px-4 py-3 bg-gray-50 border-2 border-transparent focus:border-teal-500 focus:bg-white rounded-xl outline-none transition-all font-bold text-gray-700 uppercase text-sm cursor-pointer"
                >
                    <option value="">All Departments</option>
                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                </select>
            </div>
            <div>
                <select 
                    v-model="filters.has_balance" 
                    class="w-full px-4 py-3 bg-gray-50 border-2 border-transparent focus:border-teal-500 focus:bg-white rounded-xl outline-none transition-all font-bold text-gray-700 uppercase text-sm cursor-pointer"
                >
                    <option value="">Filter with Balance In...</option>
                    <option value="vl">Vacation Leave (VL)</option>
                    <option value="sl">Sick Leave (SL)</option>
                    <option value="pl">Paternity Leave (PL)</option>
                    <option value="sp">Solo Parent (SP)</option>
                    <option value="bl">Bereavement (BL)</option>
                    <option value="vawc">VAWC Leave (VW)</option>
                </select>
            </div>
        </div>

        <!-- Main Table Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative">
            <div v-if="loading" class="absolute inset-0 bg-white/50 backdrop-blur-[1px] z-10 flex items-center justify-center">
                <div class="flex flex-col items-center gap-2">
                    <i class="pi pi-spin pi-spinner text-3xl text-teal-600"></i>
                    <span class="text-[10px] font-black uppercase tracking-widest text-teal-700">Loading Employees...</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 text-gray-400 text-[10px] font-black uppercase tracking-[0.2em] border-b border-gray-100">
                            <th class="px-6 py-4 w-12">
                                <input 
                                    type="checkbox" 
                                    :checked="allSelected" 
                                    @change="toggleSelectAll"
                                    class="w-4 h-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500 cursor-pointer"
                                >
                            </th>
                            <th class="px-6 py-4">Employee</th>
                            <th class="px-6 py-4 text-center">VL</th>
                            <th class="px-6 py-4 text-center">SL</th>
                            <th class="px-6 py-4 text-center">PL</th>
                            <th class="px-6 py-4 text-center">SP</th>
                            <th class="px-6 py-4 text-center">BL</th>
                            <th class="px-6 py-4 text-center">VW</th>
                            <th class="px-6 py-4 text-center">ML</th>
                            <th class="px-6 py-4 text-center">MC</th>
                            <th class="px-6 py-4 text-center">EL</th>
                            <th class="px-6 py-4 text-right pr-10">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="emp in employees" :key="emp.id" class="hover:bg-teal-50/10 transition-colors group">
                            <td class="px-6 py-4">
                                <input 
                                    type="checkbox" 
                                    v-model="selectedEmployees" 
                                    :value="emp.id"
                                    class="w-4 h-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500 cursor-pointer"
                                >
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-[10px] font-black text-gray-500 shrink-0 border border-gray-200">
                                        {{ emp.first_name[0] }}{{ emp.last_name[0] }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-gray-800 text-sm truncate uppercase">{{ emp.first_name }} {{ emp.last_name }}</p>
                                        <p class="text-[10px] text-gray-400 font-bold tracking-widest truncate uppercase">#{{ emp.employee_id }} • {{ emp.department?.name }}</p>
                                    </div>
                                </div>
                            </td>
                            <!-- Balances -->
                            <td class="px-2 py-4 text-center">
                                <span :class="{'text-gray-300': !emp.vacation_leave, 'text-teal-600 font-black': emp.vacation_leave}" class="text-sm">
                                    {{ emp.vacation_leave || '0' }}
                                </span>
                            </td>
                            <td class="px-2 py-4 text-center">
                                <span :class="{'text-gray-300': !emp.sick_leave, 'text-rose-600 font-black': emp.sick_leave}" class="text-sm">
                                    {{ emp.sick_leave || '0' }}
                                </span>
                            </td>
                            <td class="px-2 py-4 text-center text-sm font-bold text-gray-600">{{ emp.paternity_leave || '0' }}</td>
                            <td class="px-2 py-4 text-center text-sm font-bold text-gray-600">{{ emp.solo_parent_leave || '0' }}</td>
                            <td class="px-2 py-4 text-center text-sm font-bold text-gray-600">{{ emp.bereavement_leave || '0' }}</td>
                            <td class="px-2 py-4 text-center text-sm font-bold text-gray-600">{{ emp.vawc_leave || '0' }}</td>
                            <td class="px-2 py-4 text-center text-sm font-bold text-gray-600">{{ emp.maternity_leave || '0' }}</td>
                            <td class="px-2 py-4 text-center text-sm font-bold text-gray-600">{{ emp.magna_carta_leave || '0' }}</td>
                            <td class="px-2 py-4 text-center text-sm font-bold text-gray-600">{{ emp.emergency_leave || '0' }}</td>
                            
                            <td class="px-6 py-4 text-right pr-10">
                                <button 
                                    @click="openIndividualEdit(emp)"
                                    class="p-2 text-teal-600 hover:bg-teal-50 rounded-lg transition-all active:scale-95"
                                    title="Edit Official Credits"
                                >
                                    <i class="pi pi-pencil"></i>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="employees.length === 0 && !loading">
                            <td colspan="9" class="px-6 py-12 text-center text-gray-400 italic text-sm">
                                No employees found matching the criteria.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 bg-gray-50/30 border-t border-gray-100 flex justify-between items-center">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                    Showing {{ employees.length }} of {{ totalEmployees }} entries
                </span>
                <div class="flex gap-2">
                    <button 
                        :disabled="currentPage === 1"
                        @click="changePage(currentPage - 1)"
                        class="px-4 py-2 text-xs font-black uppercase tracking-widest rounded-lg border border-gray-200 disabled:opacity-30 hover:bg-white transition-all active:scale-95"
                    >
                        Prev
                    </button>
                    <button 
                        :disabled="currentPage === lastPage"
                        @click="changePage(currentPage + 1)"
                        class="px-4 py-2 text-xs font-black uppercase tracking-widest rounded-lg border border-gray-200 disabled:opacity-30 hover:bg-white transition-all active:scale-95"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>

        <!-- Individual Edit Modal -->
        <transition name="modal">
            <div v-if="editingEmp" class="fixed inset-0 z-[100] flex items-center justify-center p-4 overflow-hidden">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="editingEmp = null"></div>
                <div class="relative bg-white w-full max-w-xl rounded-3xl shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200">
                    <div class="bg-gradient-to-r from-teal-600 to-emerald-600 p-8 text-white">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-2xl font-black uppercase tracking-tight">Setup Official Credits</h3>
                                <p class="text-xs font-bold uppercase tracking-widest opacity-80 mt-1">{{ editingEmp.first_name }} {{ editingEmp.last_name }}</p>
                            </div>
                            <button @click="editingEmp = null" class="p-2 hover:bg-white/20 rounded-xl transition-all"><i class="pi pi-times"></i></button>
                        </div>
                    </div>
                    
                    <div class="p-8 space-y-6">
                        <div class="grid grid-cols-2 gap-6">
                            <div v-for="(label, key) in leaveFields" :key="key" class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest flex justify-between">
                                    {{ label }}
                                    <span class="text-teal-600 opacity-60">Value: {{ editForm[key] }}</span>
                                </label>
                                <div class="relative group">
                                    <input 
                                        v-model="editForm[key]" 
                                        type="number" 
                                        step="0.5" 
                                        class="w-full pl-4 pr-12 py-3 bg-gray-50 border-2 border-transparent focus:border-teal-500 focus:bg-white rounded-xl outline-none transition-all font-black text-gray-700 text-lg shadow-inner"
                                    >
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-gray-300 uppercase tracking-widest pointer-events-none">Days</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-gray-100 flex gap-3">
                            <button 
                                @click="editingEmp = null"
                                class="flex-1 py-4 text-xs font-black uppercase tracking-widest text-gray-400 hover:text-gray-600 transition-all active:scale-95"
                            >
                                Cancel
                            </button>
                            <button 
                                @click="handleSaveIndividual"
                                :disabled="saving"
                                class="flex-[2] bg-teal-600 text-white py-4 rounded-2xl font-black uppercase text-xs tracking-[0.2em] shadow-xl shadow-teal-100 hover:bg-teal-700 hover:-translate-y-1 transition-all active:scale-95 disabled:opacity-50"
                            >
                                <span v-if="saving">Updating...</span>
                                <span v-else>Apply Official Credits</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Bulk Adjustment Modal -->
        <transition name="modal">
            <div v-if="showBulkModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 overflow-hidden">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showBulkModal = false"></div>
                <div class="relative bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200">
                    <div class="bg-gradient-to-r from-indigo-600 to-blue-600 p-8 text-white">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-2xl font-black uppercase tracking-tight">Bulk Credit Tool</h3>
                                <p class="text-xs font-bold uppercase tracking-widest opacity-80 mt-1">Applying to {{ selectedEmployees.length }} employees</p>
                            </div>
                            <button @click="showBulkModal = false" class="p-2 hover:bg-white/20 rounded-xl transition-all"><i class="pi pi-times"></i></button>
                        </div>
                    </div>
                    
                    <div class="p-8 space-y-6">
                        <div class="space-y-4">
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">1. Select Leave Type</label>
                                <select v-model="bulkForm.leave_type" class="w-full px-4 py-3 bg-gray-50 border-2 border-transparent focus:border-indigo-500 focus:bg-white rounded-xl outline-none transition-all font-bold text-gray-700 uppercase">
                                    <option v-for="(label, key) in leaveFields" :key="key" :value="key">{{ label }}</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">2. Action Mode</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <button 
                                        @click="bulkForm.mode = 'set'"
                                        :class="bulkForm.mode === 'set' ? 'bg-indigo-600 text-white border-indigo-600 shadow-lg' : 'bg-gray-50 text-gray-400 border-gray-100 hover:border-indigo-200'"
                                        class="py-3 px-4 rounded-xl border-2 font-black uppercase text-[10px] tracking-widest transition-all"
                                    >
                                        Set Official Value
                                    </button>
                                    <button 
                                        @click="bulkForm.mode = 'add'"
                                        :class="bulkForm.mode === 'add' ? 'bg-indigo-600 text-white border-indigo-600 shadow-lg' : 'bg-gray-50 text-gray-400 border-gray-100 hover:border-indigo-200'"
                                        class="py-3 px-4 rounded-xl border-2 font-black uppercase text-[10px] tracking-widest transition-all"
                                    >
                                        Increment/Add
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">3. Amount (Days)</label>
                                <input 
                                    v-model="bulkForm.amount" 
                                    type="number" 
                                    step="0.5" 
                                    class="w-full px-4 py-3 bg-gray-50 border-2 border-transparent focus:border-indigo-500 focus:bg-white rounded-xl outline-none transition-all font-black text-gray-700 text-xl"
                                >
                            </div>

                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Reason / Memo</label>
                                <input 
                                    v-model="bulkForm.reason" 
                                    type="text" 
                                    placeholder="e.g. Annual Credit Refresh"
                                    class="w-full px-4 py-3 bg-gray-50 border-2 border-transparent focus:border-indigo-500 focus:bg-white rounded-xl outline-none transition-all font-bold text-gray-700 text-sm italic"
                                >
                            </div>
                        </div>

                        <div class="pt-6 border-t border-gray-100 flex gap-3">
                            <button 
                                @click="showBulkModal = false"
                                class="flex-1 py-4 text-xs font-black uppercase tracking-widest text-gray-400 hover:text-gray-600 transition-all active:scale-95"
                            >
                                Back
                            </button>
                            <button 
                                @click="handleSaveBulk"
                                :disabled="saving || !bulkForm.leave_type"
                                class="flex-[2] bg-indigo-600 text-white py-4 rounded-2xl font-black uppercase text-xs tracking-[0.2em] shadow-xl shadow-indigo-100 hover:bg-indigo-700 hover:-translate-y-1 transition-all active:scale-95 disabled:opacity-50"
                            >
                                <span v-if="saving">Processing...</span>
                                <span v-else>Process Bulk Update</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
            </div>
        </MainLayout>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../../stores/auth';
import { storeToRefs } from 'pinia';
import MainLayout from '../../layouts/MainLayout.vue';

const authStore = useAuthStore();
const { user } = storeToRefs(authStore);

const loading = ref(false);
const saving = ref(false);
const employees = ref([]);
const departments = ref([]);
const totalEmployees = ref(0);
const currentPage = ref(1);
const lastPage = ref(1);

const filters = reactive({
    search: '',
    department_id: '',
    has_balance: ''
});

const leaveFields = {
    'vacation_leave': 'Vacation Leave (VL)',
    'sick_leave': 'Sick Leave (SL)',
    'paternity_leave': 'Paternity Leave (PL)',
    'solo_parent_leave': 'Solo Parent (SP)',
    'bereavement_leave': 'Bereavement (BL)',
    'vawc_leave': 'VAWC Leave (VW)',
    'maternity_leave': 'Maternity (ML)',
    'magna_carta_leave': 'Magna Carta (MC)',
    'emergency_leave': 'Emergency (EL)'
};

// Selection State
const selectedEmployees = ref([]);
const allSelected = computed(() => {
    return employees.value.length > 0 && selectedEmployees.value.length === employees.value.length;
});

const toggleSelectAll = () => {
    if (allSelected.value) {
        selectedEmployees.value = [];
    } else {
        selectedEmployees.value = employees.value.map(e => e.id);
    }
};

// Edit States
const editingEmp = ref(null);
const editForm = reactive({});
const showBulkModal = ref(false);
const bulkForm = reactive({
    leave_type: 'vacation_leave',
    amount: 0,
    mode: 'set',
    reason: ''
});

const fetchEmployees = async (page = 1) => {
    loading.value = true;
    try {
        const response = await axios.get('/api/employees', {
            params: {
                page,
                search: filters.search,
                department_id: filters.department_id,
                has_balance: filters.has_balance
            }
        });
        employees.value = response.data.data;
        totalEmployees.value = response.data.total;
        lastPage.value = response.data.last_page;
        currentPage.value = response.data.current_page;
    } catch (error) {
        console.error('Failed to fetch employees:', error);
    } finally {
        loading.value = false;
    }
};

const fetchDepartments = async () => {
    try {
        const response = await axios.get('/api/departments');
        departments.value = response.data;
    } catch (error) {
        console.error('Failed to fetch departments:', error);
    }
};

const changePage = (page) => {
    if (page >= 1 && page <= lastPage.value) {
        fetchEmployees(page);
    }
};

const debounce = (fn, delay) => {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), delay);
    };
};

const debounceSearch = debounce(() => {
    fetchEmployees(1);
}, 500);

watch(() => filters.department_id, () => fetchEmployees(1));
watch(() => filters.has_balance, () => fetchEmployees(1));

const openIndividualEdit = (emp) => {
    editingEmp.value = emp;
    Object.keys(leaveFields).forEach(key => {
        editForm[key] = emp[key] || 0;
    });
};

const handleSaveIndividual = async () => {
    saving.value = true;
    try {
        await axios.put(`/api/employees/${editingEmp.value.id}`, editForm);
        editingEmp.value = null;
        fetchEmployees(currentPage.value);
    } catch (error) {
        console.error('Save failed:', error);
        alert('Failed to update employee leave credits.');
    } finally {
        saving.value = false;
    }
};

const handleSaveBulk = async () => {
    if (!bulkForm.leave_type || bulkForm.amount === undefined) return;
    
    saving.value = true;
    try {
        await axios.post('/api/employees/bulk-update-leaves', {
            employee_ids: selectedEmployees.value,
            ...bulkForm
        });
        showBulkModal.value = false;
        selectedEmployees.value = [];
        fetchEmployees(currentPage.value);
        alert('Bulk adjustment successful!');
    } catch (error) {
        console.error('Bulk update failed:', error);
        alert('Failed to process bulk adjustment.');
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    fetchEmployees();
    fetchDepartments();
});
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.3s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }

input:focus {
    box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.1);
}

select:focus {
    box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.1);
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
</style>
