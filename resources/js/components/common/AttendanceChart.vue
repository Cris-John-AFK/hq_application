<template>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div class="mb-6">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6">
                <h2 class="text-lg font-bold text-gray-800 whitespace-nowrap">{{ title }}</h2>
                
                <div class="flex flex-wrap items-center gap-3 bg-gray-50 p-1.5 rounded-xl border border-gray-100 max-w-full">
                    <!-- Date Pickers based on period -->
                    <div class="flex items-center gap-2">
                        <div v-if="activePeriod === 'Daily'" class="flex flex-wrap sm:flex-nowrap items-center gap-2">
                            <input 
                                type="date" 
                                v-model="startDate"
                                class="px-2 py-1.5 text-xs sm:text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none shadow-sm w-32 sm:w-auto"
                            />
                            <span class="text-gray-400 text-xs sm:text-sm font-medium">to</span>
                            <input 
                                type="date" 
                                v-model="endDate"
                                class="px-2 py-1.5 text-xs sm:text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none shadow-sm w-32 sm:w-auto"
                            />
                        </div>
                        <div v-else-if="activePeriod === 'Weekly'" class="flex items-center gap-2">
                            <input 
                                type="month" 
                                v-model="selectedMonth"
                                class="px-2 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none shadow-sm"
                            />
                        </div>
                        <div v-else-if="activePeriod === 'Monthly'" class="flex items-center gap-2">
                            <input 
                                type="number" 
                                v-model="selectedYear"
                                min="2020"
                                max="2030"
                                class="w-20 px-2 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none shadow-sm"
                                placeholder="Year"
                            />
                        </div>
                    </div>

                    <div class="h-6 w-px bg-gray-300 hidden sm:block"></div>
                    
                    <!-- Period Selector -->
                    <div class="flex bg-white rounded-lg p-1 border border-gray-200 shadow-sm overflow-x-auto max-w-full">
                        <button 
                            v-for="period in periods" 
                            :key="period"
                            @click="changePeriod(period)"
                            :class="[
                                'px-3 py-1 text-xs font-semibold rounded-md transition-all cursor-pointer whitespace-nowrap',
                                activePeriod === period ? 'bg-teal-50 text-teal-700 shadow-sm ring-1 ring-teal-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'
                            ]"
                        >
                            {{ period }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Chart Legend -->
            <div class="flex flex-wrap justify-center gap-6 pb-2">
                <div class="flex items-center cursor-pointer opacity-100 hover:opacity-80 transition-opacity" @click="toggleSeries(0)">
                    <div class="w-3 h-3 rounded-full bg-green-500 mr-2 shadow-sm ring-2 ring-green-100"></div>
                    <span class="text-sm font-medium text-gray-600">Present</span>
                </div>
                <div class="flex items-center cursor-pointer opacity-100 hover:opacity-80 transition-opacity" @click="toggleSeries(1)">
                    <div class="w-3 h-3 rounded-full bg-red-500 mr-2 shadow-sm ring-2 ring-red-100"></div>
                    <span class="text-sm font-medium text-gray-600">Absent</span>
                </div>
                <div class="flex items-center cursor-pointer opacity-100 hover:opacity-80 transition-opacity" @click="toggleSeries(2)">
                    <div class="w-3 h-3 rounded-full bg-yellow-500 mr-2 shadow-sm ring-2 ring-yellow-100"></div>
                    <span class="text-sm font-medium text-gray-600">Late</span>
                </div>
                <div class="flex items-center cursor-pointer opacity-100 hover:opacity-80 transition-opacity" @click="toggleSeries(3)">
                    <div class="w-3 h-3 rounded-full bg-purple-500 mr-2 shadow-sm ring-2 ring-purple-100"></div>
                    <span class="text-sm font-medium text-gray-600">Leave</span>
                </div>
            </div>
        </div>

        <!-- Chart Container -->
        <div class="chart-container relative h-80 w-full">
            <canvas ref="chartCanvas"></canvas>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { Chart, registerables } from 'chart.js';

// Register Chart.js components
Chart.register(...registerables);

const props = defineProps({
    title: {
        type: String,
        default: 'Attendance Overview'
    }
});

const periods = ['Daily', 'Weekly', 'Monthly'];
const activePeriod = ref('Weekly');

// Date pickers
const today = new Date();
const startDate = ref(new Date(today.getTime() - 7 * 86400000).toISOString().split('T')[0]);
const endDate = ref(today.toISOString().split('T')[0]);
const selectedMonth = ref(`${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}`);
const selectedYear = ref(today.getFullYear());

// Chart reference
const chartCanvas = ref(null);
let chartInstance = null;

// Toggle series visibility
const toggleSeries = (index) => {
    if (chartInstance) {
        const meta = chartInstance.getDatasetMeta(index);
        meta.hidden = meta.hidden === null ? !chartInstance.data.datasets[index].hidden : null;
        chartInstance.update();
    }
};

// Generate time series data for daily/weekly views
const generateDayWiseTimeSeries = (baseval, count, yrange) => {
    const data = [];
    for (let i = 0; i < count; i++) {
        const y = Math.floor(Math.random() * (yrange.max - yrange.min + 1)) + yrange.min;
        data.push(y);
    }
    return data;
};

// Generate monthly data (one data point per month)
const generateMonthlyData = (yrange) => {
    const data = [];
    for (let month = 0; month < 12; month++) {
        const y = Math.floor(Math.random() * (yrange.max - yrange.min + 1)) + yrange.min;
        data.push(y);
    }
    return data;
};

// Get labels based on period
const getLabels = () => {
    if (activePeriod.value === 'Daily') {
        const start = new Date(startDate.value);
        const end = new Date(endDate.value);
        const days = Math.ceil((end - start) / 86400000) + 1;
        const labels = [];
        for (let i = 0; i < days; i++) {
            const date = new Date(start);
            date.setDate(start.getDate() + i);
            labels.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
        }
        return labels;
    } else if (activePeriod.value === 'Weekly') {
        const [year, month] = selectedMonth.value.split('-');
        const firstDay = new Date(year, month - 1, 1);
        const lastDay = new Date(year, month, 0);
        const weeks = Math.ceil(lastDay.getDate() / 7);
        const labels = [];
        for (let i = 0; i < weeks; i++) {
            const weekStart = new Date(firstDay);
            weekStart.setDate(1 + i * 7);
            const weekEnd = new Date(weekStart);
            weekEnd.setDate(weekStart.getDate() + 6);
            if (weekEnd > lastDay) weekEnd.setTime(lastDay.getTime());
            labels.push(`${weekStart.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${weekEnd.toLocaleDateString('en-US', { day: 'numeric' })}`);
        }
        return labels;
    } else {
        // Monthly - show all 12 months
        return ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    }
};

// Get data count
const getDataCount = () => {
    if (activePeriod.value === 'Daily') {
        const start = new Date(startDate.value);
        const end = new Date(endDate.value);
        return Math.ceil((end - start) / 86400000) + 1;
    } else if (activePeriod.value === 'Weekly') {
        const [year, month] = selectedMonth.value.split('-');
        const lastDay = new Date(year, month, 0);
        return Math.ceil(lastDay.getDate() / 7);
    } else {
        return 12; // Monthly shows 12 months
    }
};

// Chart data
const chartData = computed(() => {
    const labels = getLabels();
    const count = getDataCount();
    const isMonthly = activePeriod.value === 'Monthly';

    return {
        labels: labels,
        datasets: [
            {
                label: 'Present',
                backgroundColor: '#22c55e', // green-500
                borderColor: '#16a34a',
                borderWidth: 1,
                borderRadius: 4,
                data: isMonthly 
                    ? generateMonthlyData({ min: 30, max: 60 })
                    : generateDayWiseTimeSeries(0, count, { min: 30, max: 60 }),
            },
            {
                label: 'Absent',
                backgroundColor: '#ef4444', // red-500
                borderColor: '#dc2626',
                borderWidth: 1,
                borderRadius: 4,
                data: isMonthly
                    ? generateMonthlyData({ min: 5, max: 25 })
                    : generateDayWiseTimeSeries(0, count, { min: 5, max: 25 }),
            },
            {
                label: 'Late',
                backgroundColor: '#eab308', // yellow-500
                borderColor: '#ca8a04',
                borderWidth: 1,
                borderRadius: 4,
                data: isMonthly
                    ? generateMonthlyData({ min: 5, max: 20 })
                    : generateDayWiseTimeSeries(0, count, { min: 5, max: 20 }),
            },
            {
                label: 'Leave',
                backgroundColor: '#a855f7', // purple-500
                borderColor: '#9333ea',
                borderWidth: 1,
                borderRadius: 4,
                data: isMonthly
                    ? generateMonthlyData({ min: 2, max: 15 })
                    : generateDayWiseTimeSeries(0, count, { min: 2, max: 15 }),
            }
        ]
    };
});

// Chart options
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    layout: {
        padding: { top: 10, bottom: 10, left: 0, right: 10 }
    },
    categoryPercentage: 0.8,
    barPercentage: 0.9,
    interaction: {
        mode: 'index',
        intersect: false,
    },
    plugins: {
        legend: {
            display: false // Hide default legend
        },
        tooltip: {
            backgroundColor: 'rgba(255, 255, 255, 0.95)',
            titleColor: '#1f2937',
            bodyColor: '#4b5563',
            borderColor: '#e5e7eb',
            borderWidth: 1,
            padding: 10,
            boxPadding: 4,
            usePointStyle: true,
            callbacks: {
                label: function(context) {
                    return ` ${context.dataset.label}: ${context.parsed.y}`;
                }
            }
        }
    },
    scales: {
        x: {
            grid: {
                display: false,
            },
            ticks: {
                font: { family: 'Inter, sans-serif', size: 10 },
                color: '#9ca3af',
                maxRotation: 0,
                autoSkip: true,
                autoSkipPadding: 15
            }
        },
        y: {
            beginAtZero: true,
            grid: {
                color: '#f3f4f6',
                borderDash: [5, 5]
            },
            ticks: {
                font: { family: 'Inter, sans-serif', size: 11 },
                color: '#9ca3af',
                padding: 10
            }
        }
    }
};

