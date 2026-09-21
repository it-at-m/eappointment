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
import { computed } from "vue";

import { resolveBuildInfoLabel } from "@/utils/buildInfo";

const label = computed(() =>
  resolveBuildInfoLabel(
    import.meta.env.VITE_GIT_COMMIT,
    import.meta.env.VITE_GIT_REF,
    import.meta.env.VITE_NODE_ENV
  )
);
</script>

<style scoped>
.build-info {
  position: fixed;
  right: 12px;
  bottom: 8px;
  z-index: 1000;
  margin: 0;
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
