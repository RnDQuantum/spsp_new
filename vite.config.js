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
            cors: true,
            host: '0.0.0.0', // This allows connections from outside a container
            hmr: {
                host: 'localhost', // This tells the browser to connect via localhost
            }
        },
    };
});