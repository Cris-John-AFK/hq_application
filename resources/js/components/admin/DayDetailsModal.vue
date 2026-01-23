<template>
    <div v-if="modelValue && date" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="close">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in-95 duration-200 flex flex-col max-h-[85vh]">
            <!-- Header -->
            <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50 shrink-0">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">{{ formatDate(date.fullDate) }}</p>
                    <h3 class="font-bold text-gray-800 text-xl">Daily Overview</h3>
                </div>
                <button @click="close" class="cursor-pointer w-8 h-8 rounded-full hover:bg-gray-200 flex items-center justify-center text-gray-500 transition-colors">
                    <i class="pi pi-times"></i>
                </button>
            </div>
            
            <!-- Content -->
            <div class="overflow-y-auto p-5 space-y-6">
                
                <!-- Empty State -->
                <div v-if="!hasAnyEvents" class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="pi pi-calendar-minus text-2xl text-gray-300"></i>
                    </div>
                    <p class="text-gray-400 font-medium">No events or leaves for this day.</p>
                </div>

                <!-- Holidays/Custom Events -->
                <div v-if="date.customEvents && date.customEvents.length > 0">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <i class="pi pi-flag"></i> Events & Holidays
                    </h4>
                    <div class="space-y-3">
                        <div v-for="evt in date.customEvents" :key="evt.id || evt.name" 
                            class="bg-white border rounded-xl p-3 shadow-sm hover:shadow-md transition-shadow relative group"
                            :class="getEventBorderClass(evt)"
                        >
                            <div class="flex justify-between items-start">
                                <div>
                                    <h5 class="font-bold text-gray-800">{{ evt.title || evt.name }}</h5>
                                    <p class="text-xs text-gray-500 mt-1 capitalize">{{ evt.type }}</p>
                                    <p v-if="evt.description" class="text-sm text-gray-600 mt-2 bg-gray-50 p-2 rounded-lg">{{ evt.description }}</p>
                                </div>
                                <!-- Delete Button (Only for custom events with ID) -->
                                <button 
                                    v-if="evt.id" 
                                    @click="deleteEvent(evt.id)"
                                    class="text-gray-300 hover:text-red-500 transition-colors p-1"
                                    title="Delete Event"
                                >
                                    <i class="pi pi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leaves -->
                <div v-if="date.events && date.events.length > 0">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <i class="pi pi-users"></i> Staff On Leave
                    </h4>
                    <div class="space-y-3">
                        <div v-for="leave in date.events" :key="leave.id" class="flex items-center gap-3 bg-purple-50/50 border border-purple-100 rounded-xl p-3">
                            <div class="w-10 h-10 rounded-full bg-purple-200 flex items-center justify-center shrink-0 overflow-hidden">
                                <img v-if="leave.avatar" :src="leave.avatar" class="w-full h-full object-cover">
                                <span v-else class="font-bold text-purple-700 text-sm">{{ (leave.user_name || leave.title || 'U').charAt(0) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-gray-800 text-sm truncate">{{ leave.user_name || leave.title }}</p>
                                <p class="text-xs text-gray-500">{{ leave.leave_type }}</p>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-1 bg-white text-purple-700 text-[10px] font-bold rounded border border-purple-100 shadow-sm uppercase tracking-wider">
                                    {{ leave.request_type }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import axios from 'axios';

const props = defineProps(['modelValue', 'date']);
const emit = defineEmits(['update:modelValue', 'delete']);

const close = () => emit('update:modelValue', false);

const hasAnyEvents = computed(() => {
    return (props.date?.customEvents?.length > 0) || (props.date?.events?.length > 0);
});

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
};

const getEventBorderClass = (evt) => {
    const type = evt.type;
    if (type === 'Regular Holiday' || type === 'holiday') return 'border-rose-200 border-l-4 border-l-rose-500';
    if (type === 'Special Non-Working') return 'border-orange-200 border-l-4 border-l-orange-500';
    if (type === 'meeting') return 'border-indigo-200 border-l-4 border-l-indigo-500';
    return 'border-blue-200 border-l-4 border-l-blue-500';
};

const deleteEvent = async (id) => {
    if (!confirm('Are you sure you want to delete this event?')) return;
    try {
        await axios.delete(`/api/custom-events/${id}`);
        emit('delete');
        close();
    } catch (e) {
        console.error("Delete failed", e);
        alert("Failed to delete event");
    }
};
</script>
