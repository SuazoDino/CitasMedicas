import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/js/app.js'],
      refresh: true,
    }),
    vue({
      template: {
        transformAssetUrls: {
          // Don't try to resolve absolute /img/ paths - they're served from public/
          img: [],
          source: [],
          image: [],
          use: [],
        },
      },
    }),
  ],
  server: {
    host: '127.0.0.1',
    port: 5173,
    hmr: { host: '127.0.0.1' },
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources/js'),
    },
  },
})
