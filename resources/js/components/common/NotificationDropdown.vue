<template>
    <div class="relative">
        <button 
            @click="isOpen = !isOpen"
            class="relative p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-all cursor-pointer"
            v-click-outside="() => isOpen = false"
        >
            <i class="pi pi-bell text-lg"></i>
            <span v-if="unreadCount > 0" class="absolute -top-1 -right-1 w-5 h-5 bg-rose-500 text-white text-[10px] font-black rounded-full flex items-center justify-center shadow-lg animate-pulse ring-2 ring-white">
                {{ unreadCount }}
            </span>
        </button>

        <Transition
            enter-active-class="transition duration-200 ease-out transform"
            enter-from-class="opacity-0 scale-95 translate-y-2"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition duration-150 ease-in transform"
            leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-95 translate-y-2"
        >
            <div v-if="isOpen" class="absolute right-0 mt-3 w-80 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden z-50">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <h3 class="font-black text-gray-800 text-sm">Notifications</h3>
                    <button 
                        v-if="unreadCount > 0"
                        @click="markAllAsRead"
                        class="text-[10px] font-black uppercase tracking-wider text-teal-600 hover:text-teal-700 cursor-pointer"
                    >
                        Mark all as read
                    </button>
                </div>

                <div class="max-h-[400px] overflow-y-auto overflow-x-hidden divide-y divide-gray-50">
                    <div v-if="notifications.length === 0" class="p-10 text-center">
                        <i class="pi pi-bell-slash text-gray-300 text-3xl mb-3 block"></i>
                        <p class="text-xs text-gray-400 font-medium">All caught up!</p>
                    </div>

                    <div 
                        v-for="note in notifications" 
                        :key="note.id"
                        @click="handleNoteClick(note)"
                        class="p-4 hover:bg-gray-50 transition-colors cursor-pointer relative group"
                        :class="{ 'bg-teal-50/30': !note.is_read }"
                    >
                        <div class="flex gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 shadow-sm" :class="getTypeBg(note.type)">
                                <i class="pi text-white text-xs" :class="getTypeIcon(note.type)"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start mb-0.5">
                                    <p class="font-bold text-gray-800 text-xs truncate leading-none pt-1">{{ note.title }}</p>
                                    <span class="text-[9px] font-bold text-gray-400 uppercase tabular-nums">{{ formatTime(note.created_at) }}</span>
                                </div>
                                <p class="text-xs text-gray-500 line-clamp-2 leading-snug">{{ note.message }}</p>
                            </div>
                        </div>
                        <button 
                            @click.stop="deleteNote(note.id)"
                            class="absolute top-2 right-2 p-1 text-gray-300 hover:text-rose-500 opacity-0 group-hover:opacity-100 transition-all rounded-md hover:bg-rose-50"
                        >
                            <i class="pi pi-times text-[10px]"></i>
                        </button>
                    </div>
                </div>

                <div v-if="notifications.length > 0" class="p-3 bg-gray-50/50 text-center border-t border-gray-100">
                    <button class="text-[10px] font-black uppercase tracking-widest text-gray-400 hover:text-gray-600 transition-colors">
                        Clear all logs
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue';
import axios from 'axios';

const isOpen = ref(false);
const notifications = ref([]);
const unreadCount = computed(() => notifications.value.filter(n => !n.is_read).length);

const fetchNotifications = async () => {
    try {
        const response = await axios.get('/api/notifications');
        notifications.value = response.data;
    } catch (error) {
        console.error('Failed to fetch notifications');
    }
};

const handleNoteClick = async (note) => {
    if (!note.is_read) {
        try {
            await axios.put(`/api/notifications/${note.id}/read`);
            note.is_read = true;
        } catch (error) {
            console.error('Failed to mark as read');
        }
    }
    if (note.link) {
        window.location.href = note.link;
    }
};

const markAllAsRead = async () => {
    try {
        await axios.post('/api/notifications/mark-all-read');
        notifications.value.forEach(n => n.is_read = true);
    } catch (error) {
        console.error('Failed to mark all as read');
    }
};

const deleteNote = async (id) => {
    try {
        await axios.delete(`/api/notifications/${id}`);
        notifications.value = notifications.value.filter(n => n.id !== id);
    } catch (error) {
        console.error('Failed to delete notification');
    }
};

const getTypeIcon = (type) => {
    switch(type) {
        case 'success': return 'pi-check';
        case 'warning': return 'pi-exclamation-circle';
        case 'urgent': return 'pi-bolt';
        default: return 'pi-info';
    }
};

const getTypeBg = (type) => {
    switch(type) {
        case 'success': return 'bg-emerald-500';
        case 'warning': return 'bg-amber-500';
        case 'urgent': return 'bg-rose-500';
        default: return 'bg-teal-500';
    }
};

const formatTime = (iso) => {
    const date = new Date(iso);
    const now = new Date();
    const diff = now - date;
    const mins = Math.floor(diff / 60000);
    const hrs = Math.floor(mins / 60);
    
    if (mins < 1) return 'Just now';
    if (mins < 60) return `${mins}m`;
    if (hrs < 24) return `${hrs}h`;
    return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
};

// Global click outside directive-like behavior
const vClickOutside = {
    mounted(el, binding) {
        el.clickOutsideEvent = (event) => {
            if (!(el === event.target || el.contains(event.target))) {
                binding.value();
            }
        };
        document.addEventListener("click", el.clickOutsideEvent);
    },
    unmounted(el) {
        document.removeEventListener("click", el.clickOutsideEvent);
    },
};

let pollInterval;
onMounted(() => {
    fetchNotifications();
    pollInterval = setInterval(fetchNotifications, 60000); // Poll every minute
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});
</script>
