<template>
  <link
    href="https://assets.muenchen.de/mde/1.1.27/css/style.css"
    rel="stylesheet"
    @load="stylesLoaded = true"
    @error="stylesLoaded = true"
  />
  <main :class="{ 'styles-loading': !stylesLoaded }">
    <div v-html="sanitizeHtml(mucIconsSprite)"></div>
    <scope-office-lookup-view :available="available" />
    <div class="scope-build-info">
      <build-info-badge />
    </div>
  </main>
</template>

<script lang="ts" setup>
import mucIconsSprite from "@muenchen/muc-patternlab-vue/assets/icons/muc-icons.svg?raw";
import { onMounted, ref } from "vue";

import BuildInfoBadge from "@/components/Common/BuildInfoBadge.vue";
import ScopeOfficeLookupView from "@/components/ScopeOfficeLookup/ScopeOfficeLookupView.vue";
import { isInternalZmsHost } from "@/utils/internalHost";
import { sanitizeHtml } from "@/utils/sanitizeHtml";

const stylesLoaded = ref(false);
const available = ref(isInternalZmsHost(window.location.hostname));

onMounted(() => {
  window.setTimeout(() => {
    stylesLoaded.value = true;
  }, 3000);
});
</script>

<style>
@import "@muenchen/muc-patternlab-vue/assets/css/custom-style.css";
@import "@muenchen/muc-patternlab-vue/style.css";

:host {
  display: block;
  font-family:
    Open Sans,
    sans-serif;
}

main {
  position: relative;
}

main.styles-loading {
  visibility: hidden;
}

.scope-build-info {
  position: absolute;
  right: 24px;
  bottom: 12px;
  z-index: 3;
}

.scope-build-info .build-info {
  position: static;
  float: none;
  margin: 0;
  color: #617586;
}
</style>
