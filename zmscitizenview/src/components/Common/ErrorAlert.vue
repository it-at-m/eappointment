<template>
  <div class="container">
    <muc-callout :type="props.type">
      <template #header>
        {{ header }}
      </template>
      <template #content>
        <p v-html="message" />
        <slot />
      </template>
    </muc-callout>
  </div>
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

<style scoped>
.container {
  padding-top: 32px;
  padding-bottom: 32px;
}
</style>
