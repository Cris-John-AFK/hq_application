<template>
    <Transition
        enter-active-class="transition duration-700 ease-out"
        enter-from-class="opacity-0 translate-y-10"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-500 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-10"
    >
        <div 
            v-if="isVisible" 
            class="fixed bottom-12 left-1/2 -translate-x-1/2 z-[9999] pointer-events-none filter drop-shadow-2xl"
        >
            <div class="flex flex-col items-center gap-3">
                <!-- Text Label with Strong Contrast -->
                <div class="bg-gray-900/90 backdrop-blur-xl px-6 py-2.5 rounded-full border border-gray-700/50 shadow-2xl flex items-center gap-3">
                    <span class="text-xs font-black text-white uppercase tracking-[0.25em]">Scroll Down</span>
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-teal-500"></span>
                    </span>
                </div>
                
                <!-- Large Animated Mouse Icon -->
                <div class="relative">
                    <div class="w-8 h-12 border-2 border-gray-800 rounded-full flex justify-center p-1 bg-white/90 backdrop-blur-md shadow-xl ring-4 ring-white/30">
                        <div class="w-1.5 h-3 bg-teal-600 rounded-full animate-bounce mt-1.5 shadow-sm"></div>
                    </div>
                    <!-- Glow effect -->
                    <div class="absolute -inset-4 bg-teal-500/20 blur-xl rounded-full -z-10 animate-pulse"></div>
                </div>

                <!-- Prominent Down Arrows -->
                <div class="flex flex-col -mt-2 space-y-[-8px]">
                    <i class="pi pi-angle-down text-xl text-teal-600 animate-pulse drop-shadow-md font-bold"></i>
                    <i class="pi pi-angle-down text-xl text-teal-600 animate-pulse delay-100 drop-shadow-md font-bold opacity-70"></i>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';

const isVisible = ref(false);

const checkScroll = () => {
    const mainContent = document.querySelector('main');
    if (!mainContent) return;

    // Use main content for scroll calculation instead of window if scrolling happens there
    // But mostly we used overflow-y-auto on main, so we check that element
    const isScrollable = mainContent.scrollHeight > mainContent.clientHeight + 50; // 50px buffer
    const isScrolledToTop = mainContent.scrollTop < 100;

    // Show if scrollable AND currently near top
    isVisible.value = isScrollable && isScrolledToTop;
};

onMounted(() => {
    const mainContent = document.querySelector('main');
    
    // Initial check with delay for dynamic content
    setTimeout(checkScroll, 1000);
    setTimeout(checkScroll, 3000); // Secondary check for slow loads

    if (mainContent) {
        mainContent.addEventListener('scroll', checkScroll);
    }
    window.addEventListener('resize', checkScroll);
});

onUnmounted(() => {
    const mainContent = document.querySelector('main');
    if (mainContent) {
        mainContent.removeEventListener('scroll', checkScroll);
    }
    window.removeEventListener('resize', checkScroll);
});
</script>

<style scoped>
.delay-75 {
    animation-delay: 0.15s;
}
</style>
