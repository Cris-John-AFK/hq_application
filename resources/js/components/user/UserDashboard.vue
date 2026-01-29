<template>
    <div class="max-w-4xl mx-auto space-y-6">
        <BulletinBoard />
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-8">
                <div v-if="loading">
                    <!-- Profile Skeleton -->
                    <div class="flex items-center gap-6 mb-8 animate-pulse">
                        <div class="w-24 h-24 rounded-full bg-gray-100 shadow-lg border-2 border-white"></div>
                        <div class="space-y-3 flex-1">
                            <div class="h-8 w-1/3 bg-gray-100 rounded"></div>
                            <div class="h-4 w-1/4 bg-gray-100 rounded"></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div v-for="i in 4" :key="i" class="bg-gray-50 p-6 rounded-xl border border-gray-100 animate-pulse h-32 flex flex-col justify-end">
                            <div class="h-3 w-1/4 bg-gray-200 rounded mb-4"></div>
                            <div class="h-6 w-1/2 bg-gray-200 rounded"></div>
                        </div>
                    </div>
                </div>

                <div v-else-if="user">
                    <div class="flex items-center gap-6 mb-8">
                        <div v-if="user.avatar_url" class="w-24 h-24 rounded-full overflow-hidden shadow-lg border-2 border-white">
                            <img :src="user.avatar_url" alt="Profile Photo" class="w-full h-full object-cover">
                        </div>
                        <div v-else class="w-24 h-24 rounded-full bg-teal-100 flex items-center justify-center text-teal-600 font-bold text-3xl shadow-lg border-2 border-white">
                            {{ authStore.getInitials(user.name) }}
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">{{ user.name }}</h2>
                            <p class="text-gray-500">{{ user.email }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 p-6 rounded-xl border border-gray-100 transition-all hover:bg-white hover:shadow-md">
                            <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Role</h3>
                            <div class="flex">
                                <span class="bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider border border-blue-100">
                                    {{ user.role }}
                                </span>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-6 rounded-xl border border-gray-100 transition-all hover:bg-white hover:shadow-md">
                            <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Position</h3>
                            <p class="text-sm font-bold text-gray-800 tracking-tight">{{ user.position || 'Not Assigned' }}</p>
                        </div>

                        <div class="bg-gray-50 p-6 rounded-xl border border-gray-100 transition-all hover:bg-white hover:shadow-md">
                            <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-3">ID Number</h3>
                            <p class="text-sm font-black text-gray-800 tracking-wider font-mono uppercase">{{ user.id_number || 'N/A' }}</p>
                        </div>
                        
                        <!-- User Specific Card -->
                            <div class="bg-teal-50 p-6 rounded-xl border border-teal-100 transition-all hover:bg-white hover:shadow-md h-full flex flex-col">
                                <h3 class="text-xs font-black text-teal-600 uppercase tracking-widest mb-3">Latest Leave Request Status</h3>
                                <div class="flex-1">
                                    <div v-if="latestRequest">
                                        <p class="text-sm font-bold text-gray-800">{{ formatDate(latestRequest.date_filed) }}</p>
                                        <p class="text-xs text-gray-500 mt-1 truncate">{{ latestRequest.leave_type }}</p>
                                        <span class="inline-flex mt-3 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest shadow-sm"
                                              :class="{
                                                'bg-yellow-50 text-yellow-700 border border-yellow-200': (latestRequest.status || 'Pending') === 'Pending',
                                                'bg-emerald-50 text-emerald-700 border border-emerald-200': latestRequest.status === 'Approved',
                                                'bg-rose-50 text-rose-700 border border-rose-200': latestRequest.status === 'Rejected',
                                                'bg-gray-50 text-gray-500 border border-gray-200': latestRequest.status === 'Cancelled'
                                              }">
                                            {{ latestRequest.status || 'Pending' }}
                                        </span>
                                    </div>
                                    <div v-else class="flex flex-col items-center justify-center py-2">
                                        <p class="text-gray-400 italic text-[10px] font-bold uppercase tracking-widest">No recent requests</p>
                                    </div>
                                </div>
                            
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
            </div>
        </div>
    </div>
</template>

<script setup>
    import { useAuthStore } from '../../stores/auth';
    import { storeToRefs } from 'pinia';
    import { ref, onMounted } from 'vue';
    import BulletinBoard from '../common/BulletinBoard.vue';
    import axios from 'axios';

    const authStore = useAuthStore();
    const { user } = storeToRefs(authStore);
    const latestRequest = ref(null);
    const loading = ref(true);

    const openLeaveRequest = () => {
        window.location.href = '/leave-requests';
    };

    const formatDate = (date) => {
        if(!date) return '';
        return new Date(date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
    };

    onMounted(async () => {
        loading.value = true;
        if (!user.value) {
           await authStore.fetchUser();
        }
        
        try {
            const response = await axios.get('/api/leave-requests');
            if(response.data.data && response.data.data.length > 0) {
                latestRequest.value = response.data.data[0];
            }
        } catch (error) {
            console.error('Failed to fetch latest request', error);
        } finally {
            loading.value = false;
        }
    });
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