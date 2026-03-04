<template>
    <div v-if="modelValue || isPortalMode" :class="isPortalMode ? 'w-full h-full' : 'fixed inset-0 z-[100] flex items-center justify-center p-4 animate-fade-in'" @click.self="!isPortalMode && closeModal()">
        <!-- Backdrop -->
        <div v-if="!isPortalMode" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
        
        <!-- Modal Container -->
        <div :class="['relative z-10 w-full flex flex-col rounded-xl overflow-hidden shadow-2xl bg-[#f0f2f5]', isPortalMode ? 'min-h-full' : 'wrapper max-w-2xl max-h-[90vh]']" @click.stop>
            <!-- Purple Top Bar & Admin Tools -->
            <div class="h-2.5 bg-[#673ab7] w-full shrink-0 relative">
                <button v-if="isAdminMode" 
                    @click="isDesignMode = !isDesignMode" 
                    :class="['absolute top-4 right-4 z-[110] px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-widest shadow-2xl border-4 transition-all flex items-center gap-2',
                        isDesignMode ? 'bg-emerald-500 text-white border-white scale-105' : 'bg-white text-[#673ab7] border-[#f0f2f5] hover:bg-gray-50'
                    ]"
                >
                    <i :class="['pi', isDesignMode ? 'pi-check' : 'pi-pencil']"></i>
                    {{ isDesignMode ? 'Finish Layout' : 'Edit Form Layout' }}
                </button>
            </div>

            <!-- Floating Admin Toolbar (Google Forms Style) -->
            <transition name="slide-down">
                <div v-if="isDesignMode" class="fixed right-12 top-1/2 -translate-y-1/2 flex flex-col gap-2 p-2 bg-white rounded-xl shadow-2xl border border-gray-200 z-[120]">
                    <button @click="addCustomSection" class="w-10 h-10 flex items-center justify-center rounded-lg hover:bg-teal-50 text-teal-600 transition-all" title="Add Card">
                        <i class="pi pi-plus-circle text-xl"></i>
                    </button>
                    <div class="h-px bg-gray-100 mx-2"></div>
                    <button @click="isDesignMode = false" class="w-10 h-10 flex items-center justify-center rounded-lg hover:bg-emerald-50 text-emerald-600 transition-all" title="Save Layout">
                        <i class="pi pi-check text-xl"></i>
                    </button>
                </div>
            </transition>
            
            <!-- Scrollable Content -->
            <div class="overflow-y-auto p-6 space-y-4">
                <!-- Design Mode Welcome -->
                <div v-if="isDesignMode" class="bg-blue-600 p-6 rounded-xl border border-blue-400 shadow-xl overflow-hidden relative text-white animate-in slide-in-from-top-4 duration-300">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center backdrop-blur-md">
                            <i class="pi pi-palette text-2xl"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-black uppercase tracking-widest">Layout Designer Active</h4>
                            <p class="text-[10px] opacity-80 font-bold uppercase">Click any card to edit its title, instructions, or visibility. Use the <i class="pi pi-plus-circle"></i> on the right to add new Radio Buttons or Text fields.</p>
                        </div>
                    </div>
                </div>
                <!-- Header Section -->
                <div v-if="getSection('header').visible || isDesignMode" 
                    :class="['bg-white p-6 rounded-lg border-2 shadow-sm relative transition-all', 
                        isDesignMode ? 'hover:border-blue-400 cursor-pointer' : 'border-gray-300',
                        isEditing('header') ? 'border-blue-600 ring-8 ring-blue-500/5 z-20' : ''
                    ]"
                    @click="isDesignMode && !isEditing('header') ? startEditingSection(getSection('header')) : null"
                >
                    <div v-if="isEditing('header')" class="absolute top-0 left-0 w-full h-1.5 bg-blue-600 rounded-t"></div>
                    
                    <template v-if="isEditing('header')">
                        <div class="space-y-4 animate-in fade-in zoom-in-95 duration-200">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-black text-blue-600 uppercase tracking-widest">Editing Card Properties</span>
                                <button @click.stop="editingSectionId = null" class="text-gray-400 hover:text-gray-600"><i class="pi pi-times"></i></button>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Header Title</label>
                                    <input v-model="sectionForm.title" class="w-full text-xl font-bold border-b-2 border-slate-100 focus:border-blue-500 outline-none py-1">
                                </div>
                                <div>
                                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Instructional Text</label>
                                    <input v-model="sectionForm.description" class="w-full text-sm border-b border-slate-100 focus:border-blue-500 outline-none py-1 text-gray-500">
                                </div>
                                <div class="flex items-center justify-between pt-6 border-t border-slate-100 mt-4">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" v-model="sectionForm.visible" class="form-checkbox h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                        <span class="text-[10px] font-black text-gray-500 uppercase">Visible to Employees</span>
                                    </label>
                                    <button @click.stop="saveSectionChanges" class="px-8 py-2.5 bg-blue-600 text-white rounded-lg text-xs font-black uppercase tracking-widest hover:bg-blue-700 shadow-md active:scale-95 transition-all">Apply Layout</button>
                                </div>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h2 class="text-2xl font-bold text-gray-800">{{ getSection('header').title }}</h2>
                                    <div v-if="isDesignMode" class="w-6 h-6 rounded-full bg-blue-500 text-white flex items-center justify-center animate-pulse"><i class="pi pi-pencil text-[10px]"></i></div>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">{{ getSection('header').description }}</p>
                            </div>
                            <div class="text-right">
                                <label class="block text-xs font-bold text-gray-500 uppercase">Date Filed</label>
                                <input type="date" v-model="form.dateFiled" class="text-sm border-b border-gray-300 focus:border-purple-600 outline-none py-1 text-gray-800 font-medium text-right">
                            </div>
                        </div>
                    </template>
                    
                    <hr class="my-4 border-gray-200"/>

                    <!-- Admin Search Row -->
                    <div v-if="isAdminMode" class="mb-6 p-4 bg-purple-50 rounded-xl border border-purple-100">
                        <label class="block text-[10px] font-black text-purple-700 uppercase tracking-widest mb-2">Admin Action: File Leave for Employee</label>
                        <div class="flex gap-2">
                            <div class="relative flex-1 bg-white rounded-lg">
                                <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-purple-400 z-10"></i>
                                
                                <!-- Ghost Text Background -->
                                <div v-if="ghostText" class="absolute left-0 top-0 w-full h-full pl-10 pr-4 py-2.5 flex items-center pointer-events-none">
                                    <span class="font-bold text-gray-400 whitespace-pre uppercase">
                                        {{ ghostText }}
                                        <span class="ml-2 px-1 rounded bg-gray-100 text-[9px] text-gray-500 font-black border border-gray-200">TAB</span>
                                    </span>
                                </div>

                                <input 
                                    v-model="adminSearchId" 
                                    type="text" 
                                    placeholder="Search by Name or ID Number..." 
                                    class="w-full pl-10 pr-4 py-2.5 bg-transparent relative z-0 border-2 border-purple-200 rounded-lg focus:border-purple-600 outline-none font-bold text-purple-900 transition-all shadow-sm uppercase"
                                    @keydown="acceptGhost"
                                    @keyup.enter="searchEmployee"
                                    @blur="hideSuggestions"
                                >
                                
                                <!-- Suggestions Dropdown -->
                                <div v-if="showSuggestions" class="absolute left-0 right-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-xl z-[110] overflow-hidden animate-in fade-in slide-in-from-top-2 duration-200">
                                    <div class="max-h-60 overflow-y-auto">
                                        <div v-if="searchingSuggestions" class="p-4 text-center text-gray-500 text-xs italic">
                                            <i class="pi pi-spin pi-spinner mr-2"></i>Searching employees...
                                        </div>
                                        <template v-else>
                                            <button 
                                                v-for="emp in suggestions" 
                                                :key="emp.id"
                                                @click="selectEmployee(emp)"
                                                class="w-full text-left p-3 hover:bg-purple-50 flex items-center gap-3 transition-colors border-b border-gray-50 last:border-0"
                                            >
                                                <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center text-purple-700 font-bold text-xs shrink-0">
                                                    {{ (emp.first_name?.[0] || '') + (emp.last_name?.[0] || '') || '??' }}
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="font-bold text-gray-800 text-sm truncate">{{ emp.name }}</p>
                                                    <div class="flex items-center gap-2 text-[10px] text-gray-500 font-medium">
                                                        <span class="text-purple-600">ID: {{ emp.employee_id }}</span>
                                                        <span>•</span>
                                                        <span>{{ emp.department?.name }}</span>
                                                        <span>{{ emp.position }}</span>
                                                    </div>
                                                </div>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            <button 
                                @click="searchEmployee" 
                                :disabled="searchingEmployee || !adminSearchId"
                                class="cursor-pointer px-6 py-2.5 bg-purple-600 text-white rounded-lg font-bold hover:bg-purple-700 disabled:opacity-50 transition-all flex items-center gap-2 shadow-md uppercase text-xs"
                            >
                                <i class="pi pi-spin pi-spinner" v-if="searchingEmployee"></i>
                                <span>{{ searchingEmployee ? 'Searching...' : 'Search' }}</span>
                            </button>
                        </div>
                        <p v-if="searchError" class="text-[10px] text-red-500 font-bold mt-2 uppercase flex items-center gap-1">
                            <i class="pi pi-exclamation-circle"></i> {{ searchError }}
                        </p>
                    </div>
                    
                    <!-- Employee Info Preview -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-2" v-if="displayUser">
                        <div>
                            <span class="block text-xs text-gray-500 uppercase">Name</span>
                            <span class="font-bold text-gray-800">{{ (displayUser.name || ((displayUser.first_name || '') + ' ' + (displayUser.last_name || ''))).trim() || 'No Name Set' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-gray-500 uppercase">Employee No.</span>
                            <span class="font-bold text-gray-800">{{ displayUser.id_number || displayUser.employee_id }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-gray-500 uppercase">Position</span>
                            <span class="font-bold text-gray-800">{{ displayUser.position || 'N/A' }}</span>
                        </div>
                         <div>
                            <span class="block text-xs text-gray-500 uppercase">Status</span>
                            <span class="inline-block px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 text-xs font-bold">{{ displayUser.employment_status || 'Regular' }}</span>
                        </div>
                    </div>

                    <div class="text-xs text-[#d93025]" v-if="formError">* {{ formError }}</div>
                    <div class="text-xs text-gray-500" v-else>* Indicates required fields</div>
                </div>

                <!-- 1. Attendance Category (ADMIN ONLY) -->
                <div v-if="isAdminMode && (getSection('attendance_category').visible || isDesignMode)" 
                    :class="['bg-white p-6 rounded-lg border-2 shadow-sm relative overflow-hidden transition-all',
                         isDesignMode ? 'hover:border-blue-400 cursor-pointer' : 'border-purple-100',
                         isEditing('attendance_category') ? 'border-blue-600 ring-8 ring-blue-500/5 z-20' : ''
                    ]"
                    @click="isDesignMode && !isEditing('attendance_category') ? startEditingSection(getSection('attendance_category')) : null"
                >
                    <div v-if="isEditing('attendance_category')" class="absolute top-0 left-0 w-full h-1.5 bg-blue-600 z-10"></div>
                    <div class="absolute top-0 right-0 bg-purple-100 text-purple-700 text-[10px] font-black px-3 py-1 rounded-bl-lg uppercase tracking-wider">Disciplinary Metadata</div>
                    
                    <template v-if="isEditing('attendance_category')">
                        <div class="space-y-4 animate-in fade-in zoom-in-95 duration-200">
                             <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-black text-blue-600 uppercase tracking-widest">Editing Card Properties</span>
                                <button @click.stop="editingSectionId = null" class="text-gray-400 hover:text-gray-600"><i class="pi pi-times"></i></button>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Section Title</label>
                                    <input v-model="sectionForm.title" class="w-full text-lg font-bold border-b-2 border-slate-100 focus:border-blue-500 outline-none py-1">
                                </div>
                                <div>
                                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Instruction / Tooltip</label>
                                    <input v-model="sectionForm.description" class="w-full text-sm border-b border-slate-100 focus:border-blue-500 outline-none py-1 text-gray-500">
                                </div>
                                <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100 mt-2">
                                    <h4 class="text-[9px] font-black text-blue-700 uppercase tracking-widest mb-3">Manage Category Options</h4>
                                    <div class="space-y-2 mb-3 max-h-32 overflow-y-auto pr-1 custom-scrollbar">
                                        <div v-for="(cat, idx) in attendanceCategories" :key="idx" class="flex items-center justify-between bg-white px-3 py-2 rounded-lg border border-blue-50 shadow-sm">
                                            <span class="text-[11px] font-bold text-gray-700 uppercase">{{ cat.label }} ({{ cat.code }})</span>
                                            <button @click.stop="removeOption(idx, 'attendance_categories')" class="text-red-400 hover:text-red-600"><i class="pi pi-trash text-[10px]"></i></button>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <input v-model="newOptionValue" placeholder="Code (e.g. SL)" class="w-1/3 px-3 py-1.5 text-xs border border-blue-200 rounded outline-none uppercase font-bold">
                                        <input v-model="newOptionLabel" placeholder="Label (e.g. Sick Leave)" class="flex-1 px-3 py-1.5 text-xs border border-blue-200 rounded outline-none font-bold">
                                        <button @click.stop="addOption('attendance_categories')" class="px-3 bg-blue-600 text-white rounded text-xs font-bold"><i class="pi pi-plus"></i></button>
                                    </div>
                                    <div class="mt-3">
                                        <button @click.stop="saveSettings('attendance_categories')" class="w-full py-2 bg-emerald-600 text-white rounded-lg text-[9px] font-black uppercase tracking-widest shadow-md">Update Selection List</button>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between pt-6 border-t border-slate-100 mt-4">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" v-model="sectionForm.visible" class="form-checkbox h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                        <span class="text-[10px] font-black text-gray-500 uppercase">Visible to Admin Filers</span>
                                    </label>
                                    <button @click.stop="saveSectionChanges" class="px-8 py-2.5 bg-blue-600 text-white rounded-lg text-xs font-black uppercase tracking-widest hover:bg-blue-700 shadow-md transition-all">Apply Layout</button>
                                </div>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <div class="mb-4 flex justify-between items-start">
                            <div>
                                <span class="text-base font-bold text-gray-800">{{ getSection('attendance_category').title }}</span>
                                <p class="text-xs text-gray-500">{{ getSection('attendance_category').description }}</p>
                            </div>
                            <div v-if="isDesignMode" class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center animate-pulse shadow-lg"><i class="pi pi-pencil text-[10px]"></i></div>
                        </div>
                    </template>
                    


                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                        <label v-for="cat in attendanceCategories" :key="cat.code" 
                            :class="[
                                'flex flex-col items-center justify-center p-3 rounded-xl border-2 transition-all cursor-pointer text-center',
                                form.category === cat.code ? 'bg-purple-600 border-purple-600 text-white shadow-md' : 'bg-white border-gray-100 text-gray-600 hover:border-purple-200'
                            ]"
                        >
                            <input type="radio" v-model="form.category" :value="cat.code" class="hidden">
                            <span class="text-xs font-black uppercase tracking-widest">{{ cat.code }}</span>
                            <span class="text-[9px] mt-1 font-medium opacity-80">{{ cat.label }}</span>
                        </label>
                    </div>
                    <div class="mt-4 p-3 bg-blue-50 rounded-lg text-[10px] text-blue-700 flex items-start gap-2">
                         <i class="pi pi-info-circle mt-0.5"></i>
                         <p>Note: Admin-filed requests are <b>automatically approved</b>. Categories are used to filter "Excessive Absence" reports later.</p>
                    </div>
                </div>

                <!-- Request Type Selection -->
                <div v-if="getSection('request_type').visible || isDesignMode" 
                    :class="['bg-white p-6 rounded-lg border-2 shadow-sm transition-all relative', 
                        isDesignMode ? 'hover:border-blue-400 cursor-pointer' : (showErrors && !form.requestType ? 'border-red-500 bg-red-50/10' : 'border-gray-300'),
                        isEditing('request_type') ? 'border-blue-600 ring-8 ring-blue-500/5 z-20' : ''
                    ]"
                    @click="isDesignMode && !isEditing('request_type') ? startEditingSection(getSection('request_type')) : null"
                >
                    <div v-if="isEditing('request_type')" class="absolute top-0 left-0 w-full h-1.5 bg-blue-600 rounded-t"></div>
                    
                    <template v-if="isEditing('request_type')">
                        <div class="space-y-4 animate-in fade-in zoom-in-95 duration-200 text-left">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-black text-blue-600 uppercase tracking-widest">Editing Card Properties</span>
                                <button @click.stop="editingSectionId = null" class="text-gray-400 hover:text-gray-600"><i class="pi pi-times"></i></button>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Section Title</label>
                                    <input v-model="sectionForm.title" class="w-full text-lg font-bold border-b-2 border-slate-100 focus:border-blue-500 outline-none py-1">
                                </div>
                                <div>
                                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Instruction / Tooltip</label>
                                    <input v-model="sectionForm.description" class="w-full text-sm border-b border-slate-100 focus:border-blue-500 outline-none py-1 text-gray-500">
                                </div>
                                <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100 mt-2">
                                    <h4 class="text-[9px] font-black text-blue-700 uppercase tracking-widest mb-3">Manage Selection Options</h4>
                                    <div class="space-y-2 mb-3 max-h-32 overflow-y-auto pr-1 custom-scrollbar">
                                        <div v-for="(type, idx) in requestTypes" :key="idx" class="flex items-center justify-between bg-white px-3 py-2 rounded-lg border border-blue-50 shadow-sm">
                                            <span class="text-[11px] font-bold text-gray-700 uppercase">{{ type }}</span>
                                            <button @click.stop="removeOption(idx, 'leave_request_types')" class="text-red-400 hover:text-red-600"><i class="pi pi-trash text-[10px]"></i></button>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <input v-model="newOptionValue" placeholder="New Option (e.g. Vacation)" class="flex-1 px-3 py-1.5 text-xs border border-blue-200 rounded outline-none font-bold uppercase">
                                        <button @click.stop="addOption('leave_request_types')" class="px-4 bg-blue-600 text-white rounded text-xs font-bold"><i class="pi pi-plus"></i> Add</button>
                                    </div>
                                    <div class="mt-3">
                                        <button @click.stop="saveSettings('leave_request_types')" class="w-full py-2 bg-emerald-600 text-white rounded-lg text-[9px] font-black uppercase tracking-widest shadow-md">Update Selection List</button>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between pt-6 border-t border-slate-100 mt-4">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" v-model="sectionForm.visible" class="form-checkbox h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                        <span class="text-[10px] font-black text-gray-500 uppercase">Visible to Employees</span>
                                    </label>
                                    <button @click.stop="saveSectionChanges" class="px-8 py-2.5 bg-blue-600 text-white rounded-lg text-xs font-black uppercase tracking-widest hover:bg-blue-700 shadow-md active:scale-95 transition-all">Apply Layout</button>
                                </div>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <div class="mb-3 flex justify-between items-start">
                            <div>
                                <span class="text-base font-medium text-gray-800">{{ getSection('request_type').title }}</span>
                                <span class="text-[#d93025] ml-1">*</span>
                                <p class="text-xs text-gray-500 mt-0.5">{{ getSection('request_type').description }}</p>
                            </div>
                            <div v-if="isDesignMode" class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center animate-pulse shadow-lg"><i class="pi pi-pencil text-[10px]"></i></div>
                        </div>
                    </template>

                    <template v-if="!isEditing('request_type')">
                        <!-- Options List (Static in View Mode) -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <label v-for="type in requestTypes" :key="type" class="flex items-center cursor-pointer p-2 rounded hover:bg-purple-50 transition-colors border border-transparent hover:border-purple-100">
                                <div class="relative flex items-center">
                                    <input type="radio" v-model="form.requestType" :value="type" name="requestType" class="peer h-4 w-4 cursor-pointer text-purple-600 focus:ring-purple-500 border-gray-300">
                                </div>
                                <span class="ml-2 text-sm text-gray-700 font-medium">{{ type }}</span>
                            </label>
                        </div>
                    </template>
                </div>

                <!-- Leave Type Selection -->
                <div v-if="getSection('leave_type').visible || isDesignMode" 
                    :class="['bg-white p-6 rounded-lg border-2 shadow-sm transition-all relative', 
                        isDesignMode ? 'hover:border-blue-400 cursor-pointer' : (showErrors && (!form.leaveType || (form.leaveType === 'Others' && !form.otherLeaveType)) ? 'border-red-500 bg-red-50/10' : 'border-gray-300'),
                        isEditing('leave_type') ? 'border-blue-600 ring-8 ring-blue-500/5 z-20' : ''
                    ]"
                    @click="isDesignMode && !isEditing('leave_type') ? startEditingSection(getSection('leave_type')) : null"
                >
                    <div v-if="isEditing('leave_type')" class="absolute top-0 left-0 w-full h-1.5 bg-blue-600 rounded-t"></div>
                    
                    <template v-if="isEditing('leave_type')">
                        <div class="space-y-4 animate-in fade-in zoom-in-95 duration-200 text-left">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-black text-blue-600 uppercase tracking-widest">Editing Card Properties</span>
                                <button @click.stop="editingSectionId = null" class="text-gray-400 hover:text-gray-600"><i class="pi pi-times"></i></button>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Section Title</label>
                                    <input v-model="sectionForm.title" class="w-full text-lg font-bold border-b-2 border-slate-100 focus:border-blue-500 outline-none py-1">
                                </div>
                                <div>
                                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Instruction / Tooltip</label>
                                    <input v-model="sectionForm.description" class="w-full text-sm border-b border-slate-100 focus:border-blue-500 outline-none py-1 text-gray-500">
                                </div>
                                <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100 mt-2">
                                    <h4 class="text-[9px] font-black text-blue-700 uppercase tracking-widest mb-3">Manage Leave Types</h4>
                                    <div class="space-y-2 mb-3 max-h-32 overflow-y-auto pr-1 custom-scrollbar">
                                        <div v-for="(type, idx) in leaveTypes" :key="idx" class="flex items-center justify-between bg-white px-3 py-2 rounded-lg border border-blue-50 shadow-sm">
                                            <span class="text-[11px] font-bold text-gray-700 uppercase">{{ type }}</span>
                                            <button @click.stop="removeOption(idx, 'leave_types')" class="text-red-400 hover:text-red-600"><i class="pi pi-trash text-[10px]"></i></button>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <input v-model="newOptionValue" placeholder="New Type (e.g. SIL)" class="flex-1 px-3 py-1.5 text-xs border border-blue-200 rounded outline-none font-bold uppercase">
                                        <button @click.stop="addOption('leave_types')" class="px-4 bg-blue-600 text-white rounded text-xs font-bold"><i class="pi pi-plus"></i> Add</button>
                                    </div>
                                    <div class="mt-3">
                                        <button @click.stop="saveSettings('leave_types')" class="w-full py-2 bg-emerald-600 text-white rounded-lg text-[9px] font-black uppercase tracking-widest shadow-md">Update Selection List</button>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between pt-6 border-t border-slate-100 mt-4">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" v-model="sectionForm.visible" class="form-checkbox h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                        <span class="text-[10px] font-black text-gray-500 uppercase">Visible to Employees</span>
                                    </label>
                                    <button @click.stop="saveSectionChanges" class="px-8 py-2.5 bg-blue-600 text-white rounded-lg text-xs font-black uppercase tracking-widest hover:bg-blue-700 shadow-md active:scale-95 transition-all">Apply Layout</button>
                                </div>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <div class="mb-3 flex justify-between items-start">
                            <div>
                                <span class="text-base font-medium text-gray-800">{{ getSection('leave_type').title }}</span>
                                <span class="text-[#d93025] ml-1">*</span>
                                <p class="text-xs text-gray-500 mt-0.5">{{ getSection('leave_type').description }}</p>
                            </div>
                            <div v-if="isDesignMode" class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center animate-pulse shadow-lg"><i class="pi pi-pencil text-[10px]"></i></div>
                        </div>
                    </template>

                    <template v-if="!isEditing('leave_type')">
                        <!-- Options List (Static in View Mode) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3">
                            <label v-for="lType in leaveTypes" :key="lType" class="flex items-center cursor-pointer group">
                                <input type="radio" v-model="form.leaveType" :value="lType" name="leaveType" class="peer h-4 w-4 cursor-pointer text-purple-600 focus:ring-purple-500 border-gray-300">
                                <span class="ml-2 text-sm text-gray-700 group-hover:text-purple-700 transition-colors">{{ lType }}</span>
                            </label>
                            
                            <div class="col-span-1 md:col-span-2 mt-2">
                                <label class="flex items-center cursor-pointer group mb-2">
                                    <input type="radio" v-model="form.leaveType" value="Others" name="leaveType" class="peer h-4 w-4 cursor-pointer text-purple-600 focus:ring-purple-500 border-gray-300">
                                    <span class="ml-2 text-sm text-gray-700 group-hover:text-purple-700 font-medium">Others (Please Specify)</span>
                                </label>
                                <transition name="fade">
                                    <input 
                                        v-if="form.leaveType === 'Others'"
                                        type="text" 
                                        v-model="form.otherLeaveType" 
                                        :class="['w-full border-b-2 outline-none py-1.5 text-sm transition-colors bg-gray-50 px-2 rounded-t', showErrors && !form.otherLeaveType ? 'border-red-500 placeholder-red-300' : 'border-gray-200 focus:border-[#673ab7]']" 
                                        placeholder="Specify leave type..." 
                                        ref="otherInput"
                                    >
                                </transition>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- 3. Details of Leave -->
                <div v-if="getSection('details').visible || isDesignMode" 
                    :class="['bg-white p-6 rounded-lg border-2 shadow-sm relative transition-all', 
                        isDesignMode ? 'hover:border-blue-400 cursor-pointer' : 'border-gray-300',
                        isEditing('details') ? 'border-blue-600 ring-8 ring-blue-500/5 z-20' : ''
                    ]"
                    @click="isDesignMode && !isEditing('details') ? startEditingSection(getSection('details')) : null"
                >
                    <div v-if="isEditing('details')" class="absolute top-0 left-0 w-full h-1.5 bg-blue-600 rounded-t"></div>
                    
                    <template v-if="isEditing('details')">
                        <div class="space-y-4 animate-in fade-in zoom-in-95 duration-200 text-left">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-black text-blue-600 uppercase tracking-widest">Editing Card Properties</span>
                                <button @click.stop="editingSectionId = null" class="text-gray-400 hover:text-gray-600"><i class="pi pi-times"></i></button>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Section Title</label>
                                    <input v-model="sectionForm.title" class="w-full text-lg font-bold border-b-2 border-slate-100 focus:border-blue-500 outline-none py-1">
                                </div>
                                <div>
                                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Instruction / Tooltip</label>
                                    <input v-model="sectionForm.description" class="w-full text-sm border-b border-slate-100 focus:border-blue-500 outline-none py-1 text-gray-500">
                                </div>
                                <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100 mt-2">
                                    <h4 class="text-[9px] font-black text-blue-700 uppercase tracking-widest mb-3">Add Custom Radio Options</h4>
                                    <div class="space-y-2 mb-3 max-h-32 overflow-y-auto pr-1 custom-scrollbar">
                                        <div v-for="(opt, idx) in sectionForm.options" :key="idx" class="flex items-center justify-between bg-white px-3 py-2 rounded-lg border border-blue-50 shadow-sm">
                                            <span class="text-[11px] font-bold text-gray-700">{{ opt }}</span>
                                            <button @click.stop="removeSectionOption(idx)" class="text-red-400 hover:text-red-600"><i class="pi pi-trash text-[10px]"></i></button>
                                        </div>
                                        <div v-if="!sectionForm.options?.length" class="text-center py-4 text-[9px] text-blue-400 italic font-medium">No custom radio buttons added yet.</div>
                                    </div>
                                    <div class="flex gap-2">
                                        <input v-model="newOptionValue" placeholder="Option (e.g. AM Only)" class="flex-1 px-3 py-1.5 text-xs border border-blue-200 rounded outline-none font-bold">
                                        <button @click.stop="addSectionOption" class="px-4 bg-blue-600 text-white rounded text-xs font-bold shadow-sm hover:bg-blue-700"><i class="pi pi-plus"></i> Add</button>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between pt-6 border-t border-slate-100 mt-4">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" v-model="sectionForm.visible" class="form-checkbox h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                        <span class="text-[10px] font-black text-gray-500 uppercase">Visible to Employees</span>
                                    </label>
                                    <button @click.stop="saveSectionChanges" class="px-8 py-2.5 bg-blue-600 text-white rounded-lg text-xs font-black uppercase tracking-widest hover:bg-blue-700 shadow-md active:scale-95 transition-all">Apply Layout</button>
                                </div>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <div class="mb-4 flex justify-between items-start">
                            <div>
                                <div class="text-base font-medium text-gray-800">{{ getSection('details').title }}</div>
                                <p class="text-xs text-gray-500 mt-0.5">{{ getSection('details').description }}</p>
                            </div>
                            <div v-if="isDesignMode" class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center animate-pulse"><i class="pi pi-pencil text-[10px]"></i></div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Date Range -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">From Date <span class="text-red-500">*</span></label>
                                    <input type="date" v-model="form.fromDate" :min="today" :class="['w-full p-2 border rounded focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition-all', showErrors && !form.fromDate ? 'border-red-500 bg-red-50' : 'border-gray-300']">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">To Date <span class="text-red-500">*</span></label>
                                    <input type="date" v-model="form.toDate" :min="form.fromDate || today" :class="['w-full p-2 border rounded focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition-all', showErrors && !form.toDate ? 'border-red-500 bg-red-50' : 'border-gray-300']">
                                </div>
                            </div>

                            <!-- Duration Metrics -->
                            <div class="space-y-4">
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">No. of Days</label>
                                    <div class="flex items-center gap-2">
                                        <input readonly type="number" step="0.5" v-model="form.numberOfDays" class="w-24 p-2 border border-gray-300 rounded font-bold text-gray-800 text-center focus:ring-purple-500 outline-none">
                                        <span class="text-sm text-gray-500">days</span>
                                    </div>
                                    
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1 mt-3">No. of Hours</label>
                                    <div class="flex items-center gap-2">
                                        <input type="number" step="0.5" v-model="form.numberOfHours" class="w-24 p-2 border border-gray-300 rounded font-bold text-gray-800 text-center focus:ring-purple-500 outline-none">
                                        <span class="text-sm text-gray-500">hours</span>
                                    </div>
                                </div>

                                <!-- Pay Configuration (ADMIN ONLY) -->
                                <div v-if="isAdminMode" class="bg-teal-50/50 p-4 rounded-lg border border-teal-100 animate-in fade-in transition-all">
                                    <label class="block text-[10px] font-black text-teal-700 uppercase tracking-widest mb-3">Pay Configuration</label>
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-6">
                                            <label class="flex items-center gap-2 cursor-pointer group">
                                                <input type="radio" v-model="form.isPaid" :value="true" class="peer h-4 w-4 cursor-pointer text-teal-600 focus:ring-teal-500 border-teal-300">
                                                <span class="text-sm font-bold text-teal-800">With Pay</span>
                                            </label>
                                            <label class="flex items-center gap-2 cursor-pointer group">
                                                <input type="radio" v-model="form.isPaid" :value="false" class="peer h-4 w-4 cursor-pointer text-gray-400 focus:ring-gray-300 border-gray-300">
                                                <span class="text-sm font-bold text-gray-600">Without Pay</span>
                                            </label>
                                        </div>
                                        <transition name="slide-down">
                                            <div v-if="form.isPaid" class="flex items-center gap-3 pt-2 border-t border-teal-100/50">
                                                <div class="flex-1">
                                                    <label class="block text-[9px] font-black text-teal-600 uppercase mb-1">Total Days to be Paid</label>
                                                    <div class="flex items-center gap-2">
                                                        <input type="number" step="0.5" v-model="form.daysPaid" class="w-full px-3 py-2 bg-white border border-teal-200 rounded-lg text-sm font-black text-teal-900 outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all">
                                                        <span class="text-xs font-bold text-teal-600">DAYS</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </transition>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Integrated Custom Radio Options for Details -->
                        <div v-if="getSection('details').options?.length" class="mt-6 pt-6 border-t border-gray-100">
                            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Additional Selection</span>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div v-for="opt in getSection('details').options" :key="opt" 
                                    class="flex flex-col justify-center p-3 rounded-lg border border-gray-100 hover:bg-purple-50 hover:border-purple-200 transition-all group cursor-pointer"
                                    @click="() => { 
                                        if (form.additionalDetails[getSection('details').id] === opt) { 
                                            form.additionalDetails[getSection('details').id] = ''; 
                                            form.additionalDetails[getSection('details').id + '_input'] = '';
                                        } else {
                                            form.additionalDetails[getSection('details').id] = opt;
                                        }
                                    }">
                                    <div class="flex items-center">
                                        <input type="radio" 
                                            :checked="form.additionalDetails[getSection('details').id] === opt"
                                            @click.stop="() => { 
                                                if (form.additionalDetails[getSection('details').id] === opt) { 
                                                    form.additionalDetails[getSection('details').id] = ''; 
                                                    form.additionalDetails[getSection('details').id + '_input'] = '';
                                                } else {
                                                    form.additionalDetails[getSection('details').id] = opt;
                                                }
                                            }"
                                            class="h-4 w-4 cursor-pointer text-purple-600 focus:ring-purple-500 border-gray-300"
                                        >
                                        <span class="ml-3 text-sm text-gray-700 group-hover:text-purple-700 font-medium">{{ opt }}</span>
                                    </div>
                                    <transition name="slide-down">
                                        <div v-if="form.additionalDetails[getSection('details').id] === opt" class="mt-2 ml-7" @click.stop>
                                            <input type="number" step="0.5" 
                                                v-model="form.additionalDetails[getSection('details').id + '_input']" 
                                                placeholder="Specify Value / Amount"
                                                class="w-full text-sm p-1.5 border border-purple-200 bg-white rounded focus:border-purple-500 focus:ring-2 focus:ring-purple-200 outline-none transition-all"
                                            >
                                        </div>
                                    </transition>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- 4. Optional Attachment -->
                <div v-if="getSection('attachment').visible || isDesignMode" 
                    :class="['bg-white p-6 rounded-lg border-2 shadow-sm relative transition-all', 
                        isDesignMode ? 'hover:border-blue-400 cursor-pointer' : 'border-gray-300',
                        isEditing('attachment') ? 'border-blue-600 ring-8 ring-blue-500/5 z-20' : ''
                    ]"
                    @click="isDesignMode && !isEditing('attachment') ? startEditingSection(getSection('attachment')) : null"
                >
                    <div v-if="isEditing('attachment')" class="absolute top-0 left-0 w-full h-1.5 bg-blue-600 rounded-t"></div>
                    
                    <template v-if="isEditing('attachment')">
                        <div class="space-y-4 animate-in fade-in zoom-in-95 duration-200 text-left">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-black text-blue-600 uppercase tracking-widest">Editing Card Properties</span>
                                <button @click.stop="editingSectionId = null" class="text-gray-400 hover:text-gray-600"><i class="pi pi-times"></i></button>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Section Title</label>
                                    <input v-model="sectionForm.title" class="w-full text-lg font-bold border-b-2 border-slate-100 focus:border-blue-500 outline-none py-1">
                                </div>
                                <div>
                                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Instruction / Tooltip</label>
                                    <input v-model="sectionForm.description" class="w-full text-sm border-b border-slate-100 focus:border-blue-500 outline-none py-1 text-gray-500">
                                </div>
                                <div class="flex items-center justify-between pt-6 border-t border-slate-100 mt-4">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" v-model="sectionForm.visible" class="form-checkbox h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                        <span class="text-[10px] font-black text-gray-500 uppercase">Visible to Employees</span>
                                    </label>
                                    <button @click.stop="saveSectionChanges" class="px-8 py-2.5 bg-blue-600 text-white rounded-lg text-xs font-black uppercase tracking-widest hover:bg-blue-700 shadow-md active:scale-95 transition-all">Apply Layout</button>
                                </div>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <div class="mb-3 flex justify-between items-start">
                            <div>
                                <span class="text-base font-medium text-gray-800">{{ getSection('attachment').title }}</span>
                                <p class="text-xs text-gray-500 mt-0.5">{{ getSection('attachment').description }}</p>
                            </div>
                            <div v-if="isDesignMode" class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center animate-pulse"><i class="pi pi-pencil text-[10px]"></i></div>
                        </div>
                        <div class="flex items-center gap-4">
                            <label class="cursor-pointer flex items-center gap-2 px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg hover:bg-purple-50 hover:border-purple-200 transition-all group">
                                <i class="pi pi-paperclip text-gray-400 group-hover:text-purple-600"></i>
                                <span class="text-sm text-gray-600 group-hover:text-purple-700 font-medium">
                                    {{ attachmentName || 'Attach File' }}
                                </span>
                                <input type="file" ref="fileInput" @change="handleFileChange" class="hidden" accept=".jpg,.jpeg,.png,.pdf">
                            </label>
                            <span v-if="attachmentName" class="text-xs text-gray-400 cursor-pointer hover:text-red-500" @click="clearFile">
                                <i class="pi pi-times"></i> Remove
                            </span>
                            <p class="text-xs text-gray-400 italic">Max 5MB. JPG, PNG, PDF only.</p>
                        </div>
                    </template>
                </div>

                <div v-if="getSection('reason').visible || isDesignMode" 
                    :class="['bg-white p-6 rounded-lg border-2 shadow-sm relative transition-all', 
                        isDesignMode ? 'hover:border-blue-400 cursor-pointer' : (showErrors && !form.reason ? 'border-red-500 bg-red-50/10' : 'border-gray-300'),
                        isEditing('reason') ? 'border-blue-600 ring-8 ring-blue-500/5 z-20' : ''
                    ]"
                    @click="isDesignMode && !isEditing('reason') ? startEditingSection(getSection('reason')) : null"
                >
                    <div v-if="isEditing('reason')" class="absolute top-0 left-0 w-full h-1.5 bg-blue-600 rounded-t"></div>
                    
                    <template v-if="isEditing('reason')">
                        <div class="space-y-4 animate-in fade-in zoom-in-95 duration-200 text-left">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-black text-blue-600 uppercase tracking-widest">Editing Card Properties</span>
                                <button @click.stop="editingSectionId = null" class="text-gray-400 hover:text-gray-600"><i class="pi pi-times"></i></button>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Section Title</label>
                                    <input v-model="sectionForm.title" class="w-full text-lg font-bold border-b-2 border-slate-100 focus:border-blue-500 outline-none py-1">
                                </div>
                                <div>
                                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Instruction / Tooltip</label>
                                    <input v-model="sectionForm.description" class="w-full text-sm border-b border-slate-100 focus:border-blue-500 outline-none py-1 text-gray-500">
                                </div>
                                <div class="flex items-center justify-between pt-6 border-t border-slate-100 mt-4">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" v-model="sectionForm.visible" class="form-checkbox h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                        <span class="text-[10px] font-black text-gray-500 uppercase">Visible to Employees</span>
                                    </label>
                                    <button @click.stop="saveSectionChanges" class="px-8 py-2.5 bg-blue-600 text-white rounded-lg text-xs font-black uppercase tracking-widest hover:bg-blue-700 shadow-md active:scale-95 transition-all">Apply Layout</button>
                                </div>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <div class="mb-3 flex justify-between items-start">
                            <div>
                                <span class="text-base font-medium text-gray-800">{{ getSection('reason').title }}</span>
                                <span class="text-[#d93025] ml-1">*</span>
                                <p class="text-xs text-gray-500 mt-0.5">{{ getSection('reason').description }}</p>
                            </div>
                            <div v-if="isDesignMode" class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center animate-pulse"><i class="pi pi-pencil text-[10px]"></i></div>
                        </div>
                        <textarea 
                            v-model="form.reason" 
                            rows="4" 
                            :class="['w-full p-3 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition-shadow resize-none placeholder-gray-400 text-sm', showErrors && !form.reason ? 'border-red-500 placeholder-red-300' : 'border-gray-300']"
                            placeholder="Please state the specific reason for your request..."
                        ></textarea>
                    </template>
                </div>

                <!-- Custom Cards (Dynamic Loop) -->
                <template v-for="section in settingsStore.formSections" :key="section.id">
                    <div v-if="section.type === 'custom' && (section.visible !== false || isDesignMode)" 
                        :class="['bg-white p-6 rounded-lg border-2 shadow-sm relative transition-all group animate-in slide-in-from-bottom-2 duration-300',
                            isDesignMode ? 'hover:border-blue-400 cursor-pointer' : 'border-gray-300',
                            isEditing(section.id) ? 'border-blue-600 ring-8 ring-blue-500/5 z-20' : ''
                        ]"
                        @click="isDesignMode && !isEditing(section.id) ? startEditingSection(section) : null"
                    >
                        <div v-if="isEditing(section.id)" class="absolute top-0 left-0 w-full h-1.5 bg-blue-600 rounded-t"></div>

                        <template v-if="isEditing(section.id)">
                            <div class="space-y-4 animate-in fade-in zoom-in-95 duration-200 text-left">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[10px] font-black text-blue-600 uppercase tracking-widest">Editing Custom Card</span>
                                    <div class="flex gap-2">
                                        <button @click.stop="removeSection(section.id)" class="text-xs font-black text-red-500 uppercase hover:bg-red-50 px-2 py-1 rounded transition-colors" title="Delete Card">Delete</button>
                                        <button @click.stop="editingSectionId = null" class="text-gray-400 hover:text-gray-600"><i class="pi pi-times"></i></button>
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Card Title</label>
                                        <input v-model="sectionForm.title" class="w-full text-lg font-bold border-b-2 border-slate-100 focus:border-blue-500 outline-none py-1">
                                    </div>
                                    <div>
                                        <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Instructions</label>
                                        <input v-model="sectionForm.description" class="w-full text-sm border-b border-slate-100 focus:border-blue-500 outline-none py-1 text-gray-500">
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-4 py-2">
                                        <div>
                                            <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Field Type</label>
                                            <select v-model="sectionForm.field_type" class="w-full text-xs font-bold border rounded p-2 focus:ring-2 focus:ring-blue-500 outline-none">
                                                <option value="text">Single Line Text</option>
                                                <option value="textarea">Paragraph / Multi-line</option>
                                                <option value="radio">Selection (Radio Buttons)</option>
                                            </select>
                                        </div>
                                        <div class="flex flex-col justify-center">
                                            <label class="flex items-center gap-2 cursor-pointer mt-4">
                                                <input type="checkbox" v-model="sectionForm.visible" class="form-checkbox h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500 transition-all">
                                                <span class="text-[10px] font-black text-gray-500 uppercase">Visible to Employees</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div v-if="sectionForm.field_type === 'radio'" class="mt-2 p-4 bg-blue-50 rounded-xl border border-blue-100 animate-in zoom-in duration-200">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center gap-2">
                                                <i class="pi pi-list text-blue-600"></i>
                                                <h4 class="text-[10px] font-black text-blue-700 uppercase tracking-widest">Manage Selection Options</h4>
                                            </div>
                                        </div>
                                        <div class="space-y-2 mb-3 max-h-32 overflow-y-auto pr-2 custom-scrollbar">
                                            <div v-for="(item, idx) in sectionForm.options" :key="idx" class="flex items-center justify-between bg-white px-3 py-1.5 rounded border border-blue-50 group">
                                                <span class="text-[11px] font-bold text-gray-700">{{ item }}</span>
                                                <button @click.stop="removeSectionOption(idx)" class="text-red-400 hover:text-red-600 p-1 opacity-100 sm:opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <i class="pi pi-trash text-[10px]"></i>
                                                </button>
                                            </div>
                                            <div v-if="!sectionForm.options?.length" class="text-center py-4 text-[10px] text-blue-400 italic">No options added yet.</div>
                                        </div>
                                        <div class="flex gap-2">
                                            <input v-model="newOptionValue" placeholder="e.g. Yes / No" class="flex-1 px-3 py-1.5 text-xs border border-blue-200 rounded outline-none focus:border-blue-600 font-medium" @keyup.enter="addSectionOption">
                                            <button @click.stop="addSectionOption" class="px-4 py-1.5 bg-blue-600 text-white rounded text-xs font-bold hover:bg-blue-700 flex items-center gap-1 shadow-sm"><i class="pi pi-plus text-[10px]"></i> Add</button>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-end pt-4 border-t border-slate-100 mt-2">
                                        <button @click.stop="saveSectionChanges" class="px-8 py-2.5 bg-blue-600 text-white rounded-lg text-xs font-black uppercase tracking-widest hover:bg-blue-700 shadow-md active:scale-95 transition-all">Apply Layout</button>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-base font-medium text-gray-800">{{ section.title }}</h3>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ section.description }}</p>
                                </div>
                                <div v-if="isDesignMode" class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center animate-pulse"><i class="pi pi-pencil text-[10px]"></i></div>
                            </div>

                            <div v-if="isAdminMode" class="mt-2 text-gray-400 border-2 border-dashed border-gray-100 p-2 text-center text-[10px] font-black uppercase tracking-widest mb-4">
                                Custom Field ({{ section.field_type }})
                            </div>

                            <div class="space-y-4">
                                <template v-if="section.field_type === 'text'">
                                    <input 
                                        type="text" 
                                        v-model="form.additionalDetails[section.id]"
                                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none text-sm"
                                        :placeholder="'Enter ' + section.title + '...'"
                                    >
                                </template>
                                <template v-else-if="section.field_type === 'radio'">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <label v-for="opt in section.options" :key="opt" class="flex items-center cursor-pointer p-3 rounded-lg border border-gray-100 hover:bg-purple-50 hover:border-purple-200 transition-all group">
                                            <input type="radio" v-model="form.additionalDetails[section.id]" :value="opt" class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300">
                                            <span class="ml-3 text-sm text-gray-700 group-hover:text-purple-700 font-medium">{{ opt }}</span>
                                        </label>
                                    </div>
                                    <div v-if="!section.options?.length" class="text-xs text-amber-500 italic bg-amber-50 p-2 rounded border border-amber-100 flex items-center gap-2">
                                        <i class="pi pi-exclamation-triangle"></i> No options configured for this radio field.
                                    </div>
                                </template>
                                <template v-else>
                                    <textarea 
                                        v-model="form.additionalDetails[section.id]"
                                        rows="3" 
                                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none text-sm resize-none"
                                        :placeholder="'Enter ' + section.title + '...'"
                                    ></textarea>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Footer Actions -->
            <div class="bg-[#f0f2f5] p-4 flex justify-between items-center shrink-0 border-t border-gray-200">
                <div v-if="!isPortalMode" @click="closeModal" class="btn-text cursor-pointer text-[#673ab7] text-sm font-bold uppercase hover:bg-[#673ab7]/10 px-4 py-2 rounded transition-colors tracking-wide">
                    Cancel
                </div>
                <div v-else></div> <!-- Spacer for portal mode -->
                <button 
                    @click="submitForm" 
                    :disabled="loading"
                    class="cursor-pointer bg-[#673ab7] text-white px-8 py-2.5 rounded shadow-md text-sm font-bold uppercase hover:bg-[#5e35b1] hover:shadow-lg transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                >
                    <i v-if="loading" class="pi pi-spin pi-spinner"></i>
                    <span>{{ isEdit ? 'Update Request' : 'Submit Request' }}</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, computed, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { storeToRefs } from 'pinia';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    initialData: { type: Object, default: null },
    isEdit: { type: Boolean, default: false },
    isAdminMode: { type: Boolean, default: false },
    isPortalMode: { type: Boolean, default: false },
    employeeData: { type: Object, default: null }
});

