<template>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-gray-800">Employee List</h3>
                <p class="text-sm text-gray-500">Manage and view all registered employees</p>
            </div>
            
            <div class="flex flex-col md:flex-row gap-3 items-center">
                <div class="flex items-center gap-2">
                    <button 
                        @click="showAddModal = true"
                        class="cursor-pointer h-10 px-4 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2 shadow-sm whitespace-nowrap"
                    >
                        <i class="pi pi-plus"></i>
                        Add Employee
                    </button>

                    <!-- Bulk Credits Action -->
                    <button 
                        v-if="selectedEmployeeIds.length > 0"
                        @click="showBulkCreditsModal = true"
                        class="cursor-pointer h-10 px-5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all flex items-center justify-center gap-3 shadow-xl shadow-indigo-100 animate-in slide-in-from-left-4 duration-300 whitespace-nowrap group"
                    >
                        <i class="pi pi-bolt text-indigo-200 group-hover:text-white transition-colors"></i>
                        <span>Apply Bulk Credits</span>
                        <span class="bg-indigo-500 text-[10px] px-2 py-0.5 rounded-full border border-indigo-400/50 shadow-inner">
                            {{ selectedEmployeeIds.length }}
                        </span>
                    </button>

                    <!-- Nuclear Reset Action -->
                    <button 
                        @click="openNuclearReset"
                        class="cursor-pointer h-10 px-4 bg-white border border-rose-200 text-rose-500 hover:bg-rose-50 rounded-xl text-xs font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 shadow-sm whitespace-nowrap"
                    >
                        <i class="pi pi-trash text-[10px]"></i>
                        Reset All
                    </button>

                    <!-- Bulk Import (Future Intent) -->
                    <button 
                        @click="triggerBulkImport"
                        class="cursor-pointer h-10 px-4 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2 shadow-sm whitespace-nowrap"
                    >
                        <i class="pi pi-upload text-teal-600"></i>
                        Bulk Import
                    </button>
                    <input 
                        type="file" 
                        ref="importInput" 
                        style="display: none" 
                        accept=".xlsx,.xls,.csv"
                        @change="handleFileUpload"
                    />
                </div>
                <!-- Department Filter -->
                <div class="relative w-full md:w-auto">
                    <i class="pi pi-filter absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                    <select 
                        v-model="selectedDepartment" 
                        class="h-10 pl-10 pr-8 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none bg-white appearance-none cursor-pointer w-full"
                    >
                        <option value="All">All Departments</option>
                        <option v-for="dept in departments" :key="dept" :value="dept">{{ dept }}</option>
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
                        <th class="px-4 py-4 font-semibold text-center w-12">
                            <input 
                                type="checkbox" 
                                :checked="isAllSelected" 
                                @change="toggleSelectAll" 
                                class="w-4 h-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500 cursor-pointer"
                            >
                        </th>
                        <th class="px-6 py-4 font-semibold">Employee</th>
                        <th class="px-6 py-4 font-semibold">ID</th>
                        <th class="px-6 py-4 font-semibold">Department</th>
                        <th class="px-6 py-4 font-semibold">Position</th>
                        <th class="px-6 py-4 font-semibold text-center whitespace-nowrap">SIL Credits</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-12 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <!-- Skeleton Loading Rows -->
                    <template v-if="isLoading">
                        <tr v-for="i in 5" :key="i" class="animate-pulse">
                            <td class="px-4 py-4 text-center"><div class="w-4 h-4 bg-gray-100 rounded mx-auto"></div></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gray-100"></div>
                                    <div class="space-y-2">
                                        <div class="h-4 w-24 bg-gray-100 rounded"></div>
                                        <div class="h-2 w-32 bg-gray-100 rounded"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4"><div class="h-4 w-16 bg-gray-100 rounded"></div></td>
                            <td class="px-6 py-4"><div class="h-4 w-20 bg-gray-100 rounded"></div></td>
                            <td class="px-6 py-4"><div class="h-4 w-24 bg-gray-100 rounded"></div></td>
                            <td class="px-6 py-4"><div class="h-6 w-8 bg-gray-100 rounded mx-auto"></div></td>
                            <td class="px-6 py-4"><div class="h-8 w-24 bg-gray-100 rounded ml-auto"></div></td>
                        </tr>
                    </template>
                    
                    <template v-else>
                        <tr v-for="(employee, index) in paginatedEmployees" :key="employee.id" class="hover:bg-gray-50/50 transition-colors" :class="{'bg-teal-50/30': selectedEmployeeIds.includes(employee.id)}">
                            <td class="px-4 py-4 text-center">
                                <input 
                                    type="checkbox" 
                                    :value="employee.id" 
                                    v-model="selectedEmployeeIds"
                                    class="w-4 h-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500 cursor-pointer"
                                >
                            </td>
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
                                        <div class="flex items-center gap-2">
                                            <p class="font-medium text-gray-800">{{ employee.name }}</p>
                                            <span v-if="employee.role === 'admin'" class="px-1.5 py-0.5 bg-blue-100 text-blue-700 text-[9px] font-bold rounded uppercase tracking-tighter shadow-sm border border-blue-200">Admin</span>
                                        </div>
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
                            <td class="px-6 py-4 text-sm text-gray-600">{{ employee.position }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-sm font-black bg-teal-50 text-teal-700 border border-teal-100 min-w-[3rem] justify-center shadow-sm">
                                    {{ Number(employee.leave_credits) % 1 === 0 ? Number(employee.leave_credits).toFixed(0) : employee.leave_credits }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <span 
                                    :class="{
                                        'bg-green-100 text-green-700': employee.status === 'Available',
                                        'bg-gray-100 text-gray-600': employee.status === 'On Leave',
                                        'bg-blue-100 text-blue-600': employee.status === 'Working',
                                        'bg-red-100 text-red-600': employee.status === 'Absent',
                                    }"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium"
                                >
                                    {{ employee.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Edit Employee -->
                                    <div class="relative group">
                                        <button 
                                            @click="openEditModal(employee)" 
                                            class="cursor-pointer w-8 h-8 rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition-colors"
                                        >
                                            <i class="pi pi-pencil text-xs"></i>
                                        </button>
                                        <span class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50 shadow-lg">
                                            Edit Details
                                            <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-800"></span>
                                        </span>
                                    </div>

                                    <!-- View Leaves -->
                                    <div class="relative group">
                                        <button 
                                            @click="handleViewLeaves(employee)" 
                                            class="cursor-pointer w-8 h-8 rounded-full bg-teal-50 text-teal-600 hover:bg-teal-100 flex items-center justify-center transition-colors"
                                        >
                                            <i class="pi pi-calendar text-xs"></i>
                                        </button>
                                        <span class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50 shadow-lg">
                                            View Leaves
                                            <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-800"></span>
                                        </span>
                                    </div>

                                    <!-- View Report -->
                                    <div class="relative group">
                                        <button 
                                            @click="handleViewReport(employee)" 
                                            class="cursor-pointer w-8 h-8 rounded-full bg-purple-50 text-purple-600 hover:bg-purple-100 flex items-center justify-center transition-colors"
                                        >
                                            <i class="pi pi-chart-bar text-xs"></i>
                                        </button>
                                        <span class="absolute bottom-full mb-2 right-0 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50 shadow-lg">
                                            View Report
                                            <span class="absolute top-full right-4 border-4 border-transparent border-t-gray-800"></span>
                                        </span>
                                    </div>

                                    <!-- Mark On Leave -->
                                    <div class="relative group">
                                        <button 
                                            @click="openLeaveModal(employee.id, 'On Leave')"
                                            :class="[
                                                'cursor-pointer w-8 h-8 rounded-full flex items-center justify-center transition-colors',
                                                employee.status === 'On Leave' 
                                                    ? 'bg-orange-100 text-orange-600 hover:bg-orange-200' 
                                                    : 'bg-blue-50 text-blue-600 hover:bg-blue-100'
                                            ]"
                                        >
                                            <i class="pi pi-clock text-xs"></i>
                                        </button>
                                        <span class="absolute bottom-full mb-2 right-0 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50 shadow-lg">
                                            Mark On Leave
                                            <span class="absolute top-full right-2 border-4 border-transparent border-t-gray-800"></span>
                                        </span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredByStatus.length === 0">
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                No employees found matching your criteria.
                            </td>
                        </tr>
                    </template>
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
            :departments="departments"
            @submit="handleCreateEmployee"
        />

        <!-- Edit Employee Modal -->
        <EditEmployeeModal 
            v-model="showEditModal" 
            :employee="selectedEmployee"
            :loading="isEditing"
            :departments="departments"
            @submit="handleUpdateEmployee"
            @changePassword="handleChangePassword"
        />

        <!-- Leave Form Modal (Reusable Component) -->
        <LeaveRequestModal 
            v-model="showLeaveModal" 
            :leave-type="leaveForm.type"
            :employee-name="getEmployeeName(leaveForm.employeeId)"
            @submit="handleLeaveSubmit"
        />

        <!-- Change Password Modal -->
        <ChangePasswordModal 
            v-model="showPasswordModal"
            :employee-id="selectedEmployee?.id"
            :employee-name="selectedEmployee?.name"
            @success="handlePasswordChanged"
        />

        <!-- Employee Report Modal -->
        <EmployeeReportModal 
            v-model="showReportModal"
            :employee="selectedEmployee"
        />

        <!-- Bulk Credits Modal -->
        <div v-if="showBulkCreditsModal" class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-in fade-in duration-200">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden animate-in zoom-in-95 duration-200">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <div>
                        <h3 class="text-xl font-black text-gray-800 tracking-tight">Bulk Crediting</h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">Update {{ selectedEmployeeIds.length }} Employees</p>
                    </div>
                    <button @click="showBulkCreditsModal = false" class="p-2 hover:bg-gray-200 rounded-full transition-colors text-gray-400">
                        <i class="pi pi-times text-sm"></i>
                    </button>
                </div>
                <div class="p-8">
                    <div class="mb-6 bg-teal-50 border border-teal-100 p-4 rounded-xl flex items-center gap-4">
                        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-sm text-teal-600">
                            <i class="pi pi-bolt text-xl animate-pulse"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-teal-900 uppercase tracking-widest">Automatic Add</p>
                            <p class="text-[10px] text-teal-600">Enter the amount to add to all selected candidates.</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 pl-1">Amount of Points/Credits</label>
                            <div class="relative">
                                <input 
                                    v-model="bulkCreditAmount" 
                                    type="number" 
                                    step="0.5"
                                    class="w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:bg-white transition-all outline-none font-bold text-lg"
                                    placeholder="e.g. 5.0"
                                >
                                <i class="pi pi-plus-circle absolute left-4 top-1/2 -translate-y-1/2 text-teal-500 text-lg"></i>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex gap-3">
                        <button 
                            @click="handleBulkCredits"
                            :disabled="isBulkSaving || bulkCreditAmount === 0"
                            class="cursor-pointer flex-1 py-4 bg-teal-600 hover:bg-teal-700 disabled:bg-gray-100 disabled:text-gray-400 text-white rounded-2xl font-black uppercase tracking-widest text-xs transition-all shadow-lg shadow-teal-100 flex items-center justify-center gap-2"
                        >
                            <i class="pi pi-check-circle" v-if="!isBulkSaving"></i>
                            <i class="pi pi-spin pi-spinner" v-else></i>
                            {{ isBulkSaving ? 'Processing...' : 'Apply to Selected' }}
                        </button>
                        <button @click="showBulkCreditsModal = false" class="px-6 py-4 bg-gray-100 text-gray-500 rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-gray-200 transition-colors">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Nuclear Reset Modal (3-Step Confirmation) -->
        <div v-if="showNuclearModal" class="fixed inset-0 z-[80] flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-md animate-in fade-in duration-300">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden animate-in zoom-in-95 duration-200 border-4 border-rose-500/20">
                <!-- Progress Header -->
                <div class="flex h-1.5 bg-gray-100">
                    <div :class="['transition-all duration-500 bg-rose-500', nuclearStep === 1 ? 'w-1/3' : nuclearStep === 2 ? 'w-2/3' : 'w-full']"></div>
                </div>

                <div class="p-8 text-center">
                    <!-- Step 1: Warning -->
                    <div v-if="nuclearStep === 1" class="animate-in slide-in-from-bottom-4">
                        <div class="w-20 h-20 bg-rose-50 rounded-full flex items-center justify-center mx-auto mb-6 text-rose-500 ring-8 ring-rose-50/50">
                            <i class="pi pi-exclamation-triangle text-3xl"></i>
                        </div>
                        <h3 class="text-2xl font-black text-gray-800 mb-2">Nuclear Reset</h3>
                        <p class="text-gray-500 mb-8 px-4 leading-relaxed">You are about to wipe <span class="font-black text-rose-600">ALL employee leave credits</span> to zero. This action is permanent and cannot be undone.</p>
                        
                        <div class="flex gap-3">
                            <button @click="showNuclearModal = false" class="flex-1 py-4 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-2xl font-black uppercase tracking-widest text-xs transition-colors cursor-pointer">Abort Mission</button>
                            <button @click="nuclearStep = 2" class="flex-1 py-4 bg-rose-600 hover:bg-rose-700 text-white rounded-2xl font-black uppercase tracking-widest text-xs transition-all shadow-lg shadow-rose-100 cursor-pointer">I Understand</button>
                        </div>
                    </div>

                    <!-- Step 2: Impact -->
                    <div v-if="nuclearStep === 2" class="animate-in slide-in-from-bottom-4">
                        <div class="w-20 h-20 bg-amber-50 rounded-full flex items-center justify-center mx-auto mb-6 text-amber-500 ring-8 ring-amber-50/50">
                            <i class="pi pi-users text-3xl"></i>
                        </div>
                        <h3 class="text-2xl font-black text-gray-800 mb-2">Are you really sure?</h3>
                        <p class="text-gray-500 mb-6 leading-relaxed">This will affect <span class="font-black text-gray-900">{{ employees.length }} employees</span>. This is usually done only at the end of a fiscal year.</p>
                        
                        <div class="bg-amber-50 border border-amber-100 p-4 rounded-2xl mb-8 text-left">
                            <ul class="text-[11px] text-amber-800 space-y-2 font-medium">
                                <li class="flex items-center gap-2"><i class="pi pi-check-circle"></i> SIL Credits will become 0.00</li>
                                <li class="flex items-center gap-2"><i class="pi pi-check-circle"></i> Personal balances will be cleared</li>
                                <li class="flex items-center gap-2"><i class="pi pi-check-circle"></i> Security audit log will be triggered</li>
                            </ul>
                        </div>

                        <div class="flex gap-3">
                            <button @click="nuclearStep = 1" class="px-6 py-4 bg-gray-100 hover:bg-gray-200 text-gray-500 rounded-2xl font-black uppercase tracking-widest text-xs transition-colors cursor-pointer">Go Back</button>
                            <button @click="nuclearStep = 3" class="flex-1 py-4 bg-rose-600 hover:bg-rose-700 text-white rounded-2xl font-black uppercase tracking-widest text-xs transition-all shadow-lg shadow-rose-100 cursor-pointer">Proceed to Step 3</button>
                        </div>
                    </div>

                    <!-- Step 3: Confirmation -->
                    <div v-if="nuclearStep === 3" class="animate-in slide-in-from-bottom-4">
                        <div class="w-20 h-20 bg-rose-600 rounded-full flex items-center justify-center mx-auto mb-6 text-white shadow-xl shadow-rose-200 ring-8 ring-rose-50">
                            <i class="pi pi-lock-open text-3xl"></i>
                        </div>
                        <h3 class="text-2xl font-black text-gray-800 mb-2">Final Confirmation</h3>
                        <p class="text-sm text-gray-500 mb-6 italic">To authorize this purge, please type the word below:</p>
                        
                        <div class="mb-4">
                            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 pl-1">Typed word must match "CONFIRM"</div>
                            <input 
                                v-model="nuclearConfirmText" 
                                type="text" 
                                class="w-full px-6 py-4 bg-gray-50 border-2 border-gray-100 focus:border-rose-500 rounded-2xl text-center font-black tracking-[0.3em] transition-all outline-none text-rose-600"
                                placeholder="TYPE HERE..."
                            >
                        </div>

                        <div class="flex gap-3 mt-8">
                            <button @click="resetNuclear" class="px-6 py-4 bg-gray-100 hover:bg-gray-200 text-gray-500 rounded-2xl font-black uppercase tracking-widest text-xs transition-colors cursor-pointer">Cancel</button>
                            <button 
                                @click="handleNuclearReset"
                                :disabled="nuclearConfirmText.toUpperCase() !== 'CONFIRM' || isPurging"
                                class="flex-1 py-4 bg-rose-600 hover:bg-rose-700 disabled:bg-gray-100 disabled:text-gray-300 text-white rounded-2xl font-black uppercase tracking-widest text-xs transition-all shadow-lg shadow-rose-100 cursor-pointer flex items-center justify-center gap-2"
                            >
                                <i class="pi pi-spin pi-spinner" v-if="isPurging"></i>
                                <span>EXECUTE RESET</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useEmployeeStore } from '../../stores/employees';
import { storeToRefs } from 'pinia';
import LeaveRequestModal from './LeaveRequestModal.vue';
import AddEmployeeModal from './AddEmployeeModal.vue';
import EditEmployeeModal from './EditEmployeeModal.vue';
import ChangePasswordModal from './ChangePasswordModal.vue';
import EmployeeReportModal from './EmployeeReportModal.vue';
import axios from 'axios';

const router = useRouter();
const employeeStore = useEmployeeStore();
const { employees, departments, loading: isLoading } = storeToRefs(employeeStore);

const selectedDepartment = ref('All');
const selectedStatus = ref('All');
const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = 10;
const openActionMenuId = ref(null);
const importInput = ref(null);

// Modal Logic
const showLeaveModal = ref(false);
const showAddModal = ref(false);
const showEditModal = ref(false);
const showPasswordModal = ref(false);
const showReportModal = ref(false);
const isCreating = ref(false);
const isEditing = ref(false);

// Nuclear Reset State
const showNuclearModal = ref(false);
const nuclearStep = ref(1);
const nuclearConfirmText = ref('');
const isPurging = ref(false);

const openNuclearReset = () => {
    nuclearStep.value = 1;
    nuclearConfirmText.value = '';
    showNuclearModal.value = true;
};

const resetNuclear = () => {
    showNuclearModal.value = false;
    nuclearStep.value = 1;
    nuclearConfirmText.value = '';
};

const handleNuclearReset = async () => {
    if (nuclearConfirmText.value !== 'CONFIRM') return;
    
    isPurging.value = true;
    try {
        await axios.post('/api/users/reset-all-credits');
        await employeeStore.fetchEmployees(true);
        resetNuclear();
        alert('GLOBAL RESET COMPLETE: All employee credits have been set to 0.');
    } catch (e) {
        alert('System error during purge.');
    } finally {
        isPurging.value = false;
    }
};

// Bulk Action State
const selectedEmployeeIds = ref([]);
const showBulkCreditsModal = ref(false);
const bulkCreditAmount = ref(0);
const isBulkSaving = ref(false);

const isAllSelected = computed(() => {
    const pageIds = paginatedEmployees.value.map(e => e.id);
    return pageIds.length > 0 && pageIds.every(id => selectedEmployeeIds.value.includes(id));
});

const toggleSelectAll = () => {
    const pageIds = paginatedEmployees.value.map(e => e.id);
    if (isAllSelected.value) {
        selectedEmployeeIds.value = selectedEmployeeIds.value.filter(id => !pageIds.includes(id));
    } else {
        selectedEmployeeIds.value = Array.from(new Set([...selectedEmployeeIds.value, ...pageIds]));
    }
};

const handleBulkCredits = async () => {
    if (bulkCreditAmount.value === 0) return;
    
    isBulkSaving.value = true;
    try {
        await axios.post('/api/users/bulk-credits', {
            user_ids: selectedEmployeeIds.value,
            amount: bulkCreditAmount.value
        });
        
        await employeeStore.fetchEmployees(true);
        selectedEmployeeIds.value = [];
        showBulkCreditsModal.value = false;
        bulkCreditAmount.value = 0;
        alert('Bulk credits added successfully!');
    } catch (e) {
        alert('Failed to apply credits.');
    } finally {
        isBulkSaving.value = false;
    }
};

const triggerBulkImport = () => {
    importInput.value.click();
};

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        alert(`Bulk Import initialized for: ${file.name}. \n\nThis feature is ready for the master access integration. Data validation and processing will occur in the next update.`);
        // Placeholder for future axios.post('/api/users/import', formData)
    }
};
const leaveForm = ref({
    employeeId: null,
    type: ''
});
const selectedEmployee = ref(null);

