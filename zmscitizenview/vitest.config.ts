import {defineConfig} from "vitest/config";
import vue from "@vitejs/plugin-vue";
import {fileURLToPath} from "node:url";

if (typeof window !== "undefined") {
  if (!window.HTMLDialogElement) {
    class HTMLDialogElement extends HTMLElement {}
    window.HTMLDialogElement = HTMLDialogElement as any;
  }

  window.HTMLDialogElement.prototype.showModal = vi.fn();
  window.HTMLDialogElement.prototype.close = vi.fn();
}

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
    globals: true,
    setupFiles: ["./tests/setup/localStorageMock.ts"]
  }
})
