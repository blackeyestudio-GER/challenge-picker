// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2024-04-03',
  devtools: { enabled: true },
  runtimeConfig: {
    public: {
      apiBase: '/api',
      dev: process.env.NODE_ENV === 'development',
    }
  },
  css: [
    '~/assets/css/main.css'
  ],

  routeRules: {
    '/api/**': {
      proxy: { to: (process.env.API_HOST || 'http://nginx:80') + '/api/**' }
    }
  },

  postcss: {
    plugins: {
      tailwindcss: {},
      autoprefixer: {},
    },
  },

  modules: ["@nuxtjs/tailwindcss", "@nuxt/eslint", "nuxt-icon"],
  
  icon: {
    provider: 'iconify',
    serverBundle: {
      collections: ['heroicons']
    }
  }
})