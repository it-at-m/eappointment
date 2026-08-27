<script setup>
import { computed } from "vue";

import {
  cucumberGroupHasMatch,
  cucumberUsesRemoteCatalog,
} from "./cucumberAccordion.js";

const props = defineProps({
  testType: {
    type: String,
    required: true,
  },
  module: {
    type: String,
    default: "",
  },
  remote: {
    type: Boolean,
    default: false,
  },
});

const isVisible = computed(() => {
  if (props.remote !== cucumberUsesRemoteCatalog()) {
    return false;
  }
  return cucumberGroupHasMatch(props.testType, props.module);
});
</script>

<template>
  <div
    v-if="isVisible"
    class="cucumber-feature-group"
  >
    <slot />
  </div>
</template>
