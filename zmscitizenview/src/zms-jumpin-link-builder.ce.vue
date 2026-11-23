<template>
  <main :class="{ 'styles-loading': !stylesLoaded }">
    <div v-html="sanitizeHtml(mucIconsSprite)"></div>
    <jumpin-link-builder-view :available="available" />
    <div class="jumpin-build-info">
      <build-info-badge />
    </div>
  </main>
</template>

<script lang="ts" setup>
import mucIconsSprite from "@muenchen/muc-patternlab-vue/assets/icons/muc-icons.svg?raw";
import { ref } from "vue";

import "@/utils/installMdePreviewFonts";

import BuildInfoBadge from "@/components/Common/BuildInfoBadge.vue";
import JumpinLinkBuilderView from "@/components/JumpinLinkBuilder/JumpinLinkBuilderView.vue";
import { isInternalZmsHost } from "@/utils/internalHost";
import { sanitizeHtml } from "@/utils/sanitizeHtml";

const stylesLoaded = ref(true);
const available = ref(isInternalZmsHost(window.location.hostname));
</script>

<style>
@import "@/styles/mde-preview.css";
@import "@muenchen/muc-patternlab-vue/style.css";

:host {
  display: block;
  font-family: var(--mde-typography-font-family-base);
}

main {
  position: relative;
}

main.styles-loading {
  visibility: hidden;
}

.jumpin-build-info {
  position: absolute;
  right: 24px;
  bottom: 12px;
  z-index: 3;
}

.jumpin-build-info .build-info {
  position: static;
  float: none;
  margin: 0;
  color: #617586;
}
</style>
