<template>
  <muc-intro
    v-if="appointment"
    :tagline="introTagline"
    :title="formatMultilineTitle(appointment)"
    variant="detail"
    :id="`process-${appointment?.processId}-displayNumber-${appointment?.displayNumber}`"
  >
    <div class="appointment-data">
      <p>
        <strong> {{ t("appointmentNumber") }}: </strong>
        {{ appointment.displayNumber ?? appointment.processId }}
      </p>
      <muc-link
        :label="
          formatAppointmentDateTime(appointment.timestamp) +
          ' ' +
          t('timeStampSuffix')
        "
        prepend-icon="calendar"
        @click.prevent="focusTime"
      />
      <br />
      <muc-link
        v-if="introLocation"
        :id="selectedProvider ? `provider-${selectedProvider.id}` : undefined"
        :label="introLocation"
        prepend-icon="map-pin"
        :aria-label="introLocationAriaLabel"
        @click.prevent="focusLocation"
      />
      <br />
      <!--      Used after the content of hint has been checked-->
      <!--      <p-->
      <!--        v-if="-->
      <!--          selectedProvider &&-->
      <!--          selectedProvider.scope &&-->
      <!--          selectedProvider.scope.hint-->
      <!--        "-->
      <!--      >-->
      <!--        {{ selectedProvider.scope.hint }}-->
      <!--      </p>-->
    </div>
    <div class="m-button-group">
      <muc-button
        icon="arrow-right"
        @click="rescheduleAppointment"
      >
        <template #default>{{ t("rescheduleAppointment") }}</template>
      </muc-button>
      <muc-button
        icon="trash"
        variant="secondary"
        @click="cancelAppointment"
      >
        <template #default>
          <span>{{ t("cancelAppointment") }}</span>
        </template>
      </muc-button>
    </div>
  </muc-intro>
</template>

<script setup lang="ts">
import { MucButton, MucIntro, MucLink } from "@muenchen/muc-patternlab-vue";
import { computed } from "vue";

import { AppointmentImpl } from "@/types/AppointmentImpl";
import { OfficeImpl } from "@/types/OfficeImpl";
import { VARIANT_ID_TELEPHONE, VARIANT_ID_VIDEO } from "@/utils/Constants";
import { formatAppointmentDateTime } from "@/utils/formatAppointmentDateTime";
import { formatMultilineTitle } from "@/utils/formatMultilineTitle";

const props = defineProps<{
  appointment: AppointmentImpl | undefined;
  selectedProvider: OfficeImpl | undefined;
  variantId: number | null;
  t: (key: string) => string;
}>();

const introTagline = computed(() => {
  if (props.variantId === VARIANT_ID_TELEPHONE) {
    return props.t(`appointmentTypes.${VARIANT_ID_TELEPHONE}`);
  }

  if (props.variantId === VARIANT_ID_VIDEO) {
    return props.t(`appointmentTypes.${VARIANT_ID_VIDEO}`);
  }

  return props.t("appointment");
});

const introLocation = computed(() => {
  if (props.variantId === VARIANT_ID_VIDEO) {
    return props.t("appointmentDetailVideoIntroLocation");
  }

  if (props.variantId === VARIANT_ID_TELEPHONE) {
    return props.appointment?.telephone ?? "";
  }

  if (!props.selectedProvider) {
    return "";
  }

  return (
    props.selectedProvider.address.street +
    " " +
    props.selectedProvider.address.house_number
  );
});

const introLocationAriaLabel = computed(() => {
  if (
    props.variantId !== VARIANT_ID_TELEPHONE &&
    props.variantId !== VARIANT_ID_VIDEO &&
    props.selectedProvider
  ) {
    return props.selectedProvider.name;
  }

  return introLocation.value;
});

const emit =
  defineEmits<
    (
      e:
        | "cancelAppointment"
        | "focusLocation"
        | "focusTime"
        | "rescheduleAppointment"
    ) => void
  >();

const cancelAppointment = () => emit("cancelAppointment");
const focusLocation = () => emit("focusLocation");
const focusTime = () => emit("focusTime");
const rescheduleAppointment = () => emit("rescheduleAppointment");
</script>
<style scoped>
.appointment-data {
  margin-top: 32px;
  margin-bottom: 16px;
}

.appointment-data p,
a {
  padding-bottom: 16px;
}
</style>
<style>
.m-intro-vertical__title {
  margin-bottom: 0 !important;
  white-space: pre-wrap !important;
}
</style>
