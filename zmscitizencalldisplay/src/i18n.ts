import { createI18n } from "vue-i18n";
import deDE from "./utils/de-DE.json";
import enUS from "./utils/en-US.json";

const i18n = createI18n({
  legacy: false,
  locale: "de-DE",
  fallbackLocale: "en-US",
  messages: {
    "de-DE": deDE,
    "en-US": enUS,
  },
});

export default i18n;
