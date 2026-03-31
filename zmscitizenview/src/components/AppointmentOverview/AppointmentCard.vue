<template>
  <muc-card
    class="multiline-text"
    :tagline="t('appointment')"
    :title="formatMultilineTitle(appointment)"
    :href="getAppointmentLink()"
  >
    <template #headerPrefix>
      <calendar-icon
        :timestamp="appointment.timestamp"
        aria-hidden="true"
      />
    </template>
    <template #content>
      <p class="m-teaser-contained-contact__detail">
        <muc-icon icon="calendar" />
        {{ formatAppointmentDateTime(appointment.timestamp) }}
        {{ t("timeStampSuffix") }} <br />
      </p>
      <p class="m-teaser-contained-contact__detail">
        <muc-icon icon="map-pin" />
        {{ selectedProvider?.address.street }}
        {{ selectedProvider?.address.house_number }} <br />
      </p>
      <strong>{{ t("appointmentNumber") }}:</strong>
      {{ appointment.displayNumber ?? appointment.processId }}
    </template>
  </muc-card>
</template>

<script setup lang="ts">
import { MucCard, MucIcon } from "@muenchen/muc-patternlab-vue";
import { onMounted, ref } from "vue";

import { AppointmentDTO } from "@/api/models/AppointmentDTO";
import { Office } from "@/api/models/Office";
import CalendarIcon from "@/components/Common/CalendarIcon.vue";
import {
  QUERY_PARAM_APPOINTMENT_DISPLAY_NUMBER,
  QUERY_PARAM_APPOINTMENT_ID,
} from "@/utils/Constants";
import { formatAppointmentDateTime } from "@/utils/formatAppointmentDateTime";
import { formatMultilineTitle } from "@/utils/formatMultilineTitle";

const props = defineProps<{
  appointment: AppointmentDTO;
  appointmentDetailUrl: string;
  offices: Office[];
  t: (key: string) => string;
}>();

const selectedProvider = ref<Office>();

const getAppointmentLink = () => {
  const url = new URL(props.appointmentDetailUrl, window.location.origin);
  url.searchParams.set(
    QUERY_PARAM_APPOINTMENT_ID,
    props.appointment.processId!
  );
  if (props.appointment.displayNumber) {
    url.searchParams.set(
      QUERY_PARAM_APPOINTMENT_DISPLAY_NUMBER,
      props.appointment.displayNumber
    );
  }
  return url.toString();
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
</style>