const emit = defineEmits(['update:modelValue', 'submit', 'update']);
const authStore = useAuthStore();
const { user } = storeToRefs(authStore);

import { useSettingsStore } from '@/stores/settings';

const settingsStore = useSettingsStore();
const { requestTypes, leaveTypes, attendanceCategories } = storeToRefs(settingsStore);

// Admin Mode State
import axios from 'axios';
// State
const showErrors = ref(false);
const formError = ref('');
const loading = ref(false);
const isDesignMode = ref(false);

const newOptionValue = ref('');
const newOptionLabel = ref('');

// Admin / Search State
const adminSearchId = ref('');
const searchedEmployee = ref(null);
const searchingEmployee = ref(false);
const searchError = ref('');
const suggestions = ref([]);
const showSuggestions = ref(false);
const searchingSuggestions = ref(false);
let searchDebounce = null;

const ghostText = computed(() => {
    if (!adminSearchId.value || adminSearchId.value.length < 2 || suggestions.value.length === 0) return '';
    const q = adminSearchId.value.toLowerCase();
    const emp = suggestions.value[0];
    
    // Match starts with first name (or full name)
    if (emp.name.toLowerCase().startsWith(q)) {
        return adminSearchId.value + emp.name.slice(q.length);
    }
    
    // Match starts with last name - rearrange to align ghost text
    if (emp.last_name && emp.last_name.toLowerCase().startsWith(q)) {
        const rearranged = `${emp.last_name || ''}, ${emp.first_name || ''}`;
        return adminSearchId.value + rearranged.slice(q.length);
    }
    
    return '';
});

