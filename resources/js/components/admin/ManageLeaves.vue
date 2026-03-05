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
                    <div class="flex gap-3">
                        <button 
                            @click="showAdminApplyModal = true" 
                            class="cursor-pointer flex items-center gap-2 px-4 py-2 bg-purple-600 border border-purple-500 text-white rounded-lg hover:bg-purple-700 transition-colors shadow-sm font-semibold"
                        >
                            <i class="pi pi-plus-circle"></i>
                            <span>File Employee Leave</span>
                        </button>
                        <button 
                            @click="exportReport" 
                            class="cursor-pointer flex items-center gap-2 px-4 py-2 bg-teal-600 border border-teal-500 text-white rounded-lg hover:bg-teal-700 transition-colors shadow-sm font-semibold"
                        >
                            <i class="pi pi-download"></i>
                            <span>{{ filters.user_id ? 'Export Employee Record' : 'Export General Report' }}</span>
                        </button>
                        <router-link 
                            to="/archive-leaves"
                            class="cursor-pointer flex items-center gap-2 px-4 py-2 bg-gray-100 border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors shadow-sm font-semibold"
                        >
                            <i class="pi pi-folder text-gray-500"></i>
                            <span>Archive Registry</span>
                        </router-link>
                        <button 
                            @click="showBulkArchiveModal = true" 
                            class="cursor-pointer flex items-center gap-2 px-4 py-2 bg-slate-800 border border-slate-700 text-white rounded-lg hover:bg-slate-900 transition-colors shadow-sm font-semibold"
                        >
                            <i class="pi pi-history"></i>
                            <span>Cleanup Archive</span>
                        </button>
                    </div>
                </div>

                <!-- Admin Action Modal -->
                <LeaveRequestModal 
                    v-model="showAdminApplyModal"
                    :isAdminMode="true"
                    @submit="handleAdminSubmit"
                />

                <!-- Employee Insight Hero (Visible when filtered) -->
                <div v-if="filters.user_id && filteredEmployeeData" class="mb-8 animate-in fade-in slide-in-from-top-4 duration-500">
                    <div class="bg-gradient-to-r from-teal-600 to-teal-800 rounded-2xl shadow-xl overflow-hidden text-white">
                        <div class="p-6 flex flex-col md:flex-row items-center gap-8 relative">
                            <!-- User Backdrop Decor -->
                            <div class="absolute right-0 top-0 opacity-10 pointer-events-none">
                                <i class="pi pi-user text-[200px]"></i>
                            </div>

                            <div class="relative group">
                                <div 
                                    class="w-32 h-32 rounded-2xl bg-white/20 backdrop-blur-md border-4 border-white/30 flex items-center justify-center text-4xl font-bold overflow-hidden shadow-2xl transition-transform group-hover:scale-105 duration-300"
                                    :style="filteredEmployeeData.avatar ? { backgroundImage: `url(${filteredEmployeeData.avatar_url || filteredEmployeeData.avatar})`, backgroundSize: 'cover' } : {}"
                                >
                                    <span v-if="!filteredEmployeeData.avatar">{{ getInitials(filteredEmployeeData.name) }}</span>
                                </div>
                                <div class="absolute -bottom-2 -right-2 px-3 py-1 bg-white text-teal-800 text-[10px] font-bold rounded-lg shadow-lg uppercase tracking-widest border border-teal-100">
                                    {{ filteredEmployeeData.status || 'Available' }}
                                </div>
                            </div>

                            <div class="flex-1 text-center md:text-left">
                                <div class="flex flex-col md:flex-row md:items-center gap-3 mb-2">
                                    <h2 class="text-3xl font-black tracking-tight">{{ filteredEmployeeData.name }}</h2>
                                    <span class="px-3 py-1 bg-white/20 backdrop-blur-sm rounded-full text-xs font-bold uppercase tracking-wider border border-white/20">
                                        ID: {{ filteredEmployeeData.id_number }}
                                    </span>
                                </div>
                                <p class="text-teal-50/80 font-medium mb-4 flex items-center justify-center md:justify-start gap-2">
                                    <i class="pi pi-briefcase text-sm"></i>
                                    {{ filteredEmployeeData.position || 'Staff member' }} • {{ filteredEmployeeData.department }}
                                </p>
                                
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 border border-white/10 group hover:bg-white/20 transition-all">
                                        <p class="text-[10px] text-teal-100/70 uppercase font-bold tracking-widest mb-1">Available SIL</p>
                                        <p class="text-2xl font-black">{{ filteredEmployeeData.leave_credits }} <span class="text-sm font-normal opacity-70">Days</span></p>
                                    </div>
                                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 border border-white/10 group hover:bg-white/20 transition-all">
                                        <p class="text-[10px] text-teal-100/70 uppercase font-bold tracking-widest mb-1">Total Filed</p>
                                        <p class="text-2xl font-black">{{ requests.length }} <span class="text-sm font-normal opacity-70">Times</span></p>
                                    </div>
                                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 border border-white/10 group hover:bg-white/20 transition-all">
                                        <p class="text-[10px] text-teal-100/70 uppercase font-bold tracking-widest mb-1">Status</p>
                                        <p class="text-2xl font-black capitalize">{{ filteredEmployeeData.employment_status || 'Regular' }}</p>
                                    </div>
                                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 border border-white/10 group hover:bg-white/20 transition-all">
                                        <p class="text-[10px] text-teal-100/70 uppercase font-bold tracking-widest mb-1">Current</p>
                                        <p class="text-2xl font-black">{{ filteredEmployeeData.status || 'Active' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Metric Cards -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
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
                        <div class="w-12 h-12 rounded-lg bg-red-50 flex items-center justify-center text-red-500 text-xl font-bold">
                            {{ rejectedCount }}
                        </div>
                        <div>
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Rejected</h3>
                            <p class="text-lg font-bold text-gray-800">Requests</p>
                        </div>
                    </div>
                     <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 text-xl font-bold">
                            {{ scheduledCount }}
                        </div>
                        <div>
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Cancelled</h3>
                            <p class="text-lg font-bold text-gray-800">Requests</p>
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

                <!-- Enhanced Filters -->
                <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100 mb-6 space-y-4">
                    <!-- Top Row: Search & Status -->
                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="relative flex-1">
                            <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input 
                                v-model="searchQuery" 
                                type="text" 
                                placeholder="Search by name or Employee ID (e.g. HQI-0001)..." 
                                class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all text-sm"
                            >
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <select v-model="filters.status" class="cursor-pointer px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 bg-white min-w-[160px]">
                                <option value="">All Statuses</option>
                                <option value="Pending">Pending</option>
                                <option value="Dept Approved">Dept Approved (→ HR)</option>
                                <option value="Approved">Approved (Official)</option>
                                <option value="Rejected">Rejected</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                            <select v-model="filters.department" class="cursor-pointer px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 bg-white min-w-[150px]">
                                <option value="">All Departments</option>
                                <option v-for="dept in departmentNames" :key="dept" :value="dept">{{ dept }}</option>
                            </select>
                             <select v-model="filters.request_type" class="cursor-pointer px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 bg-white min-w-[130px]">
                                <option value="">All Categories</option>
                                <option value="Leave">Leave</option>
                                <option value="Halfday">Halfday</option>
                                <option value="Undertime">Undertime</option>
                                <option value="Official Business">Official Business</option>
                            </select>
                        </div>
                    </div>

                    <!-- Bottom Row: Date Range & Type -->
                    <div class="flex flex-col md:flex-row gap-4 items-center border-t border-gray-50 pt-4">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-black uppercase text-gray-400 tracking-wider">Range:</span>
                            <input type="date" v-model="filters.from_date" class="cursor-pointer px-2 py-1.5 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-teal-500/20 outline-none">
                            <span class="text-gray-300">to</span>
                            <input type="date" v-model="filters.to_date" class="cursor-pointer px-2 py-1.5 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-teal-500/20 outline-none">
                        </div>

                        <select v-model="filters.type" class="cursor-pointer px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 bg-white flex-1">
                            <option value="">All Leave Types</option>
                            <option value="SIL">SIL (Service Incentive)</option>
                            <option value="Sick">Sick Leave</option>
                            <option value="Vacation">Vacation Leave</option>
                            <option value="Emergency">Emergency Leave</option>
                            <option value="Maternity">Maternity Leave</option>
                            <option value="Paternity">Paternity Leave</option>
                            <option value="Solo Parent">Solo Parent</option>
                            <option value="VAWS">VAWS</option>
                            <option value="Magna Carta">Magna Carta</option>
                            <option value="Others">Others</option>
                        </select>

                        <div class="flex gap-2">
                           <button @click="Object.keys(filters).forEach(k => k !== 'user_id' ? filters[k] = '' : null); searchQuery = ''" class="cursor-pointer px-3 py-2 text-xs font-bold text-gray-400 hover:text-rose-500 transition-colors uppercase tracking-widest">
                               <i class="pi pi-refresh mr-1"></i> Reset Filters
                           </button>
                        </div>

                        <!-- User Filter Indicator -->
                        <div v-if="filters.user_id" class="flex items-center gap-2 px-3 py-1.5 bg-teal-50 text-teal-700 rounded-lg border border-teal-200 text-xs font-bold animate-in fade-in slide-in-from-right-2">
                            <i class="pi pi-user"></i>
                            <span>Single Employee View</span>
                            <button @click="filters.user_id = ''" class="ml-1 p-1 hover:bg-teal-100 rounded-full transition-colors cursor-pointer">
                                <i class="pi pi-times text-[10px]"></i>
                            </button>
                        </div>
                    </div>
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
                                    <th class="p-4">Pay Status</th>
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
                                                <img v-if="request.user?.avatar_url" :src="request.user.avatar_url" class="w-full h-full object-cover">
                                                <div v-else class="w-full h-full flex items-center justify-center bg-teal-100 text-teal-600 font-bold text-sm">
                                                    {{ getInitials(request.user?.name || (request.employee?.first_name + ' ' + request.employee?.last_name)) }}
                                                </div>
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <p class="font-bold text-gray-800 text-sm">
                                                        {{ request.user?.name || (request.employee?.last_name + ', ' + request.employee?.first_name) }}
                                                    </p>
                                                    <span class="text-[9px] font-mono px-1 bg-gray-100 text-gray-500 rounded border border-gray-200">
                                                        {{ request.user?.id_number || request.employee?.employee_id }}
                                                    </span>
                                                </div>
                                                <p class="text-xs text-gray-400 font-medium">
                                                    {{ request.user?.department || request.employee?.department?.name }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex flex-col gap-1">
                                            <span class="font-bold text-gray-800 text-sm block leading-none">{{ request.leave_type }}</span>
                                            <div class="flex items-center gap-1">
                                                <span class="text-[10px] text-gray-400 font-medium uppercase tracking-tight">{{ request.request_type }}</span>
                                                <span v-if="request.category" class="px-1.5 py-0.5 rounded text-[9px] font-black bg-indigo-50 text-indigo-600 border border-indigo-100 italic">
                                                    {{ request.category }}
                                                </span>
                                            </div>
                                        </div>
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
                                        <!-- Status Badge -->
                                        <div>
                                            <span :class="[
                                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wide',
                                                request.status === 'Approved'      ? 'bg-emerald-100 text-emerald-700' :
                                                request.status === 'Dept Approved' ? 'bg-blue-100 text-blue-700':
                                                request.status === 'Pending'       ? 'bg-orange-100 text-orange-700' :
                                                request.status === 'Cancelled'     ? 'bg-gray-100 text-gray-500' :
                                                'bg-red-100 text-red-700'
                                            ]">
                                                {{ request.status === 'Dept Approved' ? '→ Forwarded to HR' : request.status }}
                                            </span>

                                            <!-- Pending: Awaiting dept head -->
                                            <div v-if="request.status === 'Pending'" class="mt-1.5 flex items-center gap-1 text-[9px] text-amber-600 font-bold">
                                                <i class="pi pi-hourglass text-[8px]"></i> Awaiting Dept Head
                                            </div>

                                            <!-- Dept Approved: show who forwarded + dept + remarks -->
                                            <div v-if="request.status === 'Dept Approved' && request.dept_head" class="mt-1.5 space-y-0.5">
                                                <div class="flex items-center gap-1 text-[9px] text-blue-600 font-bold">
                                                    <i class="pi pi-user text-[8px]"></i> {{ request.dept_head.name }}
                                                </div>
                                                <div v-if="request.dept_head_remarks" class="text-[9px] text-gray-400 italic truncate max-w-[160px]" :title="request.dept_head_remarks">
                                                    "{{ request.dept_head_remarks }}"
                                                </div>
                                            </div>

                                            <!-- HR Approver indicator -->
                                            <div v-if="request.hr_approver" class="mt-1 flex items-center gap-1 text-[9px] text-emerald-600 font-bold">
                                                <i class="pi pi-verified text-[8px]"></i> {{ request.hr_approver.name }}
                                            </div>
                                        </div>
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
                                        <button class="cursor-pointer p-2 hover:bg-white rounded-full text-gray-400 hover:text-teal-600 transition-colors shadow-sm border border-transparent hover:border-gray-200">
                                            <i class="pi pi-chevron-right"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="bg-gray-50 p-4 border-t border-gray-100 flex justify-between items-center text-sm">
                        <span class="text-gray-500 font-medium">Showing {{ requests.length }} of {{ totalRecords }} records</span>
                        <div class="flex items-center gap-4">
                            <span class="text-gray-400 text-xs font-bold uppercase tracking-widest">Page {{ page }} of {{ lastPage }}</span>
                            <div class="flex gap-2">
                                <button 
                                    class="cursor-pointer px-3 py-1.5 border border-gray-200 rounded-lg bg-white hover:bg-gray-50 disabled:opacity-30 disabled:hover:bg-white text-gray-600 transition-colors shadow-sm font-bold text-xs uppercase" 
                                    :disabled="page === 1 || loading" 
                                    @click="page--"
                                >
                                    Prev
                                </button>
                                <button 
                                    class="cursor-pointer px-3 py-1.5 border border-gray-200 rounded-lg bg-white hover:bg-gray-50 disabled:opacity-30 disabled:hover:bg-white text-gray-600 transition-colors shadow-sm font-bold text-xs uppercase" 
                                    :disabled="page >= lastPage || loading" 
                                    @click="page++"
                                >
                                    Next
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail Modal -->
                <div v-if="selectedRequest" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="closeModal">
                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[95vh] overflow-hidden flex flex-col">
                        <!-- Modal Header -->
                        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                            <div>
                                <h2 class="text-xl font-bold text-gray-800">Review Leave Request</h2>
                                <p class="text-xs text-gray-500 font-mono mt-1">RID-{{ String(selectedRequest.id).padStart(6, '0') }}</p>
                            </div>
                            <button @click="closeModal" class="cursor-pointer p-2 hover:bg-gray-200 rounded-full text-gray-500 transition-colors"><i class="pi pi-times"></i></button>
                        </div>

                        <!-- Modal Content -->
                        <div class="flex-1 overflow-y-auto p-6 bg-gray-50/30">
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                                
                                <!-- Left Panel: Context & Analysis (New) -->
                                <div class="lg:col-span-4 space-y-4">
                                    <!-- User Card -->
                                    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-full bg-teal-100 flex items-center justify-center text-teal-700 font-bold overflow-hidden">
                                            <img v-if="selectedRequest.user?.avatar_url" :src="selectedRequest.user.avatar_url" class="w-full h-full object-cover">
                                            <span v-else>{{ getInitials(selectedRequest.user?.name) }}</span>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-gray-800 text-sm">{{ selectedRequest.user?.name || selectedRequest.employee?.name || 'Unknown Employee' }}</h3>
                                            <p v-if="selectedRequest.user?.position || selectedRequest.user?.department" class="text-xs text-gray-500">
                                                {{ [selectedRequest.user?.position, selectedRequest.user?.department].filter(Boolean).join(' • ') }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Loading Analysis -->
                                    <div v-if="loadingAnalysis" class="p-8 text-center text-gray-400 bg-white rounded-xl border border-gray-200 dashed">
                                        <i class="pi pi-spin pi-spinner text-2xl mb-2"></i>
                                        <p class="text-xs">Analyzing impact & patterns...</p>
                                    </div>

                                    <!-- Analysis Results -->
                                    <div v-else-if="analysis" class="space-y-4 animate-in fade-in slide-in-from-left-4 duration-500">
                                        
                                        <!-- Patterns / Flags -->
                                        <div v-if="analysis.patterns?.length > 0" class="bg-amber-50 border border-amber-200 rounded-xl p-4 shadow-sm relative overflow-hidden">
                                            <div class="absolute top-0 right-0 w-24 h-24 bg-white/30 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>
                                            <h4 class="text-xs font-black text-amber-800 uppercase tracking-widest mb-4 flex items-center gap-2">
                                                <i class="pi pi-sparkles"></i> AI Attendance Insights
                                            </h4>
                                            <div class="space-y-3">
                                                <div v-for="(pat, idx) in analysis.patterns" :key="idx" class="flex gap-3 items-start bg-white p-3.5 rounded-xl border border-amber-100 shadow-sm transition-all hover:shadow-md hover:border-amber-300">
                                                    <div class="w-9 h-9 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 shrink-0 shadow-inner">
                                                        <i :class="['pi', pat.icon || 'pi-info-circle', 'text-lg']"></i>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-black text-amber-900 leading-none mb-1.5">{{ pat.label }}</p>
                                                        <p class="text-xs font-medium text-gray-600 leading-snug">{{ pat.description }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Compliance Check (Hidden) -->
                                        <div v-if="false" :class="['rounded-xl p-4 border', analysis.compliance?.passed ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200']">
                                            <h4 :class="['text-xs font-bold uppercase tracking-widest mb-2 flex items-center gap-2', analysis.compliance?.passed ? 'text-green-800' : 'text-red-800']">
                                                <i :class="['pi', analysis.compliance?.passed ? 'pi-verified' : 'pi-ban']"></i>
                                                Compliance Check
                                            </h4>
                                            <p v-if="!analysis.compliance?.passed" class="text-xs font-bold text-red-700">
                                                {{ analysis.compliance.message }}
                                            </p>
                                            <p v-else class="text-xs text-green-700">
                                                Request adheres to {{ selectedRequest.leave_type }} policy rules.
                                            </p>
                                        </div>

                                        <!-- Department Impact -->
                                        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
                                            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-3">Department Impact</h4>
                                            <div v-if="analysis.impact">
                                                <div class="flex justify-between items-end mb-1">
                                                    <span class="text-2xl font-black text-gray-800">{{ analysis.impact.absent_percentage }}%</span>
                                                    <span class="text-xs text-gray-500 mb-1">of {{ analysis.impact.department }} absent</span>
                                                </div>
                                                <div class="w-full bg-gray-100 rounded-full h-2 mb-3 overflow-hidden">
                                                    <div 
                                                        class="h-full rounded-full transition-all duration-1000" 
                                                        :style="{ width: `${analysis.impact.absent_percentage}%` }"
                                                        :class="analysis.impact.absent_percentage > 20 ? 'bg-red-500' : 'bg-teal-500'"
                                                    ></div>
                                                </div>
                                                <div v-if="analysis.impact.others_on_leave_count > 0">
                                                    <p class="text-xs font-bold text-gray-600 mb-1">Also on leave:</p>
                                                    <ul class="text-xs text-gray-500 list-disc pl-4">
                                                        <li v-for="name in analysis.impact.others_on_leave_details" :key="name">{{ name }}</li>
                                                    </ul>
                                                </div>
                                                <p v-else class="text-xs text-gray-400 italic">No overlap with others.</p>
                                            </div>
                                            <p v-else class="text-xs text-gray-400">No department info available.</p>
                                        </div>

                                        <!-- Recent Request History (Admin View) -->
                                        <div v-if="analysis.history?.length > 0" class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm relative overflow-hidden">
                                            <div class="absolute top-0 right-0 w-24 h-24 bg-purple-50 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2 pointer-events-none opacity-50"></div>
                                            <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                                                <i class="pi pi-history text-purple-600"></i> Recent Employee History
                                            </h4>
                                            <div class="space-y-3">
                                                <div v-for="h in analysis.history" :key="h.id" class="flex items-center justify-between p-2.5 rounded-lg bg-gray-50/50 border border-transparent hover:border-purple-100 hover:bg-white transition-all group">
                                                    <div class="flex flex-col gap-0.5">
                                                        <span class="text-xs font-black text-slate-700 uppercase tracking-tight group-hover:text-purple-700 transition-colors">{{ h.leave_type }}</span>
                                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ formatDate(h.from_date) }}</span>
                                                    </div>
                                                    <span :class="['px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest border', 
                                                        h.status === 'Approved' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 
                                                        h.status === 'Pending' ? 'bg-amber-50 text-amber-600 border-amber-100' : 
                                                        'bg-rose-50 text-rose-600 border-rose-100']">
                                                        {{ h.status }}
                                                    </span>
                                                </div>
                                            </div>
                                            <p class="text-[9px] text-center text-gray-400 mt-4 font-bold uppercase tracking-widest opacity-60">Showing latest 5 records</p>
                                        </div>

                                        <!-- Credit Forecast (Hidden) -->
                                        <div v-if="false" class="bg-blue-50/50 rounded-xl p-4 border border-blue-100">
                                            <h4 class="text-xs font-bold text-blue-800 uppercase tracking-widest mb-3">Credit Forecast</h4>
                                            <div class="flex justify-between items-center mb-2">
                                                <span class="text-xs text-gray-600">Current Balance</span>
                                                <span class="font-bold text-gray-800">{{ Number(selectedRequest.user?.leave_credits || 0).toFixed(2) }}</span>
                                            </div>
                                            <div class="flex justify-between items-center mb-2">
                                                <span class="text-xs text-gray-600">This Request</span>
                                                <span class="font-bold text-red-600">-{{ Number(selectedRequest.days_taken).toFixed(2) }}</span>
                                            </div>
                                            <div class="border-t border-blue-200 py-2">
                                                <div class="flex justify-between items-center">
                                                    <span class="text-xs font-bold text-blue-700">Projected Year-End</span>
                                                    <span class="font-bold text-blue-700">{{ analysis.forecast?.projected_balance ? Number(analysis.forecast.projected_balance).toFixed(2) : '---' }}</span>
                                                </div>
                                                <p class="text-[10px] text-blue-600/80 mt-1 leading-tight">{{ analysis.forecast?.message }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Panel: Request & Actions -->
                                <div class="lg:col-span-8 space-y-6">
                                    <!-- Main Request Info -->
                                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                                        <div class="flex justify-between items-start mb-6">
                                            <div>
                                                <span class="text-xs font-bold text-teal-600 uppercase tracking-wider">{{ selectedRequest.leave_type }}</span>
                                                <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ formatDate(selectedRequest.from_date) }} <span class="text-gray-400 font-normal">to</span> {{ formatDate(selectedRequest.to_date || selectedRequest.from_date) }}</h3>
                                                <div class="flex items-center gap-2 mt-2">
                                                     <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        {{ selectedRequest.days_taken }} Day(s)
                                                    </span>
                                                    <span class="text-sm text-gray-500">{{ selectedRequest.request_type }}</span>
                                                </div>
                                            </div>
                                            <div :class="[
                                                'px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide border',
                                                selectedRequest.status === 'Approved'      ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                                                selectedRequest.status === 'Dept Approved' ? 'bg-blue-50 text-blue-700 border-blue-200' :
                                                selectedRequest.status === 'Pending'       ? 'bg-orange-50 text-orange-700 border-orange-200' :
                                                selectedRequest.status === 'Cancelled'     ? 'bg-gray-50 text-gray-500 border-gray-200' :
                                                'bg-rose-50 text-rose-700 border-rose-200'
                                            ]">
                                                {{ selectedRequest.status }}
                                            </div>
                                        </div>

                                        <!-- ── Approval Timeline ────────────────────────────────── -->
                                        <div class="mb-4 p-4 bg-gradient-to-br from-slate-50 to-teal-50/40 rounded-xl border border-slate-200">
                                            <h4 class="text-[10px] font-black text-slate-600 uppercase tracking-widest mb-4 flex items-center gap-1.5">
                                                <i class="pi pi-list-check text-teal-500"></i> Approval Trail
                                            </h4>
                                            <div class="relative pl-5">
                                                <!-- Vertical connector line -->
                                                <div class="absolute left-[7px] top-2 bottom-2 w-0.5 bg-gray-200 rounded-full"></div>

                                                <!-- Step 1: Employee filed -->
                                                <div class="relative mb-4">
                                                    <div class="absolute -left-5 top-0.5 w-3.5 h-3.5 rounded-full bg-indigo-500 border-2 border-white shadow-sm"></div>
                                                    <div class="bg-white rounded-lg border border-gray-100 p-3 shadow-sm">
                                                        <p class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Filed by Employee</p>
                                                        <p class="text-xs font-bold text-gray-700 mt-0.5">
                                                            {{ selectedRequest.user?.name || (selectedRequest.employee?.last_name + ', ' + selectedRequest.employee?.first_name) }}
                                                        </p>
                                                        <p class="text-[10px] text-gray-400 mt-0.5">
                                                            {{ selectedRequest.user?.department || selectedRequest.employee?.department?.name || '—' }}
                                                        </p>
                                                        <p class="text-[9px] text-gray-400 mt-1">{{ selectedRequest.date_filed ? new Date(selectedRequest.date_filed).toLocaleDateString('en-US', {month:'long',day:'numeric',year:'numeric'}) : '—' }}</p>
                                                    </div>
                                                </div>

                                                <!-- Step 2: Dept Head -->
                                                <div class="relative mb-4">
                                                    <div class="absolute -left-5 top-0.5 w-3.5 h-3.5 rounded-full border-2 border-white shadow-sm"
                                                        :class="selectedRequest.dept_head ? 'bg-teal-500' : 'bg-amber-300'"></div>
                                                    <div class="bg-white rounded-lg border p-3 shadow-sm"
                                                        :class="selectedRequest.dept_head ? 'border-teal-100' : 'border-amber-100'">
                                                        <p class="text-[10px] font-black uppercase tracking-widest"
                                                            :class="selectedRequest.dept_head ? 'text-teal-600' : 'text-amber-500'">
                                                            {{ selectedRequest.dept_head ? 'Dept Head Reviewed' : 'Awaiting Dept Head Review' }}
                                                        </p>
                                                        <template v-if="selectedRequest.dept_head">
                                                            <p class="text-xs font-bold text-gray-700 mt-0.5">{{ selectedRequest.dept_head.name }}</p>
                                                            <p class="text-[10px] text-gray-400">
                                                                {{ ['Dept Approved', 'Approved'].includes(selectedRequest.status) ? 'Forwarded to HR for final approval' : 'Request was Rejected at dept level' }}
                                                            </p>
                                                            <p v-if="selectedRequest.dept_head_remarks" class="text-[10px] text-gray-500 italic mt-1 border-l-2 border-teal-200 pl-2">
                                                                "{{ selectedRequest.dept_head_remarks }}"
                                                            </p>
                                                            <p v-if="selectedRequest.dept_reviewed_at" class="text-[9px] text-gray-400 mt-1">
                                                                {{ new Date(selectedRequest.dept_reviewed_at).toLocaleDateString('en-US', {month:'short',day:'numeric',year:'numeric',hour:'2-digit',minute:'2-digit'}) }}
                                                            </p>
                                                        </template>
                                                        <template v-else>
                                                            <p class="text-[10px] text-amber-500 mt-0.5 animate-pulse">Pending review by department head</p>
                                                        </template>
                                                    </div>
                                                </div>

                                                <!-- Step 3: HR/Admin Final -->
                                                <div class="relative">
                                                    <div class="absolute -left-5 top-0.5 w-3.5 h-3.5 rounded-full border-2 border-white shadow-sm"
                                                        :class="selectedRequest.hr_approver ? 'bg-emerald-500' : (selectedRequest.status === 'Dept Approved' ? 'bg-blue-400 animate-pulse' : 'bg-gray-200')"></div>
                                                    <div class="bg-white rounded-lg border p-3 shadow-sm"
                                                        :class="selectedRequest.hr_approver ? 'border-emerald-100' : (selectedRequest.status === 'Dept Approved' ? 'border-blue-200' : 'border-gray-100')">
                                                        <p class="text-[10px] font-black uppercase tracking-widest"
                                                            :class="selectedRequest.hr_approver ? 'text-emerald-600' : (selectedRequest.status === 'Dept Approved' ? 'text-blue-500' : 'text-gray-400')">
                                                            {{ selectedRequest.hr_approver ? 'HR / Admin Final Decision' : (selectedRequest.status === 'Dept Approved' ? 'Ready for HR Final Approval' : 'Awaiting HR / Admin') }}
                                                        </p>
                                                        <template v-if="selectedRequest.hr_approver">
                                                            <p class="text-xs font-bold text-gray-700 mt-0.5">{{ selectedRequest.hr_approver.name }}</p>
                                                            <p class="text-[10px] font-bold mt-0.5"
                                                                :class="selectedRequest.status === 'Approved' ? 'text-emerald-600' : 'text-rose-600'">
                                                                {{ selectedRequest.status === 'Approved' ? 'Officially Approved ✓' : 'Rejected ✗' }}
                                                            </p>
                                                            <p v-if="selectedRequest.hr_remarks" class="text-[10px] text-gray-500 italic mt-1 border-l-2 border-emerald-200 pl-2">
                                                                "{{ selectedRequest.hr_remarks }}"
                                                            </p>
                                                            <p v-if="selectedRequest.hr_approved_at" class="text-[9px] text-gray-400 mt-1">
                                                                {{ new Date(selectedRequest.hr_approved_at).toLocaleDateString('en-US', {month:'short',day:'numeric',year:'numeric',hour:'2-digit',minute:'2-digit'}) }}
                                                            </p>
                                                        </template>
                                                        <template v-else-if="selectedRequest.status === 'Dept Approved'">
                                                            <p class="text-[10px] text-blue-500 mt-0.5 animate-pulse">Forwarded — awaiting final HR decision</p>
                                                        </template>
                                                        <template v-else>
                                                            <p class="text-[10px] text-gray-400 mt-0.5">Not yet reached this stage</p>
                                                        </template>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                        <!-- ── END Approval Timeline ────────────────────────────── -->

                                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 mb-4">
                                            <p class="text-xs text-gray-400 uppercase font-bold mb-2">Employee Reason</p>
                                            <p class="text-gray-700 italic">"{{ selectedRequest.reason }}"</p>
                                        </div>

                                        <!-- Additional Custom Details -->
                                        <div v-if="selectedRequest.additional_details && Object.keys(selectedRequest.additional_details).filter(k => !k.endsWith('_input')).length" class="mb-5">
                                            <h4 class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-3">Custom Form Details Collected</h4>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div v-for="key in Object.keys(selectedRequest.additional_details).filter(k => !k.endsWith('_input'))" :key="key" class="bg-indigo-50/50 p-4 rounded-xl border border-indigo-100 shadow-sm relative overflow-hidden hover:border-indigo-300 transition-all">
                                                    <div class="absolute top-0 right-0 w-16 h-16 bg-white/40 rounded-full -translate-y-1/2 translate-x-1/3 blur-xl pointer-events-none"></div>
                                                    <p class="text-[9px] font-black text-indigo-400 uppercase tracking-widest mb-2 flex items-center gap-1.5"><i class="pi pi-list"></i> {{ key.replace('_', ' ') }}</p>
                                                    <div class="flex flex-col gap-2">
                                                        <p class="text-sm font-black text-indigo-900 leading-tight">Option: {{ selectedRequest.additional_details[key] || 'Not specified' }}</p>
                                                        <div v-if="selectedRequest.additional_details[key + '_input']" class="inline-flex items-center gap-2 px-3 py-1.5 bg-white/80 backdrop-blur-sm rounded-lg border border-indigo-50 w-max shadow-sm">
                                                            <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest">Input Value:</span>
                                                            <span class="text-sm font-black text-indigo-700">{{ selectedRequest.additional_details[key + '_input'] }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div v-if="selectedRequest.attachment_path" class="mb-2">
                                            <a :href="selectedRequest.attachment_path" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-50 text-purple-700 rounded-lg border border-purple-200 hover:bg-purple-100 transition-colors text-sm font-bold">
                                                <i class="pi pi-download"></i>
                                                View Supporting Document
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Decision Area -->
                                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-lg ring-1 ring-gray-100">
                                        <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                                            <i class="pi pi-gavel text-teal-600"></i> Decision & Justification
                                        </h4>

                                        <div class="flex flex-col gap-6 mb-6">
                                            <!-- Pay Config -->
                                            <div>
                                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Pay Configuration</label>
                                                <div class="flex items-center gap-3">
                                                    <button 
                                                        @click="selectedRequest.is_paid = !selectedRequest.is_paid"
                                                        :class="['cursor-pointer flex items-center gap-2 px-4 py-2.5 rounded-xl border-2 transition-all text-sm font-black uppercase tracking-tight', selectedRequest.is_paid ? 'bg-teal-50 border-teal-500 text-teal-700 shadow-sm shadow-teal-100' : 'bg-white border-gray-200 text-gray-500 hover:border-gray-300 hover:bg-gray-50']"
                                                    >
                                                        <i :class="['pi', selectedRequest.is_paid ? 'pi-check-circle' : 'pi-circle']"></i>
                                                        With Pay
                                                    </button>
                                                    <div v-if="selectedRequest.is_paid" class="animate-in fade-in slide-in-from-left-2 flex items-center gap-2 px-3 py-2 bg-gray-50 rounded-xl border-2 border-gray-200">
                                                        <label class="text-[10px] uppercase font-bold text-gray-400">Days Paid:</label>
                                                        <input 
                                                            v-model="selectedRequest.days_paid" 
                                                            type="number" 
                                                            step="0.5" 
                                                            class="w-20 px-2 py-1 border border-gray-300 rounded-lg text-sm font-bold outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 bg-white"
                                                        >
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Classification Metadata -->
                                            <div class="pt-5 border-t border-gray-100">
                                                <label class="block text-xs font-bold text-gray-500 uppercase mb-3">Disciplinary Metadata / Category</label>
                                                <div class="flex flex-wrap gap-3">
                                                    <label class="flex items-center gap-3 px-4 py-3 rounded-xl border-2 cursor-pointer transition-all hover:bg-purple-50 group hover:border-purple-200 min-w-[160px]" :class="!selectedRequest.category ? 'border-purple-500 bg-purple-50 shadow-sm shadow-purple-100' : 'border-gray-100 bg-white'">
                                                        <input type="radio" v-model="selectedRequest.category" :value="''" class="peer h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 cursor-pointer">
                                                        <div class="flex flex-col text-left">
                                                            <span class="text-xs font-black uppercase tracking-tight" :class="!selectedRequest.category ? 'text-purple-800' : 'text-gray-600'">None Applied</span>
                                                            <span class="text-[9px] font-bold text-gray-400">Regular Leave</span>
                                                        </div>
                                                    </label>
                                                    <label v-for="cat in settingsStore.attendanceCategories" :key="cat.code" class="flex items-center gap-3 px-4 py-3 rounded-xl border-2 cursor-pointer transition-all hover:bg-purple-50 group hover:border-purple-200 min-w-[160px]" :class="selectedRequest.category === cat.code ? 'border-purple-500 bg-purple-50 shadow-sm shadow-purple-100' : 'border-gray-100 bg-white'">
                                                        <input type="radio" v-model="selectedRequest.category" :value="cat.code" class="peer h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 cursor-pointer">
                                                        <div class="flex flex-col text-left">
                                                            <span class="text-xs font-black uppercase tracking-tight" :class="selectedRequest.category === cat.code ? 'text-purple-800' : 'text-gray-600'">{{ cat.code }}</span>
                                                            <span class="text-[9px] font-bold text-gray-400 line-clamp-1 truncate max-w-[140px]" :title="cat.label">{{ cat.label }}</span>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>

                                            <!-- Decision Justification -->
                                            <div class="pt-5 border-t border-gray-100">
                                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">
                                                    Decision Justification <span class="text-red-500">*</span>
                                                </label>
                                                <textarea 
                                                    v-model="justification" 
                                                    rows="3" 
                                                    class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none text-sm transition-all bg-gray-50 hover:bg-white"
                                                    placeholder="Required: Explain why you are approving or rejecting this request..."
                                                    :class="showJustificationError ? 'border-red-300 ring-4 ring-red-50 bg-red-50/30' : 'border-gray-200'"
                                                ></textarea>
                                                <p v-if="showJustificationError" class="text-xs text-red-500 mt-2 font-bold flex items-center gap-1"><i class="pi pi-exclamation-circle"></i> Justification is required for this action.</p>
                                            </div>
                                        </div>

                                        <div class="flex gap-3 border-t border-gray-100 pt-6">
                                            <button 
                                                @click="handleAction(selectedRequest.status)" 
                                                class="cursor-pointer flex-1 py-3 bg-teal-600 hover:bg-teal-700 text-white rounded-lg font-bold shadow-lg shadow-teal-200 transition-all flex items-center justify-center gap-2"
                                                v-if="['Approved', 'Rejected', 'Cancelled'].includes(selectedRequest.status)"
                                                :disabled="processing"
                                            >
                                                <i class="pi pi-save" v-if="!processing"></i>
                                                <i class="pi pi-spin pi-spinner" v-else></i>
                                                Update Configuration
                                            </button>

                                            <!-- For Dept Approved (waiting for HR sign-off) -->
                                            <button 
                                                @click="handleAction('Approved')" 
                                                class="cursor-pointer flex-1 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold shadow-lg shadow-emerald-200 transition-all flex items-center justify-center gap-2 hover:-translate-y-0.5 active:translate-y-0"
                                                v-if="selectedRequest.status === 'Dept Approved'"
                                                :disabled="processing"
                                            >
                                                <i class="pi pi-verified" v-if="!processing"></i>
                                                <i class="pi pi-spin pi-spinner" v-else></i>
                                                Final HR Approval
                                            </button>

                                            <!-- For Pending (fallback admin override) -->
                                            <button 
                                                @click="handleAction('Approved')" 
                                                class="cursor-pointer flex-1 py-3 bg-teal-600 hover:bg-teal-700 text-white rounded-lg font-bold shadow-lg shadow-teal-200 transition-all flex items-center justify-center gap-2 hover:-translate-y-0.5 active:translate-y-0"
                                                v-if="selectedRequest.status === 'Pending'"
                                                :disabled="processing"
                                            >
                                                <i class="pi pi-check" v-if="!processing"></i>
                                                <i class="pi pi-spin pi-spinner" v-else></i>
                                                Approve (Override)
                                            </button>
                                            
                                            <button 
                                                @click="handleAction('Rejected')"
                                                class="cursor-pointer flex-1 py-3 bg-white border-2 border-rose-100 text-rose-600 hover:bg-rose-50 hover:border-rose-200 rounded-lg font-bold transition-all flex items-center justify-center gap-2 hover:-translate-y-0.5 active:translate-y-0"
                                                v-if="['Pending', 'Dept Approved', 'Cancelled'].includes(selectedRequest.status)"
                                                :disabled="processing"
                                            >
                                                <i class="pi pi-times" v-if="!processing"></i>
                                                Reject Request
                                            </button>

                                            <button 
                                                @click="handleAction('Cancelled')"
                                                class="cursor-pointer flex-1 py-3 bg-gray-100 text-gray-600 hover:bg-gray-200 rounded-lg font-bold transition-all flex items-center justify-center gap-2"
                                                v-if="selectedRequest.status === 'Approved' || selectedRequest.status === 'Pending'"
                                                :disabled="processing"
                                            >
                                                Cancel Request
                                            </button>

                                            <!-- Archive Button -->
                                            <button 
                                                @click="archiveRequest"
                                                class="cursor-pointer flex-1 py-3 bg-slate-800 text-white hover:bg-slate-900 rounded-lg font-bold transition-all flex items-center justify-center gap-2"
                                                v-if="['Approved', 'Rejected', 'Cancelled'].includes(selectedRequest.status)"
                                                :disabled="processing"
                                            >
                                                <i class="pi pi-archive"></i>
                                                Archive Record
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bulk Archive Modal -->
                <div v-if="showBulkArchiveModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60 backdrop-blur-md animate-in fade-in duration-300">
                    <div class="bg-white rounded-[32px] shadow-2xl w-full max-w-md overflow-hidden border border-white/20">
                        <div class="p-8 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-slate-800 flex items-center justify-center text-white shadow-xl shadow-slate-500/20">
                                    <i class="pi pi-history text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-black text-gray-900 leading-tight">Bulk Archiving</h3>
                                    <p class="text-[10px] text-gray-400 font-extrabold uppercase tracking-widest">Database Maintenance</p>
                                </div>
                            </div>
                            <button @click="showBulkArchiveModal = false" class="w-10 h-10 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-400 cursor-pointer transition-colors">
                                <i class="pi pi-times"></i>
                            </button>
                        </div>

                        <div class="p-8">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Select Archival Threshold</label>
                            <div class="space-y-3">
                                <label 
                                    v-for="option in [
                                        { label: '30 Days Ago', value: '30', desc: 'Archive records from last month' },
                                        { label: '60 Days Ago', value: '60', desc: 'Archive records older than 2 months' },
                                        { label: '90 Days Ago', value: '90', desc: 'Archive records from 3 months back' },
                                        { label: 'Full History', value: 'all', desc: 'Archive ALL historical processed records' }
                                    ]" 
                                    :key="option.value"
                                    class="flex items-center gap-4 p-4 rounded-2xl border-2 cursor-pointer transition-all active:scale-[0.98]"
                                    :class="bulkArchiveThreshold === option.value ? 'border-slate-800 bg-slate-50' : 'border-gray-100 hover:border-gray-200'"
                                    @click="bulkArchiveThreshold = option.value"
                                >
                                    <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center" :class="bulkArchiveThreshold === option.value ? 'border-slate-800 bg-slate-800' : 'border-gray-200'">
                                        <div v-if="bulkArchiveThreshold === option.value" class="w-2 h-2 rounded-full bg-white"></div>
                                    </div>
                                    <div>
                                        <p class="font-black text-sm text-gray-800">{{ option.label }}</p>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tight">{{ option.desc }}</p>
                                    </div>
                                </label>
                            </div>

                            <div class="mt-8 p-4 bg-amber-50 rounded-2xl border border-amber-100 flex gap-3">
                                <i class="pi pi-exclamation-triangle text-amber-500 mt-0.5"></i>
                                <p class="text-[10px] text-amber-700 font-bold leading-relaxed">
                                    ONLY processed requests (Approved, Rejected, or Cancelled) will be archived. Active pending requests will remain untouched.
                                </p>
                            </div>
                        </div>

                        <div class="p-8 bg-gray-50 border-t border-gray-100 flex flex-col gap-3">
                            <button 
                                @click="handleBulkArchive" 
                                :disabled="performingBulkArchive"
                                class="w-full py-4 bg-slate-800 hover:bg-slate-900 disabled:opacity-50 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-slate-900/20 transition-all flex items-center justify-center gap-2 cursor-pointer"
                            >
                                <i class="pi pi-spin pi-spinner" v-if="performingBulkArchive"></i>
                                <i class="pi pi-archive" v-else></i>
                                Run Archival Process
                            </button>
                            <button @click="showBulkArchiveModal = false" class="w-full py-4 bg-white border border-gray-200 text-gray-500 rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-gray-100 transition-all cursor-pointer">
                                Cancel
                            </button>
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
import { useLeaveStore } from '../../stores/leaves';
import { useEmployeeStore } from '../../stores/employees';
import { useSettingsStore } from '@/stores/settings';
import { storeToRefs } from 'pinia';
import MainLayout from '../../layouts/MainLayout.vue';
import LeaveRequestModal from '../common/LeaveRequestModal.vue';

