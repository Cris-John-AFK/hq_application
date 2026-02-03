import { defineStore } from 'pinia';
import axios from 'axios';

export const useLeaveStore = defineStore('leaves', {
    state: () => ({
        stats: {
            pending: 0,
            approved: 0,
            rejected: 0,
            cancelled: 0,
            on_leave_today: 0,
            recent: [],
            approved_this_month: 0,
            scheduled: 0,
            total_all_time: 0,
            total_employees: 0
        },
        lastFetched: null,
        loading: false,
        fetchingPromise: null
    }),

    actions: {
        async fetchStats(force = false) {
            if (this.fetchingPromise) return this.fetchingPromise;

            // Cache for 1 minute
            if (!force && this.lastFetched && (Date.now() - this.lastFetched < 60 * 1000)) {
                return this.stats;
            }

            this.loading = true;
            this.fetchingPromise = axios.get('/api/leave-stats').then(response => {
                this.stats = response.data;
                this.lastFetched = Date.now();
                return this.stats;
            }).catch(error => {
                console.error('Failed to fetch leave stats', error);
                throw error;
            }).finally(() => {
                this.loading = false;
                this.fetchingPromise = null;
            });

            return this.fetchingPromise;
        },

        invalidate() {
            this.lastFetched = null;
        }
    }
});
