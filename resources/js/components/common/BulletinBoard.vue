<template>
    <div class="space-y-4 mb-6">
        <!-- Skeleton Loading State -->
        <div v-if="loading" class="animate-pulse space-y-4 min-h-[148px]">
            <div class="flex items-center justify-between pl-1">
                <div class="h-4 w-32 bg-gray-200 rounded"></div>
                <div class="flex gap-1">
                    <div class="w-6 h-6 rounded-full bg-gray-200"></div>
                    <div class="w-6 h-6 rounded-full bg-gray-200"></div>
                </div>
            </div>
            <div class="h-28 w-full bg-gray-100 rounded-2xl border border-gray-200 p-5 flex items-center gap-5">
                <div class="w-14 h-14 rounded-2xl bg-gray-200 shrink-0"></div>
                <div class="flex-1 space-y-3">
                    <div class="h-4 w-1/3 bg-gray-200 rounded"></div>
                    <div class="h-3 w-full bg-gray-200 rounded"></div>
                    <div class="h-3 w-2/3 bg-gray-200 rounded"></div>
                </div>
            </div>
        </div>

        <!-- Actual Content or Fallback banner if Admin -->
        <div v-else class="space-y-4">
            <div class="flex items-center justify-between pl-1">
                <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                    {{ announcements.length > 0 ? 'Company Bulletins' : 'System Status' }}
                </h3>
                <div v-if="announcements.length > 0 || user?.role === 'admin'" class="flex gap-1 items-center">
                    <!-- Admin Manage Button -->
                    <button 
                        v-if="user?.role === 'admin'"
                        @click="showManageModal = true"
                        class="mr-2 text-[10px] font-black uppercase text-gray-400 hover:text-teal-600 transition-colors cursor-pointer flex items-center gap-1"
                    >
                        <i class="pi pi-cog text-[10px]"></i>
                        Manage
                    </button>
                    
                    <template v-if="announcements.length > 1">
                        <button 
                            @click="prev" 
                            class="w-6 h-6 rounded-full border border-gray-200 flex items-center justify-center text-gray-400 hover:bg-gray-100 transition-colors"
                        >
                            <i class="pi pi-chevron-left text-[10px]"></i>
                        </button>
                        <button 
                            @click="next" 
                            class="w-6 h-6 rounded-full border border-gray-200 flex items-center justify-center text-gray-400 hover:bg-gray-100 transition-colors"
                        >
                            <i class="pi pi-chevron-right text-[10px]"></i>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Announcements Display -->
            <div 
                v-if="announcements.length > 0"
                class="relative overflow-hidden rounded-2xl border bg-white shadow-sm transition-all duration-300 h-28" 
                :class="getTypeBorder(currentAnnouncement.type)"
                @mouseenter="stopAutoplay"
                @mouseleave="startAutoplay"
            >
                <Transition name="slide" mode="out-in">
                    <div :key="activeIndex" class="absolute inset-0 p-5 flex items-center gap-5">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center shrink-0 shadow-sm" :class="getTypeBg(currentAnnouncement.type)">
                            <i class="pi text-xl text-white" :class="getTypeIcon(currentAnnouncement.type)"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <h4 class="font-black text-gray-900 truncate tracking-tight">{{ currentAnnouncement.title }}</h4>
                                <span v-if="activeIndex === 0" class="px-1.5 py-0.5 bg-rose-500 text-white text-[8px] font-black rounded uppercase tracking-tighter animate-pulse">New</span>
                            </div>
                            <p class="text-sm text-gray-500 line-clamp-2 leading-snug">{{ currentAnnouncement.content }}</p>
                        </div>
                    </div>
                </Transition>
                
                <!-- Progress Indicators -->
                <div v-if="announcements.length > 1" class="absolute bottom-3 right-5 flex gap-1.5">
                    <div 
                        v-for="(_, i) in announcements" 
                        :key="i"
                        @click="activeIndex = i"
                        class="h-1 rounded-full transition-all duration-300 cursor-pointer"
                        :class="[activeIndex === i ? 'w-4 bg-teal-500' : 'w-1.5 bg-gray-200 hover:bg-gray-300']"
                    ></div>
                </div>
            </div>

            <!-- Fallback Banner if no announcements -->
            <div 
                v-else-if="user?.role === 'admin'"
                class="relative overflow-hidden rounded-2xl border border-teal-100 bg-gradient-to-r from-teal-50 to-white shadow-sm h-28 flex items-center p-5 group transition-all hover:shadow-md"
            >
                <div class="w-14 h-14 rounded-2xl bg-teal-500 flex items-center justify-center shrink-0 shadow-lg text-white group-hover:scale-110 transition-transform">
                    <i class="pi pi-shield text-xl"></i>
                </div>
                <div class="ml-5">
                    <h4 class="font-black text-gray-900 tracking-tight">Admin Command Center Active</h4>
                    <p class="text-sm text-gray-500">Welcome back, {{ user.name.split(' ')[0] }}. All systems are operational and running v2.4 Enterprise.</p>
                </div>
                <!-- Subtle decorative element -->
                <i class="pi pi-prime absolute -right-4 -bottom-4 text-8xl text-teal-500/5 rotate-12"></i>
            </div>
            
            <!-- Management Modal -->
            <ManageAnnouncementsModal 
                v-if="showManageModal" 
                @close="showManageModal = false"
                @refresh="fetchAnnouncements"
            />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { storeToRefs } from 'pinia';