const authStore = useAuthStore();
const leaveStore = useLeaveStore();
const employeeStore = useEmployeeStore();
const settingsStore = useSettingsStore();

const { user } = storeToRefs(authStore);
const { stats } = storeToRefs(leaveStore);
const departmentNames = computed(() => employeeStore.departmentNames);

const showAdminApplyModal = ref(false);

const requests = ref([]);
const loading = ref(false);
const page = ref(1);
const lastPage = ref(1);
const totalRecords = ref(0);

// Modal & Logic State
const selectedRequest = ref(null);
const analysis = ref(null);
const loadingAnalysis = ref(false);
const justification = ref('');
const showJustificationError = ref(false);
const processing = ref(false);
const adminRemarks = ref(''); // Keep for backward compat or merge

const filters = ref({
    search: '',
    status: 'Dept Approved',
    type: '',
    request_type: '',
    department: '',
    from_date: '',
    to_date: '',
    user_id: ''
});

const searchQuery = ref('');
let debounceTimeout = null;

watch(searchQuery, (newVal) => {
    if (debounceTimeout) clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        filters.value.search = newVal;
    }, 300);
});

// Use store stats
const pendingCount = computed(() => stats.value.pending);
const approvedCount = computed(() => stats.value.approved_this_month);
const rejectedCount = computed(() => stats.value.rejected);
const scheduledCount = computed(() => stats.value.cancelled); // Mapped in AdminDashboard similarly? No, AdminDashboard uses cancelled. ManageLeaves mapped scheduled to cancelled? Let's check original.
// Original ManageLeaves: scheduledCount = computed(() => stats.value.cancelled);
// Wait, the template says "Cancelled Requests" for scheduledCount?
// Line 110: <h3 ...>Cancelled</h3> and <p ...>Requests</p> inside the block where scheduledCount is used?
// Let's check lines 106-111 of ManageLeaves.vue
// original: 
// 106: <div ...>{{ scheduledCount }}</div>
// 110: <h3>Cancelled</h3>
// But wait, the controller returns 'scheduled' and 'cancelled'.
// Inspecting Controller:
// 'cancelled' => $cancelled (LeaveRequest::where('status', 'Cancelled')->count())
// 'scheduled' => $scheduled (Approved > now)
// In ManageLeaves original: values were mapped:
// const stats = ref({...})
// const pendingCount = computed(() => stats.value.pending)
// ...
// const scheduledCount = computed(() => stats.value.cancelled); <-- This looks like a bug in original code or visual mismatch
// The UI label says "Cancelled".
// Let's map it to cancelled.
const totalCount = computed(() => stats.value.total_all_time);

