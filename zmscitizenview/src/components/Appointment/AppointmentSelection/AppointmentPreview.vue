<template>
  <div
    aria-live="polite"
    aria-atomic="true"
    ref="summary"
  >
    <muc-callout
      v-if="
        selectedProvider &&
        selectedDay &&
        typeof selectedTimeslot === 'number' &&
        selectedTimeslot > 0
      "
      type="info"
    >
      <template #content>
        <div>
          <div v-if="selectedProvider">
            <h3>{{ t("location") }}</h3>
            <div
              :id="`provider-${selectedProvider.id}`"
              class="m-teaser-contained-contact__summary"
            >
              {{ selectedProvider.name }}
              <br />
              <span v-if="detailIcon">
                <br />
                <svg
                  aria-hidden="true"
                  class="icon icon--before"
                >
                  <use :xlink:href="`#${detailIcon}`"></use>
                </svg>
                {{ t(`appointmentTypes.${variantId}`) }}
              </span>
              <span v-else>
                {{ selectedProvider.address.street }}
                {{ selectedProvider.address.house_number }}
              </span>
            </div>
          </div>
          <div v-if="selectedDay">
            <h3>{{ t("time") }}</h3>
            <p class="m-teaser-contained-contact__detail">
              {{ formatDayFromDate(selectedDay) }},
              {{ formatTimeFromUnix(selectedTimeslot) }}
              {{ t("clock") }}
              <br />
              {{ t("estimatedDuration") }} {{ localEstimatedDuration }}
              {{ t("minutes") }}
            </p>
          </div>
          <div
            v-if="
              selectedProvider.scope &&
              selectedProvider.scope.infoForAppointment
            "
          >
            <h3>{{ t("hint") }}</h3>
            <div
              v-html="sanitizeHtml(selectedProvider.scope.infoForAppointment)"
            ></div>
          </div>
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
import {
  formatDayFromDate,
  formatTimeFromUnix,
} from "@/utils/formatAppointmentDateTime";
import { sanitizeHtml } from "@/utils/sanitizeHtml";

const props = defineProps<{
  t: (key: string) => string;
  selectedProvider: OfficeImpl | null | undefined;
  selectedDay: Date | undefined;
  selectedTimeslot: number;
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

const variantId = computed<number | null>(() => {
  const id = (props.selectedService as any)?.variantId;
  return Number.isFinite(id) ? id : null;
});

const detailIcon = computed<string | null>(() => {
  if (variantId.value === 2) return "icon-telephone";
  if (variantId.value === 3) return "icon-video-camera";
  return null;
});
</script>
