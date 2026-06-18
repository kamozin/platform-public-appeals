export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  ssr: true,
  srcDir: 'app/',

  modules: [
    '@nuxt/eslint',
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
    '/dashboard': { ssr: false },
    '/dashboard/**': { ssr: false },
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
          content: 'width=device-width, initial-scale=1',
        },
        {
          name: 'format-detection',
          content: 'telephone=no',
        },
      ],
      link: [
        {
          rel: 'icon',
          type: 'image/svg+xml',
          href: '/assets/favicon.svg',
        },
      ],
    },
  },
});