const filteredEmployeeData = computed(() => {
    if (!filters.value.user_id || requests.value.length === 0) return null;
    return requests.value[0]?.user;
});

onMounted(async () => {
    authStore.fetchUser();
    employeeStore.fetchDepartments();
    const urlParams = new URLSearchParams(window.location.search);
    const userId = urlParams.get('user_id');
    const search = urlParams.get('search');
    const adminFileTarget = urlParams.get('admin_file_target');

    if (userId) filters.value.user_id = userId;
    if (search) {
        searchQuery.value = search;
        filters.value.search = search;
    }
    if (adminFileTarget) {
        showAdminApplyModal.value = true;
    }
    
    settingsStore.fetchSettings();
    await fetchRequests();
    leaveStore.fetchStats();
});

const fetchRequests = async () => {
    loading.value = true;
    try {
        const params = {
            page: page.value,
            status: filters.value.status,
            leave_type: filters.value.type,
            request_type: filters.value.request_type,
            department: filters.value.department,
            from_date: filters.value.from_date,
            to_date: filters.value.to_date,
            search: filters.value.search,
            user_id: filters.value.user_id
        };
        const response = await axios.get('/api/leave-requests', { params });
        requests.value = response.data.data;
        lastPage.value = response.data.last_page;
        totalRecords.value = response.data.total;
    } catch (e) {
        console.error("Fetch failed", e);
    } finally {
        loading.value = false;
    }
};

