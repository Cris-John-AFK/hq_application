<template>
    <div class="min-h-screen bg-[#f0f2f5] py-10 px-4 flex justify-center w-full">
        
        <div class="flex flex-col md:flex-row gap-8 max-w-5xl w-full">
            
            <!-- Left Side: Main Content (The Form or History) -->
            <div class="flex-1 w-full min-w-0 max-w-3xl">
                
                <div class="mb-10 flex flex-col md:flex-row items-center gap-6 px-12 text-center md:text-left relative overflow-hidden py-6 rounded-2xl bg-white/40 border border-white/60 backdrop-blur-sm shadow-sm ring-1 ring-black/5">
                    <div class="relative group">
                        <!-- Advanced ambient glow -->
                        <div class="absolute inset-0 bg-teal-400/10 blur-xl rounded-full scale-110 pointer-events-none transition-opacity duration-700 group-hover:opacity-40"></div>
                        <img src="/logo_v2.svg" alt="Logo" class="relative w-20 h-auto object-contain transition-transform group-hover:scale-105 duration-1000 ease-in-out filter drop-shadow-[0_8px_15px_rgba(0,0,0,0.05)]">
                    </div>
                    <div class="relative pt-1">
                        <div class="inline-block px-2.5 py-0.5 bg-teal-500/10 text-teal-600 rounded-lg text-[9px] font-black uppercase tracking-[0.2em] mb-2 border border-teal-500/20">
                            Service Portal
                        </div>
                        <h1 class="text-4xl font-black text-slate-800 tracking-[-0.05em] leading-tight mb-1 uppercase">Employee Portal</h1>
                        <p class="text-[10px] font-bold text-slate-400 tracking-[0.4em] opacity-80 pl-1 uppercase">HQ Unified Leave System</p>
                    </div>
                </div>

                <!-- Recent Activity Glance -->
                <transition name="fade">
                    <div v-if="lastLeave && activeTab === 'form'" class="mb-8 p-1 rounded-[22px] bg-gradient-to-r from-teal-500/20 via-purple-500/20 to-teal-500/20 animate-gradient-x">
                        <div class="bg-white/80 backdrop-blur-xl rounded-[20px] p-4 flex items-center justify-between border border-white shadow-xl ring-1 ring-black/5">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center relative overflow-hidden group shadow-inner" 
                                    :class="lastLeave.status === 'Approved' ? 'bg-emerald-50 text-emerald-600' : 
                                            lastLeave.status === 'Pending' ? 'bg-amber-50 text-amber-600' : 'bg-rose-50 text-rose-600'">
                                    <div class="absolute inset-0 opacity-10 group-hover:scale-150 transition-transform duration-700"
                                        :class="lastLeave.status === 'Approved' ? 'bg-emerald-400' : 'bg-amber-400'"></div>
                                    <i class="pi text-xl relative z-10" :class="lastLeave.status === 'Approved' ? 'pi-check-circle' : 'pi-clock'"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Latest Record</span>
                                        <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ formatDate(lastLeave.date_filed) }}</span>
                                    </div>
                                    <h4 class="text-sm font-black text-slate-800 uppercase tracking-tight">{{ lastLeave.leave_type }} <span class="text-slate-400 font-bold mx-1">—</span> {{ formatDate(lastLeave.from_date) }}</h4>
                                    <p v-if="lastLeave.hr_remarks || lastLeave.dept_head_remarks" class="text-[9px] font-bold text-rose-400 mt-1 max-w-[300px] truncate italic" :title="lastLeave.hr_remarks || lastLeave.dept_head_remarks">
                                        Note: "{{ lastLeave.hr_remarks || lastLeave.dept_head_remarks }}"
                                    </p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span :class="['px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest shadow-sm border transition-all',
                                    lastLeave.status === 'Approved' ? 'bg-emerald-500 text-white border-emerald-400' :
                                    lastLeave.status === 'Pending' ? 'bg-amber-400 text-white border-amber-300' :
                                    'bg-rose-500 text-white border-rose-400'
                                ]">
                                    {{ lastLeave.status }}
                                </span>
                                <button v-if="lastLeave.status === 'Pending'" @click="startEdit(lastLeave)" class="text-[9px] font-black text-purple-600 hover:text-purple-800 uppercase tracking-tighter flex items-center gap-1 mt-1 cursor-pointer">
                                    <i class="pi pi-pencil text-[8px]"></i> Edit Entry
                                </button>
                            </div>
                        </div>
                    </div>
                </transition>

                <transition name="fade" mode="out-in">
                    <div v-if="activeTab === 'form'" key="form">
                        <!-- Reusable Leave Request Component in "Portal Mode" -->
                        
                        <!-- Full Screen Success Overlay Wizard -->
                        <transition name="success-modal">
                            <div v-if="successMsg" class="fixed inset-0 z-[200] flex items-center justify-center bg-white/90 backdrop-blur-md overflow-hidden" @click="successMsg = ''">
                                <!-- Confetti Elements -->
                                <div class="absolute inset-0 pointer-events-none flex justify-center items-center">
                                    <div class="confetti-piece" v-for="n in 60" :key="n" :style="`--x: ${(Math.random() - 0.5) * 200}vw; --y: ${(Math.random() - 0.5) * 200}vh; --r: ${Math.random() * 1080}deg; --c: ${['#10b981', '#3b82f6', '#f59e0b', '#ec4899', '#8b5cf6'][Math.floor(Math.random() * 5)]}; --d: ${Math.random() * 0.1}s;`"></div>
                                </div>
                                
                                <div class="relative z-10 flex flex-col items-center bg-white p-12 rounded-[40px] shadow-2xl border border-emerald-100 max-w-lg w-full text-center mx-4" @click.stop>
                                    
                                    <!-- Animated Checkmark -->
                                    <div class="success-animation mb-8 relative">
                                        <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                                            <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                                            <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                                        </svg>
                                        <div class="pulse-ring"></div>
                                    </div>
                                    
                                    <h2 class="text-3xl lg:text-4xl font-black text-emerald-600 tracking-tight uppercase mb-3">Submitted Successfully</h2>
                                    <p class="text-gray-500 font-bold tracking-widest uppercase text-sm mb-10">{{ successMsg }}</p>
                                    
                                    <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden mb-6 relative">
                                        <div class="absolute inset-0 bg-emerald-500 origin-left animate-progress-shrink"></div>
                                    </div>

                                    <button @click="handleRefresh" class="w-full py-4 bg-emerald-500 hover:bg-emerald-600 text-white rounded-[24px] font-black uppercase tracking-widest shadow-xl shadow-emerald-500/30 transition-all hover:scale-105 active:scale-95 text-xl cursor-pointer">
                                        Continue
                                    </button>
                                </div>
                            </div>
                        </transition>

                        <LeaveRequestModal 
                            :modelValue="true" 
                            :isAdminMode="false" 
                            :isPortalMode="true"
                            :employeeData="employee"
                            :initialData="editData"
                            :isEdit="!!editData"
                            @submit="handleFormSubmit"
                            @update="handleFormUpdate"
                        />
                    </div>

                    <div v-else-if="activeTab === 'history'" key="history" class="w-full bg-white shadow-2xl rounded-xl border border-gray-200 p-6 md:p-8 animate-in fade-in zoom-in-95 duration-200">
                        <h3 class="text-xl font-black text-gray-800 mb-6 pb-4 border-b border-gray-100 flex items-center gap-3">
                            <i class="pi pi-history text-[#673ab7] text-2xl"></i> My Leave History
                        </h3>

                        <div class="space-y-4">
                            <div v-if="leaveHistory.length === 0" class="text-center py-10 bg-gray-50 rounded-lg border border-gray-200">
                                <p class="text-sm font-bold text-gray-500">No past leave forms found.</p>
                            </div>

                            <div v-for="leave in leaveHistory" :key="leave.id" 
                                @click="leave.status === 'Pending' ? startEdit(leave) : null"
                                :class="[
                                    'p-5 border border-gray-200 rounded-xl transition-all group bg-white flex flex-col md:flex-row justify-between gap-4 relative',
                                    leave.status === 'Pending' ? 'cursor-pointer hover:border-[#673ab7] hover:shadow-md hover:bg-purple-50/20 shadow-sm' : 'cursor-default'
                                ]">
                                
                                <button type="button" @click.stop.prevent="promptArchive(leave)" class="absolute top-3 right-3 text-rose-300 hover:text-white bg-rose-50 hover:bg-rose-500 p-2.5 rounded-lg opacity-100 transition-all z-20 cursor-pointer shadow-sm hover:shadow-md" title="Remove / Archive">
                                    <i class="pi pi-trash text-sm"></i>
                                </button>

                                <div class="pr-12">
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest"
                                            :class="leave.status === 'Approved' ? 'bg-emerald-100 text-emerald-700' :
                                                    leave.status === 'Pending' ? 'bg-amber-100 text-amber-700' :
                                                    leave.status === 'Rejected' ? 'bg-rose-100 text-rose-700' :
                                                    'bg-gray-100 text-gray-700'">
                                            {{ leave.status }}
                                        </span>
                                        <span class="text-xs font-bold text-gray-400">Filed {{ formatDate(leave.date_filed) }}</span>
                                    </div>
                                    <h4 class="text-base font-bold text-gray-800">{{ leave.leave_type }}</h4>
                                    <p class="text-sm text-gray-600 truncate max-w-sm">{{ leave.reason }}</p>

                                    <!-- Reviewer Remarks / Justification -->
                                    <div v-if="leave.hr_remarks || leave.admin_remarks || leave.dept_head_remarks" class="mt-4 overflow-hidden rounded-xl border border-dashed border-gray-200 bg-gray-50/50">
                                        <div class="px-3 py-1.5 bg-gray-50/80 border-b border-gray-100 flex items-center justify-between">
                                            <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-1.5">
                                                <i class="pi pi-comments"></i> 
                                                Reviewer Justification
                                            </span>
                                            <span v-if="leave.hr_approver || leave.dept_head" class="text-[8px] font-bold text-gray-300 uppercase tracking-tighter">
                                                Processed by {{ leave.hr_approver?.name || leave.dept_head?.name }}
                                            </span>
                                        </div>
                                        <div class="px-3 py-2.5">
                                            <p class="text-xs font-bold text-gray-600 italic leading-relaxed">
                                                "{{ leave.hr_remarks || leave.admin_remarks || leave.dept_head_remarks }}"
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <p v-if="leave.status === 'Pending'" class="mt-3 text-[10px] font-black uppercase tracking-widest text-[#673ab7] flex items-center gap-1.5 opacity-60 group-hover:opacity-100 transition-opacity">
                                        <i class="pi pi-pencil"></i> Click anywhere to edit this form
                                    </p>
                                </div>
                                <div class="text-left md:text-right overflow-hidden mt-1 md:mt-0 pt-3 md:pt-0 border-t border-gray-50 md:border-0 md:pr-12">
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Duration</p>
                                    <p class="text-sm font-bold text-gray-800">{{ formatDate(leave.from_date) }}</p>
                                    <p class="text-xs font-bold text-[#673ab7] mt-1">{{ leave.days_taken }} Day(s)</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </transition>
            </div>
            
            <!-- Right Side: Sidebar Navigation Tabs -->
            <div :class="['w-full md:w-56 shrink-0 sticky top-10 self-start flex flex-col gap-3 group/sidebar transition-all duration-500 ease-out', isScrolling ? 'translate-z-10 -translate-y-2 scale-[1.02]' : 'translate-y-0 scale-100']">
                <!-- Cyberpunk Neon Neural Aura & Trails -->
                <div class="absolute inset-0 -z-20 pointer-events-none transition-opacity duration-500" :class="isScrolling ? 'opacity-100' : 'opacity-0'">
                    <!-- If scrolling DOWN, trails shoot UP -->
                    <template v-if="scrollDirection === 'down'">
                        <div class="neon-trail neon-trail-cyan-up"></div>
                        <div class="neon-trail neon-trail-magenta-up delay-75"></div>
                    </template>
                    <!-- If scrolling UP, trails shoot DOWN -->
                    <template v-if="scrollDirection === 'up'">
                        <div class="neon-trail neon-trail-cyan-down"></div>
                        <div class="neon-trail neon-trail-magenta-down delay-100"></div>
                    </template>
                    
                    <!-- Smoother Neural Aura -->
                    <div class="absolute -inset-4 rounded-[40px] bg-[radial-gradient(circle_at_center,rgba(0,243,255,0.08)_0%,rgba(255,0,255,0.03)_50%,transparent_80%)] blur-2xl animate-neural-pulse"></div>
                </div>

                <!-- Neural Smoke Dispersion (On Stop) -->
                <div v-if="showSmoke" class="absolute inset-x-0 bottom-0 -z-10 pointer-events-none">
                    <div class="smoke-particle smoke-1"></div>
                    <div class="smoke-particle smoke-2"></div>
                    <div class="smoke-particle smoke-3"></div>
                </div>
                
                <!-- Profile Box -->
                <div v-if="employee" class="bg-white/40 backdrop-blur-md rounded-xl shadow-sm p-4 flex flex-col items-center text-center border border-white/60 mb-2">
                    <div class="w-16 h-16 rounded-full bg-purple-50 flex items-center justify-center text-purple-600 shadow-inner mb-3">
                        <i class="pi pi-user text-2xl"></i>
                    </div>
                    <h2 class="text-sm font-black text-gray-800 uppercase tracking-tight">{{ employee.first_name }} {{ employee.last_name }}</h2>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">{{ employee.position || 'Employee' }}</p>
                    
                    <div class="mt-3 px-3 py-2 bg-gray-50 rounded-lg border border-gray-100 w-full mb-1">
                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest block">ID Number</span>
                        <span class="text-xs font-black text-purple-600 block">{{ employee.employee_id }}</span>
                    </div>

                    <!-- Shift Info -->
                    <div class="px-3 py-2 bg-teal-50/50 rounded-lg border border-teal-100 w-full mb-3 text-left">
                        <span class="text-[9px] text-teal-600/70 font-bold uppercase tracking-widest block mb-0.5"><i class="pi pi-clock text-[8px] mr-0.5"></i> My Shift</span>
                        <span class="text-xs font-black text-teal-700 block whitespace-nowrap overflow-hidden text-ellipsis">{{ employee.working_hours || 'Standard (8:30 AM)' }}</span>
                    </div>
                    
                    <!-- Leave Balances Mini Grid -->
                    <div class="w-full grid grid-cols-3 gap-1.5 mb-3">
                        <div v-for="type in leaveTypesList" :key="type.key" 
                            :class="[type.bg, type.border, 'rounded-lg p-2 border text-center relative overflow-hidden group']">
                            <span :class="[type.color.replace('text-', 'text-').replace('-600', '-400'), 'block text-[8px] uppercase tracking-widest font-bold']">{{ type.label }}</span>
                            <span :class="[type.color.replace('text-', 'text-').replace('-600', '-700'), 'block text-lg font-black']">{{ employee[type.key] || 0 }}</span>
                        </div>
                    </div>
                </div>
 
                <button @click="openNewForm" :class="['w-full py-4 px-6 text-sm font-black uppercase tracking-widest rounded-xl transition-all shadow-sm flex items-center justify-center gap-2 cursor-pointer', activeTab === 'form' && !editData ? 'bg-[#673ab7] hover:bg-[#5e35b1] text-white active:scale-95' : 'bg-white/40 backdrop-blur-md text-[#673ab7] hover:bg-purple-50/50 border border-white/60']">
                    NEW LEAVE FORM
                </button>
                <button @click="activeTab = 'history'" :class="['w-full py-4 px-6 text-sm font-black uppercase tracking-widest rounded-xl transition-all shadow-sm flex items-center justify-center gap-2 cursor-pointer', activeTab === 'history' ? 'bg-[#673ab7] hover:bg-[#5e35b1] text-white active:scale-95' : 'bg-white/40 backdrop-blur-md text-[#673ab7] hover:bg-purple-50/50 border border-white/60']">
                    FORM HISTORY
                </button>
                
                <div class="h-4"></div> <!-- visual separator -->
                
                <button @click="logout" class="w-full py-3 px-6 text-sm font-black uppercase tracking-widest rounded-xl transition-all shadow-sm flex items-center justify-center gap-2 cursor-pointer bg-rose-500/10 backdrop-blur-md text-rose-500 hover:bg-rose-500 hover:text-white active:scale-95 border border-rose-500/30 mb-2">
                    LOGOUT
                </button>
                
                <div class="text-center">
                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-1">Session Auto-Logout In:</p>
                    <p class="text-sm font-black text-rose-400 tabular-nums">{{ formatTimeLeft(timeLeft) }}</p>
                </div>
            </div>
            
        </div>
        
        <!-- Archive Confirm Modal -->
        <transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-100 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showArchiveModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/40 backdrop-blur-sm" @click.stop="showArchiveModal = false">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xs overflow-hidden" @click.stop>
                    <div class="p-6 text-center">
                        <div class="w-16 h-16 rounded-full bg-rose-50 flex items-center justify-center text-rose-500 mx-auto mb-4">
                            <i class="pi pi-exclamation-circle text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-black text-gray-800 mb-2">Confirm Action</h3>
                        <p class="text-xs font-bold text-gray-400 mb-6 px-2">
                            {{ archiveTarget?.status === 'Pending' ? 'Are you sure you want to delete this pending request? This will formally cancel it.' : 'Are you sure you want to remove this requested leave from your history?' }}
                        </p>
                        
                        <div class="flex gap-3">
                            <button type="button" @click="showArchiveModal = false" class="flex-1 px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-black text-xs uppercase tracking-widest cursor-pointer transition-colors shadow-sm">
                                Cancel
                            </button>
                            <button type="button" @click.prevent="confirmArchive" :disabled="archiveLoading" class="flex-1 px-4 py-3 bg-rose-500 hover:bg-rose-600 text-white rounded-xl font-black text-xs uppercase tracking-widest cursor-pointer shadow-md shadow-rose-200 transition-all flex justify-center items-center gap-2">
                                <i v-if="archiveLoading" class="pi pi-spinner pi-spin"></i>
                                <span>Proceed</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import axios from 'axios';