const getInitials = (name) => employeeStore.getInitials(name);

onMounted(() => {
    employeeStore.fetchEmployees();
    employeeStore.fetchDepartments();
    document.addEventListener('click', closeActionMenu);
});

const handleCreateEmployee = async (formData) => {
    isCreating.value = true;
    try {
        await axios.post('/api/users', formData);
        showAddModal.value = false;
        await employeeStore.fetchEmployees(true); // Refresh list
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
    router.push({ path: '/manage-leaves', query: { user_id: employee.id } });
};

const handleChangePassword = (employee) => {
    selectedEmployee.value = employee;
    showPasswordModal.value = true;
};

const handlePasswordChanged = () => {
    alert('Password updated successfully!');
    // Optionally refresh employee list or show toast notification
};

const handleViewReport = (employee) => {
    selectedEmployee.value = employee;
    showReportModal.value = true;
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

const openEditModal = (employee) => {
    selectedEmployee.value = employee;
    showEditModal.value = true;
};

const handleUpdateEmployee = async (formData) => {
    isEditing.value = true;
    try {
        const payload = {
            name: formData.name,
            department: formData.department,
            role: formData.role,
            position: formData.position,
            id_number: formData.id_number,
            employment_status: formData.employment_status,
            leave_credits: formData.leave_credits
        };

        const response = await axios.put(`/api/users/${formData.id}`, payload);
        
        // Update local list
        const idx = employees.value.findIndex(e => e.id === formData.id);
        if (idx !== -1) {
            // Merge response data (updated user) into existing employee object
            // Ensure avatar_url and other computed props are kept or updated
            employees.value[idx] = { ...employees.value[idx], ...response.data };
        }
        
        showEditModal.value = false;
        alert('Employee updated successfully');
    } catch (error) {
        console.error('Update failed:', error);
        alert(error.response?.data?.message || 'Failed to update employee');
    } finally {
        isEditing.value = false;
    }
};

onMounted(() => {
    employeeStore.fetchEmployees();
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
