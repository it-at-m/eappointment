<template>
  <div
    ref="summary"
    tabindex="0"
  >
    <muc-callout
      v-if="selectedProvider && selectedDay && selectedTimeslot !== 0"
      type="info"
    >
      <template #content>
        <div v-if="selectedProvider">
          <b>{{ t("location") }}</b>
          <p class="m-teaser-contained-contact__summary">
            {{ selectedProvider.name }}
            <br />
            {{ selectedProvider.address.street }}
            {{ selectedProvider.address.house_number }}
          </p>
        </div>
        <div v-if="selectedDay">
          <b>{{ t("time") }}</b>
          <br />
          <p class="m-teaser-contained-contact__detail">
            {{ formatDay(selectedDay) }}, {{ formatTime(selectedTimeslot) }}
            {{ t("clock") }}
            <br />
            {{ t("estimatedDuration") }} {{ localEstimatedDuration }}
            {{ t("minutes") }}
          </p>
        </div>
        <div
          v-if="
            selectedProvider.scope && selectedProvider.scope.infoForAppointment
          "
        >
          <b>{{ t("hint") }}</b>
          <br />
          <div
            v-html="sanitizeHtml(selectedProvider.scope.infoForAppointment)"
          ></div>
        </div>
      </template>

      <template #header>{{ t("selectedAppointment") }}</template>
    </muc-callout>
  </div>
</template>

<script setup lang="ts">
import type { OfficeImpl } from "@/types/OfficeImpl";

import { MucCallout } from "@muenchen/muc-patternlab-vue";
import { computed, ref } from "vue";

// Calculate duration locally
import { calculateEstimatedDuration } from "@/utils/calculateEstimatedDuration";
import { sanitizeHtml } from "@/utils/sanitizeHtml";

const props = defineProps<{
  t: (key: string) => string;
  selectedProvider: OfficeImpl | null | undefined;
  selectedDay: Date | undefined;
  selectedTimeslot: number;
  formatDay: (date: Date) => string | undefined;
  formatTime: (time: number) => string;
  selectedService: any;
}>();

const summary = ref<HTMLElement | null>(null);

defineExpose({
  summary,
});

const localEstimatedDuration = computed(() =>
  calculateEstimatedDuration(
    props.selectedService,
    (props.selectedProvider ?? undefined) as OfficeImpl | undefined
  )
);
</script>
