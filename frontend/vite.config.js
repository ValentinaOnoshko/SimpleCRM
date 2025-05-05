import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import rollupNodePolyFill from 'rollup-plugin-polyfill-node';
import path from 'path';

export default defineConfig({
  root: '.',
  plugins: [vue()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'src'),
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
      input: path.resolve(__dirname, 'index.html'),
      plugins: [rollupNodePolyFill()],
    },
  },
  base: '/',
});
