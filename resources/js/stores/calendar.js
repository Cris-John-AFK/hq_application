import { defineStore } from 'pinia';
import axios from 'axios';

export const useCalendarStore = defineStore('calendar', {
    state: () => ({
        events: [],
        lastFetched: null,
        loading: false,
        fetchingPromise: null,
    }),

    actions: {
        async fetchEvents(force = false) {
            if (this.fetchingPromise) return this.fetchingPromise;

            // Cache for 5 minutes
            if (!force && this.lastFetched && (Date.now() - this.lastFetched < 5 * 60 * 1000)) {
                return this.events;
            }

            this.loading = true;
            this.fetchingPromise = axios.get('/api/calendar-events').then(response => {
                this.events = response.data;
                this.lastFetched = Date.now();
                return this.events;
            }).catch(error => {
                console.error('Failed to fetch calendar events', error);
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
