// Plugins
import vue from '@vitejs/plugin-vue'

// Utilities
import fs from 'node:fs'
import path from 'node:path'
import {fileURLToPath, URL} from 'node:url'
import {defineConfig, type Plugin} from 'vite'

/**
 * Branch switches leave Vite's module graph / optimizeDeps cache pointing at
 * paths that no longer exist (or miss new files). Polling alone is not enough;
 * restart when Git HEAD changes so imports resolve again without a manual clear.
 *
 * Do not use server.watcher for this: Vite ignores the .git directory, so HEAD
 * changes never arrive. fs.watchFile polls outside that ignore list.
 *
 * Resolve paths from server.config.root (not import.meta.url): Vite loads the
 * config from a temp file under node_modules/.vite-temp/, so import.meta.url
 * would point at the wrong directory.
 */
function restartOnGitCheckout(): Plugin {
  return {
    name: 'restart-on-git-checkout',
    configureServer(server) {
      const projectRoot = server.config.root
      const gitHead = path.join(projectRoot, '..', '.git', 'HEAD')
      if (!fs.existsSync(gitHead)) {
        return
      }

      const cacheDir = path.join(projectRoot, 'node_modules', '.vite')
      let restarting = false
      let lastHead = fs.readFileSync(gitHead, 'utf8')

      const onHeadChange = async () => {
        let nextHead: string
        try {
          nextHead = fs.readFileSync(gitHead, 'utf8')
        } catch {
          return
        }
        if (nextHead === lastHead || restarting) {
          return
        }
        lastHead = nextHead
        restarting = true
        try {
          fs.rmSync(cacheDir, {recursive: true, force: true})
          await server.restart()
        } finally {
          restarting = false
        }
      }

      fs.watchFile(gitHead, {interval: 1000}, () => {
        void onHeadChange()
      })

      server.httpServer?.once('close', () => {
        fs.unwatchFile(gitHead)
      })
    },
  }
}

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
    }),
    restartOnGitCheckout(),
  ],
  // Expose SHOW_CITIZEN_LOGIN from .env / compose (same name as zms-deployment).
  envPrefix: ['VITE_', 'SHOW_'],
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
            const raw = req.headers.host
            const host = Array.isArray(raw) ? raw[0] : raw
            if (host) {
              proxyReq.setHeader('X-Forwarded-Host', host)
            }
          })
        },
      },
      // Logged-in flows use /buergeransicht/authenticated/api/citizen (see getAPIBaseURL).
      '/buergeransicht/authenticated/api': {
        target: 'http://refarch-gateway:8080',
        changeOrigin: true,
        configure: (proxy) => {
          proxy.on('proxyReq', (proxyReq, req) => {
            const raw = req.headers.host
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
