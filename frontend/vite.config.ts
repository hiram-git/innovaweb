import { defineConfig } from 'vitest/config'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'
import { VitePWA } from 'vite-plugin-pwa'
import path from 'path'

export default defineConfig({
  plugins: [
    react(),
    tailwindcss(),
    VitePWA({
      registerType: 'autoUpdate',
      injectRegister: 'auto',
      workbox: {
        // Precachear todos los assets estáticos del build
        globPatterns: ['**/*.{js,css,html,ico,png,svg,woff2}'],
        // Estrategias de caché por tipo de recurso
        runtimeCaching: [
          {
            // API de solo lectura: StaleWhileRevalidate (rápido + actualiza en BG)
            urlPattern: /^https?:\/\/innovanew\.test\/api\/v1\/(clientes|inventario|presupuestos)/,
            handler: 'StaleWhileRevalidate',
            options: {
              cacheName: 'api-read-cache',
              expiration: { maxEntries: 200, maxAgeSeconds: 60 * 30 }, // 30 min
              cacheableResponse: { statuses: [0, 200] },
            },
          },
          {
            // Facturas: NetworkFirst (siempre intenta red primero)
            urlPattern: /^https?:\/\/innovanew\.test\/api\/v1\/facturas/,
            handler: 'NetworkFirst',
            options: {
              cacheName: 'facturas-cache',
              networkTimeoutSeconds: 10,
              expiration: { maxEntries: 100, maxAgeSeconds: 60 * 60 * 24 }, // 24h
              cacheableResponse: { statuses: [0, 200] },
            },
          },
          {
            // Assets estáticos: CacheFirst
            urlPattern: /\.(png|jpg|jpeg|svg|ico|woff2)$/,
            handler: 'CacheFirst',
            options: {
              cacheName: 'assets-cache',
              expiration: { maxEntries: 100, maxAgeSeconds: 60 * 60 * 24 * 30 }, // 30 días
            },
          },
        ],
        // Background Sync para facturas offline
        offlineGoogleAnalytics: false,
      },
      manifest: {
        name: 'InnovaWeb — Facturación Electrónica',
        short_name: 'InnovaWeb',
        description: 'Sistema de Facturación Electrónica para Panamá',
        theme_color: '#1e40af',
        background_color: '#0f172a',
        display: 'standalone',
        start_url: '/',
        scope: '/',
        lang: 'es',
        orientation: 'any',
        categories: ['business', 'finance', 'productivity'],
        icons: [
          { src: '/icons/icon-72x72.png',   sizes: '72x72',   type: 'image/png' },
          { src: '/icons/icon-96x96.png',   sizes: '96x96',   type: 'image/png' },
          { src: '/icons/icon-128x128.png', sizes: '128x128', type: 'image/png' },
          { src: '/icons/icon-144x144.png', sizes: '144x144', type: 'image/png' },
          { src: '/icons/icon-192x192.png', sizes: '192x192', type: 'image/png', purpose: 'any' },
          { src: '/icons/icon-512x512.png', sizes: '512x512', type: 'image/png', purpose: 'any maskable' },
        ],
        shortcuts: [
          {
            name: 'Nueva Factura',
            short_name: 'Factura',
            url: '/facturas/nueva',
            icons: [{ src: '/icons/shortcut-factura.png', sizes: '96x96' }],
          },
          {
            name: 'Nuevo Cliente',
            short_name: 'Cliente',
            url: '/clientes/nuevo',
            icons: [{ src: '/icons/shortcut-cliente.png', sizes: '96x96' }],
          },
        ],
      },
      devOptions: {
        enabled: true,  // Activar SW en desarrollo para pruebas
        type: 'module',
      },
    }),
  ],
  resolve: {
    alias: { '@': path.resolve(__dirname, './src') },
  },
  server: {
    port: 5173,
    proxy: {
      '/api': {
        target: 'http://innovanew.test',
        changeOrigin: true,
        secure: false,
      },
    },
  },
  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: ['./src/test/setup.ts'],
    coverage: {
      provider: 'v8',
      reporter: ['text', 'html'],
      include: ['src/**/*.{ts,tsx}'],
      exclude: ['src/test/**', 'src/main.tsx', 'src/App.tsx'],
    },
  },
})
