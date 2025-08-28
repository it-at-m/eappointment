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
      },
      template: {
        compilerOptions: {
          isCustomElement: (tag) => tag.startsWith('altcha-')
        }
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
    assetsDir: "src",
    rollupOptions: {
      input: {
        "index": "./index.html",
        "appointment-html": "./appointment.html",
        "appointment-detail-html": "./appointment-detail.html",
        "appointment-overview-html": "./appointment-overview.html",
        "appointment-slider-html": "./appointment-slider.html",
        "webcomponents-html": "./webcomponents.html",
        "zms-appointment": "./src/zms-appointment-webcomponent.ts",
        "zms-appointment-detail": "./src/zms-appointment-detail-webcomponent.ts",
        "zms-appointment-overview": "./src/zms-appointment-overview-webcomponent.ts",
        "zms-appointment-slider": "./src/zms-appointment-slider-webcomponent.ts",
      },
      output: {
        entryFileNames: "src/entry-[name]-[hash].js",
        dir: "dist",
      },
    }
  },
  // esbuild: {
  //   drop: process.env.NODE_ENV === 'development' ? [] : ['console', 'debugger'],
  // },
  base: './',
})
