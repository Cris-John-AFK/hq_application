<template>
    <div v-if="user">
        <MainLayout :user="user">
            <div class="space-y-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">My Attendance</h1>
                        <p class="text-sm text-gray-500 mt-1">Track your attendance history and records</p>
                    </div>
                </div>

                <!-- Attendance Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center">
                                <i class="pi pi-check-circle text-green-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-800">22</p>
                                <p class="text-xs text-gray-500">Present Days</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center">
                                <i class="pi pi-times-circle text-red-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-800">2</p>
                                <p class="text-xs text-gray-500">Absent Days</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-yellow-100 flex items-center justify-center">
                                <i class="pi pi-clock text-yellow-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-800">3</p>
                                <p class="text-xs text-gray-500">Late Arrivals</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                                <i class="pi pi-percentage text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-800">92%</p>
                                <p class="text-xs text-gray-500">Attendance Rate</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attendance Table -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800">Recent Attendance</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                                    <th class="px-6 py-4 font-semibold">Date</th>
                                    <th class="px-6 py-4 font-semibold">Time In</th>
                                    <th class="px-6 py-4 font-semibold">Time Out</th>
                                    <th class="px-6 py-4 font-semibold">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4 text-sm text-gray-800">January 21, 2026</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">08:30 AM</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">05:00 PM</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                            Present
                                        </span>
                                    </td>
                                </tr>
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4 text-sm text-gray-800">January 20, 2026</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">08:15 AM</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">05:15 PM</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                            Present
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </MainLayout>
    </div>
    <div v-else class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="text-center">
            <i class="pi pi-spin pi-spinner text-4xl mb-4 text-teal-600"></i>
            <p class="text-gray-500">Loading...</p>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { storeToRefs } from 'pinia';
import MainLayout from '../../layouts/MainLayout.vue';

const authStore = useAuthStore();
const { user } = storeToRefs(authStore);

onMounted(() => {
    authStore.fetchUser();
});
</script>