const acceptGhost = (e) => {
    if (ghostText.value && (e.key === 'Tab' || e.key === 'ArrowRight')) {
        e.preventDefault();
        selectEmployee(suggestions.value[0]);
    }
};

const displayUser = computed(() => {
    if (props.isPortalMode && props.employeeData) return props.employeeData;
    if (props.isAdminMode && searchedEmployee.value) return searchedEmployee.value;
    return user.value;
});

const form = ref({
    dateFiled: new Date().toISOString().split('T')[0],
    requestType: 'Leave',
    leaveType: '',
    category: '',
    otherLeaveType: '',
    fromDate: '',
    toDate: '',
    numberOfDays: 1,
    numberOfHours: 0,
    startTime: '',
    endTime: '',
    reason: '',
    isPaid: false,
    daysPaid: 0,
    attachment: null,
    additionalDetails: {}
});

const attachmentName = ref('');
const fileInput = ref(null);

// Get today's date in YYYY-MM-DD format for min attribute
const today = computed(() => {
    const now = new Date();
    return now.toISOString().split('T')[0];
});

onMounted(() => {
    if (!props.isPortalMode && !user.value) authStore.fetchUser();
    settingsStore.fetchSettings();
});

const addOption = (targetKey) => {
    if (!targetKey || !newOptionValue.value) return;

    if (targetKey === 'attendance_categories') {
        if (!newOptionLabel.value) return;
        const newItem = { code: newOptionValue.value.toUpperCase(), label: newOptionLabel.value };
        const updated = [...attendanceCategories.value, newItem];
        settingsStore.updateSetting(targetKey, updated);
    } else {
        const val = newOptionValue.value;
        const currentList = targetKey === 'leave_request_types' ? requestTypes.value : leaveTypes.value;
        const updated = [...currentList, val];
        settingsStore.updateSetting(targetKey, updated);
    }
    newOptionValue.value = '';
    newOptionLabel.value = '';
};

