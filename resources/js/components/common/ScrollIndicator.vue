<template>
    <Transition
        enter-active-class="transition duration-500 ease-out"
        enter-from-class="opacity-0 translate-y-4"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-300 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-4"
    >
        <div 
            v-if="isVisible" 
            class="fixed bottom-8 left-1/2 -translate-x-1/2 z-50 pointer-events-none"
        >
            <div class="flex flex-col items-center gap-2">
                <!-- Text Label -->
                <span class="text-[10px] font-black text-teal-600 uppercase tracking-[0.3em] bg-white/80 backdrop-blur-md px-3 py-1 rounded-full border border-teal-100 shadow-sm">
                    Scroll for more
                </span>
                
                <!-- Animated Mouse/Arrow Icon -->
                <div class="w-6 h-10 border-2 border-teal-500/30 rounded-full flex justify-center p-1 bg-white/50 backdrop-blur-sm shadow-lg">
                    <div class="w-1 h-2 bg-teal-500 rounded-full animate-bounce mt-1"></div>
                </div>

                <!-- Down Arrows -->
                <div class="flex flex-col -mt-1 opacity-50">
                    <i class="pi pi-chevron-down text-[8px] text-teal-600 animate-pulse"></i>
                    <i class="pi pi-chevron-down text-[8px] text-teal-600 animate-pulse delay-75 -mt-1"></i>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const isVisible = ref(true);

const checkScroll = () => {
    // Only show if the page is scrollable
    const scrollableHeight = document.documentElement.scrollHeight - window.innerHeight;
    
    // If we've scrolled more than 100px or reached near the bottom, hide it
    if (window.scrollY > 100 || scrollableHeight <= 0 || (window.innerHeight + window.scrollY) >= document.documentElement.scrollHeight - 50) {
        isVisible.value = false;
    } else {
        isVisible.value = true;
    }
};

onMounted(() => {
    // Initial check
    setTimeout(checkScroll, 500); // Small delay to ensure DOM is ready
    window.addEventListener('scroll', checkScroll);
    window.addEventListener('resize', checkScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', checkScroll);
    window.removeEventListener('resize', checkScroll);
});
</script>

<style scoped>
.delay-75 {
    animation-delay: 0.15s;
}
</style>
