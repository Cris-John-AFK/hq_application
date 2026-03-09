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
                            <option disabled>--- Leaves ---</option>
                            <option value="Vacation Leave">Vacation Leave</option>
                            <option value="Sick Leave">Sick Leave</option>
                            <option value="Paternity Leave">Paternity Leave</option>
                            <option value="Solo Parent Leave">Solo Parent Leave</option>
                            <option value="Bereavement Leave">Bereavement Leave</option>
                            <option value="VAWC Leave">VAWC Leave</option>
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
                
                <!-- Imported Dates Visualization (Quick Filter) -->
                <div v-if="availableDates.length > 0" class="bg-gray-50 p-4 rounded-2xl border border-dashed border-gray-200">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2 text-gray-600">
                            <i class="pi pi-calendar-clock text-teal-600"></i>
                            <h3 class="text-xs font-black uppercase tracking-widest">Available Imported Records</h3>
                        </div>
                        <button 
                            @click="clearDateFilters" 
                            class="text-[10px] font-bold text-teal-600 hover:text-teal-700 uppercase tracking-widest flex items-center gap-1 cursor-pointer"
                        >
                            <i class="pi pi-history"></i>
                            Show Everything
                        </button>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button 
                            v-for="date in availableDates" 
                            :key="date"
                            @click="selectSpecificDate(date)"
                            :class="isDateSelected(date) ? 'bg-teal-600 text-white border-teal-600 ring-2 ring-teal-100' : 'bg-white text-gray-600 border-gray-200 hover:border-teal-300'"
                            class="px-3 py-1.5 border rounded-xl text-xs font-bold transition-all cursor-pointer shadow-sm flex items-center gap-2"
                        >
                            <span class="w-1.5 h-1.5 rounded-full" :class="isDateSelected(date) ? 'bg-white' : 'bg-teal-400'"></span>
                            {{ formatDate(date) }}
                        </button>
                    </div>
                </div>

                <!-- Attendance Table -->
                <div class="relative">
                    <div v-if="isLoadingRecords" class="absolute inset-0 z-10 bg-white/50 flex items-center justify-center">
                        <i class="pi pi-spin pi-spinner text-3xl text-teal-600"></i>
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
                                            <div class="w-8 h-8 rounded-full overflow-hidden bg-teal-50 flex items-center justify-center text-teal-600 font-black text-[10px] uppercase shadow-inner border border-teal-100">
                                                <img v-if="record.avatar" :src="record.avatar" class="w-full h-full object-cover">
                                                <span v-else>{{ getInitials(record.employee_name) }}</span>
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
                                                'bg-yellow-100 text-yellow-700': record.status === 'Half Day',
                                                'bg-blue-100 text-blue-700': !['Present', 'Absent', 'Late', 'Half Day'].includes(record.status)
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
                    
                    <div class="mt-6 flex flex-col gap-4">
                        <!-- Progress View -->
                        <div v-if="isImporting" class="space-y-3">
                            <div class="flex justify-between items-end">
                                <span class="text-xs font-bold text-teal-600 uppercase tracking-widest">{{ importStatus }}</span>
                                <span class="text-xs font-black text-gray-400">{{ importProgress }}%</span>
                            </div>
                            <div class="h-2 w-full bg-gray-100 rounded-full overflow-hidden border border-gray-50">
                                <div 
                                    class="h-full bg-teal-500 rounded-full transition-all duration-300 shadow-sm"
                                    :style="{ width: importProgress + '%' }"
                                ></div>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <button 
                                @click="showImportModal = false"
                                :disabled="isImporting"
                                class="cursor-pointer flex-1 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-medium transition-colors disabled:opacity-50"
                            >
                                Cancel
                            </button>
                            <button 
                                @click="importFile"
                                :disabled="!selectedFile || isImporting"
                                class="cursor-pointer flex-1 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                            >
                                <i v-if="isImporting" class="pi pi-spin pi-spinner"></i>
                                <span>{{ isImporting ? 'Processing...' : 'Import' }}</span>
                            </button>
                        </div>
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
import { ref, computed, onMounted, watch } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { useEmployeeStore } from '../../stores/employees';
import { storeToRefs } from 'pinia';
import MainLayout from '../../layouts/MainLayout.vue';
import EmployeeAttendanceModal from '../common/EmployeeAttendanceModal.vue';
import * as XLSX from 'xlsx';
import axios from 'axios';

