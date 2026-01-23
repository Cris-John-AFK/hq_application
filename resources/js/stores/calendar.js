import { defineStore } from 'pinia';
import axios from 'axios';

export const useCalendarStore = defineStore('calendar', {
    state: () => ({
        events: [],
        lastFetched: null,
        loading: false
    }),

    actions: {
        async fetchEvents(force = false) {
            if (this.loading) return;
            // Cache for 5 minutes
            if (!force && this.lastFetched && (Date.now() - this.lastFetched < 5 * 60 * 1000)) {
                return;
            }

            this.loading = true;
            try {
                const response = await axios.get('/api/calendar-events');
                this.events = response.data;
                this.lastFetched = Date.now();
            } catch (error) {
                console.error('Failed to fetch calendar events', error);
            } finally {
                this.loading = false;
            }
        },

        invalidate() {
            this.lastFetched = null;
        }
    }
});
