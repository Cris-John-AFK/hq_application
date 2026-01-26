<template>
    <div class="fixed bottom-6 right-6 z-[100] flex flex-col items-end pointer-events-none">
        
        <!-- Chat Window -->
        <Transition
            enter-active-class="transition duration-300 ease-out transform"
            enter-from-class="opacity-0 translate-y-10 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition duration-200 ease-in transform"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-10 scale-95"
        >
            <div 
                v-if="isOpen" 
                class="bg-white pointer-events-auto rounded-3xl shadow-2xl border border-teal-100 w-[400px] h-[600px] flex flex-col overflow-hidden mb-6 filter drop-shadow-[0_25px_25px_rgba(20,184,166,0.15)]"
            >
                <!-- AI Header -->
                <div class="bg-gradient-to-r from-slate-900 to-teal-900 p-5 flex items-center justify-between border-b border-white/10">
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-teal-400 to-emerald-600 flex items-center justify-center text-white shadow-lg rotate-3 group animate-pulse">
                                <i class="pi pi-sparkles text-xl"></i>
                            </div>
                            <span class="absolute -top-1 -right-1 flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-teal-500"></span>
                            </span>
                        </div>
                        <div>
                            <h3 class="font-black text-white text-md tracking-tight">HatQ AI Assistant</h3>
                            <p class="text-[10px] text-teal-300 font-bold uppercase tracking-widest flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-teal-400 animate-pulse"></span>
                                Core Intelligence Active
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="clearChat" title="Clear History" class="text-white/40 hover:text-white transition-colors p-2 rounded-xl hover:bg-white/10">
                            <i class="pi pi-trash text-xs"></i>
                        </button>
                        <button @click="isOpen = false" class="text-white/40 hover:text-white transition-colors p-2 rounded-xl hover:bg-white/10">
                            <i class="pi pi-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Messages Area -->
                <div ref="scrollArea" class="flex-1 bg-slate-50 p-5 overflow-y-auto space-y-6 scrollbar-hide">
                    <!-- Default AI Greeting -->
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl bg-teal-600 flex-shrink-0 flex items-center justify-center text-white shadow-sm">
                            <i class="pi pi-sparkles text-sm"></i>
                        </div>
                        <div class="flex flex-col gap-1.5 max-w-[85%]">
                            <div class="bg-white p-4 rounded-2xl rounded-tl-none border border-teal-50 shadow-sm text-sm text-gray-700 leading-relaxed">
                                <p class="font-bold text-teal-600 mb-1">Hello {{ authStore.user?.name }}! 👋</p>
                                <p>I am your HatQ AI. I have full access to company records, leave balances, and department performance. Ask me anything about the system!</p>
                            </div>
                        </div>
                    </div>

                    <!-- Conversation History -->
                    <div v-for="msg in messages" :key="msg.id" class="flex w-full" :class="msg.type === 'user' ? 'justify-end' : 'justify-start'">
                        <div class="flex items-start gap-2.5 max-w-[85%] animate-fade-in-up">
                            <!-- AI Icon for AI messages -->
                            <div v-if="msg.type === 'ai'" class="w-8 h-8 rounded-lg bg-slate-800 flex-shrink-0 flex items-center justify-center text-teal-400 text-xs shadow-sm mt-1">
                                <i class="pi pi-bolt"></i>
                            </div>

                            <div 
                                class="rounded-2xl p-4 text-sm shadow-sm transition-all"
                                :class="[
                                    msg.type === 'user' 
                                        ? 'bg-teal-600 text-white rounded-tr-none border-teal-700' 
                                        : 'bg-white text-gray-800 rounded-tl-none border-gray-200'
                                ]"
                            >
                                <p class="leading-relaxed whitespace-pre-wrap" v-html="formatMessage(msg.message)"></p>
                                <div class="flex items-center justify-end gap-1 mt-2 opacity-50">
                                    <span class="text-[9px] font-bold uppercase">{{ formatTime(msg.created_at) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- AI Typing Indicator -->
                    <div v-if="isTyping" class="flex items-start gap-3 animate-pulse">
                        <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-teal-400">
                            <i class="pi pi-spin pi-spinner text-xs"></i>
                        </div>
                        <div class="bg-white px-4 py-3 rounded-2xl rounded-tl-none border border-gray-100 shadow-sm">
                            <div class="flex gap-1">
                                <div class="w-1.5 h-1.5 bg-teal-400 rounded-full animate-bounce"></div>
                                <div class="w-1.5 h-1.5 bg-teal-400 rounded-full animate-bounce [animation-delay:0.2s]"></div>
                                <div class="w-1.5 h-1.5 bg-teal-400 rounded-full animate-bounce [animation-delay:0.4s]"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Input Area -->
                <div class="p-5 bg-white border-t border-gray-100">
                    <div class="relative flex items-center gap-3 bg-gray-50 rounded-2xl p-2 border border-gray-200 focus-within:border-teal-500 focus-within:ring-2 focus-within:ring-teal-500/10 transition-all">
                        
                        <!-- Ghost Suggestion Layer -->
                        <div v-if="suggestionText && userInput" class="absolute left-5 top-1/2 -translate-y-1/2 pointer-events-none text-sm font-medium text-gray-300">
                            <span class="opacity-0">{{ userInput }}</span><span>{{ suggestionText.slice(userInput.length) }}</span>
                        </div>

                        <input 
                            v-model="userInput" 
                            @keyup.enter="handleSend"
                            @keydown.tab.prevent="applySuggestion"
                            @input="updateSuggestion"
                            type="text" 
                            placeholder="Ask about leave, stats, or navigate..." 
                            class="flex-1 bg-transparent text-gray-800 text-sm px-3 py-2 outline-none font-medium z-10"
                            :disabled="isTyping"
                        />
                        <button 
                            @click="handleSend"
                            :disabled="!userInput.trim() || isTyping"
                            class="w-10 h-10 bg-teal-600 hover:bg-teal-700 text-white rounded-xl flex items-center justify-center transition-all shadow-md disabled:opacity-30 disabled:cursor-not-allowed z-10"
                        >
                            <i class="pi pi-send text-xs"></i>
                        </button>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button 
                            v-for="hint in ['My SIL credits', 'Who is on leave?', 'System Stats']" 
                            :key="hint"
                            @click="userInput = hint; handleSend()"
                            class="text-[9px] font-black uppercase tracking-wider text-teal-600 bg-teal-50 hover:bg-teal-100 px-2.5 py-1.5 rounded-lg border border-teal-100/50 transition-colors"
                        >
                            {{ hint }}
                        </button>
                    </div>
                    <p v-if="suggestionText && userInput" class="text-[9px] text-gray-400 mt-2 ml-1">Tip: Press <span class="bg-gray-100 px-1 border border-gray-200 rounded">TAB</span> to autocomplete</p>
                </div>
            </div>
        </Transition>

        <!-- Main Toggle Floating Agent -->
        <button 
            @click="isOpen = !isOpen" 
            class="pointer-events-auto w-16 h-16 bg-slate-900 rounded-2xl shadow-[0_15px_35px_rgba(0,0,0,0.4)] flex items-center justify-center text-white hover:bg-teal-600 transition-all duration-500 hover:scale-110 active:scale-95 group ring-4 ring-white relative overflow-hidden"
        >
            <!-- AI Pulse Scanline -->
            <div v-if="!isOpen" class="absolute inset-0 bg-gradient-to-t from-teal-500/20 to-transparent -translate-y-full group-hover:animate-scanline"></div>

            <div class="relative">
                <i v-if="!isOpen" class="pi pi-sparkles text-2xl group-hover:scale-125 transition-transform duration-500"></i>
                <i v-else class="pi pi-chevron-down text-xl"></i>
            </div>
            
            <!-- AI Pulse Badge -->
            <span v-if="!isOpen" class="absolute -top-1 -right-1 w-5 h-5 bg-teal-500 border-4 border-white rounded-full"></span>
        </button>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch, computed } from 'vue';
import { useAuthStore } from '../../stores/auth';
import axios from 'axios';

const authStore = useAuthStore();
const isOpen = ref(false);
const userInput = ref('');
const messages = ref([]);
const isTyping = ref(false);
const scrollArea = ref(null);
const suggestionText = ref('');

const phrases = [
    'how to file a leave request',
    'how to check my sil balance',
    'how to see my attendance records',
    'who is on leave today',
    'what are the company holidays',
    'my current sil credits',
    'remaining leave balance',
    'system analytics and stats',
    'top performing departments'
];

const updateSuggestion = () => {
    if (!userInput.value.trim()) {
        suggestionText.value = '';
        return;
    }
    const match = phrases.find(p => p.startsWith(userInput.value.toLowerCase()));
    suggestionText.value = match || '';
};

const applySuggestion = () => {
    if (suggestionText.value) {
        userInput.value = suggestionText.value;
        suggestionText.value = '';
    }
};

const scrollToBottom = async () => {
    await nextTick();
    if (scrollArea.value) {
        scrollArea.value.scrollTop = scrollArea.value.scrollHeight;
    }
};

const fetchChatHistory = async () => {
    try {
        const response = await axios.get('/api/ai/chat');
        messages.value = response.data;
        scrollToBottom();
    } catch (err) {
        console.error('Failed to fetch chat history');
    }
};

const handleSend = async () => {
    if (!userInput.value.trim() || isTyping.value) return;

    const messageContent = userInput.value;
    userInput.value = '';
    
    // Add User Message Optimistically
    messages.value.push({
        id: Date.now(),
        type: 'user',
        message: messageContent,
        created_at: new Date().toISOString()
    });
    
    isTyping.value = true;
    scrollToBottom();

    try {
        const response = await axios.post('/api/ai/chat', { message: messageContent });
        
        // Wait a small bit to simulate AI thinking
        setTimeout(() => {
            messages.value.push(response.data.ai_message);
            isTyping.value = false;
            scrollToBottom();
        }, 600);

    } catch (err) {
        console.error('AI Processing Error');
        isTyping.value = false;
    }
};

const clearChat = async () => {
    if (!confirm('Discard AI conversation history?')) return;
    await axios.delete('/api/ai/chat');
    messages.value = [];
};

const formatMessage = (txt) => {
    // Basic Markdown support for bold
    return txt.replace(/\*\*(.*?)\*\*/g, '<strong class="text-teal-600">$1</strong>');
};

const formatTime = (iso) => {
    return new Date(iso).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

watch(isOpen, (val) => {
    if (val) fetchChatHistory();
});

onMounted(() => {
    // Optionally fetch history on mount if you want the unread count feature
});
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

@keyframes fade-in-up {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-up {
    animation: fade-in-up 0.4s ease-out forwards;
}
@keyframes scanline {
    from { transform: translateY(-100%); }
    to { transform: translateY(100%); }
}
.animate-scanline {
    animation: scanline 2s linear infinite;
}
</style>
