<template>
  <muc-callout :type="props.type">
    <template #header>
      {{ header }}
    </template>
    <template #content>
      <p v-html="message" />
      <slot />
    </template>
  </muc-callout>
</template>

<script lang="ts" setup>
import type { CalloutType } from "@/utils/callout";

import { MucCallout } from "@muenchen/muc-patternlab-vue";
import { withDefaults } from "vue";

const props = withDefaults(
  defineProps<{
    message: string;
    header: string;
    type?: CalloutType;
  }>(),
  {
    type: "error",
  }
);

defineSlots<{
  default(): unknown;
}>();
</script>

<style>
/* Dev API debug under system-failure callout (slot from parent) */
.api-debug-callout {
  margin-top: 0.75rem;
  padding-top: 0.5rem;
  border-top: 1px dashed rgba(0, 0, 0, 0.2);
}
.api-debug-callout__sub {
  display: block;
  margin-top: 0.65rem;
  font-size: 0.8rem;
}
.api-debug-callout pre {
  margin: 0.35rem 0 0;
  font-size: 0.72rem;
  line-height: 1.35;
  white-space: pre-wrap;
  word-break: break-word;
  background: rgba(0, 0, 0, 0.06);
  padding: 0.5rem 0.65rem;
  border-radius: 4px;
  max-height: 70vh;
  overflow: auto;
}
</style>
