import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/addon.js',
            publicDirectory: 'resources/dist',
        }),
        vue(),
    ],
});
