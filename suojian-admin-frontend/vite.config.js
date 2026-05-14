import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  base: '/suojian-admin/',
  build: {
    outDir: 'dist',
    assetsDir: 'assets',
  },
  server: {
    host: '0.0.0.0',
    port: 5173,
    strictPort: false,
    proxy: {
      '/suojian-api': {
        target: 'http://47.114.125.123',
        changeOrigin: true,
      },
      '/Public': {
        target: 'http://47.114.125.123',
        changeOrigin: true,
      },
    },
  },
})
