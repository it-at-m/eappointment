import { defineCustomElement } from "vue";
import { createI18n } from "vue-i18n";

import deDE from "@/utils/de-DE.json";
import enUS from "@/utils/en-US.json";
import ZMSJumpinLinkBuilderElement from "@/zms-jumpin-link-builder.ce.vue";

const i18n = createI18n({
  legacy: false,
  locale: "de-DE",
  messages: {
    "de-DE": deDE,
    "en-US": enUS,
  },
});

const zmsJumpinLinkBuilderWebcomponent = defineCustomElement(
  ZMSJumpinLinkBuilderElement,
  {
    configureApp(app) {
      app.use(i18n);
    },
  }
);
customElements.define(
  "zms-jumpin-link-builder",
  zmsJumpinLinkBuilderWebcomponent
);
