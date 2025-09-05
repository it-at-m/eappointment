<template>
  <muc-card
    class="multiline-text"
    :tagline="t('appointment')"
    :title="formatMultilineTitle(appointment)"
    @click="goToAppointmentLink(appointment.processId!)"
  >
    <template #headerPrefix>
      <calendar-icon
        :timestamp="appointment.timestamp"
        aria-hidden="true"
      />
    </template>
    <template #content>
      <div class="text-padding">
        <muc-icon icon="calendar" />
        {{ formatAppointmentDateTime(appointment.timestamp) }}
        {{ t("timeStampSuffix") }} <br />
      </div>
      <div class="text-padding">
        <muc-icon icon="map-pin" />
        {{ selectedProvider?.address.street }}
        {{ selectedProvider?.address.house_number }} <br />
      </div>
      <b>{{ t("appointmentNumber") }}:</b>
      {{ appointment.processId }}
    </template>
  </muc-card>
</template>

<script setup lang="ts">
import { MucCard, MucIcon } from "@muenchen/muc-patternlab-vue";
import { onMounted, ref } from "vue";

import { AppointmentDTO } from "@/api/models/AppointmentDTO";
import { Office } from "@/api/models/Office";
import CalendarIcon from "@/components/Common/CalendarIcon.vue";
import { QUERY_PARAM_APPOINTMENT_ID } from "@/utils/Constants";
import { formatAppointmentDateTime } from "@/utils/formatAppointmentDateTime";
import { formatMultilineTitle } from "@/utils/formatMultilineTitle";

const props = defineProps<{
  appointment: AppointmentDTO;
  appointmentDetailUrl: string;
  offices: Office[];
  t: (key: string) => string;
}>();

const selectedProvider = ref<Office>();

const goToAppointmentLink = (appointmentNumber: string) => {
  location.href = `${props.appointmentDetailUrl}?${QUERY_PARAM_APPOINTMENT_ID}=${appointmentNumber}`;
};

onMounted(() => {
  const foundOffice = props.offices.find(
    (office) => office.id == props.appointment.officeId
  );

  if (foundOffice) {
    selectedProvider.value = foundOffice;
  }
});
</script>

<style scoped>
.multiline-text {
  white-space: pre-wrap;
}

.text-padding {
  padding-bottom: 12px;
}
</style>
