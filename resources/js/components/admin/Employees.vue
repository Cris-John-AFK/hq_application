<template>
    <div v-if="user">
        <MainLayout :user="user">
            <div class="max-w-7xl mx-auto">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">Employees</h1>
                    <p class="text-gray-500">Manage your workforce.</p>
                </div>
                <!-- Reusing the Employee List component here as it fits perfectly -->
                <EmployeeList />
            </div>
        </MainLayout>
    </div>
</template>

<script setup>
    import { onMounted } from 'vue';
    import { useAuthStore } from '../../stores/auth';
    import { storeToRefs } from 'pinia';
    import MainLayout from '../../layouts/MainLayout.vue';
    import EmployeeList from '../common/EmployeeList.vue';

    const authStore = useAuthStore();
    const { user } = storeToRefs(authStore);

    onMounted(() => {
        authStore.fetchUser();
    });
</script>
