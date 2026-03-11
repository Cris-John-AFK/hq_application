<template>
    <div v-if="modelValue" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" @click.self="closeModal">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-200 flex flex-col max-h-[90vh]">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50 shrink-0">
                <div>
                    <h3 class="font-bold text-gray-800 text-lg">Manage Working Hours</h3>
                    <p class="text-[11px] text-gray-500 font-bold uppercase tracking-wide mt-1">Configure assigned shift codes & times</p>
                </div>
                <button 
                    @click="closeModal"
                    class="p-2 hover:bg-gray-200 rounded-full transition-colors text-gray-400 hover:text-gray-600 cursor-pointer"
                >
                    <i class="pi pi-times"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto flex-1 space-y-4 bg-slate-50">
                <div v-if="isLoading" class="flex flex-col items-center justify-center py-8">
                    <i class="pi pi-spin pi-spinner text-3xl text-teal-600 mb-2"></i>
                    <p class="text-sm text-gray-500 font-bold">Loading shifts...</p>
                </div>
                
                <div v-else class="space-y-3">
                    <div v-for="(shift, index) in shifts" :key="index" class="bg-white p-3 rounded-xl border border-gray-200 shadow-sm flex items-center gap-3 relative group transition-all" :class="{'ring-2 ring-teal-500 ring-offset-1': editIndex === index}">
                        <div v-if="editIndex !== index" class="w-8 h-8 rounded-full bg-teal-50 text-teal-600 flex items-center justify-center font-black shrink-0 border border-teal-100">
                            {{ shift.code }}
                        </div>
                        <div v-else class="flex flex-col gap-2 p-1">
                            <p class="text-[8px] font-black text-teal-600 uppercase tracking-widest pl-1">Pick Code</p>
                            <div class="flex flex-wrap gap-1 max-w-[120px]">
                                <button 
                                    v-for="char in 'ABCDEFGHIJKLMN'.split('')" 
                                    :key="char"
                                    @click="editForm.code = char"
                                    type="button"
                                    :class="editForm.code === char ? 'bg-teal-600 text-white' : 'bg-teal-50 text-teal-700 hover:bg-teal-100'"
                                    class="w-6 h-6 rounded flex items-center justify-center text-[9px] font-black transition-all cursor-pointer"
                                >
                                    {{ char }}
                                </button>
                            </div>
                        </div>
                        
                        <div v-if="editIndex !== index" class="flex-1">
                            <p class="text-sm font-bold text-gray-800 tracking-tight">{{ shift.time }}</p>
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-0.5">Shift Configuration</p>
                        </div>
                        <input v-else v-model="editForm.time" type="text" placeholder="HH:MM AM - HH:MM PM" class="flex-1 px-3 h-8 rounded-lg bg-gray-50 text-gray-800 text-sm font-bold border border-gray-200 outline-none focus:ring-2 focus:ring-teal-500" required>
                        
                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <template v-if="editIndex !== index">
                                <button @click="startEdit(index, shift)" class="w-8 h-8 rounded flex items-center justify-center text-teal-600 hover:bg-teal-50 transition-colors" title="Edit Shift">
                                    <i class="pi pi-pencil text-xs"></i>
                                </button>
                                <button @click="removeShift(index)" class="w-8 h-8 rounded flex items-center justify-center text-rose-500 hover:bg-rose-50 transition-colors" title="Delete Shift">
                                    <i class="pi pi-trash text-xs"></i>
                                </button>
                            </template>
                            <template v-else>
                                <button @click="saveEdit" class="w-8 h-8 rounded flex items-center justify-center text-emerald-600 hover:bg-emerald-50 transition-colors" title="Save">
                                    <i class="pi pi-check text-xs font-bold"></i>
                                </button>
                                <button @click="cancelEdit" class="w-8 h-8 rounded flex items-center justify-center text-gray-400 hover:bg-gray-100 transition-colors" title="Cancel">
                                    <i class="pi pi-times text-xs"></i>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Add New -->
                    <div v-if="isAdding" class="bg-teal-50/50 p-4 rounded-xl border border-teal-200 border-dashed space-y-4">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-teal-600 uppercase tracking-widest">Select Shift Code</label>
                            <div class="flex flex-wrap gap-2">
                                <button 
                                    v-for="char in 'ABCDEFGHIJKLMN'.split('')" 
                                    :key="char"
                                    @click="newForm.code = char"
                                    type="button"
                                    :class="newForm.code === char ? 'bg-teal-600 text-white shadow-md ring-2 ring-teal-500 ring-offset-1' : 'bg-white text-gray-400 hover:bg-teal-50 hover:text-teal-600'"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-black transition-all cursor-pointer border border-teal-100"
                                >
                                    {{ char }}
                                </button>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-teal-600 uppercase tracking-widest">Set Time Range</label>
                            <div class="flex items-center gap-2">
                                <input v-model="newForm.time" type="text" placeholder="e.g. 07:00 AM - 03:00 PM" class="flex-1 px-3 h-10 rounded-xl bg-white text-gray-800 text-sm font-bold border border-teal-200 outline-none focus:ring-2 focus:ring-teal-500 placeholder:text-gray-300">
                                <div class="flex items-center gap-1">
                                    <button @click="submitAdd" :disabled="!newForm.code || !newForm.time" class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center hover:bg-emerald-600 transition-colors disabled:opacity-50 cursor-pointer shadow-lg shadow-emerald-200">
                                        <i class="pi pi-check font-bold"></i>
                                    </button>
                                    <button @click="cancelAdd" class="w-10 h-10 rounded-xl bg-white text-gray-400 flex items-center justify-center hover:bg-gray-100 transition-colors border border-gray-200 cursor-pointer">
                                        <i class="pi pi-times"></i>
                                    </button>
                                </div>
                            </div>
                            <p class="text-[9px] text-teal-600/70 font-medium italic pl-1">Must follow format: 00:00 AM - 00:00 PM</p>
                        </div>
                    </div>
                    
                    <button v-if="!isAdding" @click="isAdding = true" class="w-full py-3 rounded-xl border-2 border-dashed border-gray-200 text-gray-400 hover:border-teal-300 hover:text-teal-600 hover:bg-teal-50/50 transition-all flex justify-center items-center gap-2 font-bold text-xs uppercase cursor-pointer">
                        <i class="pi pi-plus"></i>
                        Add New Shift Code
                    </button>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 bg-white flex justify-end gap-3 shrink-0">
                <button 
                    @click="closeModal"
                    class="px-5 py-2.5 text-gray-500 font-bold hover:bg-gray-100 rounded-xl transition-colors text-sm"
                >
                    Cancel
                </button>
                <button 
                    @click="saveToServer"
                    :disabled="isSaving || editIndex !== -1 || isAdding"
                    class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-black rounded-xl shadow-md shadow-teal-200 transition-all flex items-center gap-2 disabled:opacity-50 text-sm cursor-pointer"
                >
                    <i v-if="isSaving" class="pi pi-spin pi-spinner"></i>
                    <i v-else class="pi pi-save"></i>
                    <span>Save Changes</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, reactive } from 'vue';