const authStore = useAuthStore();
const employeeStore = useEmployeeStore();
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
const importProgress = ref(0);
const importStatus = ref('');

// Pinia refs
const { departments: storeDepartments } = storeToRefs(employeeStore);

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
const availableDates = ref([]);

const isLoadingRecords = ref(false);
const fetchAttendanceRecords = async () => {
    isLoadingRecords.value = true;
    try {
        const params = {};
        if (filters.value.startDate) params.start_date = filters.value.startDate;
        if (filters.value.endDate) params.end_date = filters.value.endDate;

        // Note: Department and Status filters are handled client-side in 'filteredRecords'
        // for better consistency with the employee masterlist.
        const { data } = await axios.get('/api/attendance-records', { params });
        attendanceRecords.value = data.map(r => {
            return {
                ...r,
                employee_id: r.employee_id_number,
                department: r.employee_department || r.department || '--',
                position: r.employee_position || r.position || '--',
            };
        });
    } catch (error) {
        console.error('Failed to fetch attendance records:', error);
    } finally {
        isLoadingRecords.value = false;
    }
};

const fetchAvailableDates = async () => {
    try {
        const { data } = await axios.get('/api/attendance-records/dates');
        availableDates.value = data;
    } catch (error) {
        console.error('Failed to fetch available dates:', error);
    }
};

// Fetch employees from database
// Fetch light data (departments) from store
const fetchInitData = async () => {
    try {
        await employeeStore.fetchDepartments(); // Lightweight department fetch
        fetchAttendanceRecords();
        fetchAvailableDates();
    } catch (error) {
        console.error('Failed to fetch initial data:', error);
    }
};

watch(() => filters.value, () => {
    fetchAttendanceRecords();
}, { deep: true });

