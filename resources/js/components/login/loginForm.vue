<template>
    <div class="bg-white/90 backdrop-blur-md p-8 rounded-2xl shadow-xl w-full min-h-[400px] flex flex-col justify-center">
        <!-- Loading State -->
        <div v-if="isLoading" class="flex flex-col items-center justify-center space-y-4 animate-fade-in">
            <Loader />
            <p class="text-teal-600 font-medium mt-2">Signing in...</p>
        </div>

        <!-- Form Content -->
        <div v-else class="animate-fade-in">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center p-3 bg-teal-50 rounded-2xl mb-4 shadow-inner ring-1 ring-teal-100">
                    <img src="/logo.png" alt="HQ Logo" class="w-16 h-16 object-contain">
                </div>
                <h2 class="text-3xl font-bold text-gray-800">{{ title }}</h2>
                <p class="text-gray-500 mt-2 text-sm">Welcome back! Please enter your details.</p>
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
                    class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-3 rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5 active:translate-y-0 cursor-pointer flex justify-center items-center"
                >
                    <span>Sign In</span>
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
    import { ref } from 'vue';
    import { useAuthStore } from '../../stores/auth';
    import Loader from '../../components/Loader.vue';

    const title = ref('Login');
    const showPassword = ref(false);
    const isLoading = ref(false);
    
    const username = ref('');
    const password = ref('');
    const remember = ref(false);
    const authStore = useAuthStore();
    const error = ref('');

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
            error.value = 'Invalid credentials. Please try again.';
            isLoading.value = false;
        }
    };
</script>

