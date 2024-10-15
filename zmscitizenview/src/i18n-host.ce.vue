<template>
  <slot />
</template>

<script setup lang="ts">
import { provide } from "vue";
import { createI18n, I18nInjectionKey } from "vue-i18n";

import deDE from "./utils/de-DE.json";
import enUS from "./utils/en-US.json";

const i18n = createI18n({
  legacy: false,
  locale: "de-DE",
  messages: {
    "de-DE": deDE,
    "en-US": enUS,
  },
});

/**
 * provide i18n instance with `I18nInjectionKey` for other web components
 */
provide(I18nInjectionKey, i18n);
</script>