import LeaveRequestModal from '@/components/common/LeaveRequestModal.vue';

const activeTab = ref('form');
const employee = ref(null);
const leaveHistory = ref([]);
const birthdateCred = ref('');
const successMsg = ref('');
const editData = ref(null);
const leaveTypesList = ref([]);

const colorThemes = [
    { color: 'text-purple-600', bg: 'bg-purple-50', border: 'border-purple-100' },
    { color: 'text-pink-600', bg: 'bg-pink-50', border: 'border-pink-100' },
    { color: 'text-blue-600', bg: 'bg-blue-50', border: 'border-blue-100' },
    { color: 'text-emerald-600', bg: 'bg-emerald-50', border: 'border-emerald-100' },
    { color: 'text-gray-600', bg: 'bg-gray-50', border: 'border-gray-200' },
    { color: 'text-rose-600', bg: 'bg-rose-50', border: 'border-rose-100' },
    { color: 'text-amber-600', bg: 'bg-amber-50', border: 'border-amber-100' },
    { color: 'text-indigo-600', bg: 'bg-indigo-50', border: 'border-indigo-100' },
    { color: 'text-orange-600', bg: 'bg-orange-50', border: 'border-orange-100' },
    { color: 'text-teal-600', bg: 'bg-teal-50', border: 'border-teal-100' },
];

