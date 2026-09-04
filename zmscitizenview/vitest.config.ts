import {defineConfig} from "vitest/config";
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
  define: {
    __ZMS_MAX_BOOKABLE_IN_DAYS__: JSON.stringify(
      process.env.ZMS_MAX_BOOKABLE_IN_DAYS || "180"
    ),
  },
  resolve: {
    alias: {
      "@": fileURLToPath(new URL("./src", import.meta.url)),
    },
  },
  test: {
    environment: "jsdom",
    globals: true,
    setupFiles: ["./tests/setup/localStorageMock.ts"]
  }
})
