import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { VitePWA } from 'vite-plugin-pwa';
import path from 'path';

export default defineConfig({
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
        },
    },
    server: {
        host: '127.0.0.1',
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
        VitePWA({
            registerType: 'autoUpdate',
            outDir: 'public',
            buildBase: '/',
            scope: '/',
            injectRegister: 'auto',

            // ── Service Worker ─────────────────────────────────────
            workbox: {
                skipWaiting: true,
                clientsClaim: true,
                cleanupOutdatedCaches: true,
                globDirectory: 'public',
                globPatterns: ['**/*.{js,css,html,ico,png,svg,woff2,webp}'],
                navigateFallback: '/offline',
                navigateFallbackDenylist: [/^\/api\//],

                // Runtime Caching
                runtimeCaching: [
                    {
                        // Cache API calls (NetworkFirst — luôn lấy mới, fallback cache)
                        urlPattern: /^\/api\/.*/i,
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'htms-api-cache',
                            expiration: {
                                maxEntries: 50,
                                maxAgeSeconds: 60 * 60 * 24, // 24h
                            },
                            networkTimeoutSeconds: 10,
                        },
                    },
                    {
                        // Cache ảnh & static assets (CacheFirst)
                        urlPattern: /\.(?:png|jpg|jpeg|svg|gif|webp|ico)$/i,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'htms-image-cache',
                            expiration: {
                                maxEntries: 100,
                                maxAgeSeconds: 60 * 60 * 24 * 30, // 30 ngày
                            },
                        },
                    },
                    {
                        // Cache Google Fonts (StaleWhileRevalidate)
                        urlPattern: /^https:\/\/fonts\.googleapis\.com\/.*/i,
                        handler: 'StaleWhileRevalidate',
                        options: {
                            cacheName: 'google-fonts-cache',
                        },
                    },
                ],
            },

            // ── Manifest ───────────────────────────────────────────
            manifest: {
                name: 'HTMS - Hệ Thống Quản Lý Hội Thánh',
                short_name: 'HTMS',
                description: 'Ứng dụng quản trị và điều hành trung tâm dành cho Hội Thánh',
                theme_color: '#10b981',
                background_color: '#f8fafc',
                display: 'standalone',
                orientation: 'portrait',
                start_url: '/?source=pwa',
                id: '/?source=pwa',
                lang: 'vi',

                // ── Icons ─────────────────────────────────────────────
                icons: [
                    {
                        src: '/icon-192.png',
                        sizes: '192x192',
                        type: 'image/png',
                        purpose: 'any',
                    },
                    {
                        src: '/icon-512.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'any',
                    },
                    {
                        // Maskable icon — hiển thị đúng trên tất cả màn hình Android
                        src: '/icon-512.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'maskable',
                    },
                ],

                // ── Shortcuts (Home Screen quick actions) ─────────────
                shortcuts: [
                    {
                        name: 'Điểm Danh',
                        short_name: 'Điểm danh',
                        description: 'Mở trang điểm danh ban ngành',
                        url: '/portal/attendance?source=pwa-shortcut',
                        icons: [{ src: '/icon-192.png', sizes: '192x192' }],
                    },
                    {
                        name: 'Bảng Điều Khiển',
                        short_name: 'Dashboard',
                        description: 'Mở bảng điều khiển chính',
                        url: '/dashboard?source=pwa-shortcut',
                        icons: [{ src: '/icon-192.png', sizes: '192x192' }],
                    },
                ],

                // ── Categories & Display Overrides ────────────────────
                categories: ['productivity', 'utilities'],
                display_override: ['window-controls-overlay', 'standalone', 'browser'],
                prefer_related_applications: false,
            },
        }),
    ],
});
