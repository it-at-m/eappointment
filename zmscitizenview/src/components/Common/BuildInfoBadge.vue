<template>
  <p
    v-if="label"
    class="build-info"
    aria-hidden="true"
  >
    {{ label }}
  </p>
</template>

<script lang="ts" setup>
import { onMounted, ref } from "vue";

import {
  isProductionBuild,
  resolveBuildInfoLabel,
  runtimeConfigUrl,
} from "@/utils/buildInfo";

const label = ref<string | null>(null);

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
  const commit = import.meta.env.VITE_GIT_COMMIT;
  const gitRef = import.meta.env.VITE_GIT_REF;
  const viteNodeEnv = import.meta.env.VITE_NODE_ENV;

  const zmsEnv = isProductionBuild(viteNodeEnv)
    ? await readZmsEnv()
    : undefined;
  label.value = resolveBuildInfoLabel(commit, gitRef, viteNodeEnv, zmsEnv);
});
</script>

<style scoped>
.build-info {
  position: relative;
  float: right;
  margin: 0 24px 0 0;
  font-size: 11px;
  line-height: 1.2;
  color: #c4c4c4;
  font-family:
    ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono",
    "Courier New", monospace;
  white-space: nowrap;
  pointer-events: none;
  user-select: none;
}
</style>
