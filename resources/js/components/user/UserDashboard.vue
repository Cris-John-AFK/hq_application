<template>
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-8">
                <div v-if="user">
                    <div class="flex items-center gap-6 mb-8">
                        <div class="h-20 w-20 rounded-full bg-teal-100 flex items-center justify-center text-teal-600 text-3xl font-bold">
                            {{ user.name.charAt(0) }}
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">{{ user.name }}</h2>
                            <p class="text-gray-500">{{ user.email }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 p-6 rounded-xl border border-gray-100">
                            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Role</h3>
                            <div class="flex items-center gap-2">
                                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-medium capitalize">
                                    {{ user.role }}
                                </span>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-6 rounded-xl border border-gray-100">
                            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Position</h3>
                            <p class="text-lg font-medium text-gray-800">{{ user.position || 'Not Assigned' }}</p>
                        </div>

                        <div class="bg-gray-50 p-6 rounded-xl border border-gray-100">
                            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">ID Number</h3>
                            <p class="text-lg font-mono font-medium text-gray-800">{{ user.id_number || 'N/A' }}</p>
                        </div>
                        
                        <!-- User Specific Card -->
                            <div class="bg-teal-50 p-6 rounded-xl border border-teal-100">
                            <h3 class="text-sm font-semibold text-teal-600 uppercase tracking-wider mb-2">My Tasks</h3>
                            <p class="text-gray-600">You have no pending tasks for today.</p>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center text-gray-500">
                    <i class="pi pi-spin pi-spinner text-4xl mb-4"></i>
                    <p>Loading user profile...</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { useAuthStore } from '../../stores/auth';
    import { storeToRefs } from 'pinia';

    const authStore = useAuthStore();
    const { user } = storeToRefs(authStore);
</script>
