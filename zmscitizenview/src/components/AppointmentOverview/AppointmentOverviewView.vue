<template>
  <div class="container">
    <h2
      tabindex="0"
      style="display: flex; align-items: center; margin-bottom: 24px"
    >
      <muc-icon
        style="width: 32px; height: 32px; margin-right: 8px"
        icon="calendar"
      />
      {{ t("upcomingAppointments") }} ({{ appointments.length }})
    </h2>
    <error-alert
      v-if="loadingError"
      :message="t('apiErrorLoadingAppointmentsText')"
      :header="t('apiErrorLoadingAppointmentsHeader')"
    >
      <muc-button
        icon="arrow-right"
        @onclick="goToOverviewLink"
      >
        {{ t("buttonBackToOverview") }}
      </muc-button>
    </error-alert>
    <div v-else>
      <muc-card-container
        v-if="loading"
        class="appointment-card-container"
      >
        <skeleton-loader
          v-for="elem in [1, 2, 3, 4]"
          :key="elem"
        >
        </skeleton-loader>
      </muc-card-container>
      <muc-card-container
        v-else
        class="appointment-card-container"
      >
        <appointment-card
          v-for="(appointment, index) in appointments"
          :key="index"
          :appointment="appointment"
          :appointment-detail-url="appointmentDetailUrl"
          :offices="offices"
          :t="t"
          tabindex="0"
        >
        </appointment-card>
        <add-appointment-card
          :title="t('newAppointmentTitle')"
          :new-appointment-url="newAppointmentUrl"
          :t="t"
        >
          <template #content>
            <add-appointment-svg />
          </template>
        </add-appointment-card>
      </muc-card-container>
    </div>
  </div>
</template>

<script setup lang="ts">
import {
  MucButton,
  MucCardContainer,
  MucIcon,
} from "@muenchen/muc-patternlab-vue";
import { onMounted, ref } from "vue";

import { AppointmentDTO } from "@/api/models/AppointmentDTO";
import { Office } from "@/api/models/Office";
import { fetchServicesAndProviders } from "@/api/ZMSAppointmentAPI";
import { getMyAppointments } from "@/api/ZMSAppointmentUserAPI";
import AddAppointmentCard from "@/components/AppointmentOverview/AddAppointmentCard.vue";
import AddAppointmentSvg from "@/components/AppointmentOverview/AddAppointmentSvg.vue";
import ErrorAlert from "@/components/Common/ErrorAlert.vue";
import SkeletonLoader from "@/components/Common/SkeletonLoader.vue";
import AppointmentCard from "./AppointmentCard.vue";

const props = defineProps<{
  baseUrl?: string;
  appointmentDetailUrl: string;
  newAppointmentUrl: string;
  overviewUrl: string;
  t: (key: string) => string;
}>();

const appointments = ref<AppointmentDTO[]>([]);
const offices = ref<Office[]>([]);
const loading = ref(true);
const loadingError = ref(false);

const goToOverviewLink = () => {
  location.href = props.overviewUrl;
};

onMounted(() => {
  loading.value = true;
  fetchServicesAndProviders(undefined, undefined, props.baseUrl ?? undefined)
    .then((data) => {
      offices.value = data.offices;
      getMyAppointments(props.baseUrl).then((data) => {
        if (
          Array.isArray(data) &&
          data.every((item) => item.processId !== undefined)
        ) {
          appointments.value = data;
        } else {
          loadingError.value = true;
        }
      });
    })
    .finally(() => (loading.value = false));
});
</script>

<style scoped>
.m-button {
  margin-bottom: 0 !important;
}

.card:hover {
  background-color: var(--color-neutrals-blue-xlight) !important;
}

.appointment-card-container {
  grid-template-columns: repeat(auto-fit, 100%);
}

@media (min-width: 768px) {
  .appointment-card-container {
    grid-template-columns: repeat(auto-fit, 589px);
  }
}
</style>