const departments = computed(() => {
    return employeeStore.departmentNames;
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
const isDateSelected = (date) => {
    return filters.value.startDate === date && filters.value.endDate === date;
};

const selectSpecificDate = (date) => {
    if (isDateSelected(date)) {
        filters.value.startDate = '';
        filters.value.endDate = '';
    } else {
        filters.value.startDate = date;
        filters.value.endDate = date;
    }
};

const clearDateFilters = () => {
    filters.value.startDate = '';
    filters.value.endDate = '';
};
const formatDate = (dateStr) => {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const getInitials = (name) => {
    if (!name) return '??';
    const parts = name.trim().split(' ').filter(Boolean);
    return parts.map(n => n.charAt(0)).join('').toUpperCase().substring(0, 2);
};

const handleFileSelect = (event) => {
    selectedFile.value = event.target.files[0];
};

const importFile = async () => {
    if (!selectedFile.value) return;
    
    isImporting.value = true;
    importProgress.value = 10;
    importStatus.value = 'Reading Excel file...';
    
    try {
        const reader = new FileReader();
        reader.onload = async (e) => {
            importProgress.value = 30;
            importStatus.value = 'Parsing biometric logs...';
            
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, { type: 'array' });
            const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
            
            // Use header:1 to get array of arrays, skipping the first 5 rows (metadata + headers)
            const rows = XLSX.utils.sheet_to_json(firstSheet, { header: 1 });
            const dataRows = rows.slice(5);
            
            if (dataRows.length === 0) {
                alert('No data found in the Excel file.');
                isImporting.value = false;
                return;
            }

            importProgress.value = 50;
            importStatus.value = 'Grouping logs by personnel...';

            const groups = {};
            
            dataRows.forEach(row => {
                if (!row || row.length < 5) return;
                
                const personnelId = String(row[0] || '').trim();
                const personnelName = String(row[1] || '').trim();
                const rawDate = String(row[2] || '').trim();
                const rawTime = String(row[3] || '').trim();
                const logType = String(row[4] || '').trim().toUpperCase();

                if (!personnelId || !rawDate || personnelId === 'undefined' || rawDate === 'undefined') return;

                // Format date from MM-DD-YYYY to YYYY-MM-DD
                let formattedDate = rawDate;
                if (rawDate.includes('-') || rawDate.includes('/')) {
                    const parts = rawDate.split(/[-/]/);
                    if (parts.length === 3) {
                        let m = parts[0], d = parts[1], y = parts[2];
                        if (y.length === 2) y = "20" + y;
                        formattedDate = `${y}-${m.padStart(2, '0')}-${d.padStart(2, '0')}`;
                    }
                }

                const key = `${personnelId}_${formattedDate}`;
                if (!groups[key]) {
                    groups[key] = {
                        id: personnelId,
                        name: personnelName, // Initially take the name from the first row found
                        date: formattedDate,
                        logs: []
                    };
                }
                
                // If name was missing on the first row but exists on this one, update it
                if (!groups[key].name && personnelName) {
                    groups[key].name = personnelName;
                }

                groups[key].logs.push({ time: rawTime, type: logType });
            });

            importProgress.value = 70;
            importStatus.value = 'Calculating work hours...';

            const recordsToUpload = Object.values(groups).map(group => {
                const inLogs = group.logs.filter(l => l.type === 'IN').sort((a, b) => a.time.localeCompare(b.time));
                const outLogs = group.logs.filter(l => l.type === 'OUT').sort((a, b) => b.time.localeCompare(a.time));

                const timeIn = inLogs.length > 0 ? inLogs[0].time : '-';
                const timeOut = outLogs.length > 0 ? outLogs[0].time : '-';

                // Calculate hours worked
                let hoursWorked = 0;
                if (timeIn !== '-' && timeOut !== '-') {
                    try {
                        const parseTime = (s) => {
                            const [timePart, period] = s.split(' ');
                            let [h, m] = timePart.split(':').map(Number);
                            if (period === 'PM' && h < 12) h += 12;
                            if (period === 'AM' && h === 12) h = 0;
                            return h * 60 + m;
                        };
                        const start = parseTime(timeIn);
                        const end = parseTime(timeOut);
                        let diff = end - start;
                        if (diff < 0) diff += 1440; // overnight check
                        hoursWorked = (diff / 60).toFixed(2);
                    } catch (e) {
                        hoursWorked = 0;
                    }
                }

                // Since we don't have all employees locally anymore, we rely on the backend to match them
                // But we can try to guess from store if it's already there (rarely)
                return {
                    employee_id_number: group.id,
                    employee_name: group.name,
                    date: group.date,
                    department: 'N/A', // Let backend fix or update via sync
                    time_in: timeIn,
                    time_out: timeOut,
                    hours_worked: hoursWorked,
                    status: hoursWorked > 0 ? (hoursWorked < 4.5 ? 'Half Day' : 'Present') : 'Absent'
                };
            });

            importProgress.value = 90;
            importStatus.value = 'Uploading to server...';

            try {
                await axios.post('/api/attendance-records/bulk', { records: recordsToUpload });
                await fetchAttendanceRecords();
                await fetchAvailableDates();
                importProgress.value = 100;
                importStatus.value = 'Done!';
                setTimeout(() => {
                    showImportModal.value = false;
                    selectedFile.value = null;
                    isImporting.value = false;
                    importProgress.value = 0;
                    alert(`Successfully imported ${recordsToUpload.length} attendance records!`);
                }, 500);
            } catch (err) {
                console.error('Bulk upload failed:', err);
                alert('Failed to upload records to server.');
                isImporting.value = false;
            }
        };
        reader.readAsArrayBuffer(selectedFile.value);
    } catch (error) {
        console.error('Import failed:', error);
        alert('Failed to import file. Please check the format and try again.');
        isImporting.value = false;
    }
};

const downloadTemplate = () => {
    const template = [
        {
            'Date': '2026-01-20',
            'Employee Name': 'John Doe',
            'Employee ID': 'HQI-0001',
            'Department': 'Production',
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
    fetchInitData();
});
</script>
