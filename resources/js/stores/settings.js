import { defineStore } from 'pinia';
import { ref, watch } from 'vue';
import axios from 'axios';

const STORAGE_KEY = 'hq_admin_settings';

function loadSettings() {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        return raw ? JSON.parse(raw) : {};
    } catch {
        return {};
    }
}

export const useSettingsStore = defineStore('settings', () => {
    const saved = loadSettings();

    // Experimental / placeholder nav items
    const showAttendance = ref(saved.showAttendance ?? false);
    const showReports = ref(saved.showReports ?? false);

    // Leave Form Options
    const requestTypes = ref(['Leave', 'Halfday', 'Undertime', 'Official Business']);
    const leaveTypes = ref(['SIL', 'Solo Parent', 'Maternity', 'VAWS', 'Paternity', 'Magna Carta', 'Emergency']);
    const attendanceCategories = ref([
        { code: 'UA', label: 'Unauthorized Absence' },
        { code: 'WMC', label: 'With Medical Certificate' },
        { code: 'WD', label: 'With Documents' },
        { code: 'UH', label: 'Unauthorized Halfday' }
    ]);
    const formSections = ref([]);

    const loading = ref(false);

    async function fetchSettings() {
        loading.value = true;
        try {
            const response = await axios.get('/api/settings');
            const data = response.data;
            if (data.leave_request_types) requestTypes.value = data.leave_request_types;
            if (data.leave_types) leaveTypes.value = data.leave_types;
            if (data.attendance_categories) attendanceCategories.value = data.attendance_categories;
            if (data.leave_form_sections) formSections.value = data.leave_form_sections;
        } catch (e) {
            console.error('Failed to fetch settings', e);
        } finally {
            loading.value = false;
        }
    }

    async function updateSetting(key, value) {
        try {
            await axios.put(`/api/settings/${key}`, { value });
            if (key === 'leave_request_types') requestTypes.value = value;
            if (key === 'leave_types') leaveTypes.value = value;
            if (key === 'attendance_categories') attendanceCategories.value = value;
            if (key === 'leave_form_sections') formSections.value = value;
            return true;
        } catch (e) {
            console.error('Failed to update setting', e);
            return false;
        }
    }

    function persist() {
        localStorage.setItem(STORAGE_KEY, JSON.stringify({
            showAttendance: showAttendance.value,
            showReports: showReports.value,
        }));
    }

    watch([showAttendance, showReports], persist);

    return {
        showAttendance,
        showReports,
        requestTypes,
        leaveTypes,
        attendanceCategories,
        formSections,
        loading,
        fetchSettings,
        updateSetting
    };
});