const loadLeaveSettings = async () => {
    try {
        const res = await axios.get('/api/settings/leave_types');
        const typesList = res.data;
        leaveTypesList.value = typesList.map((label, idx) => {
            let abbr = '';
            const typeLower = label.toLowerCase();

            // Explicit PH standard abbreviations to avoid duplicates (VA vs VAWC, SL vs SIL)
            if (typeLower.includes('sick')) abbr = 'SL';
            else if (typeLower.includes('vacation')) abbr = 'VL';
            else if (typeLower.includes('sil')) abbr = 'SI';
            else if (typeLower.includes('vawc')) abbr = 'VW';
            else if (typeLower.includes('solo parent')) abbr = 'SP';
            else if (typeLower.includes('maternity')) abbr = 'ML';
            else if (typeLower.includes('paternity')) abbr = 'PL';
            else if (typeLower.includes('emergency')) abbr = 'EL';
            else if (typeLower.includes('bereavement')) abbr = 'BL';
            else if (typeLower.includes('magna carta')) abbr = 'MC';
            else {
                const match = label.match(/\(([^)]+)\)/);
                if (match) {
                    abbr = match[1];
                } else {
                    const words = label.replace(/leave/i, '').trim().split(' ');
                    if (words.length > 1) {
                        abbr = (words[0][0] + words[1][0]).toUpperCase();
                    } else {
                        abbr = words[0].substring(0, 2).toUpperCase();
                    }
                }
            }
            
            // Generate valid DB column
            let col = '';
            if (typeLower.includes('paternity')) col = 'paternity_leave';
            else if (typeLower.includes('solo')) col = 'solo_parent_leave';
            else if (typeLower.includes('bereavement')) col = 'bereavement_leave';
            else if (typeLower.includes('vawc') || typeLower.includes('vaws')) col = 'vawc_leave';
            else if (typeLower.includes('maternity')) col = 'maternity_leave';
            else if (typeLower.includes('magna') || typeLower.includes('carta')) col = 'magna_carta_leave';
            else if (typeLower.includes('emergency')) col = 'emergency_leave';
            else if (typeLower.includes('sick') || typeLower === 'sl') col = 'sick_leave';
            else if (typeLower.includes('vacation') || typeLower === 'vl') col = 'vacation_leave';
            else {
                col = typeLower.replace(/ *\([^)]*\) */g, "").trim().replace(/ /g, '_');
                if (!col.endsWith('_leave')) col += '_leave';
            }
            
            const theme = colorThemes[idx % colorThemes.length];
            return { key: col, label: abbr, fullLabel: label, color: theme.color, bg: theme.bg, border: theme.border };
        });
    } catch (error) {
        console.error('Failed to load leave types settings', error);
    }
};

