/// <reference types="vite/client" />

interface ImportMetaEnv {
  readonly VITE_VUE_APP_API_URL: string;
}

interface ImportMeta {
  readonly env: ImportMetaEnv;
}
