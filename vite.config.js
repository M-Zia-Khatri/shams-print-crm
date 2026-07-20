import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/item-entries-create.js',
                'resources/js/item-entries-index.js',
                'resources/js/expenses-create.js',
                'resources/js/employee-shifts-create.js',
                'resources/js/employee-paid-laberi-bulk.js',
                'resources/js/employee-daily-laberi-create.js',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
        VitePWA({
            registerType: 'prompt',
            injectRegister: false,
            manifest: {
                name: 'Shams Print CRM',
                short_name: 'Shams CRM',
                description: 'Shams Print CRM — offline-capable print shop management',
                theme_color: '#0A0B0D',
                background_color: '#0A0B0D',
                display: 'standalone',
                orientation: 'any',
                start_url: '/',
                scope: '/',
                icons: [
                    {
                        src: '/icons/icon-192.png',
                        sizes: '192x192',
                        type: 'image/png',
                        purpose: 'any',
                    },
                    {
                        src: '/icons/icon-512.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'any',
                    },
                    {
                        src: '/icons/icon-512.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'maskable',
                    },
                ],
            },
            includeAssets: [
                'favicon.svg',
                'icons/icon-192.png',
                'icons/icon-512.png',
                'offline.html',
                'manifest.json',
                'sw-offline-sync.js',
            ],
            workbox: {
                // Ensures a newly-activated SW claims already-open clients
                // immediately (no extra full reload needed before it starts
                // intercepting fetches/navigations). Does not affect the
                // prompt-based update UX — skipWaiting stays unset, so an
                // existing controlling SW is never force-replaced without
                // the user clicking "Reload" in pwa-update-banner.
                clientsClaim: true,
                importScripts: ['/sw-offline-sync.js'],
                navigateFallback: '/offline.html',
                navigateFallbackDenylist: [
                    /^\/login/,
                    /^\/logout/,
                    /^\/employee-payroll/,
                    /^\/employee-paid-laberi/,
                    /\/paid-laberi/,
                    /^\/up/,
                    /^\/healthz/,
                ],
                runtimeCaching: [
                    {
                        // Auth, payroll, paid-laberi, and all mutating requests — never cache.
                        urlPattern: ({ url, request }) => {
                            const path = url.pathname;
                            const method = request.method.toUpperCase();

                            if (['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)) {
                                return true;
                            }

                            if (/^\/login\/?$/.test(path) || /^\/logout\/?$/.test(path)) {
                                return true;
                            }

                            if (path.startsWith('/employee-payroll')) {
                                return true;
                            }

                            if (path.startsWith('/employee-paid-laberi')) {
                                return true;
                            }

                            if (/\/paid-laberi(\/|$)/.test(path)) {
                                return true;
                            }

                            return false;
                        },
                        handler: 'NetworkOnly',
                    },
                    {
                        // Hashed Vite assets, fonts, icons, images — Cache First.
                        urlPattern: ({ url }) => {
                            const path = url.pathname;

                            if (path.startsWith('/build/assets/')) {
                                return true;
                            }

                            return (
                                /\.(?:css|js|woff2?|ttf|eot|png|svg|ico|webp|jpe?g|avif)$/i.test(path) &&
                                !/sw\.js$/i.test(path) &&
                                !/workbox-/i.test(path)
                            );
                        },
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'static-assets',
                            expiration: {
                                maxEntries: 128,
                                maxAgeSeconds: 60 * 60 * 24 * 30,
                            },
                        },
                    },
                    {
                        // Dashboard, list pages, employee show, payment JSON — Network First.
                        urlPattern: ({ url, request }) => {
                            if (request.method.toUpperCase() !== 'GET') {
                                return false;
                            }

                            const path = url.pathname;

                            if (path === '/' || path === '') {
                                return true;
                            }

                            if (path === '/item-entries' || path === '/item-entries/') {
                                return true;
                            }

                            if (path === '/expenses' || path === '/expenses/') {
                                return true;
                            }

                            if (path === '/employees' || path === '/employees/') {
                                return true;
                            }

                            if (/^\/employees\/\d+\/?$/.test(path)) {
                                return true;
                            }

                            if (path.startsWith('/item-payment-receiveds')) {
                                return true;
                            }

                            return false;
                        },
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'network-first-pages',
                            networkTimeoutSeconds: 5,
                            expiration: {
                                maxEntries: 64,
                                maxAgeSeconds: 60 * 60 * 24,
                            },
                        },
                    },
                    {
                        // Other GET pages (create/edit forms, etc.) — Stale While Revalidate.
                        urlPattern: ({ url, request }) => {
                            if (request.method.toUpperCase() !== 'GET') {
                                return false;
                            }

                            const path = url.pathname;

                            if (path.startsWith('/login') || path.startsWith('/logout')) {
                                return false;
                            }

                            if (path.startsWith('/employee-payroll')) {
                                return false;
                            }

                            if (path.startsWith('/employee-paid-laberi')) {
                                return false;
                            }

                            if (/\/paid-laberi(\/|$)/.test(path)) {
                                return false;
                            }

                            if (path.startsWith('/build/')) {
                                return false;
                            }

                            if (
                                /\.(?:css|js|woff2?|ttf|eot|png|svg|ico|webp|jpe?g|avif|json|webmanifest)$/i.test(
                                    path,
                                )
                            ) {
                                return false;
                            }

                            return true;
                        },
                        handler: 'StaleWhileRevalidate',
                        options: {
                            cacheName: 'swr-pages',
                            expiration: {
                                maxEntries: 64,
                                maxAgeSeconds: 60 * 60 * 24,
                            },
                        },
                    },
                ],
            },
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});