const showArchiveModal = ref(false);
const archiveTarget = ref(null);
const archiveLoading = ref(false);

const SESSION_TIMEOUT = 5 * 60; // 5 minutes in seconds
const timeLeft = ref(SESSION_TIMEOUT);
let countdownInterval = null;

const resetTimer = () => {
    timeLeft.value = SESSION_TIMEOUT;
};

const lastLeave = computed(() => {
    if (!leaveHistory.value || leaveHistory.value.length === 0) return null;
    return leaveHistory.value[0];
});

// Scroll Spark Logic
const isScrolling = ref(false);
const showSmoke = ref(false);
const scrollDirection = ref('down'); // 'up' or 'down'
let scrollStopTimeout = null;
let lastScrollTop = 0;

const handleScrollEffect = () => {
    isScrolling.value = true;
    showSmoke.value = false;
    
    const st = window.pageYOffset || document.documentElement.scrollTop;
    if (st > lastScrollTop) {
        scrollDirection.value = 'down';
    } else {
        scrollDirection.value = 'up';
    }
    lastScrollTop = st <= 0 ? 0 : st;

    if (scrollStopTimeout) clearTimeout(scrollStopTimeout);
    scrollStopTimeout = setTimeout(() => {
        isScrolling.value = false;
        showSmoke.value = true;
        // Turn off smoke after it dissipates
        setTimeout(() => { showSmoke.value = false; }, 1000);
    }, 150);
};

