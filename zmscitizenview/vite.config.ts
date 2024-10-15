// Plugins
import vue from '@vitejs/plugin-vue'

// Utilities
import {defineConfig, PluginOption} from 'vite'
import {fileURLToPath, URL} from 'node:url'
import cssInjectedByJsPlugin from "vite-plugin-css-injected-by-js";
import {viteVueCESubStyle} from '@unplugin-vue-ce/sub-style'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [
    vue({
      customElement: true
    }),
    viteVueCESubStyle({}) as PluginOption,
    cssInjectedByJsPlugin()
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
    drop: ['console', 'debugger']
  },
})
