import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        isAuthenticated: false,
        fetchingPromise: null,
    }),
    actions: {
        async login(credentials) {
            // await axios.get('/sanctum/csrf-cookie'); // Not needed if using meta tag strategy
            await axios.post('/login', credentials);
            await this.fetchUser();
        },
        async fetchUser() {
            if (this.fetchingPromise) return this.fetchingPromise;

            this.fetchingPromise = axios.get('/api/user').then(response => {
                this.user = response.data;
                this.isAuthenticated = true;
                return this.user;
            }).catch(error => {
                this.user = null;
                this.isAuthenticated = false;
                throw error;
            }).finally(() => {
                this.fetchingPromise = null;
            });

            return this.fetchingPromise;
        },
        async logout() {
            await axios.post('/logout');
            this.user = null;
            this.isAuthenticated = false;
            window.location.href = '/login'; // Redirect to login page
        },
        async updateUser(userData) {
            try {
                const response = await axios.put('/api/user', userData);
                this.user = response.data.user; // Update local user state
                return response.data;
            } catch (error) {
                throw error;
            }
        },
    },
});
