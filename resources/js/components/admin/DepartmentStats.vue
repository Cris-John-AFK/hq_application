<template>
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
            <div class="flex flex-col">
                <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <i class="pi pi-history text-teal-600"></i>
                    Department Attendance
                </h3>
                <span class="text-[9px] text-gray-400 font-medium mt-0.5">Imported on Jan 23, 2026</span>
            </div>
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Previous Week</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] uppercase tracking-wider text-gray-500 border-b border-gray-50">
                        <th class="px-4 py-3 font-bold">Department</th>
                        <th class="px-4 py-3 font-bold text-center">Headcount</th>
                        <th class="px-4 py-3 font-bold text-center">Avg. Present</th>
                        <th class="px-4 py-3 font-bold text-center">Rate</th>
                        <th class="px-4 py-3 font-bold text-center">Leave</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <tr v-for="dept in stats" :key="dept.id" class="hover:bg-gray-50 transition-colors group">
                        <td class="px-4 py-3">
                            <span class="text-sm font-semibold text-gray-700">{{ dept.name }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs font-bold text-gray-600 bg-gray-100 px-2 py-0.5 rounded-full">{{ dept.headcount }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex flex-col items-center">
                                <span class="text-xs font-black text-green-600">{{ dept.avg_present }}</span>
                                <div class="w-8 h-1 bg-gray-100 rounded-full mt-1 overflow-hidden">
                                    <div 
                                        class="h-full bg-green-500 transition-all duration-500" 
                                        :style="{ width: dept.rate }"
                                    ></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs font-bold text-teal-600">{{ dept.rate }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs font-bold text-purple-500">{{ dept.avg_on_leave || 0 }}</span>
                        </td>
                    </tr>
                    <tr v-if="loading && stats.length === 0">
                        <td colspan="5" class="px-4 py-10 text-center">
                            <i class="pi pi-spin pi-spinner text-teal-500"></i>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useEmployeeStore } from '../../stores/employees';
import { storeToRefs } from 'pinia';

const employeeStore = useEmployeeStore();
const { departmentStats: stats, loading } = storeToRefs(employeeStore);

onMounted(() => {
    employeeStore.fetchDepartmentStats();
});
</script>
