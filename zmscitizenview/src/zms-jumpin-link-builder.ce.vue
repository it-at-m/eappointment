<template>
  <link
    href="https://assets.muenchen.de/mde/1.1.27/css/style.css"
    rel="stylesheet"
    @load="stylesLoaded = true"
    @error="stylesLoaded = true"
  />
  <main
    v-if="ready"
    :class="{ 'styles-loading': !stylesLoaded }"
  >
    <div v-html="sanitizeHtml(mucIconsSprite)"></div>
    <jumpin-link-builder-view :available="available" />
    <div class="jumpin-build-info">
      <build-info-badge />
    </div>
  </main>
</template>

<script lang="ts" setup>
import mucIconsSprite from "@muenchen/muc-patternlab-vue/assets/icons/muc-icons.svg?raw";
import { onMounted, ref } from "vue";

import BuildInfoBadge from "@/components/Common/BuildInfoBadge.vue";
import JumpinLinkBuilderView from "@/components/JumpinLinkBuilder/JumpinLinkBuilderView.vue";
import {
  isProductionBuild,
  runtimeConfigUrl,
  shouldShowBuildInfo,
} from "@/utils/buildInfo";
import { sanitizeHtml } from "@/utils/sanitizeHtml";

const stylesLoaded = ref(false);
const ready = ref(false);
const available = ref(false);

async function readZmsEnv(): Promise<string | undefined> {
  try {
    const response = await fetch(runtimeConfigUrl(import.meta.url), {
      cache: "no-store",
      signal: AbortSignal.timeout(2000),
    });
    if (!response.ok) {
      return undefined;
    }
    const data = (await response.json()) as { ZMS_ENV?: unknown };
    return typeof data.ZMS_ENV === "string" && data.ZMS_ENV.trim()
      ? data.ZMS_ENV.trim()
      : undefined;
  } catch {
    return undefined;
  }
}

onMounted(async () => {
  const viteNodeEnv = import.meta.env.VITE_NODE_ENV;
  const zmsEnv = isProductionBuild(viteNodeEnv)
    ? await readZmsEnv()
    : undefined;
  available.value = shouldShowBuildInfo(viteNodeEnv, zmsEnv);
  ready.value = true;
});

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
