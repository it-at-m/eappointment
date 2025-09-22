<template>
  <muc-intro
    v-if="appointment"
    class="multiline-text"
    :tagline="t('appointment')"
    :title="formatMultilineTitle(appointment)"
  >
    <div class="appointment-data">
      <p tabindex="0">
        <b> {{ t("appointmentNumber") }}: </b>
        {{ appointment.processId }}
      </p>
      <p tabindex="0">
        <muc-icon icon="calendar" />
        {{ formatAppointmentDateTime(appointment.timestamp) }}
        {{ t("timeStampSuffix") }} <br />
      </p>
      <p
        v-if="selectedProvider"
        tabindex="0"
      >
        <muc-icon icon="map-pin" />
        {{ selectedProvider.address.street }}
        {{ selectedProvider.address.house_number }} <br />
      </p>
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
import { MucButton, MucIcon, MucIntro } from "@muenchen/muc-patternlab-vue";

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
  defineEmits<(e: "cancelAppointment" | "rescheduleAppointment") => void>();

const cancelAppointment = () => emit("cancelAppointment");
const rescheduleAppointment = () => emit("rescheduleAppointment");
</script>
<style scoped>
.multiline-text {
  white-space: pre-wrap;
}

.appointment-data {
  margin-top: 32px;
  margin-bottom: 16px;
}

.appointment-data p {
  padding-bottom: 16px;
}

:deep(.m-intro-vertical__title) {
  margin-bottom: 0 !important;
}
</style>