watch(filters, () => { page.value = 1; fetchRequests(); }, { deep: true });
watch(page, () => { fetchRequests(); });

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

// New Modal Logic
const viewDetails = async (req) => {
    selectedRequest.value = { 
        ...req,
        days_paid: req.days_paid || req.days_taken || 0
    };
    justification.value = req.justification || req.admin_remarks || ''; // Pre-fill if exists
    adminRemarks.value = req.admin_remarks || '';
    
    // Fetch Analysis
    loadingAnalysis.value = true;
    analysis.value = null;
    try {
        const res = await axios.get(`/api/leave-requests/${req.id}/analysis`);
        analysis.value = res.data;
    } catch (e) {
        console.error("Failed to load analysis", e);
    } finally {
        loadingAnalysis.value = false;
    }
};

const closeModal = () => {
    selectedRequest.value = null;
    justification.value = '';
    showJustificationError.value = false;
};

const handleAction = async (newStatus) => {
    if (!justification.value.trim()) {
        showJustificationError.value = true;
        return;
    }
    
    if (!confirm(`Confirm action: ${newStatus}?`)) return;
    
    processing.value = true;
    try {
        const payload = {
            status: newStatus,
            is_paid: selectedRequest.value.is_paid,
            days_paid: selectedRequest.value.days_paid || 0,
            admin_remarks: justification.value,
            category: selectedRequest.value.category || null
        };
        
        await axios.put(`/api/leave-requests/${selectedRequest.value.id}`, payload);
        
        // Update local list
        const idx = requests.value.findIndex(r => r.id === selectedRequest.value.id);
        if (idx !== -1) {
            requests.value[idx] = { ...requests.value[idx], ...payload, updated_at: new Date() };
        }
        
        leaveStore.fetchStats(true); // Force refresh
        closeModal();
    } catch (e) {
        console.error("Action failed", e);
        alert('Action failed: ' + (e.response?.data?.message || 'Server error'));
    } finally {
        processing.value = false;
    }
};

