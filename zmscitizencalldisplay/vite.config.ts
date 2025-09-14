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
  assetsInclude: ['**/*.svg'],
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
    port: 8083,
  },
  build: {
    ssrManifest: true,
    manifest: true,
    minify: true,
    assetsDir: "src",
    rollupOptions: {
      input: {
        "index": "./index.html"
      },
      output: {
        entryFileNames: "src/entry-[name]-[hash].js",
        dir: "dist",
      },
    }
  },
  base: './',
})
