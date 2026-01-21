<template>
    <div v-if="user">
        <MainLayout :user="user">
            <AdminDashboard v-if="user.role === 'admin'" />
            <UserDashboard v-else />
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
    import { useAuthStore } from '../stores/auth';
    import { storeToRefs } from 'pinia';
    import MainLayout from '../layouts/MainLayout.vue';
    import AdminDashboard from './admin/AdminDashboard.vue';
    import UserDashboard from './user/UserDashboard.vue';

    const authStore = useAuthStore();
    const { user } = storeToRefs(authStore);

    onMounted(() => {
        authStore.fetchUser();
    });
</script>
