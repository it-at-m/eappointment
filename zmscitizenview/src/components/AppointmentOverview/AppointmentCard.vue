<template>
  <muc-card
    class="multiline-text"
    :tagline="appointmentTypeLabel"
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
      <p
        data-test="appointment-location"
        class="m-teaser-contained-contact__detail"
      >
        <muc-icon :icon="locationIcon" />
        {{ locationText }}
      </p>
      <strong>{{ t("appointmentNumber") }}:</strong>
      {{ appointment.displayNumber ?? appointment.processId }}
    </template>
  </muc-card>
</template>

<script setup lang="ts">
import { MucCard, MucIcon } from "@muenchen/muc-patternlab-vue";
import { computed } from "vue";

import { AppointmentDTO } from "@/api/models/AppointmentDTO";
import { Office } from "@/api/models/Office";
import { Service } from "@/api/models/Service";
import CalendarIcon from "@/components/Common/CalendarIcon.vue";
import {
  QUERY_PARAM_APPOINTMENT_DISPLAY_NUMBER,
  QUERY_PARAM_APPOINTMENT_ID,
  resolveAgainstCurrentPage,
  VARIANT_ID_PRESENCE,
  VARIANT_ID_TELEPHONE,
  VARIANT_ID_VIDEO,
} from "@/utils/Constants";
import { formatAppointmentDateTime } from "@/utils/formatAppointmentDateTime";
import { formatMultilineTitle } from "@/utils/formatMultilineTitle";

const props = defineProps<{
  appointment: AppointmentDTO;
  appointmentDetailUrl: string;
  offices: Office[];
  services: Service[];
  t: (key: string) => string;
}>();

const selectedProvider = computed<Office | undefined>(() =>
  props.offices.find(
    (office) => String(office.id) === String(props.appointment.officeId)
  )
);

const selectedService = computed<Service | undefined>(() =>
  props.services.find(
    (service) => String(service.id) === String(props.appointment.serviceId)
  )
);

const variantId = computed<number | null>(
  () => selectedService.value?.variantId ?? null
);

const appointmentTypeLabel = computed<string>(() => {
  if (variantId.value === VARIANT_ID_TELEPHONE) {
    return props.t(`appointmentTypes.${VARIANT_ID_TELEPHONE}`);
  }

  if (variantId.value === VARIANT_ID_VIDEO) {
    return props.t(`appointmentTypes.${VARIANT_ID_VIDEO}`);
  }

  return props.t(`appointmentTypes.${VARIANT_ID_PRESENCE}`);
});

const locationIcon = computed<string>(() => {
  if (variantId.value === VARIANT_ID_TELEPHONE) {
    return "telephone";
  }

  if (variantId.value === VARIANT_ID_VIDEO) {
    return "video-camera";
  }

  return "map-pin";
});

const locationText = computed<string>(() => {
  if (variantId.value === VARIANT_ID_TELEPHONE) {
    return props.appointment.telephone ?? "";
  }

  if (variantId.value === VARIANT_ID_VIDEO) {
    return props.t("appointmentDetailVideoIntroLocation");
  }

  return [
    selectedProvider.value?.address.street,
    selectedProvider.value?.address.house_number,
  ]
    .filter(Boolean)
    .join(" ");
});

const getAppointmentLink = () => {
  const url = resolveAgainstCurrentPage(props.appointmentDetailUrl);
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
</script>

<style scoped>
.multiline-text {
  white-space: pre-wrap;
}
</style>