// Create or update chart
const updateChart = async () => {
    await nextTick();
    
    if (!chartCanvas.value) return;

    // Destroy existing chart
    if (chartInstance) {
        chartInstance.destroy();
    }

    // Create new chart
    const ctx = chartCanvas.value.getContext('2d');
    chartInstance = new Chart(ctx, {
        type: 'bar',
        data: chartData.value,
        options: chartOptions
    });
};

const changePeriod = (period) => {
    activePeriod.value = period;
};

// Watch for changes
watch([activePeriod, startDate, endDate, selectedMonth, selectedYear], () => {
    updateChart();
});

// Lifecycle hooks
onMounted(() => {
    updateChart();
});

onBeforeUnmount(() => {
    if (chartInstance) {
        chartInstance.destroy();
    }
});
</script>

<style scoped>
.chart-container {
    position: relative;
    height: 320px;
    width: 100%;
}

/* Custom date input styling */
input[type="date"],
input[type="month"],
input[type="number"] {
    font-family: 'Inter', sans-serif;
    color-scheme: light;
}

input[type="date"]::-webkit-calendar-picker-indicator,
input[type="month"]::-webkit-calendar-picker-indicator {
    cursor: pointer;
    opacity: 0.5;
    transition: opacity 0.2s;
}

input[type="date"]:hover::-webkit-calendar-picker-indicator,
input[type="month"]:hover::-webkit-calendar-picker-indicator {
    opacity: 1;
}
</style>
