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
                                    'bg-gray-100 text-gray-600': employee.status === 'On Leave'
                                }"
                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium"
                            >
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                {{ employee.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button class="text-gray-400 hover:text-teal-600 transition-colors cursor-pointer">
                                <i class="pi pi-ellipsis-v"></i>
                            </button>
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
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const selectedDepartment = ref('All');
const selectedStatus = ref('All');
const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = 10;

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
</script>
