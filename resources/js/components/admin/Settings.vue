<template>
    <div v-if="user">
        <MainLayout :user="user">
            <div class="max-w-6xl mx-auto space-y-8 pb-20">
                <!-- Premium Header -->
                <div class="bg-white p-10 rounded-[2.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.04)] border border-slate-100 flex flex-col md:flex-row items-center justify-between gap-8 relative overflow-hidden group">
                    <div class="absolute -right-20 -top-20 w-64 h-64 bg-teal-500/5 rounded-full blur-3xl group-hover:bg-teal-500/10 transition-all duration-700"></div>
                    <div class="relative z-10 text-center md:text-left">
                        <div class="flex items-center justify-center md:justify-start gap-4 mb-4">
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center text-white shadow-xl shadow-teal-200">
                                <i class="pi pi-cog text-2xl"></i>
                            </div>
                            <h1 class="text-4xl font-black text-slate-800 tracking-tight">System Settings</h1>
                        </div>
                        <p class="text-slate-500 font-medium text-lg max-w-xl leading-relaxed">Global configuration for leave types, working hours, and request form layouts.</p>
                    </div>
                </div>

                <!-- Settings Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    
                    <!-- 1. Working Hours / Shifts -->
                    <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)] hover:shadow-[0_20px_40px_rgba(0,0,0,0.04)] transition-all group">
                        <div class="w-14 h-14 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-500 mb-6 group-hover:scale-110 transition-transform">
                            <i class="pi pi-clock text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-2">Working Hours</h3>
                        <p class="text-slate-500 text-sm mb-8 leading-relaxed">Define shift codes (A, B, C) and their corresponding time ranges for employee assignment.</p>
                        <button 
                            @click="showShiftsModal = true"
                            class="w-full py-4 bg-slate-900 hover:bg-slate-800 text-white rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-lg shadow-slate-200 active:scale-95 flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <i class="pi pi-sliders-h"></i>
                            Configure Shifts
                        </button>
                    </div>

                    <!-- 2. Leave Types Management -->
                    <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)] hover:shadow-[0_20px_40px_rgba(0,0,0,0.04)] transition-all group">
                        <div class="w-14 h-14 rounded-2xl bg-teal-50 flex items-center justify-center text-teal-600 mb-6 group-hover:scale-110 transition-transform">
                            <i class="pi pi-calendar-plus text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-2">Leave Types</h3>
                        <p class="text-slate-500 text-sm mb-8 leading-relaxed">Adjust available leave categories and global credit policies for the organization.</p>
                        <button 
                            class="w-full py-4 bg-teal-600 hover:bg-teal-700 text-white rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-lg shadow-teal-200 active:scale-95 flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <i class="pi pi-list"></i>
                            Manage Categories
                        </button>
                    </div>

                    <!-- 3. Form Layout Design -->
                    <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)] hover:shadow-[0_20px_40px_rgba(0,0,0,0.04)] transition-all group">
                        <div class="w-14 h-14 rounded-2xl bg-purple-50 flex items-center justify-center text-purple-600 mb-6 group-hover:scale-110 transition-transform">
                            <i class="pi pi-pencil-square text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-2">Form Design</h3>
                        <p class="text-slate-500 text-sm mb-8 leading-relaxed">Customize the leave request form by adding instruction cards or custom selection fields.</p>
                        <button 
                            @click="openFormDesigner"
                            class="w-full py-4 bg-purple-600 hover:bg-purple-700 text-white rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-lg shadow-purple-200 active:scale-95 flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <i class="pi pi-palette"></i>
                            Open Designer
                        </button>
                    </div>

                </div>

                <!-- Secondary Sections -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="bg-slate-900 rounded-[2rem] p-8 text-white relative overflow-hidden">
                        <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-teal-500/20 rounded-full blur-3xl"></div>
                        <h4 class="text-xs font-black text-teal-400 uppercase tracking-widest mb-2">Security Warning</h4>
                        <p class="text-lg font-bold mb-4">Any changes made here will reflect immediately across all employee portals.</p>
                        <p class="text-slate-400 text-sm italic">Ensure compliance with company policies before updating leave credits or shifts.</p>
                    </div>

                    <div class="bg-white rounded-[2rem] p-8 border border-slate-100 flex items-center justify-between">
                        <div>
                            <h4 class="text-xl font-bold text-slate-800">System Visibility</h4>
                            <p class="text-slate-500 text-sm mt-1">Manage who can see the leave filing button.</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-black text-emerald-600 uppercase tracking-widest bg-emerald-50 px-3 py-1 rounded-full">Active</span>
                        </div>
                    </div>
                </div>

                <!-- Modals -->
                <ManageShiftsModal v-model="showShiftsModal" />
                
                <!-- Virtual Leave Request Form for Design mode -->
                <LeaveRequestModal 
                    v-model="showFormDesignModal" 
                    :isDesignMode="true"
                    :isAdminMode="true"
                />
            </div>
        </MainLayout>
    </div>
</template>

<script setup>
    import { ref, onMounted } from 'vue';
    import { useAuthStore } from '../../stores/auth';
    import { storeToRefs } from 'pinia';
    import MainLayout from '../../layouts/MainLayout.vue';
    import ManageShiftsModal from '../common/ManageShiftsModal.vue';
    import LeaveRequestModal from '../common/LeaveRequestModal.vue';

    const authStore = useAuthStore();
    const { user } = storeToRefs(authStore);

    const showShiftsModal = ref(false);
    const showFormDesignModal = ref(false);

    const openFormDesigner = () => {
        showFormDesignModal.value = true;
    };

    onMounted(() => {
        authStore.fetchUser();
    });
</script>
