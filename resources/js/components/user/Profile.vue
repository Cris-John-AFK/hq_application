<template>
    <div v-if="user">
        <MainLayout :user="user">
            <div class="space-y-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">My Profile</h1>
                        <p class="text-sm text-gray-500 mt-1">View and manage your personal information</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                    <div class="flex items-center gap-6 mb-8">
                        <div class="relative group">
                            <!-- Avatar Display -->
                            <div v-if="user.avatar_url" class="w-24 h-24 rounded-full overflow-hidden shadow-lg border-2 border-white">
                                <img :src="user.avatar_url" alt="Profile Photo" class="w-full h-full object-cover">
                            </div>
                            <div v-else class="w-24 h-24 rounded-full bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center text-3xl font-bold text-white shadow-lg border-2 border-white">
                                {{ user.name?.charAt(0) || 'U' }}
                            </div>
                            
                            <!-- Loading Overlay -->
                            <div v-if="isUploading" class="absolute inset-0 bg-black bg-opacity-50 rounded-full flex items-center justify-center">
                                <i class="pi pi-spin pi-spinner text-white text-2xl"></i>
                            </div>
                        </div>

                        <div>
                            <h2 class="text-xl font-bold text-gray-800">{{ user.name }}</h2>
                            <p class="text-gray-600">{{ user.role === 'admin' ? 'Administrator' : 'Employee' }}</p>
                            <p class="text-sm text-gray-500">{{ user.email }}</p>
                        </div>
                        <div class="flex items-center justify-end mb-4 mt-4 ml-auto">
                            <input 
                                type="file" 
                                ref="fileInput" 
                                class="hidden" 
                                accept="image/*" 
                                @change="handleFileChange"
                            >
                            <button 
                                @click="triggerUpload" 
                                :disabled="isUploading"
                                class="cursor-pointer bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2"
                            >
                                <i class="pi pi-camera"></i>
                                {{ isUploading ? 'Uploading...' : 'Upload Photo' }}
                            </button>
                        </div>
                        <div>
                            
                            <button
                                @click="editProfile"
                                class="cursor-pointer bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center"
                            >
                                <i class="pi pi-user-edit mr-2"></i>
                                Edit profile
                            </button>
                        </div>
                    </div>


                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="text-sm font-medium text-gray-600">Email</label>
                            <p class="text-gray-800 mt-1">{{ user.email }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Role</label>
                            <p class="text-gray-800 mt-1 capitalize">{{ user.role }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Department</label>
                            <p class="text-gray-800 mt-1">Engineering</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Employee ID</label>
                            <p class="text-lg font-mono font-medium text-gray-800">{{ user.id_number || 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </MainLayout>
    </div>
    <div v-else class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="text-center">
            <i class="pi pi-spin pi-spinner text-4xl mb-4 text-teal-600"></i>
            <p class="text-gray-500">Loading...</p>
        </div>
    </div>
    <!-- Edit Profile Modal -->
    <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Edit Profile</h2>
            
            <form @submit.prevent="handleUpdateProfile">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input 
                            v-model="editForm.name" 
                            type="text" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none transition-all"
                            required
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input 
                            v-model="editForm.email" 
                            type="email" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none transition-all"
                            required
                        >
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button 
                        type="button" 
                        @click="closeEditModal"
                        class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        :disabled="isUpdating"
                        class="px-4 py-2 bg-teal-500 hover:bg-teal-600 text-white rounded-lg transition-colors flex items-center gap-2"
                    >
                        <i v-if="isUpdating" class="pi pi-spin pi-spinner"></i>
                        {{ isUpdating ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref, reactive } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { storeToRefs } from 'pinia';
import MainLayout from '../../layouts/MainLayout.vue';
import axios from 'axios';

const authStore = useAuthStore();
const { user } = storeToRefs(authStore);
const fileInput = ref(null);
const isUploading = ref(false);

// Edit Profile Logic
const showEditModal = ref(false);
const isUpdating = ref(false);
const editForm = reactive({
    name: '',
    email: ''
});

const editProfile = () => {
    editForm.name = user.value.name;
    editForm.email = user.value.email;
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
};

const handleUpdateProfile = async () => {
    isUpdating.value = true;
    try {
        await authStore.updateUser(editForm);
        showEditModal.value = false;
        // Optional: Show success notification
    } catch (error) {
        console.error('Failed to update profile:', error);
        alert(error.response?.data?.message || 'Failed to update profile.');
    } finally {
        isUpdating.value = false;
    }
};

const editPassword = () => {
    // Implement password change logic or navigation
    alert("Password change feature coming soon!");
};

onMounted(() => {
    authStore.fetchUser();
});

const triggerUpload = () => {
    fileInput.value.click();
};

const handleFileChange = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    // Validate file type and size (optional but good practice)
    if (!file.type.match('image.*')) {
        alert('Please upload an image file.');
        return;
    }
    if (file.size > 2 * 1024 * 1024) { // 2MB
        alert('File size exceeds 2MB limit.');
        return;
    }

    const formData = new FormData();
    formData.append('avatar', file);

    isUploading.value = true;
    try {
        const response = await axios.post('/api/user/avatar', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        
        // Update user store with new avatar URL
        if (response.data.avatar_url) {
            // Force update user data or manually update the avatar field
            await authStore.fetchUser();
        }
        
    } catch (error) {
        console.error('Error uploading avatar:', error);
        alert('Failed to upload photo. Please try again.');
    } finally {
        isUploading.value = false;
        // Reset file input
        if (fileInput.value) fileInput.value.value = ''; 
    }
};
</script>