const archiveRequest = async () => {
    if (!confirm('Are you sure you want to archive this request? It will be moved to the Archive Registry for permanent storage.')) return;
    
    processing.value = true;
    try {
        await axios.post(`/api/leave-requests/${selectedRequest.value.id}/archive`);
        alert('Request successfuly archived.');
        
        // Remove from current list
        requests.value = requests.value.filter(r => r.id !== selectedRequest.value.id);
        totalRecords.value--;
        
        closeModal();
        leaveStore.fetchStats(true);
    } catch (e) {
        console.error("Archive failed", e);
        alert('Archive failed: ' + (e.response?.data?.message || 'Server error'));
    } finally {
        processing.value = false;
    }
};

// Bulk Archive Logic
const showBulkArchiveModal = ref(false);
const bulkArchiveThreshold = ref('30'); // Default 30 days
const performingBulkArchive = ref(false);

const handleBulkArchive = async () => {
    let thresholdDate = 'all';
    let confirmMsg = "Are you sure you want to archive ALL historical processed leave requests (Approved/Rejected/Cancelled)?";

    if (bulkArchiveThreshold.value !== 'all') {
        const date = new Date();
        date.setDate(date.getDate() - parseInt(bulkArchiveThreshold.value));
        thresholdDate = date.toISOString().split('T')[0];
        confirmMsg = `Are you sure you want to archive ALL processed leave requests (Approved/Rejected/Cancelled) from before ${thresholdDate}?`;
    }

    if (!confirm(confirmMsg)) return;

    performingBulkArchive.value = true;
    try {
        const response = await axios.post('/api/leave-requests/bulk-archive', {
            before_date: thresholdDate
        });
        alert(response.data.message);
        showBulkArchiveModal.value = false;
        fetchRequests();
        leaveStore.fetchStats(true);
    } catch (e) {
        console.error("Bulk archive failed", e);
        alert('Bulk archive failed: ' + (e.response?.data?.message || 'Server error'));
    } finally {
        performingBulkArchive.value = false;
    }
};