const removeOption = (index, targetKey) => {
    if (!targetKey) return;
    let list;
    if (targetKey === 'leave_request_types') list = [...requestTypes.value];
    else if (targetKey === 'leave_types') list = [...leaveTypes.value];
    else if (targetKey === 'attendance_categories') list = [...attendanceCategories.value];
    
    if (list) {
        list.splice(index, 1);
        settingsStore.updateSetting(targetKey, list);
    }
};

const saveSettings = async (targetKey) => {
    // This is currently a no-op as add/remove handle updates directly,
    // but kept for future batch-save functionality in integrated cards.
    return true;
};

const isEditing = (id) => isDesignMode.value && editingSectionId.value === id;

const getSection = (id) => settingsStore.formSections.find(s => s.id === id) || { visible: true, title: '', description: '' };

const toggleSectionVisibility = async (id) => {
    const sections = [...settingsStore.formSections];
    const idx = sections.findIndex(s => s.id === id);
    if (idx !== -1) {
        sections[idx].visible = !sections[idx].visible;
        await settingsStore.updateSetting('leave_form_sections', sections);
    }
};

const startEditingSection = (section) => {
    editingSectionId.value = section.id;
    sectionForm.value = { 
        title: section.title, 
        description: section.description || '', 
        visible: section.visible !== false,
        field_type: section.field_type || 'textarea',
        options: section.options ? [...section.options] : []
    };
};

