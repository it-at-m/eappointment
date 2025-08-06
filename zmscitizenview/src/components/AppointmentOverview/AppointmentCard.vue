<template>
  <muc-card
    class="multiline-text"
    :tagline="t('appointmentOverview.onSiteAppointment')"
    :title="formatTitle()"
    @click="goToAppointmentLink(appointment.processId!)"
  >
    <template #headerPrefix>
      <div
        style="padding-right: 16px; font-size: 32px"
        class="m-teaser-vertical m-teaser-vertical-event"
      >
        <div class="m-teaser-vertical__date-range">
          <time class="m-teaser-vertical__date-range__item">
            <span class="m-teaser-vertical__date-range__item__day">{{
              formatDay()
            }}</span>
            <span class="m-teaser-vertical__date-range__item__month">{{
              formatMonth()
            }}</span>
          </time>
        </div>
      </div>
    </template>
    <template #content>
      <div class="text-padding">
        <muc-icon icon="calendar" />
        {{ formatTime(appointment.timestamp) }}
        {{ t("timeStampSuffix") }} <br />
      </div>
      <div class="text-padding">
        <muc-icon icon="map-pin" />
        {{ selectedProvider?.address.street }}
        {{ selectedProvider?.address.house_number }} <br />
      </div>
      <b>{{ t("appointmentOverview.appointmentNumber") }}:</b>
      {{ appointment.processId }}
    </template>
  </muc-card>
</template>

<script setup lang="ts">
import { MucCard, MucIcon } from "@muenchen/muc-patternlab-vue";
import { onMounted, ref } from "vue";

import { AppointmentDTO } from "@/api/models/AppointmentDTO";
import { Office } from "@/api/models/Office";
import { QUERY_PARAM_APPOINTMENT_ID } from "@/utils/Constants";
import { formatTime } from "@/utils/formatTime";

const props = defineProps<{
  appointment: AppointmentDTO;
  appointmentDetailUrl: string;
  offices: Office[];
  t: (key: string) => string;
}>();

const selectedProvider = ref<Office>();

const formatTitle = (): string => {
  const serviceTitle =
    props.appointment.serviceCount + "x " + props.appointment.serviceName;
  const subserviceTitle = props.appointment.subRequestCounts
    .map((subCount) => subCount.count + "x " + subCount.name)
    .join("\n");
  return serviceTitle + "\n" + subserviceTitle;
};

const formatDay = () => {
  const date = new Date(props.appointment.timestamp * 1000);
  return new Intl.DateTimeFormat("de-DE", { day: "numeric" }).format(date);
};

const formatMonth = () => {
  const date = new Date(props.appointment.timestamp * 1000);
  return new Intl.DateTimeFormat("de-DE", { month: "short" }).format(date);
};

const goToAppointmentLink = (ticketnumber: string) => {
  location.href = `${props.appointmentDetailUrl}?${QUERY_PARAM_APPOINTMENT_ID}=${ticketnumber}`;
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

.m-teaser-vertical {
  border-bottom: 0 !important;
}

.m-teaser-vertical__date-range {
  position: unset !important;
  border-top: 4px solid #005a9f !important;
}
</style>
