import {
    defineConfig,
    loadEnv
} from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
                refresh: true,
            }),
            tailwindcss(),
        ],
        server: {
            host: '0.0.0.0', // Listen on all interfaces
            port: 5173,      // Explicitly set port
            hmr: {
                host: env.VITE_HMR_HOST || 'localhost', // Browser should connect to this host
            },
            cors: true,
        },
    };
});