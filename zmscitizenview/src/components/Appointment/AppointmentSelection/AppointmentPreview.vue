<template>
  <div
    aria-live="polite"
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
        <div v-if="selectedProvider">
          <h3>{{ t("location") }}</h3>
          <p
            :id="`provider-${selectedProvider.id}`"
            class="m-teaser-contained-contact__summary"
          >
            <span v-if="isTelephoneOrVideoVariant">
              {{ t(`appointmentTypes.${variantId}`) }}
            </span>
            <span v-else>
              {{ selectedProvider.name }}
              <br />
              {{ selectedProvider.address.street }}
              {{ selectedProvider.address.house_number }}
            </span>
          </p>
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
            selectedProvider.scope && selectedProvider.scope.infoForAppointment
          "
        >
          <h3>{{ t("hint") }}</h3>
          <component
            :is="infoForAppointmentContainsPTag ? 'div' : 'p'"
            v-html="sanitizedInfoForAppointment"
          />
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
import { VARIANT_ID_TELEPHONE, VARIANT_ID_VIDEO } from "@/utils/Constants";
import { containsParagraphTag } from "@/utils/containsParagraphTag";
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

const isTelephoneOrVideoVariant = computed(
  () =>
    variantId.value === VARIANT_ID_TELEPHONE ||
    variantId.value === VARIANT_ID_VIDEO
);

const sanitizedInfoForAppointment = computed(() =>
  sanitizeHtml(props.selectedProvider?.scope?.infoForAppointment)
);

const infoForAppointmentContainsPTag = computed(() =>
  containsParagraphTag(sanitizedInfoForAppointment.value)
);
</script>
