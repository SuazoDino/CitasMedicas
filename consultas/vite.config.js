import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/js/app.js'],
      refresh: true,
    }),
    vue(),
  ],
  server: {
    host: true,
    hmr: { host: 'localhost' },
  },
  // (opcional) si te diera problemas de compilación de templates:
  // resolve: { alias: { 'vue': 'vue/dist/vue.esm-bundler.js' } }
  resolve: {
    alias: {
      // habilita compilación de templates en runtime
      vue: 'vue/dist/vue.esm-bundler.js',
      '@': '/resources/js',
    },
  },
})
