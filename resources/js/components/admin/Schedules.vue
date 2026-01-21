<template>
    <div v-if="user">
        <MainLayout :user="user">
            <div class="max-w-7xl mx-auto">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                    <div class="w-20 h-20 bg-purple-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="pi pi-calendar text-3xl text-purple-600"></i>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-800 mb-2">Shift Schedules</h1>
                    <p class="text-gray-500 mb-8">Manage employee shifts and rosters here.</p>
                </div>
            </div>
        </MainLayout>
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
