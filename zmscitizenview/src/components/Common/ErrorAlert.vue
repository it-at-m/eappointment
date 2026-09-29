<template>
  <div v-bind="regionAttributes">
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
import { computed, withDefaults } from "vue";

const props = withDefaults(
  defineProps<{
    message: string;
    header: string;
    type?: CalloutType;
    /**
     * When false, the caller owns the live region. Used where the region must
     * already be mounted before this message appears.
     */
    live?: boolean;
  }>(),
  {
    type: "error",
    live: true,
  }
);

const regionAttributes = computed(() => {
  if (!props.live) {
    return {};
  }
  if (props.type === "error") {
    return {
      role: "alert",
      "aria-live": "assertive",
      "aria-atomic": "true",
    };
  }
  return {
    role: "status",
    "aria-live": "polite",
    "aria-atomic": "true",
  };
});

defineSlots<{
  default(): unknown;
}>();
</script>
