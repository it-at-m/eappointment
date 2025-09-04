<template>
  <div class="m-component m-component-form">
    <div class="container">
      <div class="m-component__grid">
        <div class="m-component__column">
          <muc-callout :type="systemFailureCalloutType">
            <template #header>
              {{ t("systemFailurePageHeader") }}
            </template>
            <template #content>
              <p>{{ t("systemFailurePageText") }}</p>
            </template>
          </muc-callout>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { MucCallout } from "@muenchen/muc-patternlab-vue";
import type { CalloutType } from "@/utils/callout";

defineProps<{
  t: (key: string) => string;
}>();

const systemFailureCalloutType: CalloutType = "error";
</script>

<style scoped>
.container {
  padding-top: 32px;
  padding-bottom: 32px;
}
</style>