const addSectionOption = () => {
    if (!newOptionValue.value) return;
    if (!sectionForm.value.options) sectionForm.value.options = [];
    sectionForm.value.options.push(newOptionValue.value);
    newOptionValue.value = '';
};

const removeSectionOption = (idx) => {
    sectionForm.value.options.splice(idx, 1);
};

const addCustomSection = async () => {
    const sections = [...settingsStore.formSections];
    const newId = 'custom_' + Date.now();
    sections.push({
        id: newId,
        type: 'custom',
        title: 'New Custom Card',
        description: 'Provide more information here.',
        field_type: 'textarea',
        visible: true,
        removable: true
    });
    await settingsStore.updateSetting('leave_form_sections', sections);
};

const removeSection = async (id) => {
    if (!confirm('Are you sure you want to delete this custom card?')) return;
    const sections = settingsStore.formSections.filter(s => s.id !== id);
    await settingsStore.updateSetting('leave_form_sections', sections);
};

const saveSectionChanges = async () => {
    const sections = [...settingsStore.formSections];
    const idx = sections.findIndex(s => s.id === editingSectionId.value);
    if (idx !== -1) {
        sections[idx] = { ...sections[idx], ...sectionForm.value };
        await settingsStore.updateSetting('leave_form_sections', sections);
        editingSectionId.value = null;
    }
};

