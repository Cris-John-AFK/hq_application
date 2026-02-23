<template>
    <div v-if="user">
        <MainLayout :user="user">
            <div class="max-w-7xl mx-auto space-y-6">
                <!-- Header -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Attendance Management</h2>
                        <p class="text-gray-500">Import, view, and manage employee attendance records.</p>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <button 
                            @click="showImportModal = true"
                            class="cursor-pointer h-10 px-4 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-medium shadow-sm shadow-teal-200 transition-all flex items-center gap-2"
                        >
                            <i class="pi pi-upload"></i>
                            Import Excel
                        </button>
                        <button 
                            @click="downloadTemplate"
                            class="cursor-pointer h-10 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-medium transition-all flex items-center gap-2"
                        >
                            <i class="pi pi-download"></i>
                            Download Template
                        </button>
                    </div>
                </div>

                <!-- WIP Banner -->
                <div class="flex items-center gap-4 bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                        <i class="pi pi-wrench text-amber-600 text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm font-black text-amber-800">This page is a Work in Progress — All data shown is a placeholder.</p>
                        <p class="text-xs text-amber-600 mt-0.5 font-medium">Attendance Management requires a biometric or time-keeping system integration before real records can be tracked. Import and filter features are non-functional at this time.</p>
                    </div>
                    <div class="ml-auto flex-shrink-0">
                        <span class="text-[10px] font-black uppercase tracking-widest text-amber-500 bg-amber-100 border border-amber-200 px-3 py-1.5 rounded-full">Coming Soon</span>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-wrap gap-4 items-center">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-600">Date Range:</span>
                        <input 
                            v-model="filters.startDate" 
                            type="date" 
                            class="h-9 px-3 border border-gray-200 rounded-lg text-sm bg-white focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none"
                        >
                        <span class="text-gray-400">to</span>
                        <input 
                            v-model="filters.endDate" 
                            type="date" 
                            class="h-9 px-3 border border-gray-200 rounded-lg text-sm bg-white focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none"
                        >
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-600">Department:</span>
                        <select v-model="filters.department" class="h-9 px-3 border border-gray-200 rounded-lg text-sm bg-white focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none">
                            <option value="">All Departments</option>
                            <option v-for="dept in departments" :key="dept" :value="dept">{{ dept }}</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-600">Status:</span>
                        <select v-model="filters.status" class="h-9 px-3 border border-gray-200 rounded-lg text-sm bg-white focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none">
                            <option value="">All Status</option>
                            <option value="Present">Present</option>
                            <option value="Absent">Absent</option>
                            <option value="Late">Late</option>
                            <option value="Half Day">Half Day</option>
                        </select>
                    </div>

                    <div class="relative flex-1 min-w-[200px]">
                        <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                        <input 
                            v-model="filters.search" 
                            type="text" 
                            placeholder="Search employee..." 
                            class="h-9 pl-10 pr-3 w-full border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none"
                        >
                    </div>
                </div>

                <!-- Attendance Table -->
                <div class="relative">
                    <!-- Frosted Glass WIP Overlay -->
                    <div class="absolute inset-0 z-10 rounded-2xl backdrop-blur-[3px] bg-white/60 flex flex-col items-center justify-center gap-4 border border-dashed border-amber-300" style="min-height: 200px;">
                        <div class="bg-white rounded-2xl shadow-xl border border-amber-100 px-10 py-8 flex flex-col items-center gap-3 max-w-md text-center">
                            <div class="w-16 h-16 rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-center">
                                <i class="pi pi-clock text-3xl text-amber-500"></i>
                            </div>
                            <h3 class="text-lg font-black text-gray-900">Attendance Tracking Not Yet Available</h3>
                            <p class="text-sm text-gray-500 font-medium leading-relaxed">This section will show real-time attendance records once a biometric or time-keeping integration is in place. All data below is <strong class="text-amber-600">sample/placeholder only</strong>.</p>
                            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-500 bg-amber-50 border border-amber-200 px-4 py-2 rounded-full mt-1">🔧 Under Construction</span>
                        </div>
                    </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-gray-900 text-white uppercase tracking-wider text-xs">
                                    <th class="px-6 py-4 font-semibold">Date</th>
                                    <th class="px-6 py-4 font-semibold">Employee</th>
                                    <th class="px-4 py-4 font-semibold">Department</th>
                                    <th class="px-4 py-4 text-center font-semibold">Time In</th>
                                    <th class="px-4 py-4 text-center font-semibold">Time Out</th>
                                    <th class="px-4 py-4 text-center font-semibold">Hours Worked</th>
                                    <th class="px-4 py-4 text-center font-semibold">Status</th>
                                    <th class="px-4 py-4 text-center font-semibold">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="record in paginatedRecords" :key="record.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-3 font-medium text-gray-800">{{ formatDate(record.date) }}</td>
                                    <td class="px-6 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-teal-100 flex items-center justify-center text-teal-600 font-bold text-xs">
                                                {{ record.avatar }}
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-800">{{ record.employee_name }}</p>
                                                <p class="text-xs text-gray-500">{{ record.employee_id }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ record.department }}</td>
                                    <td class="px-4 py-3 text-center text-gray-600">{{ record.time_in || '-' }}</td>
                                    <td class="px-4 py-3 text-center text-gray-600">{{ record.time_out || '-' }}</td>
                                    <td class="px-4 py-3 text-center font-bold text-teal-600">{{ record.hours_worked || '-' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span 
                                            :class="{
                                                'bg-green-100 text-green-700': record.status === 'Present',
                                                'bg-red-100 text-red-700': record.status === 'Absent',
                                                'bg-orange-100 text-orange-700': record.status === 'Late',
                                                'bg-yellow-100 text-yellow-700': record.status === 'Half Day'
                                            }"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        >
                                            {{ record.status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <button 
                                            @click="viewEmployeeAttendance(record)"
                                            class="cursor-pointer w-8 h-8 rounded-full bg-purple-50 text-purple-600 hover:bg-purple-100 flex items-center justify-center transition-colors mx-auto"
                                        >
                                            <i class="pi pi-eye text-xs"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="filteredRecords.length === 0">
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                        <i class="pi pi-inbox text-4xl mb-3 block text-gray-300"></i>
                                        No attendance records found. Import an Excel file to get started.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="p-4 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
                        <span>Showing {{ startEntry }} to {{ endEntry }} of {{ filteredRecords.length }} entries</span>
                        <div class="flex gap-2">
                            <button 
                                @click="prevPage" 
                                :disabled="currentPage === 1"
                                class="cursor-pointer px-3 py-1 border border-gray-200 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
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
                                class="cursor-pointer px-3 py-1 border border-gray-200 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </MainLayout>

        <!-- Import Modal -->
        <div v-if="showImportModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
                <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-800">Import Attendance</h3>
                    <button 
                        @click="showImportModal = false" 
                        class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors"
                    >
                        <i class="pi pi-times text-gray-500"></i>
                    </button>
                </div>
                
                <div class="p-6">
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-teal-500 transition-colors">
                        <input 
                            type="file" 
                            ref="fileInput"
                            @change="handleFileSelect"
                            accept=".xlsx,.xls"
                            class="hidden"
                        >
                        <i class="pi pi-cloud-upload text-4xl text-gray-400 mb-3"></i>
                        <p class="text-gray-700 font-medium mb-1">Drop your Excel file here</p>
                        <p class="text-sm text-gray-500 mb-4">or click to browse</p>
                        <button 
                            @click="$refs.fileInput.click()"
                            class="cursor-pointer px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg font-medium transition-colors"
                        >
                            Select File
                        </button>
                        <p v-if="selectedFile" class="mt-4 text-sm text-teal-600 font-medium">
                            <i class="pi pi-file-excel mr-2"></i>{{ selectedFile.name }}
                        </p>
                    </div>
                    
                    <div class="mt-6 flex gap-3">
                        <button 
                            @click="showImportModal = false"
                            class="cursor-pointer flex-1 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-medium transition-colors"
                        >
                            Cancel
                        </button>
                        <button 
                            @click="importFile"
                            :disabled="!selectedFile || isImporting"
                            class="cursor-pointer flex-1 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                        >
                            <i v-if="isImporting" class="pi pi-spin pi-spinner"></i>
                            <span>{{ isImporting ? 'Importing...' : 'Import' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employee Attendance Modal -->
        <EmployeeAttendanceModal 
            v-model="showEmployeeModal"
            :employee="selectedEmployee"
            :records="employeeRecords"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { storeToRefs } from 'pinia';
import MainLayout from '../../layouts/MainLayout.vue';
import EmployeeAttendanceModal from '../common/EmployeeAttendanceModal.vue';
import * as XLSX from 'xlsx';
import axios from 'axios';

const authStore = useAuthStore();
const { user } = storeToRefs(authStore);

// State
const showImportModal = ref(false);
const showEmployeeModal = ref(false);
const selectedFile = ref(null);
const isImporting = ref(false);
const fileInput = ref(null);
const selectedEmployee = ref(null);
const currentPage = ref(1);
const itemsPerPage = 10;
const employees = ref([]);
const isLoadingEmployees = ref(false);

// Filters
const filters = ref({
    startDate: '',
    endDate: '',
    department: '',
    status: '',
    search: ''
});

// Attendance records - will be populated from Excel import
const attendanceRecords = ref([]);

// Fetch employees from database
const fetchEmployees = async () => {
    isLoadingEmployees.value = true;
    try {
        const response = await axios.get('/api/users');
        employees.value = response.data.map(user => ({
            id: user.id,
            name: user.name,
            employee_id: user.id_number || `HQI-${String(user.id).padStart(4, '0')}`,
            department: user.department?.name || user.department || 'N/A',
            avatar: user.avatar_url
        }));
        
        // Generate sample attendance for demonstration (optional - remove in production)
        generateSampleAttendance();
    } catch (error) {
        console.error('Failed to fetch employees:', error);
    } finally {
        isLoadingEmployees.value = false;
    }
};

// Generate sample attendance records using real employees (for demo purposes)
const generateSampleAttendance = () => {
    if (employees.value.length === 0) return;
    
    const statuses = ['Present', 'Absent', 'Late', 'Half Day'];
    const today = new Date();
    const sampleRecords = [];
    
    // Generate last 7 days of attendance for each employee
    for (let dayOffset = 0; dayOffset < 7; dayOffset++) {
        const date = new Date(today);
        date.setDate(date.getDate() - dayOffset);
        const dateStr = date.toISOString().split('T')[0];
        
        employees.value.forEach((emp, index) => {
            const status = statuses[Math.floor(Math.random() * statuses.length)];
            const timeIn = status !== 'Absent' ? '08:00' : '-';
            const timeOut = status === 'Half Day' ? '12:30' : (status !== 'Absent' ? '17:00' : '-');
            const hoursWorked = status === 'Absent' ? '-' : (status === 'Half Day' ? '4.5' : '8.0');
            
            sampleRecords.push({
                id: sampleRecords.length + 1,
                date: dateStr,
                employee_name: emp.name,
                employee_id: emp.employee_id,
                department: emp.department,
                time_in: timeIn,
                time_out: timeOut,
                hours_worked: hoursWorked,
                status: status,
            });
        });
    }
    
    attendanceRecords.value = sampleRecords;
};

const departments = computed(() => {
    const depts = [...new Set(employees.value.map(e => e.department))];
    return depts.filter(d => d !== 'N/A');
});

// Computed
const filteredRecords = computed(() => {
    return attendanceRecords.value.filter(record => {
        const matchesSearch = !filters.value.search || 
            record.employee_name.toLowerCase().includes(filters.value.search.toLowerCase()) ||
            record.employee_id.toLowerCase().includes(filters.value.search.toLowerCase());
        
        const matchesDepartment = !filters.value.department || record.department === filters.value.department;
        const matchesStatus = !filters.value.status || record.status === filters.value.status;
        
        const matchesDateRange = (!filters.value.startDate || record.date >= filters.value.startDate) &&
                                 (!filters.value.endDate || record.date <= filters.value.endDate);
        
        return matchesSearch && matchesDepartment && matchesStatus && matchesDateRange;
    });
});

const totalPages = computed(() => Math.ceil(filteredRecords.value.length / itemsPerPage));

const paginatedRecords = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return filteredRecords.value.slice(start, end);
});

const startEntry = computed(() => filteredRecords.value.length === 0 ? 0 : (currentPage.value - 1) * itemsPerPage + 1);
const endEntry = computed(() => Math.min(currentPage.value * itemsPerPage, filteredRecords.value.length));

const employeeRecords = computed(() => {
    if (!selectedEmployee.value) return [];
    return attendanceRecords.value.filter(r => r.employee_id === selectedEmployee.value.employee_id);
});

// Methods
const formatDate = (dateStr) => {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const getInitials = (name) => {
    if (!name) return '??';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
};

const handleFileSelect = (event) => {
    selectedFile.value = event.target.files[0];
};

const importFile = async () => {
    if (!selectedFile.value) return;
    
    isImporting.value = true;
    
    try {
        const reader = new FileReader();
        reader.onload = (e) => {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, { type: 'array' });
            const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
            const jsonData = XLSX.utils.sheet_to_json(firstSheet);
            
            // Process and add to attendance records
            jsonData.forEach((row, index) => {
                // Try to match with existing employee
                const empId = row['Employee ID'] || '';
                const empName = row['Employee Name'] || '';
                const matchedEmployee = employees.value.find(e => 
                    e.employee_id === empId || e.name.toLowerCase() === empName.toLowerCase()
                );
                
                attendanceRecords.value.push({
                    id: attendanceRecords.value.length + index + 1,
                    date: row.Date || new Date().toISOString().split('T')[0],
                    employee_name: matchedEmployee?.name || empName || 'Unknown',
                    employee_id: matchedEmployee?.employee_id || empId || 'N/A',
                    department: matchedEmployee?.department || row.Department || 'N/A',
                    time_in: row['Time In'] || '-',
                    time_out: row['Time Out'] || '-',
                    hours_worked: row['Hours Worked'] || '-',
                    status: row.Status || 'Present',
                    avatar: avatar_url
                });
            });
            
            showImportModal.value = false;
            selectedFile.value = null;
            alert(`Successfully imported ${jsonData.length} attendance records!`);
        };
        reader.readAsArrayBuffer(selectedFile.value);
    } catch (error) {
        console.error('Import failed:', error);
        alert('Failed to import file. Please check the format and try again.');
    } finally {
        isImporting.value = false;
    }
};

const downloadTemplate = () => {
    const sampleEmployee = employees.value.length > 0 ? employees.value[0] : {
        name: 'John Doe',
        employee_id: 'HQI-0001',
        department: 'Production'
    };
    
    const template = [
        {
            'Date': '2026-01-20',
            'Employee Name': sampleEmployee.name,
            'Employee ID': sampleEmployee.employee_id,
            'Department': sampleEmployee.department,
            'Time In': '08:00',
            'Time Out': '17:00',
            'Hours Worked': '8.0',
            'Status': 'Present'
        }
    ];
    
    const ws = XLSX.utils.json_to_sheet(template);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Attendance Template");
    XLSX.writeFile(wb, "attendance_template.xlsx");
};

const viewEmployeeAttendance = (record) => {
    selectedEmployee.value = record;
    showEmployeeModal.value = true;
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) currentPage.value++;
};

const prevPage = () => {
    if (currentPage.value > 1) currentPage.value--;
};

onMounted(() => {
    authStore.fetchUser();
    fetchEmployees();
});
</script>
