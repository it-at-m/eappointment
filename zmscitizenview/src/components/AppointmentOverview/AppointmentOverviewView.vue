<template>
  <div class="container">
    <h2 style="display: flex; align-items: center; margin-bottom: 24px">
      <muc-icon
        style="width: 32px; height: 32px; margin-right: 8px"
        icon="calendar"
      />
      {{ t('appointmentOverview.upcomingAppointments') }} ({{ appointments.length }})
    </h2>
    <error-alert
      v-if="loadingError"
      :message="t('appointmentOverview.loadingAppointmentErrorText')"
      :header="t('appointmentOverview.loadingAppointmentErrorHeader')"
    >
      <muc-button
        icon="arrow-right"
        @onclick="goToOverviewLink"
      > {{ t('appointmentOverview.buttonBackToOverview') }} </muc-button>
    </error-alert>
    <div v-else>
      <muc-card-container
        v-if="loading"
        class="checklist-card-container"
      >
        <skeleton-loader
          v-for="elem in [1, 2, 3, 4]"
          :key="elem"
        >
        </skeleton-loader>
      </muc-card-container>
      <muc-card-container
        v-else
        class="checklist-card-container"
      >
        <appointment-card
          v-for="(appoinement, index) in appointments"
          :key="index"
          :appointment="appoinement"
          :appointment-detail-url="appointmentDetailUrl"
          :offices="offices"
          :t="t"
        >
        </appointment-card>
      </muc-card-container>
    </div>
  </div>
</template>

<script setup lang="ts">
import {  onMounted, ref } from "vue";
import {MucButton, MucCardContainer, MucIcon} from "@muenchen/muc-patternlab-vue";
import {AppointmentDTO} from "@/api/models/AppointmentDTO";
import {getAppointments} from "@/api/ZMSAppointmentUserAPI";
import ErrorAlert from "@/components/Common/ErrorAlert.vue";
import SkeletonLoader from "@/components/Common/SkeletonLoader.vue";
import AppointmentCard from "./AppointmentCard.vue";
import {fetchServicesAndProviders} from "@/api/ZMSAppointmentAPI";
import {Office} from "@/api/models/Office";

const props = defineProps<{
  baseUrl?: string;
  appointmentDetailUrl: string,
  newAppointmentUrl: string,
  overviewUrl: string,
  t: (key: string) => string
}>();

const appointments = ref<AppointmentDTO[]>([]);
const offices = ref<Office[]>([]);
const loading = ref(true);
const loadingError = ref(false);

const goToOverviewLink = () =>{
  location.href = props.overviewUrl;
}

onMounted(() => {
  loading.value = true;
  fetchServicesAndProviders(
    undefined,
    undefined,
    props.baseUrl ?? undefined
  ).then((data) => {
       offices.value = data.offices;
    getAppointments("user").then(
      (data) => {
        if (Array.isArray(data) && data.every(item => item.processId !== undefined)) {
          appointments.value = data;
        } else {
          loadingError.value = true;
        }
      }
    )

  }).finally(() => (loading.value = false));

});

</script>

<style scoped>
.m-button {
  margin-bottom: 0 !important;
}

.checklist-card-container {
  grid-template-columns: repeat(auto-fit, 100%);
}

@media (min-width: 768px) {
  .checklist-card-container {
    grid-template-columns: repeat(auto-fit, 589px);
  }
}
</style>
