import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            // These are the two frontend entry points Laravel injects through @vite(...).
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            // The bundled font plugin keeps the project typography predictable.
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        // DDEV exposes the Vite dev server through the local project hostname.
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        cors: true,
        // Use the secure project origin so hot mode does not mix http and https.
        origin: 'https://mics-hub.ddev.site:5173',
        hmr: {
            host: 'mics-hub.ddev.site',
            protocol: 'wss',
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
