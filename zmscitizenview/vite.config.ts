// Plugins
import vue from '@vitejs/plugin-vue'

// Utilities
import {defineConfig} from 'vite'
import {fileURLToPath, URL} from 'node:url'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [
    vue({
      features: {
        customElement: true
      }
    })
  ],
  define: {'process.env': {}},
  resolve: {
    dedupe: ['vue'],
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
    extensions: [
      '.js',
      '.json',
      '.jsx',
      '.mjs',
      '.ts',
      '.tsx',
      '.vue',
    ],
  },
  server: {
    port: 8082,
  },
  build: {
    ssrManifest: true,
    manifest: true,
    minify: true,
    assetsDir: "src"
  },
  esbuild: {
    drop: process.env.NODE_ENV === 'development' ? [] : ['console', 'debugger'],
  },
})
