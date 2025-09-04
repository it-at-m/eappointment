<template>
  <div class="m-component m-component-form">
    <div class="container">
      <div class="m-component__grid">
        <div class="m-component__column">
          <muc-callout :type="maintenanceCalloutType">
            <template #header>
              {{ t("maintenancePageHeader") }}
            </template>
            <template #content>
              <p>{{ t("maintenancePageText") }}</p>
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

const maintenanceCalloutType: CalloutType = "warning";
</script>

<style scoped>
.container {
  padding-top: 32px;
  padding-bottom: 32px;
}
</style>

