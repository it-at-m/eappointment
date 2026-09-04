/// <reference types="vite/client" />

declare const __ZMS_MAX_BOOKABLE_IN_DAYS__: string | undefined;

interface ImportMetaEnv {
  readonly SHOW_CITIZEN_LOGIN?: string;
  readonly VITE_SHOW_CITIZEN_LOGIN?: string;
  readonly VITE_USE_LOCAL_CITIZEN_LOGIN?: string;
  readonly VITE_DBS_LOGIN_LOADER_URL?: string;
  readonly VITE_KC_URL?: string;
  readonly VITE_KC_REALM?: string;
  readonly VITE_KC_CLIENT_ID?: string;
}

interface ImportMeta {
  readonly env: ImportMetaEnv;
}

declare module "*.vue" {
  import type { DefineComponent } from "vue";
  const component: DefineComponent<{}, {}, any>;
  export default component;
}
