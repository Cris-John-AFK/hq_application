<template>
    <div class="bg-white/90 backdrop-blur-md p-8 rounded-2xl shadow-xl w-full min-h-[400px] flex flex-col justify-center">
        <!-- Loading State -->
        <div v-if="isLoading" class="flex flex-col items-center justify-center space-y-4 animate-fade-in">
            <Loader />
            <p class="text-teal-600 font-medium mt-2">Signing in...</p>
        </div>

        <!-- Form Content -->
        <div v-else class="animate-fade-in">
            <div class="text-center mb-8 relative pt-6">
                <!-- Advanced Decorative Glows -->
                <div class="absolute -top-12 left-1/2 -translate-x-1/2 w-48 h-48 bg-teal-400/20 rounded-full blur-[100px] pointer-events-none"></div>
                
                <!-- Logo as a Design Unit -->
                <div class="relative inline-flex items-center justify-center mb-6">
                    <div class="absolute inset-0 bg-white/50 blur-xl rounded-full scale-110 pointer-events-none"></div>
                    <img src="/logo_v2.svg" alt="HQ Logo" class="relative w-24 h-auto object-contain transition-all duration-700 hover:scale-110 filter drop-shadow-[0_8px_8px_rgba(0,0,0,0.05)]">
                </div>
                
                <div class="relative space-y-1">
                    <h2 class="text-4xl font-black text-slate-800 tracking-[-0.05em] leading-tight">{{ title }}</h2>
                    <div class="flex justify-center items-center gap-1.5 font-bold">
                        <div class="w-2 h-1 bg-teal-500/20 rounded-full"></div>
                        <div class="w-8 h-1 bg-teal-500 rounded-full"></div>
                        <div class="w-2 h-1 bg-teal-500/20 rounded-full"></div>
                    </div>
                </div>
                <p class="text-[10px] font-black text-slate-400 mt-4 uppercase tracking-[0.3em] opacity-80">Welcome back</p>
            </div>
    
            <form class="space-y-6" @submit.prevent="submitForm">
                
                <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ error }}</span>
                </div>
    
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input 
                        v-model="username"
                        type="email"
                        placeholder="Enter your email address" 
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none transition-all duration-200"
                        required
                    >
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <input 
                            v-model="password"
                            :type="showPassword ? 'text' : 'password'"
                            placeholder="••••••••" 
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none transition-all duration-200"
                            required
                        >
                        <i 
                            @click="showPassword = !showPassword"
                            :class="['pi', showPassword ? 'pi-eye' : 'pi-eye-slash', 'absolute right-4 top-1/2 -translate-y-1/2 cursor-pointer text-gray-500 hover:text-teal-600 transition-colors']"
                        ></i>
                    </div>
                </div>
    
                <button 
                    type="submit" 
                    class="w-full bg-gradient-to-r from-teal-600 to-teal-500 hover:from-teal-500 hover:to-teal-400 text-white font-black py-4 rounded-xl shadow-lg shadow-teal-500/20 hover:shadow-teal-400/30 transition-all duration-300 transform hover:-translate-y-1 active:translate-y-0 cursor-pointer flex justify-center items-center uppercase tracking-widest text-sm"
                >
                    <span>Authenticate Account</span>
                </button>
            </form>

            <div class="mt-10 pt-8 border-t border-slate-100 flex flex-col items-center gap-4">
                <p class="text-[10px] font-black text-slate-300 uppercase tracking-[0.3em]">External Access</p>
                <button 
                    @click="showEmployeeModal = true"
                    class="w-full bg-slate-50 border border-slate-200 hover:bg-white hover:border-teal-400 hover:text-teal-600 text-slate-600 font-bold py-4 rounded-xl shadow-sm hover:shadow-md transition-all duration-300 cursor-pointer flex justify-center items-center gap-3 group"
                >
                    <i class="pi pi-shield text-teal-500 group-hover:scale-110 transition-transform"></i>
                    <span class="text-sm">Employee Self-Service Portal</span>
                </button>
            </div>
        </div>

        <!-- Employee Portal Login Modal -->
        <transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-100 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showEmployeeModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/40 backdrop-blur-sm">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden relative" @click.stop>
                    <button @click="showEmployeeModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 cursor-pointer">
                        <i class="pi pi-times text-lg"></i>
                    </button>
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center text-teal-600">
                                <i class="pi pi-briefcase text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-black text-gray-800 text-lg leading-tight">Employee Portal</h3>
                                <p class="text-[10px] uppercase font-bold text-teal-600 tracking-widest">Paperless Leave Filing</p>
                            </div>
                        </div>

                        <form @submit.prevent="submitEmployeeLogin" class="space-y-4">
                            <div v-if="empError" class="bg-rose-50 border border-rose-100 text-rose-600 px-3 py-2 rounded-lg text-xs font-bold text-center">
                                {{ empError }}
                            </div>

                            <div>
                                <label class="block text-[10px] font-black uppercase text-gray-500 tracking-widest mb-1.5">ID Number</label>
                                <input v-model="empId" type="text" placeholder="e.g. 001" required class="w-full px-3 py-2 text-sm font-bold bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none transition-all">
                            </div>
                            
                            <div>
                                <label class="block text-[10px] font-black uppercase text-gray-500 tracking-widest mb-1.5">Birthdate (Password)</label>
                                <input :value="empDob" @input="handleDobInput" type="password" maxlength="8" placeholder="MMDDYYYY (e.g. 06272003)" required class="w-full px-3 py-2 text-sm font-bold bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none transition-all tracking-widest text-center">
                            </div>

                            <button type="submit" :disabled="empLoading" class="w-full mt-2 bg-gray-800 hover:bg-gray-900 text-white font-bold py-2.5 rounded-lg shadow disabled:opacity-50 transition-colors flex items-center justify-center gap-2 cursor-pointer">
                                <i class="pi pi-sign-in text-sm" v-if="!empLoading"></i>
                                <i class="pi pi-spinner animate-spin text-sm" v-else></i>
                                <span>{{ empLoading ? 'Verifying...' : 'Access Portal' }}</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </transition>

    </div>
</template>

<script setup>
    import { ref } from 'vue';
    import { useRouter } from 'vue-router';
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
            empError.value = 'Birthdate password must be exactly 8 digits (MMDDYYYY).';
            return;
        }

        empLoading.value = true;
        
        try {
            await axios.post('/api/employee-portal/login', {
                employee_id: empId.value,
                birthdate: empDob.value
            });

            // Store minimally required session tokens loosely for the portal layout to verify
            localStorage.setItem('hq_employee_portal_id', empId.value);
            localStorage.setItem('hq_employee_portal_bd', empDob.value);
            
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

