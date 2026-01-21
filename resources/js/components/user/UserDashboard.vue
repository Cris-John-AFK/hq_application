<template>
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-8">
                <div v-if="user">
                    <div class="flex items-center gap-6 mb-8">
                        <div v-if="user.avatar_url" class="w-24 h-24 rounded-full overflow-hidden shadow-lg border-2 border-white">
                            <img :src="user.avatar_url" alt="Profile Photo" class="w-full h-full object-cover">
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
                                    <p class="text-gray-800 mt-1 capitalize">{{ user.role }}</p>
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
                            <h3 class="text-sm font-semibold text-teal-600 uppercase tracking-wider mb-2">Latest Leave Request Status</h3>
                            <p class="text-gray-600">January 21, 2026 | Emergency Reasons</p>
                            <!-- From Uiverse.io by adamgiebl --> 
                            <button 
                                class="cssbuttons-io-button mt-5"
                                @click="openLeaveRequest"
                                >
                            Status
                            <div class="icon">
                                <svg
                                height="24"
                                width="24"
                                viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg"
                                >
                                <path d="M0 0h24v24H0z" fill="none"></path>
                                <path
                                    d="M16.172 11l-5.364-5.364 1.414-1.414L20 12l-7.778 7.778-1.414-1.414L16.172 13H4v-2z"
                                    fill="currentColor"
                                ></path>
                                </svg>
                            </div>
                            </button>

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
    import { ref } from 'vue';

    const authStore = useAuthStore();
    const { user } = storeToRefs(authStore);

    const openLeaveRequest = () => {
        window.location.href = '/leave-requests';
    };
</script>

<style>
    /* From Uiverse.io by adamgiebl */ 
.cssbuttons-io-button {
  background: #81f070;
  color: white;
  font-family: inherit;
  padding: 0.35em;
  padding-top: 0.35em;
  padding-left: 1.2em;
  font-size: 17px;
  font-weight: 500;
  border-radius: 0.9em;
  border: none;
  letter-spacing: 0.05em;
  display: flex;
  align-items: center;
  box-shadow: inset 0 0 1.6em -0.6em #714da6;
  overflow: hidden;
  position: relative;
  height: 2.8em;
  padding-right: 3.3em;
  cursor: pointer;
}

.cssbuttons-io-button .icon {
  background: white;
  margin-left: 1em;
  position: absolute;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 2.2em;
  width: 2.2em;
  border-radius: 0.7em;
  box-shadow: 0.1em 0.1em 0.6em 0.2em #57b952;
  right: 0.3em;
  transition: all 0.3s;
}

.cssbuttons-io-button:hover .icon {
  width: calc(100% - 0.6em);
}

.cssbuttons-io-button .icon svg {
  width: 1.1em;
  transition: transform 0.3s;
  color: #52b952;
}

.cssbuttons-io-button:hover .icon svg {
  transform: translateX(0.1em);
}

.cssbuttons-io-button:active .icon {
  transform: scale(0.95);
}

</style>