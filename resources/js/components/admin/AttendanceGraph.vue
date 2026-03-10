<template>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col h-full">
        <div class="p-5 border-b border-gray-50 flex items-center justify-between">
            <div>
                <h4 class="text-xs font-black text-gray-900 uppercase tracking-widest mb-1">Attendance Trend</h4>
                <p class="text-[10px] font-bold text-gray-400">Employee presence overview</p>
            </div>
            
            <div class="flex bg-gray-50 p-1 rounded-lg border border-gray-100">
                <button 
                    v-for="type in ['daily', 'weekly', 'monthly']" 
                    :key="type"
                    @click="currentType = type"
                    class="px-3 py-1 text-[9px] font-black uppercase tracking-tighter rounded-md transition-all"
                    :class="currentType === type ? 'bg-white text-teal-600 shadow-sm border border-gray-100' : 'text-gray-400 hover:text-gray-600'"
                >
                    {{ type }}
                </button>
            </div>
        </div>

        <div class="flex-1 p-4 relative min-h-[220px]">
            <div v-if="loading" class="absolute inset-0 z-10 bg-white/50 flex items-center justify-center">
                <i class="pi pi-spin pi-spinner text-teal-600 text-xl"></i>
            </div>
            
            <apexchart 
                v-if="chartOptions && series.length > 0"
                type="area" 
                height="100%" 
                :options="chartOptions" 
                :series="series"
            ></apexchart>
            
            <div v-else-if="!loading" class="h-full flex flex-col items-center justify-center text-gray-400 opacity-50">
                <i class="pi pi-chart-bar text-3xl mb-2"></i>
                <span class="text-[10px] font-bold uppercase">No data found</span>
            </div>
        </div>
        
        <div class="px-5 py-3 bg-gray-50/50 border-t border-gray-50 flex flex-col sm:flex-row sm:justify-between items-start sm:items-center gap-2">
            <div class="flex items-center gap-2">
                <div class="w-1.5 h-1.5 rounded-full bg-teal-500 animate-pulse"></div>
                <span class="text-[9px] font-bold text-gray-500 uppercase tracking-wide">Live Presence Sync</span>
            </div>
            <div class="flex items-center gap-3 bg-white px-2 py-0.5 rounded-md border border-gray-100 shadow-sm">
                <span class="text-[10px] font-black text-teal-600">{{ totalPresent }} Present</span>
                <span class="text-gray-200">|</span>
                <span class="text-[10px] font-black text-amber-500">{{ totalLate }} Lates</span>
                <span class="text-gray-200">|</span>
                <span class="text-[10px] font-black text-rose-500">{{ totalAbsent }} Absences</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';

const currentType = ref('daily');
const loading = ref(false);
const series = ref([]);
const chartOptions = ref(null);
const totalPresent = ref(0);
const totalLate = ref(0);
const totalAbsent = ref(0);

const fetchStats = async () => {
    loading.value = true;
    try {
        const { data } = await axios.get('/api/attendance-stats/graph', {
            params: { type: currentType.value }
        });
        
        totalPresent.value = data.reduce((acc, curr) => acc + parseInt(curr.count || 0), 0);
        totalLate.value = data.reduce((acc, curr) => acc + parseInt(curr.late_count || 0), 0);
        totalAbsent.value = data.reduce((acc, curr) => acc + parseInt(curr.absent_count || 0), 0);
        
        const categories = data.map(d => d.label || d.date);
        const presentValues = data.map(d => parseFloat(d.count));
        const absentValues = data.map(d => parseFloat(d.absent_count));
        const lateValues = data.map(d => parseFloat(d.late_count));
        
        series.value = [
            { name: 'Present', data: presentValues },
            { name: 'Lates', data: lateValues },
            { name: 'Absences', data: absentValues }
        ];
        
        chartOptions.value = {
            chart: {
                toolbar: { show: false },
                fontFamily: 'inherit',
                sparkline: { enabled: false }
            },
            stroke: {
                curve: 'smooth',
                width: [3, 2, 2],
                colors: ['#0d9488', '#f59e0b', '#e11d48']
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.45,
                    opacityTo: 0.05,
                    stops: [20, 100],
                    colorStops: [
                        [ { offset: 0, color: '#0d9488', opacity: 0.4 }, { offset: 100, color: '#ffffff', opacity: 0.1 } ],
                        [ { offset: 0, color: '#f59e0b', opacity: 0.3 }, { offset: 100, color: '#ffffff', opacity: 0.05 } ],
                        [ { offset: 0, color: '#e11d48', opacity: 0.3 }, { offset: 100, color: '#ffffff', opacity: 0.05 } ]
                    ]
                }
            },
            colors: ['#0d9488', '#f59e0b', '#e11d48'],
            xaxis: {
                categories: categories,
                labels: {
                    rotate: 0,
                    style: {
                        colors: '#9ca3af',
                        fontSize: '9px',
                        fontWeight: 700
                    }
                },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                min: 0,
                labels: {
                    style: {
                        colors: '#9ca3af',
                        fontSize: '9px',
                        fontWeight: 700
                    }
                }
            },
            markers: {
                size: 4,
                colors: ['#0d9488', '#f59e0b', '#e11d48'],
                strokeWidth: 2,
                hover: { size: 6 }
            },
            grid: {
                borderColor: '#f3f4f6',
                strokeDashArray: 4,
                padding: { top: 0, bottom: 0 }
            },
            dataLabels: { enabled: false },
            legend: {
                show: true,
                position: 'top',
                horizontalAlign: 'right',
                fontSize: '10px',
                fontWeight: 700,
                markers: { width: 8, height: 8, radius: 12 },
                itemMargin: { horizontal: 10, vertical: 0 }
            },
            tooltip: {
                theme: 'light',
                x: { show: true }
            }
        };
    } catch (error) {
        console.error('Failed to fetch attendance stats:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(fetchStats);
watch(currentType, fetchStats);
</script>
