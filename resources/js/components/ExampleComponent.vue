<template>
    <div class="p-6 bg-white rounded-lg shadow-md">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">Hello from Vue!</h1>
        <p class="text-gray-600">
            This is an example component running in a Laravel application with PostgreSQL.
        </p>
        <div class="mt-4">
            <button @click="count++" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                Count is: {{ count }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const count = ref(0);
</script>
