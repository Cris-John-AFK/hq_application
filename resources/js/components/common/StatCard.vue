<template>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center hover:shadow-md transition-shadow">
        <template v-if="loading">
            <div class="w-12 h-12 rounded-full bg-gray-100 animate-pulse mr-4"></div>
            <div class="space-y-2 flex-1">
                <div class="h-6 w-12 bg-gray-100 animate-pulse rounded"></div>
                <div class="h-3 w-20 bg-gray-100 animate-pulse rounded"></div>
            </div>
        </template>
        <template v-else>
            <div :class="`p-4 rounded-full ${iconBgClass} ${iconTextClass} mr-4`">
                <i :class="`pi ${icon} text-xl`"></i>
            </div>
            <div>
                <h3 class="text-2xl font-bold text-gray-800">{{ value }}</h3>
                <p class="text-gray-500 text-sm font-medium">{{ label }}</p>
            </div>
        </template>
    </div>
</template>

<script setup>
    defineProps({
        icon: String,
        value: [String, Number],
        label: String,
        loading: Boolean,
        iconBgClass: {
            type: String,
            default: 'bg-teal-50'
        },
        iconTextClass: {
            type: String,
            default: 'text-teal-600'
        }
    });
</script>
