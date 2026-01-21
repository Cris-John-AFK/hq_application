<template>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold text-gray-800">{{ title }}</h3>
            <div class="flex bg-gray-100 rounded-lg p-1">
                <button 
                    v-for="period in periods" 
                    :key="period"
                    @click="activePeriod = period"
                    :class="[
                        'px-3 py-1 text-sm font-medium rounded-md transition-all',
                        activePeriod === period ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-500 hover:text-gray-700'
                    ]"
                >
                    {{ period }}
                </button>
            </div>
        </div>
        <div class="relative h-55 w-full">
            <Bar :data="chartData" :options="chartOptions" />
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const props = defineProps({
    title: {
        type: String,
        default: 'Attendance Overview'
    }
});

const periods = ['Daily', 'Weekly', 'Monthly'];
const activePeriod = ref('Weekly');

// Mock Data Generators
const getLabels = (accPeriod) => {
    if (accPeriod === 'Daily') return ['9 AM', '10 AM', '11 AM', '12 PM', '1 PM', '2 PM', '3 PM', '4 PM'];
    if (accPeriod === 'Weekly') return ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    return ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
};

const getRandomData = (length) => Array.from({ length }, () => Math.floor(Math.random() * 50) + 10);

const chartData = computed(() => {
    const labels = getLabels(activePeriod.value);
    return {
        labels: labels,
        datasets: [
            {
                label: 'Present',
                backgroundColor: '#14b8a6', // teal-500
                data: getRandomData(labels.length),
                borderRadius: 4,
            },
            {
                label: 'Absent',
                backgroundColor: '#fb923c', // orange-400
                data: getRandomData(labels.length),
                borderRadius: 4,
            },
            {
                label: 'Late',
                backgroundColor: '#f87171', // red-400
                data: getRandomData(labels.length),
                borderRadius: 4,
            },
            {
                label: 'On Leave',
                backgroundColor: '#a78bfa', // purple-400
                data: getRandomData(labels.length),
                borderRadius: 4,
            }
        ]
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top',
            align: 'end',
            labels: {
                usePointStyle: true,
                boxWidth: 8
            }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: {
                display: true,
                borderDash: [2, 2],
                color: '#f3f4f6'
            }
        },
        x: {
            grid: {
                display: false
            }
        }
    }
};
</script>