const fetchPortalData = async () => {
    const activeEmp = sessionStorage.getItem('hq_employee_portal_id') || localStorage.getItem('hq_employee_portal_id');
    const bdCheck = sessionStorage.getItem('hq_employee_portal_bd') || localStorage.getItem('hq_employee_portal_bd');
    
    if (!activeEmp || !bdCheck) {
        logout();
        return;
    }
    
    birthdateCred.value = bdCheck;

    try {
        const { data } = await axios.post('/api/employee-portal/login', {
            employee_id: activeEmp,
            birthdate: bdCheck
        });

        employee.value = data.employee;
        leaveHistory.value = data.leave_history || [];
        return true;
    } catch (e) {
        logout();
        return false;
    }
};

onMounted(async () => {
    await loadLeaveSettings();
    const success = await fetchPortalData();
    if (success) {
        startCountdown();
        document.addEventListener('mousemove', resetTimer);
        document.addEventListener('keydown', resetTimer);
        document.addEventListener('click', resetTimer);
        document.addEventListener('scroll', resetTimer);
        window.addEventListener('scroll', handleScrollEffect, { passive: true });
    }
});

onUnmounted(() => {
    stopCountdown();
    document.removeEventListener('mousemove', resetTimer);
    document.removeEventListener('keydown', resetTimer);
    document.removeEventListener('click', resetTimer);
    document.removeEventListener('scroll', resetTimer);
    window.removeEventListener('scroll', handleScrollEffect);
});

