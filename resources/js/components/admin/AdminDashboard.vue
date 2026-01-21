<template>
    <div class="max-w-7xl mx-auto">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <StatCard 
                    icon="pi-users" 
                    value="254" 
                    label="Total Employees" 
                    iconBgClass="bg-orange-50"
                    iconTextClass="text-orange-500"
                />
                <StatCard 
                    icon="pi-check-circle" 
                    value="20" 
                    label="Present" 
                    iconBgClass="bg-green-50"
                    iconTextClass="text-green-500"
                />
                <StatCard 
                    icon="pi-times-circle" 
                    value="20" 
                    label="Absent" 
                    iconBgClass="bg-red-50"
                    iconTextClass="text-red-500"
                />
                <StatCard 
                    icon="pi-clock" 
                    value="20" 
                    label="On Leave" 
                    iconBgClass="bg-yellow-50"
                    iconTextClass="text-yellow-500"
                />
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <!-- Left Column (Chart) -->
                <div class="lg:col-span-2">
                    <AttendanceChart />
                </div>

                <!-- Right Column (Calendar) -->
                <div class="flex flex-col gap-8">
                    <EventCalendar />
                </div>
            </div>

            <!-- Bottom Section (Employee List) -->
            <div class="w-full">
                <EmployeeList />
            </div>
        </div>
</template>

<script setup>
    import { useAuthStore } from '../../stores/auth';
    import { storeToRefs } from 'pinia';
    import StatCard from '../common/StatCard.vue';
    import AttendanceChart from '../common/AttendanceChart.vue';
    import EventCalendar from '../common/EventCalendar.vue';
    import EmployeeList from '../common/EmployeeList.vue';

    const authStore = useAuthStore();
    const { user } = storeToRefs(authStore);
</script>
