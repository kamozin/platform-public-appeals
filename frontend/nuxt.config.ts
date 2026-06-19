export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  ssr: true,
  srcDir: 'app/',

  modules: [
    '@nuxt/eslint',
    '@vite-pwa/nuxt',
  ],

  css: [
    '~/assets/styles/main.css',
  ],

  devtools: {
    enabled: false,
  },

  runtimeConfig: {
    apiInternalBase: process.env.NUXT_API_INTERNAL_BASE || 'http://nginx:8080/api/v1',
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || '/api/v1',
      siteUrl: process.env.NUXT_PUBLIC_SITE_URL || 'https://rukadobra.localhost',
      yandexMapsApiKey: process.env.NUXT_PUBLIC_YANDEX_MAPS_API_KEY || '',
    },
  },

  typescript: {
    strict: true,
  },

  routeRules: {
    '/': { prerender: true },
    '/privacy': { prerender: true },
    '/agreement': { prerender: true },
    '/book': { prerender: true },
    '/categories': { swr: 300 },
    '/news': { swr: 300 },
    '/news/**': { swr: 300 },
    '/appeals': { swr: 300 },
    '/appeals/**': { swr: 300 },
    '/login': { ssr: false },
    '/register': { ssr: false },
    '/verification': { ssr: false },
    '/password-reset': { ssr: false },
    '/appeal/new': { ssr: false },
    '/app': { ssr: false, headers: { 'X-Robots-Tag': 'noindex, nofollow' } },
    '/app/**': { ssr: false, headers: { 'X-Robots-Tag': 'noindex, nofollow' } },
    '/offline': { prerender: true, headers: { 'X-Robots-Tag': 'noindex, nofollow' } },
    '/dashboard': { ssr: false },
    '/dashboard/**': { ssr: false },
    '/admin': { ssr: false, headers: { 'X-Robots-Tag': 'noindex, nofollow' } },
    '/admin/**': { ssr: false, headers: { 'X-Robots-Tag': 'noindex, nofollow' } },
  },

  app: {
    head: {
      htmlAttrs: {
        lang: 'ru',
      },
      titleTemplate: '%s | Рука добра',
      meta: [
        {
          name: 'viewport',
          content: 'width=device-width, initial-scale=1, viewport-fit=cover',
        },
        {
          name: 'format-detection',
          content: 'telephone=no',
        },
        {
          name: 'theme-color',
          content: '#033586',
        },
        {
          name: 'apple-mobile-web-app-title',
          content: 'Рука добра',
        },
        {
          name: 'apple-mobile-web-app-capable',
          content: 'yes',
        },
        {
          name: 'apple-mobile-web-app-status-bar-style',
          content: 'default',
        },
      ],
      link: [
        {
          rel: 'manifest',
          href: '/manifest.webmanifest',
        },
        {
          rel: 'icon',
          type: 'image/svg+xml',
          href: '/assets/favicon.svg',
        },
        {
          rel: 'apple-touch-icon',
          href: '/pwa/apple-touch-icon.png',
        },
      ],
    },
  },

  pwa: {
    registerType: 'prompt',
    includeAssets: [
      'assets/favicon.svg',
      'pwa/apple-touch-icon.png',
      'offline.html',
    ],
    manifest: {
      name: 'Рука добра',
      short_name: 'Рука добра',
      description: 'Мобильное приложение для обращений граждан и общественной поддержки.',
      lang: 'ru',
      scope: '/',
      start_url: '/app',
      display: 'standalone',
      orientation: 'portrait-primary',
      theme_color: '#033586',
      background_color: '#f6faff',
      icons: [
        {
          src: '/pwa/icon-192.png',
          sizes: '192x192',
          type: 'image/png',
        },
        {
          src: '/pwa/icon-512.png',
          sizes: '512x512',
          type: 'image/png',
        },
        {
          src: '/pwa/maskable-192.png',
          sizes: '192x192',
          type: 'image/png',
          purpose: 'maskable',
        },
        {
          src: '/pwa/maskable-512.png',
          sizes: '512x512',
          type: 'image/png',
          purpose: 'maskable',
        },
      ],
      shortcuts: [
        {
          name: 'Новое обращение',
          short_name: 'Обращение',
          description: 'Открыть экран подачи обращения',
          url: '/app/new',
          icons: [
            {
              src: '/pwa/icon-192.png',
              sizes: '192x192',
            },
          ],
        },
        {
          name: 'Лента обращений',
          short_name: 'Лента',
          description: 'Открыть последние обращения',
          url: '/app/feed',
          icons: [
            {
              src: '/pwa/icon-192.png',
              sizes: '192x192',
            },
          ],
        },
      ],
    },
    workbox: {
      globPatterns: [
        '**/*.{js,css,html,webmanifest}',
        'assets/favicon.svg',
        'pwa/*.{png,svg}',
      ],
      globIgnores: [
        'assets/**/*.png',
        'assets/**/*.jpg',
        'assets/**/*.jpeg',
        'assets/**/*.webp',
        'assets/**/*.mp4',
      ],
      navigateFallback: '/offline',
      navigateFallbackDenylist: [/^\/api\//, /^\/_nuxt\//],
      runtimeCaching: [
        {
          urlPattern: ({ request }) => request.destination === 'image',
          handler: 'CacheFirst',
          options: {
            cacheName: 'rukadobra-images',
            expiration: {
              maxEntries: 80,
              maxAgeSeconds: 60 * 60 * 24 * 14,
            },
          },
        },
      ],
    },
    client: {
      installPrompt: 'rukadobra:pwa-install-dismissed',
    },
    devOptions: {
      enabled: true,
      navigateFallback: '/offline',
    },
  },
});
