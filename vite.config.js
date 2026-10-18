import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'resources/js/widget.jsx'
            ],
            refresh: true,
        }),
        react(),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0', // Permite que dispositivos en tu red (celular) se conecten
        cors: true,      // Soluciona los bloqueos rojos de CORS en la consola
        hmr: {
            host: '192.168.1.4', // Obliga a los scripts a usar tu IP real
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});