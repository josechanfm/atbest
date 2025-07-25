import { fileURLToPath, URL } from 'node:url';
import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import i18n from 'laravel-vue-i18n/vite'; 


export default defineConfig(({ mode }) => {

    process.env = {...process.env, ...loadEnv(mode, process.cwd())};
    return {
        optimizeDeps: { include: ["quill"]},
        server: {
            host: '0.0.0.0',
            hmr: {
                host: process.env.VITE_HOST ?? '127.0.0.1',
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
            i18n() 
        ],

    }
});