const startCountdown = () => {
    stopCountdown();
    countdownInterval = setInterval(() => {
        timeLeft.value--;
        if (timeLeft.value <= 0) {
            stopCountdown();
            logout();
        }
    }, 1000);
};

const stopCountdown = () => {
    if (countdownInterval) {
        clearInterval(countdownInterval);
        countdownInterval = null;
    }
};

const formatTimeLeft = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
};

const logout = () => {
    sessionStorage.removeItem('hq_employee_portal_id');
    sessionStorage.removeItem('hq_employee_portal_bd');
    localStorage.removeItem('hq_employee_portal_id');
    localStorage.removeItem('hq_employee_portal_bd');
    window.location.href = '/login';
};

const handleRefresh = () => {
    window.location.reload();
};

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return isNaN(d) ? dateStr : d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const openNewForm = () => {
    editData.value = null;
    activeTab.value = 'form';
    successMsg.value = '';
};

const startEdit = (leave) => {
    editData.value = leave;
    activeTab.value = 'form';
    successMsg.value = '';
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const handleFormSubmit = async (payload, resolve, reject) => {
    successMsg.value = '';
    
    // Supplement payload with auth info using FormData for file support
    // We merge these first to avoid duplicated keys in FormData
    const finalData = {
        ...payload,
        employee_id: employee.value?.employee_id,
        birthdate: birthdateCred.value,
    };

    const formData = new FormData();
    Object.keys(finalData).forEach(key => {
        const val = finalData[key];
        if (val === null || val === undefined) return;

        if (key === 'additional_details') {
            formData.append(key, JSON.stringify(val));
        } else {
            formData.append(key, val);
        }
    });

    try {
        const { data } = await axios.post('/api/employee-portal/submit-leave', formData);
        successMsg.value = 'Your leave request has been sent to the HR successfully.';
        
        // Full background refresh to ensure credits and history are 100% in sync
        await fetchPortalData();
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
        
        setTimeout(() => { 
            if (successMsg.value === 'Your leave request has been sent to the HR successfully.') {
                successMsg.value = ''; 
            }
        }, 10000);
        
        resolve(); // Tells the modal to clear its loading state and reset
    } catch (error) {
        reject(error.response?.data?.message || 'Failed to submit form.');
    }
};

const handleFormUpdate = async (payload, resolve, reject) => {
    successMsg.value = '';
    
    // Supplement payload with auth info using FormData for file support
    // We must use POST with _method=PUT for multipart updates in Laravel
    const finalData = {
        ...payload,
        employee_id: employee.value?.employee_id,
        birthdate: birthdateCred.value,
        _method: 'PUT'
    };

    const formData = new FormData();
    Object.keys(finalData).forEach(key => {
        const val = finalData[key];
        if (val === null || val === undefined) return;

        if (key === 'additional_details') {
            formData.append(key, JSON.stringify(val));
        } else {
            formData.append(key, val);
        }
    });

    try {
        const { data } = await axios.post(`/api/employee-portal/update-leave/${editData.value.id}`, formData);
        successMsg.value = 'Your leave request has been successfully updated.';
        
        // Full background refresh
        await fetchPortalData();
        
        editData.value = null;
        window.scrollTo({ top: 0, behavior: 'smooth' });
        
        setTimeout(() => { 
            if (successMsg.value === 'Your leave request has been successfully updated.') {
                successMsg.value = ''; 
            }
        }, 10000);
        
        resolve(); // Tells the modal to clear its loading state and reset
    } catch (error) {
        reject(error.response?.data?.message || 'Failed to update form.');
    }
};

const promptArchive = (leave) => {
    archiveTarget.value = leave;
    showArchiveModal.value = true;
};

const confirmArchive = async () => {
    if (!archiveTarget.value) return;
    archiveLoading.value = true;
    try {
        await axios.put(`/api/employee-portal/archive-leave/${archiveTarget.value.id}`, {
            employee_id: employee.value?.employee_id,
            birthdate: birthdateCred.value
        });
        await fetchPortalData();
        showArchiveModal.value = false;
        archiveTarget.value = null;
    } catch (e) {
        alert(e.response?.data?.message || 'Failed to archive request.');
    } finally {
        archiveLoading.value = false;
    }
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
    transform: translateY(5px);
}

/* Success Modal Transition */
.success-modal-enter-active, .success-modal-leave-active {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.success-modal-enter-from, .success-modal-leave-to {
    opacity: 0;
    transform: scale(0.95);
}

/* Checkmark Animation */
.success-animation {
    width: 120px;
    height: 120px;
    margin: 0 auto;
}
.checkmark {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    display: block;
    stroke-width: 4;
    stroke: #10b981;
    stroke-miterlimit: 10;
    box-shadow: inset 0px 0px 0px #10b981;
    animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
}
.checkmark-circle {
    stroke-dasharray: 166;
    stroke-dashoffset: 166;
    stroke-width: 4;
    stroke-miterlimit: 10;
    stroke: #10b981;
    fill: none;
    animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
}
.checkmark-check {
    transform-origin: 50% 50%;
    stroke-dasharray: 48;
    stroke-dashoffset: 48;
    stroke: #fff;
    animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
}
@keyframes stroke {
    100% { stroke-dashoffset: 0; }
}
@keyframes scale {
    0%, 100% { transform: none; }
    50% { transform: scale3d(1.1, 1.1, 1); }
}
@keyframes fill {
    100% { box-shadow: inset 0px 0px 0px 60px #10b981; }
}

/* Pulse Ring */
.pulse-ring {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 120px;
    height: 120px;
    background: transparent;
    border: 4px solid #10b981;
    border-radius: 50%;
    z-index: -1;
    animation: pulse 1s ease-out 0.4s forwards;
}
@keyframes pulse {
    0% { transform: translate(-50%, -50%) scale(1); opacity: 1; }
    100% { transform: translate(-50%, -50%) scale(1.8); opacity: 0; }
}

/* Confetti CSS Explosion */
.confetti-piece {
    position: absolute;
    width: 10px;
    height: 20px;
    background-color: var(--c);
    top: 50%;
    left: 50%;
    opacity: 0;
    border-radius: 2px;
    animation: explode 1.2s cubic-bezier(0.25, 1, 0.5, 1) forwards;
    animation-delay: var(--d);
}
@keyframes explode {
    0% { transform: translate(0, 0) rotate(0deg) scale(0); opacity: 1; }
    30% { opacity: 1; }
    100% { transform: translate(var(--x), var(--y)) rotate(var(--r)) scale(1.2); opacity: 0; }
}

.animate-progress-shrink {
    animation: shrink 10s linear forwards;
}

@keyframes shrink {
    from { transform: scaleX(1); }
    to { transform: scaleX(0); }
}

/* Cyberpunk Neon Neural Trails & Aura */
.neon-trail {
    position: absolute;
    width: 1px;
    height: 100px;
    border-radius: 100px;
    filter: blur(1px);
    opacity: 0.6;
}

.neon-trail-cyan-up { right: 8px; top: -60px; background: linear-gradient(to top, transparent, #00f3ff, #fff); animation: neural-up 0.6s infinite ease-out; }
.neon-trail-magenta-up { left: 8px; top: -80px; background: linear-gradient(to top, transparent, #ff00ff, #fff); animation: neural-up 0.5s infinite ease-out; }
.neon-trail-cyan-down { left: 12px; bottom: -60px; background: linear-gradient(to bottom, transparent, #00f3ff, #fff); animation: neural-down 0.6s infinite ease-out; }
.neon-trail-magenta-down { right: 12px; bottom: -80px; background: linear-gradient(to bottom, transparent, #ff00ff, #fff); animation: neural-down 0.5s infinite ease-out; }

@keyframes neural-up {
    0% { transform: translateY(0) scaleY(1); opacity: 0; }
    20% { opacity: 0.8; }
    100% { transform: translateY(-120px) scaleY(1.5); opacity: 0; }
}
@keyframes neural-down {
    0% { transform: translateY(0) scaleY(1); opacity: 0; }
    20% { opacity: 0.8; }
    100% { transform: translateY(120px) scaleY(1.5); opacity: 0; }
}

@keyframes neural-pulse {
    from { transform: scale(0.95); opacity: 0.4; }
    to { transform: scale(1.05); opacity: 0.7; }
}

.animate-neural-pulse {
    animation: neural-pulse 1.5s infinite alternate ease-in-out;
}

@keyframes scanline {
    0% { transform: translateY(0); }
    100% { transform: translateY(100%); }
}

.animate-scanline {
    animation: scanline 12s linear infinite;
}

.animate-gradient-x {
    background-size: 200% 200%;
    animation: gradient-x 15s ease infinite;
}

@keyframes gradient-x {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

.delay-75 { animation-delay: 150ms; }
.delay-100 { animation-delay: 200ms; }

/* Neural Smoke exhaust */
.smoke-particle {
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 40px;
    height: 40px;
    background: radial-gradient(circle, rgba(0, 243, 255, 0.2) 0%, transparent 70%);
    border-radius: 100%;
    filter: blur(15px);
    opacity: 0;
    animation: smoke-dissipate 1s ease-out forwards;
}

.smoke-1 { animation-delay: 0s; width: 60px; height: 60px; left: 30%; }
.smoke-2 { animation-delay: 0.1s; width: 80px; height: 80px; left: 50%; }
.smoke-3 { animation-delay: 0.2s; width: 50px; height: 50px; left: 70%; }

@keyframes smoke-dissipate {
    0% { transform: translate(-50%, 0) scale(0.5); opacity: 0.8; }
    100% { transform: translate(-50%, -40px) scale(2); opacity: 0; }
}

.shadow-2xl {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}
</style>
