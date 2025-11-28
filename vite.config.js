import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import { VitePWA } from 'vite-plugin-pwa'; // <--- Import this

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.jsx',
            refresh: true,
        }),
        react(),
        // --- PWA CONFIGURATION ---
        VitePWA({
            registerType: 'autoUpdate',
            devOptions: {
                enabled: true // Allows testing PWA in localhost
            },
            manifest: {
                name: 'RTO Data Hub',
                short_name: 'RTO Hub',
                description: 'RTO Management System for Agents',
                theme_color: '#0d6efd', // Primary Blue Color
                background_color: '#ffffff',
                display: 'standalone', // Removes browser URL bar (Native App feel)
                orientation: 'portrait',
                scope: '/',
                start_url: '/',
                icons: [
                    {
                        src: '/icons/pwa-192x192.png',
                        sizes: '192x192',
                        type: 'image/png'
                    },
                    {
                        src: '/icons/pwa-512x512.png',
                        sizes: '512x512',
                        type: 'image/png'
                    }
                ]
            }
        })
    ],
});