const setError = (msg) => {
    formError.value = msg;
};

const editingSectionId = ref(null);
const sectionForm = ref({ title: '', description: '', visible: true });

const resetForm = () => {
    form.value = {
        dateFiled: new Date().toISOString().split('T')[0],
        requestType: 'Leave',
        leaveType: '',
        category: '',
        otherLeaveType: '',
        fromDate: '',
        toDate: '',
        numberOfDays: 1,
        numberOfHours: 0,
        startTime: '',
        endTime: '',
        reason: '',
        isPaid: false,
        daysPaid: 0,
        attachment: null,
        additionalDetails: {}
    };
    attachmentName.value = '';
    formError.value = '';
    showErrors.value = false;
    adminSearchId.value = '';
    searchedEmployee.value = null;
    searchError.value = '';
    suggestions.value = [];
    showSuggestions.value = false;
};

const closeModal = () => {
    emit('update:modelValue', false);
    resetForm();
};

// Watch for modal open and initialData to populate form
watch(() => props.modelValue, (isOpen) => {
    if (isOpen) {
        if (props.isEdit && props.initialData) {
            const data = props.initialData;
            form.value = {
                dateFiled: data.date_filed || new Date().toISOString().split('T')[0],
                requestType: data.request_type || 'Leave',
                leaveType: leaveTypes.includes(data.leave_type) ? data.leave_type : 'Others',
                otherLeaveType: leaveTypes.includes(data.leave_type) ? '' : data.leave_type,
                fromDate: data.from_date,
                toDate: data.to_date || data.from_date,
                numberOfDays: data.days_taken,
                numberOfHours: data.numberOfHours || 0,
                startTime: data.start_time || '',
                endTime: data.end_time || '',
                reason: data.reason,
                attachment: null,
                isPaid: data.is_paid || false,
                daysPaid: data.days_paid || 0,
                additionalDetails: data.additional_details || {}
            };
        } else {
            resetForm();
            if (props.isAdminMode) {
                // If we were passed an employee ID via admin link, auto-populate if possible
                const urlParams = new URLSearchParams(window.location.search);
                const preppedId = urlParams.get('admin_file_target');
                if (preppedId) {
                    adminSearchId.value = preppedId;
                    searchEmployee();
                }
            }
        }
    }
});

