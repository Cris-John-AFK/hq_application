<template>
    <div class="w-full h-screen flex flex-col items-center justify-center p-3 overflow-hidden">
        <!-- GLOBAL PAGE HEADER: Kiosk Spaceship Console Logo (RE-ENLARGED) -->
        <div class="mb-6 animate-fade-in flex flex-col items-center relative w-full max-w-md">
            <div class="bg-white/95 backdrop-blur-3xl px-8 py-3 rounded-[30px] shadow-xl border-t-2 border-white flex items-center justify-center relative">
                <img src="/logo_v2.svg" alt="HQ Logo" class="relative w-28 h-auto object-contain">
                <div class="absolute top-3 left-3 w-5 h-5 border-t-2 border-l-2 border-teal-500/20 rounded-tl-lg"></div>
                <div class="absolute bottom-3 right-3 w-5 h-5 border-b-2 border-r-2 border-teal-500/20 rounded-br-lg"></div>
            </div>
            <div class="mt-2 opacity-50">
                <span class="text-[8px] font-black text-white uppercase tracking-[0.5em]">Unified Terminal Console</span>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="bg-white/90 backdrop-blur-md p-10 rounded-[30px] shadow-2xl flex flex-col items-center justify-center space-y-4 animate-fade-in border border-white/20">
            <Loader />
            <p class="text-teal-600 font-black uppercase tracking-widest text-xs mt-4">Security Handshake...</p>
        </div>

        <!-- Form Content (Side-by-Side Kiosk Layout) -->
        <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6 w-full animate-fade-in items-stretch">
            
            <!-- LEFT: Admin Login Context -->
            <div class="bg-white/90 backdrop-blur-md p-10 rounded-[40px] shadow-2xl border border-white/20 flex flex-col justify-center">
                <div class="text-center mb-8 relative">
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight leading-tight uppercase">Login Console</h2>
                    <div class="flex justify-center items-center mt-2">
                        <div class="w-16 h-1.5 bg-teal-500 rounded-full"></div>
                    </div>
                </div>
        
                <form class="space-y-6" @submit.prevent="submitForm">
                    <div v-if="error" class="bg-rose-50 border border-rose-100 text-rose-600 px-5 py-3 rounded-2xl font-bold text-center text-xs">
                        {{ error }}
                    </div>
        
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Identity (Email)</label>
                        <input 
                            v-model="username"
                            type="email"
                            autocomplete="username"
                            placeholder="Authorized Email..." 
                            class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-2 border-transparent focus:border-teal-500 focus:bg-white outline-none transition-all text-base font-bold"
                            required
                        >
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Access Key</label>
                        <div class="relative">
                            <input 
                                v-model="password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="current-password"
                                placeholder="••••••••" 
                                class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-2 border-transparent focus:border-teal-500 focus:bg-white outline-none transition-all text-base font-bold"
                                required
                            >
                            <i 
                                @click="showPassword = !showPassword"
                                :class="['pi', showPassword ? 'pi-eye' : 'pi-eye-slash', 'absolute right-5 top-1/2 -translate-y-1/2 cursor-pointer text-slate-300 hover:text-teal-600 text-xl transition-colors']"
                            ></i>
                        </div>
                    </div>
        
                    <button 
                        type="submit" 
                        class="w-full bg-slate-800 hover:bg-black text-white font-black py-5 rounded-[24px] shadow-xl transition-all active:scale-[0.98] cursor-pointer flex justify-center items-center uppercase tracking-widest text-xs"
                    >
                        <span>Authorize Access</span>
                    </button>
                </form>
            </div>

            <!-- RIGHT: Employee Self-Service Portal Entry -->
            <div class="bg-slate-900 p-10 rounded-[40px] shadow-2xl border border-white/10 flex flex-col items-center justify-center relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-80 h-80 bg-teal-500/10 rounded-full blur-[100px] pointer-events-none"></div>

                <div class="relative z-10 text-center space-y-8 w-full max-w-sm">
                    <div class="space-y-3">
                        <h3 class="text-4xl font-black text-white leading-tight tracking-tight uppercase">Leave & Portal</h3>
                        <p class="text-base font-bold text-slate-500 uppercase tracking-widest">Self-Service Terminal</p>
                    </div>

                    <div class="relative">
                        <button 
                            @click="showEmployeeModal = true"
                            class="w-full bg-teal-500 hover:bg-teal-400 text-white font-black py-8 rounded-[32px] shadow-[0_25px_50px_-12px_rgba(20,184,166,0.5)] transition-all duration-500 transform hover:scale-[1.02] active:scale-95 cursor-pointer flex flex-col items-center justify-center gap-2 border-b-8 border-teal-700 active:border-b-0 active:translate-y-2 overflow-hidden"
                        >
                            <span class="text-[10px] font-black tracking-[0.4em] text-teal-100 uppercase animate-pulse">Touch to Start</span>
                            <span class="text-3xl lg:text-4xl uppercase tracking-tighter">Access Portal</span>
                        </button>
                    </div>

                    <div class="opacity-20 flex flex-col items-center gap-2">
                         <div class="w-1.5 h-1.5 bg-teal-500 rounded-full animate-ping"></div>
                        <p class="text-[8px] font-black text-slate-500 uppercase tracking-[0.4em]">Official Kiosk Hub</p>
                    </div>
                </div>
            </div>

        </div>

        <!-- Employee Portal Login Modal -->
        <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 translate-y-8 scale-95" enter-to-class="opacity-100 translate-y-0 scale-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 translate-y-0 scale-100" leave-to-class="opacity-0 translate-y-8 scale-95">
            <div v-if="showEmployeeModal" class="fixed inset-0 z-50 flex items-center justify-center p-8 bg-slate-900/80 backdrop-blur-xl">
                <div class="bg-white rounded-[40px] shadow-[0_0_100px_rgba(0,0,0,0.4)] w-full max-w-xl overflow-hidden relative border border-white/20" @click.stop>
                    <button @click="showEmployeeModal = false" class="absolute top-8 right-8 w-14 h-14 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 hover:text-rose-500 hover:bg-rose-50 transition-all cursor-pointer">
                        <i class="pi pi-times text-2xl"></i>
                    </button>
                    
                    <div class="p-16">
                        <div class="flex items-center gap-6 mb-12">
                            <div class="w-20 h-20 rounded-[24px] bg-teal-500 flex items-center justify-center text-white shadow-xl shadow-teal-500/20">
                                <i class="pi pi-user-check text-4xl"></i>
                            </div>
                            <div>
                                <h3 class="font-black text-slate-800 text-4xl tracking-tight leading-none">Authentication</h3>
                                <p class="text-xs uppercase font-black text-teal-600 tracking-[0.3em] mt-3">Employee Portal Registry</p>
                            </div>
                        </div>

                        <form @submit.prevent="submitEmployeeLogin" class="space-y-8">
                            <div v-if="empError" class="bg-rose-50 border border-rose-100 text-rose-600 px-6 py-4 rounded-2xl font-bold text-center">
                                {{ empError }}
                            </div>

                            <div class="space-y-3">
                                <label class="block text-sm font-black uppercase text-slate-400 tracking-[0.3em] ml-1">ID Number (Only numbers)</label>
                                <input v-model="empId" type="text" autocomplete="username" placeholder="ID Number..." required class="w-full px-8 py-6 text-2xl font-black bg-slate-50 border-2 border-transparent focus:border-teal-500 focus:bg-white rounded-3xl outline-none transition-all placeholder:text-slate-200">
                            </div>
                            
                            <div class="space-y-3">
                                <label class="block text-sm font-black uppercase text-slate-400 tracking-[0.3em] ml-1">Access PIN (Birthdate: DD-MM-YYYY example: 01212003)</label>
                                <input :value="empDob" @input="handleDobInput" type="password" autocomplete="current-password" placeholder="DDMMYYYY" required class="w-full px-8 py-6 text-3xl font-black bg-slate-50 border-2 border-transparent focus:border-teal-500 focus:bg-white rounded-3xl outline-none transition-all tracking-[0.4em] text-center placeholder:text-slate-200 placeholder:tracking-normal">
                            </div>

                            <button type="submit" :disabled="empLoading" class="w-full mt-6 bg-teal-600 hover:bg-teal-700 text-white font-black py-8 rounded-[32px] shadow-2xl flex items-center justify-center gap-4 cursor-pointer text-xl uppercase tracking-widest disabled:opacity-50">
                                <i class="pi pi-unlock text-2xl" v-if="!empLoading"></i>
                                <i class="pi pi-spinner animate-spin text-2xl" v-else></i>
                                <span>{{ empLoading ? 'Loading...' : 'Unlock Portal' }}</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup>
    import { useRouter } from 'vue-router';
    import { onMounted, ref } from 'vue';
    import axios from 'axios';
    import { useAuthStore } from '../../stores/auth';
    import Loader from '../../components/Loader.vue';

    const router = useRouter();
    const title = ref('Login');
    const showPassword = ref(false);
    const isLoading = ref(false);
    
    // Admin login refs
    const username = ref('');
    const password = ref('');
    const remember = ref(false);
    const authStore = useAuthStore();
    const error = ref('');

    // Employee portal refs
    const showEmployeeModal = ref(false);
    const empId = ref('');
    const empDob = ref('');
    const empLoading = ref(false);
    const empError = ref('');

    const handleDobInput = (e) => {
        let val = e.target.value.replace(/\D/g, '');
        if (val.length > 8) val = val.substring(0, 8);
        empDob.value = val;
        e.target.value = val;
    };

    onMounted(() => {
        // PROACTIVE PROTECTION: Clear any existing portal session when landing on login page
        // This prevents the "Back and Forward" history trick from re-logging in the last user
        sessionStorage.removeItem('hq_employee_portal_id');
        sessionStorage.removeItem('hq_employee_portal_bd');
        localStorage.removeItem('hq_employee_portal_id'); // Just in case of legacy
        localStorage.removeItem('hq_employee_portal_bd');
    });

    const submitForm = async () => {
        error.value = '';
        isLoading.value = true;
        try {
            await authStore.login({
                email: username.value, 
                password: password.value,
                remember: remember.value
            });
            // Redirect on success
            window.location.href = '/dashboard';
        } catch (err) {
            console.error(err);
            if (err.response?.status === 429) {
                error.value = 'Too many failed attempts. Please wait 1 minute before trying again.';
            } else {
                error.value = 'Invalid credentials. Please try again.';
            }
            isLoading.value = false;
        }
    };

    const submitEmployeeLogin = async () => {
        empError.value = '';

        if (empDob.value.length !== 8) {
            empError.value = 'Birthdate PIN must be exactly 8 digits.';
            return;
        }

        empLoading.value = true;
        
        try {
            await axios.post('/api/employee-portal/login', {
                employee_id: empId.value,
                birthdate: empDob.value
            });

            // Store minimally required session tokens loosely for the portal layout to verify
            // Use sessionStorage for better protection (clears on tab close, not shared)
            sessionStorage.setItem('hq_employee_portal_id', empId.value);
            sessionStorage.setItem('hq_employee_portal_bd', empDob.value);
            
            showEmployeeModal.value = false;
            window.location.href = '/portal';
        } catch (err) {
            if (err.response?.status === 429) {
                empError.value = 'Too many attempts. Please wait 1 minute before trying again.';
            } else if (err.response?.status === 422) {
                empError.value = 'Please enter a valid birthdate (MMDDYYYY).';
            } else {
                empError.value = err.response?.data?.message || 'Verification failed. Please check your details.';
            }
        } finally {
            empLoading.value = false;
        }
    };
</script>

