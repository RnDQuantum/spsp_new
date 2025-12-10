import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
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
            host: 'localhost', // Browser should connect to localhost
        },
        cors: true,
    },
});