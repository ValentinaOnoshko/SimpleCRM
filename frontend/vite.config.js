import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import rollupNodePolyFill from 'rollup-plugin-polyfill-node';
import path from 'path';

const currentDir = new URL('.', import.meta.url).pathname;

export default defineConfig({
  root: '.',
  plugins: [vue()],
  resolve: {
    alias: {
      '@': path.resolve(currentDir, './src'),
      crypto: 'crypto-browserify',
      stream: 'stream-browserify',
    },
  },
  define: {
    global: 'globalThis',
  },
  optimizeDeps: {
    include: ['crypto-browserify', 'stream-browserify'],
  },
  build: {
    outDir: 'dist',
    emptyOutDir: true,
    rollupOptions: {
      input: path.resolve(currentDir, 'index.html'),
      plugins: [rollupNodePolyFill()],
    },
  },
  base: '/',

  server: {
    host: '0.0.0.0',
    port: 5173,
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
        secure: false,
      },
    },
  },
});