const searchEmployee = async () => {
    if (!adminSearchId.value) return;
    searchingEmployee.value = true;
    searchError.value = '';
    searchedEmployee.value = null;

    // Clean search ID: remove leading zeros to match numeric-string format in DB (003 -> 3)
    const cleanId = adminSearchId.value.replace(/^0+/, '') || adminSearchId.value;

    try {
        const response = await axios.get(`/api/employees/find-by-code/${cleanId}`);
        searchedEmployee.value = response.data;
    } catch (e) {
        searchError.value = "Employee not found. Please check the Name or ID number.";
    } finally {
        searchingEmployee.value = false;
        showSuggestions.value = false;
    }
};

const selectEmployee = (emp) => {
    searchedEmployee.value = emp;
    adminSearchId.value = emp.name;
    showSuggestions.value = false;
    suggestions.value = [];
    searchError.value = '';
};

const hideSuggestions = () => {
    setTimeout(() => {
        showSuggestions.value = false;
    }, 200);
};

watch(adminSearchId, (newVal) => {
    if (!props.isAdminMode || !props.modelValue || searchedEmployee.value?.name === newVal) return;
    
    if (searchDebounce) clearTimeout(searchDebounce);
    
    if (!newVal || newVal.length < 2) {
        suggestions.value = [];
        showSuggestions.value = false;
        return;
    }

    searchDebounce = setTimeout(async () => {
        searchingSuggestions.value = true;
        try {
            const response = await axios.get('/api/employees', {
                params: { search: newVal, limit: 5 }
            });
            // The API returns paginated data
            const raw = response.data.data || [];
            const q = newVal.toLowerCase();
            
            // Sort to put people whose first OR last name starts with the search at the top
            suggestions.value = [...raw].sort((a, b) => {
                const aFirst = a.first_name?.toLowerCase().startsWith(q);
                const aLast = a.last_name?.toLowerCase().startsWith(q);
                const bFirst = b.first_name?.toLowerCase().startsWith(q);
                const bLast = b.last_name?.toLowerCase().startsWith(q);
                
                const aMatch = aFirst || aLast;
                const bMatch = bFirst || bLast;

                if (aMatch && !bMatch) return -1;
                if (!aMatch && bMatch) return 1;
                
                // If both are prefix matches, prioritize first name matches or alphabetical
                if (aMatch && bMatch) {
                    if (aFirst && !bFirst) return -1;
                    if (!aFirst && bFirst) return 1;
                }
                return 0;
            });
            showSuggestions.value = suggestions.value.length > 0;
        } catch (e) {
            console.error('Failed to fetch suggestions', e);
        } finally {
            searchingSuggestions.value = false;
        }
    }, 300);
});

