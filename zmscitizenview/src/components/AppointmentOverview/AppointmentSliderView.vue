<template>
  <div
    v-if="appointments.length > 0 || !displayedOnDetailScreen"
    :class="displayedOnDetailScreen ? 'details-background' : 'overview-padding'"
  >
    <div
      class="container"
    >
      <div class="header">
        <div class="headline">
          <span class="header-icon">
            <muc-icon icon="calendar" />
          </span>
          <h2>
            <span v-if="displayedOnDetailScreen">{{ t('appointmentOverview.myAppointments') }}</span>
            <span v-else>{{ t('appointmentOverview.myFurtherAppointments') }}</span>

            <span v-if="appointments.length && !displayedOnDetailScreen && !loadingError"> ({{ appointments.length }})</span>
          </h2>
        </div>
        <muc-link
          v-if="!loadingError && appointments.length > 3 && !isMobile"
          :label="t('appointmentOverview.showAllAppointments')"
          icon="chevron-right"
          target="_self"
          no-underline
          :href="appointmentOverviewUrl"
        />
      </div>
      <error-alert
        v-if="loadingError"
        class="no-padding-top"
        :message="t('appointmentOverview.loadingAppointmentErrorText')"
        :header="t('appointmentOverview.loadingAppointmentErrorHeader')"
      />
      <skeleton-loader
        v-else-if="loading"
        class="container"
      />
      <div v-else>
        <appointment-card-viewer
          :all-appointments="appointments"
          :is-mobile="isMobile"
          :new-appointment-url="newAppointmentUrl"
          :appointment-detail-url="appointmentDetailUrl"
          :displayed-on-detail-screen="displayedOnDetailScreen"
          :offices="offices"
          :t="t"
        />
        <muc-link
          v-if="!loadingError && appointments.length > 3 && isMobile"
          class="mobile-link"
          :label="t('appointmentOverview.showAllAppointments')"
          icon="chevron-right"
          target="_self"
          no-underline
          :href="appointmentOverviewUrl"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {onMounted, ref} from "vue";
import {AppointmentDTO} from "@/api/models/AppointmentDTO";
import {getAppointments} from "@/api/ZMSAppointmentUserAPI";
import ErrorAlert from "@/components/Common/ErrorAlert.vue";
import SkeletonLoader from "@/components/Common/SkeletonLoader.vue";
import AppointmentCardViewer from "./AppointmentCardViewer.vue";
import {fetchServicesAndProviders} from "@/api/ZMSAppointmentAPI";
import {Office} from "@/api/models/Office";
import {MucIcon, MucLink} from "@muenchen/muc-patternlab-vue";


const props = defineProps<{
  baseUrl?: string;
  appointmentDetailUrl: string,
  appointmentOverviewUrl: string,
  newAppointmentUrl: string,
  displayedOnDetailScreen: boolean,
  t: (key: string) => string
}>();

const loading = ref(true);
const loadingError = ref(false);
const isMobile = ref(false);

const appointments = ref<AppointmentDTO[]>([]);
const offices = ref<Office[]>([]);

const appointmentNumber = ref('');

const checksMobile = () => {
  isMobile.value = window.matchMedia('(max-width: 767px)').matches
};

onMounted(() => {
  loading.value = true;
  checksMobile();
  window.addEventListener('resize', checksMobile);

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

  }).finally(() => {
    loading.value = false;
    if(props.displayedOnDetailScreen) {
      appointments.value = appointments.value.filter((appoinement: AppointmentDTO) => appoinement.processId != appointmentNumber.value);
    }
  });

});
</script>

<style>
/* Padding on overview page */
.overview-padding {
  padding-top: 40px;
}

/* Background color on details page */
.details-background {
  background-color: var(--color-neutrals-blue-xlight);
  padding: 24px 0;
}

/* Header styles */
.header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-bottom: 24px;
}

/* Headline styles */
.headline {
  display: flex;
  align-items: center;
}

.header-icon {
  margin-right: 8px;
}

/* Mobile link styles */
.mobile-link {
  padding-top: 24px;
}

/* No padding-top on error message */
.no-padding-top {
  padding-top: 0 !important;
}

/* CSS for desktop */
@media (min-width: 768px) {
  .overview-padding {
    padding-top: 40px;
  }

  .details-background {
    padding: 64px 0;
  }
}
</style>

