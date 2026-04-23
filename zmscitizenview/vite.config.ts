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
    host: '0.0.0.0',
    port: 8082,
    watch: {
      // In containerized dev on macOS/Podman, inotify events may not
      // propagate correctly for bind mounts, so force polling.
      usePolling: true,
    },
    // Allow dev server access via the Docker service name "citizenview"
    // from the zms-web container (used by ATAF UI tests).
    allowedHosts: ['citizenview'],
    proxy: {
      '/buergeransicht/api': {
        target: 'http://refarch-gateway:8080',
        changeOrigin: true,
        // zmscitizenapi prefers HTTP_X_FORWARDED_HOST for ACCESS_UNPUBLISHED_ON_DOMAIN; changeOrigin
        // overwrites Host to refarch-gateway, so forward the browser host for gateway → zms-web.
        configure: (proxy) => {
          proxy.on('proxyReq', (proxyReq, req) => {
            const raw = req.headers['x-forwarded-host'] ?? req.headers.host
            const host = Array.isArray(raw) ? raw[0] : raw
            if (host) {
              proxyReq.setHeader('X-Forwarded-Host', host)
            }
          })
        },
      },
    },
  },
  build: {
    ssrManifest: true,
    manifest: true,
    minify: true,
    assetsDir: "src",
    rollupOptions: {
      input: {
        "index": "./index.html",
        "appointment-view-html": "./appointment-view.html",
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
  esbuild: {
    drop: process.env.NODE_ENV === 'development' ? [] : ['console', 'debugger'],
  },
  base: './',
})