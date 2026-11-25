import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig({
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
        },
    },
    server: {
    host: 'localhost',      // or '0.0.0.0' for all interfaces
    port: 5173,             // your dev server port
    hmr: {
        host: 'localhost',
        protocol: 'ws',
        },
    },
    plugins: [
    laravel({
        input: 'resources/js/app.js',
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
    ],
});
