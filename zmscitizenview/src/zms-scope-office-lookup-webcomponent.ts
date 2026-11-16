import { defineCustomElement } from "vue";
import { createI18n } from "vue-i18n";

import deDE from "@/utils/de-DE.json";
import enUS from "@/utils/en-US.json";
import ZMSScopeOfficeLookupElement from "@/zms-scope-office-lookup.ce.vue";

const i18n = createI18n({
  legacy: false,
  locale: "de-DE",
  messages: {
    "de-DE": deDE,
    "en-US": enUS,
  },
});

const zmsScopeOfficeLookupWebcomponent = defineCustomElement(
  ZMSScopeOfficeLookupElement,
  {
    configureApp(app) {
      app.use(i18n);
    },
  }
);
customElements.define(
  "zms-scope-office-lookup",
  zmsScopeOfficeLookupWebcomponent
);
