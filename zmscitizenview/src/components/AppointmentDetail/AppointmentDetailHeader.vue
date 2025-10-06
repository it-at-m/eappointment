<template>
  <muc-intro
    v-if="appointment"
    :tagline="t('appointment')"
    :title="formatMultilineTitle(appointment)"
  >
    <div class="appointment-data">
      <p>
        <b> {{ t("appointmentNumber") }}: </b>
        {{ appointment.processId }}
      </p>
      <muc-link
        :label="
          formatAppointmentDateTime(appointment.timestamp) +
          '' +
          t('timeStampSuffix')
        "
        prepend-icon="calendar"
        @click.prevent="focusTime"
      />
      <br />
      <muc-link
        v-if="selectedProvider"
        :label="
          selectedProvider.address.street +
          ' ' +
          selectedProvider.address.house_number
        "
        prepend-icon="map-pin"
        @click.prevent="focusLocation"
      />
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

import { AppointmentImpl } from "@/types/AppointmentImpl";
import { OfficeImpl } from "@/types/OfficeImpl";
import { formatAppointmentDateTime } from "@/utils/formatAppointmentDateTime";
import { formatMultilineTitle } from "@/utils/formatMultilineTitle";

defineProps<{
  appointment: AppointmentImpl | undefined;
  selectedProvider: OfficeImpl | undefined;
  t: (key: string) => string;
}>();

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

:deep(.m-intro-vertical__title) {
  margin-bottom: 0 !important;
  white-space: pre-wrap;
}
</style>
