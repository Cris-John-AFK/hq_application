<template>
    <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="close">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="font-bold text-gray-800 text-lg">Add New Event</h3>
                <button @click="close" class="cursor-pointer text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="pi pi-times"></i>
                </button>
            </div>
            
            <form @submit.prevent="submit" class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Event Title</label>
                    <input 
                        v-model="form.title" 
                        type="text" 
                        required
                        class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none transition-all font-medium text-gray-800 placeholder-gray-400"
                        placeholder="e.g., Company Outing, Special Holiday"
                    >
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Start Date</label>
                        <input 
                            v-model="form.start_date" 
                            type="date" 
                            required
                            class="cursor-pointer w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none transition-all font-medium text-gray-800"
                        >
                    </div>
                     <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Type</label>
                        <select 
                            v-model="form.type" 
                            class="cursor-pointer w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none transition-all font-medium text-gray-800"
                        >
                            <option value="event">General Event</option>
                            <option value="holiday">Holiday</option>
                            <option value="meeting">Meeting</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Description (Optional)</label>
                    <textarea 
                        v-model="form.description" 
                        rows="3"
                        class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none transition-all font-medium text-gray-800 placeholder-gray-400 text-sm resize-none"
                        placeholder="Additional details..."
                    ></textarea>
                </div>

                <div class="pt-2 flex justify-end gap-3">
                    <button 
                        type="button" 
                        @click="close"
                        class="cursor-pointer px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg font-medium transition-colors"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        :disabled="loading"
                        class="cursor-pointer px-6 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg font-medium shadow-sm transition-all disabled:opacity-70 disabled:cursor-not-allowed flex items-center gap-2"
                    >
                        <i v-if="loading" class="pi pi-spin pi-spinner"></i>
                        {{ loading ? 'Creating...' : 'Create Event' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import axios from 'axios';

const props = defineProps(['modelValue']);
const emit = defineEmits(['update:modelValue', 'success']);

const loading = ref(false);
const form = reactive({
    title: '',
    start_date: new Date().toISOString().split('T')[0],
    type: 'event',
    description: ''
});

const close = () => {
    emit('update:modelValue', false);
    // Reset form slightly delayed or keep state? Resetting is better UX
    setTimeout(() => {
        form.title = '';
        form.description = '';
        form.type = 'event';
    }, 200);
};

const submit = async () => {
    loading.value = true;
    try {
        await axios.post('/api/custom-events', form);
        emit('success');
        close();
    } catch (e) {
        console.error("Failed to create event", e);
        alert("Failed to create event");
    } finally {
        loading.value = false;
    }
};
</script>
