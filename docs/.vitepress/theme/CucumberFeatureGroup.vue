<script setup>
import { computed } from "vue";

import { cucumberGroupHasMatch } from "./cucumberAccordion.js";

const props = defineProps({
  testType: {
    type: String,
    required: true,
  },
  module: {
    type: String,
    default: "",
  },
});

const isVisible = computed(() =>
  cucumberGroupHasMatch(props.testType, props.module)
);
</script>

<template>
  <div
    v-show="isVisible"
    class="cucumber-feature-group"
  >
    <slot />
  </div>
</template>
