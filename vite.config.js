import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
    server: {
        host: true, // Listen on all network interfaces
        hmr: {
            host: '10.10.10.8',
        },
        proxy: {
            '/api': {
                target: 'http://10.10.10.8:8000',
                changeOrigin: true,
            },
            '/login': {
                target: 'http://10.10.10.8:8000',
                changeOrigin: true,
            },
            '/logout': {
                target: 'http://10.10.10.8:8000',
                changeOrigin: true,
            },
            '/sanctum': {
                target: 'http://10.10.10.8:8000',
                changeOrigin: true,
            },
            '/vue': {
                target: 'http://10.10.10.8:8000',
                changeOrigin: true,
            },
        }
    }
});