const handleAdminSubmit = async (payload) => {
    try {
        const formData = new FormData();
        Object.keys(payload).forEach(key => {
            const val = payload[key];
            if (val !== null && val !== undefined) {
                if (key === 'additional_details' && typeof val === 'object' && !Array.isArray(val)) {
                    Object.entries(val).forEach(([subKey, subVal]) => {
                        formData.append(`${key}[${subKey}]`, subVal);
                    });
                } else {
                    const value = typeof val === 'boolean' ? (val ? 1 : 0) : val;
                    formData.append(key, value);
                }
            }
        });
        
        await axios.post('/api/leave-requests', formData);
        alert('Leave request filed successfully on behalf of employee.');
        fetchRequests();
        leaveStore.fetchStats(true);
    } catch (e) {
        console.error("Admin file failed", e);
        alert('Failed to file request: ' + (e.response?.data?.message || 'Server error'));
    }
};

const exportReport = () => {
    const params = new URLSearchParams({
        search: filters.value.search,
        status: filters.value.status,
        leave_type: filters.value.type,
        request_type: filters.value.request_type,
        department: filters.value.department,
        from_date: filters.value.from_date,
        to_date: filters.value.to_date,
        user_id: filters.value.user_id || ''
    });
    window.location.href = `/api/leave-requests/export?${params.toString()}`;
};
</script>