const submitForm = async () => {
    formError.value = '';
    showErrors.value = true;
    
    // Validation
    if (!form.value.leaveType) return setError('Please select a leave type before submitting.');
    if (form.value.leaveType === 'Others' && !form.value.otherLeaveType) return setError('Please specify the "Others" leave type.');
    if (!form.value.fromDate || !form.value.toDate) return setError('Please select both the "From" and "To" dates.');
    if (!form.value.reason) return setError('Please state the specific reason for your request.');

    if (props.isAdminMode && !searchedEmployee.value) {
        return setError('Please search and select an employee first.');
    }

    loading.value = true;

    // Use employee id from props if portal mode
    let targetEmployeeId = null;
    if (props.isPortalMode) {
        targetEmployeeId = props.employeeData?.id;
    } else if (props.isAdminMode) {
        targetEmployeeId = searchedEmployee.value?.id;
    } else {
        targetEmployeeId = user.value?.id;
    }

    // Map payload to snake_case for backend validation
    const payload = {
        leave_type: form.value.leaveType === 'Others' ? form.value.otherLeaveType : form.value.leaveType,
        category: form.value.category,
        request_type: form.value.requestType,
        from_date: form.value.fromDate,
        to_date: form.value.toDate || form.value.fromDate,
        days_taken: form.value.numberOfDays,
        start_time: form.value.startTime,
        end_time: form.value.endTime,
        reason: form.value.reason,
        date_filed: form.value.dateFiled,
        is_paid: form.value.isPaid,
        days_paid: form.value.isPaid ? form.value.daysPaid : 0,
        employee_id: targetEmployeeId,
        attachment: form.value.attachment,
        additional_details: form.value.additionalDetails
    };

    try {
        if (props.isEdit) {
            await new Promise((resolve, reject) => emit('update', payload, resolve, reject));
        } else {
            await new Promise((resolve, reject) => emit('submit', payload, resolve, reject));
        }
        // Only close on success
        closeModal();
    } catch (errMsg) {
        // Show the backend error message in the form
        setError(errMsg || 'Submission failed. Please try again.');
    } finally {
        loading.value = false;
    }
};

// Watchers for date sync
watch(() => form.value.requestType, (newType) => {
    if (newType === 'Halfday' || newType === 'Undertime') {
        if (form.value.fromDate) form.value.toDate = form.value.fromDate;
    }
});

watch(() => form.value.fromDate, (val) => {
    if ((form.value.requestType === 'Halfday' || form.value.requestType === 'Undertime') && val) {
        form.value.toDate = val;
    }
});

watch([() => form.value.fromDate, () => form.value.toDate, () => form.value.requestType], ([start, end, type]) => {
    if (start && end) {
        const s = new Date(start);
        const e = new Date(end);
        s.setHours(0, 0, 0, 0);
        e.setHours(0, 0, 0, 0);

        if (s > e) {
            form.value.numberOfDays = 0;
            return;
        }

        // Count days excluding Sundays
        let diffDays = 0;
        let cur = new Date(s);
        while (cur <= e) {
            if (cur.getDay() !== 0) { // 0 is Sunday
                diffDays++;
            }
            cur.setDate(cur.getDate() + 1);
        }
        
        if (type === 'Halfday') form.value.numberOfDays = 0.5;
        else if (type === 'Undertime') form.value.numberOfDays = 0;
        else form.value.numberOfDays = diffDays;

        if (form.value.isPaid) form.value.daysPaid = form.value.numberOfDays;
    }
});

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        if (file.size > 2 * 1024 * 1024) {
            alert('File size exceeds 2MB limit.');
            clearFile();
            return;
        }
        form.value.attachment = file;
        attachmentName.value = file.name;
    }
};

const clearFile = () => {
    form.value.attachment = null;
    attachmentName.value = '';
    if (fileInput.value) fileInput.value.value = '';
};
</script>

<style scoped>
.animate-fade-in { animation: fadeIn 0.2s ease-out; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

.slide-down-enter-active, .slide-down-leave-active { transition: all 0.3s ease-out; max-height: 200px; overflow: hidden; }
.slide-down-enter-from, .slide-down-leave-to { opacity: 0; max-height: 0; transform: translateY(-10px); }
</style>
