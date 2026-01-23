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
            total_all_time: 0
        },
        lastFetched: null,
        loading: false
    }),

    actions: {
        async fetchStats(force = false) {
            if (this.loading) return;
            // Cache for 1 minute
            if (!force && this.lastFetched && (Date.now() - this.lastFetched < 60 * 1000)) {
                return;
            }

            this.loading = true;
            try {
                const response = await axios.get('/api/leave-stats');
                this.stats = response.data;
                this.lastFetched = Date.now();
            } catch (error) {
                console.error('Failed to fetch leave stats', error);
            } finally {
                this.loading = false;
            }
        },

        invalidate() {
            this.lastFetched = null;
        }
    }
});
