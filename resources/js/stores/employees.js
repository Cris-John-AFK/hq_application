import { defineStore } from 'pinia';
import axios from 'axios';

export const useEmployeeStore = defineStore('employees', {
    state: () => ({
        employees: [],
        departments: [],
        departmentStats: [],
        loading: false,
        lastFetchedEmployees: null,
        lastFetchedStats: null,
        fetchingEmployeesPromise: null,
        fetchingStatsPromise: null
    }),

    actions: {
        async fetchEmployees(force = false) {
            // If already fetching, return the existing promise to prevent duplicate requests
            if (this.fetchingEmployeesPromise) return this.fetchingEmployeesPromise;

            // Cache for 2 minutes
            if (!force && this.lastFetchedEmployees && (Date.now() - this.lastFetchedEmployees < 120 * 1000)) {
                return this.employees;
            }

            this.loading = true;
            this.fetchingEmployeesPromise = axios.get('/api/users').then(response => {
                this.employees = response.data.map(user => ({
                    id: user.id,
                    name: user.name,
                    email: user.email,
                    avatar: user.avatar_url,
                    initials: this.getInitials(user.name),
                    empId: user.id_number || 'N/A',
                    department: user.department?.name || user.department || 'N/A',
                    role: user.role,
                    status: user.status || 'Available',
                    position: user.position || 'Employee',
                    leave_credits: user.leave_credits || 0
                }));
                this.lastFetchedEmployees = Date.now();
                return this.employees;
            }).catch(error => {
                console.error('Failed to fetch employees:', error);
                throw error;
            }).finally(() => {
                this.loading = false;
                this.fetchingEmployeesPromise = null;
            });

            return this.fetchingEmployeesPromise;
        },

        async fetchDepartmentStats(force = false) {
            if (this.fetchingStatsPromise) return this.fetchingStatsPromise;

            if (!force && this.lastFetchedStats && (Date.now() - this.lastFetchedStats < 120 * 1000)) {
                return this.departmentStats;
            }

            this.fetchingStatsPromise = axios.get('/api/departments/stats').then(response => {
                this.departmentStats = response.data;
                this.lastFetchedStats = Date.now();
                return this.departmentStats;
            }).catch(error => {
                console.error('Failed to fetch department stats:', error);
                throw error;
            }).finally(() => {
                this.fetchingStatsPromise = null;
            });

            return this.fetchingStatsPromise;
        },

        async fetchDepartments() {
            if (this.departments.length > 0) return this.departments;
            try {
                const response = await axios.get('/api/departments');
                this.departments = response.data.map(d => d.name);
                return this.departments;
            } catch (error) {
                console.error('Failed to fetch departments:', error);
                return [];
            }
        },

        getInitials(name) {
            if (!name) return '??';
            return name
                .split(' ')
                .map(word => word[0])
                .join('')
                .toUpperCase()
                .substring(0, 2);
        }
    }
});
