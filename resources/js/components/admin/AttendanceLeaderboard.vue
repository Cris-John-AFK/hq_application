<template>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
        <!-- Professional Header -->
        <div class="p-5 border-b border-gray-50 flex items-center justify-between bg-white">
            <div class="flex items-center gap-3">
                <div class="w-1.5 h-6 bg-teal-500 rounded-full"></div>
                <div>
                    <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest">Performance Ranking</h3>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Attendance Excellence • Weekly</p>
                </div>
            </div>
            <div class="flex items-center gap-1.5 px-3 py-1 bg-teal-50 rounded-lg border border-teal-100">
                <i class="pi pi-chart-line text-teal-600 text-xs"></i>
                <span class="text-[10px] font-black text-teal-700 uppercase">Top 100% Goal</span>
            </div>
        </div>

        <!-- Scrollable Ranking List -->
        <div class="flex-1 p-2">
            <div v-if="loading" class="py-12 flex justify-center">
                <i class="pi pi-spin pi-spinner text-teal-500"></i>
            </div>
            
            <div v-else class="space-y-1">
                <div v-for="(dept, index) in sortedStats.slice(0, 5)" :key="dept.id" 
                    class="group flex items-center justify-between p-3 rounded-xl transition-all duration-200 hover:bg-gray-50 border border-transparent hover:border-gray-100"
                >
                    <div class="flex items-center gap-4">
                        <!-- Numeric Rank with subtle indicator -->
                        <div class="relative flex items-center justify-center w-8">
                            <span v-if="index === 0" class="text-amber-500"><i class="pi pi-star-fill text-xs"></i></span>
                            <span v-else class="text-xs font-black text-gray-300">{{ index + 1 }}</span>
                        </div>

                        <!-- Department Info -->
                        <div>
                            <p class="text-sm font-bold text-gray-700 group-hover:text-teal-600 transition-colors">{{ dept.name }}</p>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-[10px] text-gray-400 font-medium uppercase tracking-tighter">Avg. Present: </span>
                                <span class="text-[10px] font-bold text-gray-600">{{ dept.avg_present }}</span>
                                <span class="text-[10px] text-gray-300">•</span>
                                <span class="text-[10px] font-bold" :class="parseFloat(dept.rate) >= 95 ? 'text-green-500' : 'text-gray-400'">{{ dept.rate }} Rate</span>
                            </div>
                        </div>
                    </div>

                    <!-- Visual Progress Metric -->
                    <div class="flex flex-col items-end gap-1.5 w-32">
                         <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div 
                                class="h-full transition-all duration-1000 ease-out" 
                                :class="[
                                    index === 0 ? 'bg-teal-500 shadow-[0_0_8px_rgba(20,184,166,0.3)]' : 'bg-gray-200 group-hover:bg-teal-400'
                                ]"
                                :style="{ width: dept.rate }"
                            ></div>
                        </div>
                        <div class="flex items-center gap-1">
                            <span v-if="index === 0" class="text-[9px] font-black text-teal-600 uppercase tracking-widest italic">Peak Performer</span>
                            <span v-else class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">{{ dept.headcount }} Staff</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer / Action -->
        <div class="p-4 border-t border-gray-50 bg-gray-50/30">
            <button class="w-full py-2.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 hover:border-gray-300 text-gray-600 text-xs font-black uppercase tracking-[0.1em] transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-[0.98]">
                <span>View Full Organizational Rankings</span>
                <i class="pi pi-arrow-right text-[10px]"></i>
            </button>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { useEmployeeStore } from '../../stores/employees';
import { storeToRefs } from 'pinia';

const employeeStore = useEmployeeStore();
const { departmentStats, loading } = storeToRefs(employeeStore);

const sortedStats = computed(() => {
    return [...departmentStats.value].sort((a, b) => {
        const rateA = parseFloat(a.rate);
        const rateB = parseFloat(b.rate);
        return rateB - rateA;
    });
});

onMounted(() => {
    employeeStore.fetchDepartmentStats();
});
</script>
