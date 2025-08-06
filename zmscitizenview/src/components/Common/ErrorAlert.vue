<template>
  <div class="container">
    <muc-callout type="error">
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
import { MucCallout } from "@muenchen/muc-patternlab-vue";

defineProps<{
  message: string;
  header: string;
}>();

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
