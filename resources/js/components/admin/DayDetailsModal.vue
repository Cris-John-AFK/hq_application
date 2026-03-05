<template>
    <div v-if="modelValue && date" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="close">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in-95 duration-200 flex flex-col max-h-[85vh]">
            <!-- Header -->
            <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50 shrink-0">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">{{ formatDate(date.fullDate) }}</p>
                    <h3 class="font-bold text-gray-800 text-xl">Daily Overview</h3>
                </div>
                <button @click="close" class="cursor-pointer w-8 h-8 rounded-full hover:bg-gray-200 flex items-center justify-center text-gray-500 transition-colors">
                    <i class="pi pi-times"></i>
                </button>
            </div>
            
            <!-- Content -->
            <div class="overflow-y-auto p-5 space-y-6">
                
                <!-- Empty State -->
                <div v-if="!hasAnyEvents" class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="pi pi-calendar-minus text-2xl text-gray-300"></i>
                    </div>
                    <p class="text-gray-400 font-medium">No events or leaves for this day.</p>
                </div>

                <!-- Holidays/Custom Events -->
                <div v-if="date.customEvents && date.customEvents.length > 0">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <i class="pi pi-flag"></i> Events & Holidays
                    </h4>
                    <div class="space-y-3">
                        <div v-for="evt in date.customEvents" :key="evt.id || evt.name" 
                            class="bg-white border rounded-xl p-3 shadow-sm hover:shadow-md transition-shadow relative group"
                            :class="getEventBorderClass(evt)"
                        >
                            <div class="flex justify-between items-start">
                                <div>
                                    <h5 class="font-bold text-gray-800">{{ evt.title || evt.name }}</h5>
                                    <p class="text-xs text-gray-500 mt-1 capitalize">{{ evt.type }}</p>
                                    <p v-if="evt.description" class="text-sm text-gray-600 mt-2 bg-gray-50 p-2 rounded-lg">{{ evt.description }}</p>
                                </div>
                                <!-- Delete Button (Only for custom events with ID) -->
                                <button 
                                    v-if="evt.id" 
                                    @click="deleteEvent(evt.id)"
                                    class="cursor-pointer text-gray-300 hover:text-red-500 transition-colors p-1"
                                    title="Delete Event"
                                >
                                    <i class="pi pi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leaves -->
                <div v-if="date.events && date.events.length > 0">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <i class="pi pi-users"></i> Staff On Leave
                    </h4>
                    <div class="space-y-3">
                        <div v-for="leave in date.events" :key="leave.id" class="flex items-center gap-3 bg-violet-50/50 border border-violet-100 rounded-xl p-3">
                            <div class="w-10 h-10 rounded-full bg-violet-200 flex items-center justify-center shrink-0 overflow-hidden">
                                <img v-if="leave.avatar" :src="leave.avatar" class="w-full h-full object-cover">
                                <span v-else class="font-bold text-violet-700 text-sm">{{ (leave.user_name || leave.title || 'U').charAt(0) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-gray-800 text-sm truncate">{{ leave.user_name || leave.title }}</p>
                                <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                    <span v-if="leave.user_department" class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-100 rounded text-[10px] font-bold uppercase tracking-wide">
                                        <i class="pi pi-building text-[8px]"></i>
                                        {{ leave.user_department }}
                                    </span>
                                    <span v-if="leave.user_position" class="inline-flex items-center gap-1 px-2 py-0.5 bg-gray-100 text-gray-600 border border-gray-200 rounded text-[10px] font-bold uppercase tracking-wide">
                                        <i class="pi pi-briefcase text-[8px]"></i>
                                        {{ leave.user_position }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 bg-violet-100 text-violet-700 border border-violet-200 rounded text-[10px] font-bold uppercase tracking-wide">
                                        {{ leave.leave_type }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="px-2 py-1 bg-white text-violet-700 text-[10px] font-bold rounded border border-violet-100 shadow-sm uppercase tracking-wider">
                                    {{ leave.request_type }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attendance Details (Heatmap Click View) -->
                <div v-if="viewMode === 'attendance'">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4 pb-4 border-b border-gray-100 sticky top-0 bg-white z-10">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center gap-2">
                            <i class="pi pi-id-card"></i> Daily Attendance Records
                        </h4>
                        
                        <div class="flex items-center gap-2">
                            <!-- Search -->
                            <div class="relative group">
                                <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[10px] group-focus-within:text-teal-500 transition-colors"></i>
                                <input 
                                    v-model="searchQuery"
                                    type="text" 
                                    placeholder="Search staff..." 
                                    class="w-40 bg-gray-50 border-none rounded-lg py-1.5 pl-8 pr-3 text-[10px] font-bold focus:ring-2 focus:ring-teal-500/20 transition-all outline-none"
                                >
                            </div>

                            <!-- Status Filter -->
                            <select 
                                v-model="statusFilter"
                                class="bg-gray-50 border-none rounded-lg py-1.5 px-3 text-[10px] font-bold focus:ring-2 focus:ring-teal-500/20 transition-all outline-none cursor-pointer"
                            >
                                <option value="all">All Status</option>
                                <option value="Present">Present</option>
                                <option value="Absent">Absent</option>
                                <option value="Late">Late</option>
                                <option value="Half Day">Half Day</option>
                            </select>
                        </div>
                    </div>
                    
                    <div v-if="loadingAttendance" class="space-y-3">
                        <div v-for="i in 3" :key="i" class="h-16 bg-gray-50 animate-pulse rounded-xl"></div>
                    </div>
                    
                    <div v-else-if="filteredAttendance.length === 0" class="text-center py-10 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                        <i class="pi pi-info-circle text-2xl text-gray-300 mb-2 block"></i>
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">No matching records found</p>
                    </div>

                    <div v-else class="space-y-2">
                        <div v-for="record in filteredAttendance" :key="record.id" 
                            class="flex items-center justify-between p-3 bg-white border border-gray-100 rounded-xl hover:shadow-md transition-all group"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-teal-50 flex items-center justify-center text-teal-600 font-black text-xs border border-teal-100 uppercase overflow-hidden ring-2 ring-white shadow-sm group-hover:scale-105 transition-transform">
                                    <img v-if="record.avatar" :src="record.avatar" class="w-full h-full object-cover">
                                    <span v-else>{{ getInitials(record.employee_name) }}</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-black text-gray-800 leading-none mb-1 truncate">{{ record.employee_name }}</p>
                                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">{{ record.department }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <div class="text-right">
                                    <div class="flex items-center gap-1.5 text-[10px] font-black text-gray-700">
                                        <i class="pi pi-sign-in text-emerald-500"></i>
                                        {{ record.time_in }}
                                    </div>
                                    <div class="flex items-center gap-1.5 text-[10px] font-black text-gray-700 mt-1">
                                        <i class="pi pi-sign-out text-rose-500"></i>
                                        {{ record.time_out }}
                                    </div>
                                </div>
                                <span :class="[
                                    'px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-tighter border min-w-[60px] text-center',
                                    {
                                        'bg-emerald-50 text-emerald-600 border-emerald-100': record.status === 'Present',
                                        'bg-rose-50 text-rose-600 border-rose-100': record.status === 'Absent',
                                        'bg-orange-50 text-orange-600 border-orange-100': record.status === 'Late',
                                        'bg-amber-50 text-amber-600 border-amber-100': record.status === 'Half Day'
                                    }
                                ]">{{ record.status }}</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';

const props = defineProps(['modelValue', 'date', 'viewMode']);
const emit = defineEmits(['update:modelValue', 'delete']);

const dailyAttendanceRecords = ref([]);
const loadingAttendance = ref(false);
const searchQuery = ref('');
const statusFilter = ref('all');

const filteredAttendance = computed(() => {
    return dailyAttendanceRecords.value.filter(record => {
        const matchesSearch = !searchQuery.value || 
            record.employee_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            record.department.toLowerCase().includes(searchQuery.value.toLowerCase());
        
        const matchesStatus = statusFilter.value === 'all' || record.status === statusFilter.value;
        
        return matchesSearch && matchesStatus;
    });
});

const close = () => emit('update:modelValue', false);

const fetchDailyAttendance = async () => {
    if (!props.date?.fullDate || props.viewMode !== 'attendance') return;
    
    loadingAttendance.value = true;
    try {
        const { data } = await axios.get('/api/attendance-records', {
            params: { 
                start_date: props.date.fullDate,
                end_date: props.date.fullDate
            }
        });
        dailyAttendanceRecords.value = data.map(r => ({
            ...r,
            department: r.employee_department || r.department || '--'
        }));
    } catch (e) {
        console.error(e);
    } finally {
        loadingAttendance.value = false;
    }
};

watch(() => props.modelValue, (newVal) => {
    if (newVal && props.viewMode === 'attendance') {
        fetchDailyAttendance();
    } else {
        dailyAttendanceRecords.value = [];
        searchQuery.value = '';
        statusFilter.value = 'all';
    }
});

const getInitials = (name) => {
    if (!name) return '??';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
};

const hasAnyEvents = computed(() => {
    if (props.viewMode === 'attendance') return true; // Always show or empty state
    return (props.date?.customEvents?.length > 0) || (props.date?.events?.length > 0);
});

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
};

const getEventBorderClass = (evt) => {
    const type = evt.type;
    if (type === 'Regular Holiday' || type === 'holiday') return 'border-rose-200 border-l-4 border-l-rose-600 bg-rose-50/30';
    if (type === 'Special Non-Working') return 'border-amber-200 border-l-4 border-l-amber-600 bg-amber-50/30';
    if (type === 'meeting') return 'border-indigo-200 border-l-4 border-l-indigo-600 bg-indigo-50/30';
    return 'border-sky-200 border-l-4 border-l-sky-600 bg-sky-50/30';
};

const deleteEvent = async (id) => {
    if (!confirm('Are you sure you want to delete this event?')) return;
    try {
        // Strip the 'evt_' prefix if it exists to get the numeric ID
        const numericId = id.toString().replace('evt_', '');
        await axios.delete(`/api/custom-events/${numericId}`);
        emit('delete');
        close();
    } catch (e) {
        console.error("Delete failed", e);
        alert("Failed to delete event");
    }
};
</script>
