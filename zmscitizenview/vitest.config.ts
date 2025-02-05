import {defineConfig} from "vite";
import vue from "@vitejs/plugin-vue";
import {fileURLToPath} from "node:url";

export default defineConfig({
  plugins: [
    vue({
      features: {
        customElement: true
      }
    })
  ],
  resolve: {
    alias: {
      "@": fileURLToPath(new URL("./src", import.meta.url)),
    },
  },
  test: {
    environment: "jsdom",
    globals: true
  }
})


