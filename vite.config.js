import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),
    ],
    build: {
        minify: false,
        reportCompressedSize: false,
        sourcemap: true,
        rollupOptions: {
        // 不要な chunk 分割を抑える
        output: {
            manualChunks: undefined,
        },
        },
    },
    server: {
        watch: {
        ignored: ['**/vendor/**', '**/node_modules/**'],
        },
    },    
});