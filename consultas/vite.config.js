import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/js/app.js'], refresh: true}),
    vue(),
  ],
  server: {
    host: '127.0.0.1',
    port: 5173,
    hmr: { host: '127.0.0.1' },
  },
  // (opcional) si te diera problemas de compilación de templates:
  // resolve: { alias: { 'vue': 'vue/dist/vue.esm-bundler.js' } }
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources/js'),
      '@vee-validate/core': path.resolve(__dirname, 'resources/js/vendor/vee-validate-core.js'),
      '@vee-validate/rules': path.resolve(__dirname, 'resources/js/vendor/vee-validate-rules.js'),
    },
  },
})