import ManageAnnouncementsModal from './ManageAnnouncementsModal.vue';
import axios from 'axios';

const authStore = useAuthStore();
const { user } = storeToRefs(authStore);

const announcements = ref([]);
const activeIndex = ref(0);
const showManageModal = ref(false);
const loading = ref(true);

const currentAnnouncement = computed(() => announcements.value[activeIndex.value] || {});

const fetchAnnouncements = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/announcements');
        announcements.value = response.data;
        // Reset index if data changed and current index is out of bounds
        if (activeIndex.value >= announcements.value.length) {
            activeIndex.value = 0;
        }
    } catch (error) {
        console.error('Failed to fetch announcements:', error);
    } finally {
        loading.value = false;
    }
};

const next = () => {
    if (activeIndex.value < announcements.value.length - 1) {
        activeIndex.value++;
    } else {
        activeIndex.value = 0; // Loop back to start
    }
    resetAutoplay();
};

const prev = () => {
    if (activeIndex.value > 0) {
        activeIndex.value--;
    } else {
        activeIndex.value = announcements.value.length - 1; // Loop to end
    }
    resetAutoplay();
};

// Autoplay Logic
let autoplayInterval = null;
const startAutoplay = () => {
    stopAutoplay();
    if (announcements.value.length > 1) {
        autoplayInterval = setInterval(() => {
            next();
        }, 5000); // Scroll every 5 seconds
    }
};

const stopAutoplay = () => {
    if (autoplayInterval) {
        clearInterval(autoplayInterval);
        autoplayInterval = null;
    }
};

const resetAutoplay = () => {
    stopAutoplay();
    startAutoplay();
};

const getTypeIcon = (type) => {
    switch(type) {
        case 'urgent': return 'pi-exclamation-triangle';
        case 'warning': return 'pi-info-circle';
        case 'success': return 'pi-megaphone';
        default: return 'pi-bell';
    }
};

const getTypeBg = (type) => {
    switch(type) {
        case 'urgent': return 'bg-rose-500';
        case 'warning': return 'bg-amber-500';
        case 'success': return 'bg-emerald-500';
        default: return 'bg-teal-500';
    }
};

const getTypeBorder = (type) => {
    switch(type) {
        case 'urgent': return 'border-rose-100';
        case 'warning': return 'border-amber-100';
        case 'success': return 'border-emerald-100';
        default: return 'border-teal-100';
    }
};

onMounted(async () => {
    await fetchAnnouncements();
    startAutoplay();
});

onUnmounted(() => {
    stopAutoplay();
});
</script>

<style scoped>
.slide-enter-active,
.slide-leave-active {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.slide-enter-from {
  opacity: 0;
  transform: translateX(30px);
}

.slide-leave-to {
  opacity: 0;
  transform: translateX(-30px);
}
</style>