import axios from 'axios';

const props = defineProps({
    modelValue: Boolean
});

const emit = defineEmits(['update:modelValue', 'shifts-updated']);

const shifts = ref([]);
const isLoading = ref(false);
const isSaving = ref(false);

const isAdding = ref(false);
const newForm = reactive({ code: '', time: '' });

const editIndex = ref(-1);
const editForm = reactive({ code: '', time: '' });

const loadShifts = async () => {
    isLoading.value = true;
    try {
        const res = await axios.get('/api/settings/working_hours');
        shifts.value = Array.isArray(res.data) ? [...res.data] : [];
    } catch (error) {
        console.error("Failed to load shifts", error);
        shifts.value = [
            {code: 'A', time: '07:00 AM - 03:00 PM'},
            {code: 'B', time: '07:00 PM - 04:00 AM'},
            {code: 'C', time: '06:00 AM - 02:00 PM'},
            {code: 'D', time: '02:00 PM - 10:00 PM'},
            {code: 'E', time: '08:00 AM - 04:00 PM'}
        ];
    } finally {
        isLoading.value = false;
    }
};

watch(() => props.modelValue, (isOpen) => {
    if (isOpen) {
        cancelAdd();
        cancelEdit();
        loadShifts();
    }
});

const startEdit = (index, shift) => {
    editIndex.value = index;
    editForm.code = shift.code;
    editForm.time = shift.time;
    cancelAdd();
};

const cancelEdit = () => {
    editIndex.value = -1;
    editForm.code = '';
    editForm.time = '';
};

const saveEdit = () => {
    if (!editForm.code || !editForm.time) return;
    shifts.value[editIndex.value] = { ...editForm, code: editForm.code.toUpperCase() };
    cancelEdit();
};

const cancelAdd = () => {
    isAdding.value = false;
    newForm.code = '';
    newForm.time = '';
};

const submitAdd = () => {
    if (!newForm.code || !newForm.time) return;
    shifts.value.push({ code: newForm.code.toUpperCase(), time: newForm.time });
    cancelAdd();
};

const removeShift = (index) => {
    if (confirm("Remove this shift code?")) {
        shifts.value.splice(index, 1);
    }
};

const saveToServer = async () => {
    isSaving.value = true;
    try {
        await axios.put('/api/settings/working_hours', { value: shifts.value });
        alert('Working hours configurations saved successfully.');
        emit('shifts-updated', shifts.value);
        closeModal();
    } catch (e) {
        console.error(e);
        alert('Failed to save settings.');
    } finally {
        isSaving.value = false;
    }
};

const closeModal = () => {
    emit('update:modelValue', false);
};
</script>
