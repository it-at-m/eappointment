<template>
  <!-- Maintenance Page -->
  <div
    v-if="isInMaintenanceModeComputed"
    class="container"
  >
    <div class="m-component__grid">
      <div class="m-component__column">
        <error-alert
          :message="t('maintenancePageText')"
          :header="t('maintenancePageHeader')"
          type="warning"
        />
      </div>
    </div>
  </div>

  <!-- System Failure Page -->
  <div
    v-if="isInSystemFailureModeComputed"
    class="container"
  >
    <div class="m-component__grid">
      <div class="m-component__column">
        <error-alert
          :message="t('systemFailurePageText')"
          :header="t('systemFailurePageHeader')"
          type="error"
        />
      </div>
    </div>
  </div>

  <!-- Error Alert (for rate limit, etc.) -->
  <div
    v-if="
      !isInMaintenanceModeComputed &&
      !isInSystemFailureModeComputed &&
      errorStates.errorStateMap.apiErrorRateLimitExceeded.value
    "
    class="container"
  >
    <div class="m-component__grid">
      <div class="m-component__column">
        <error-alert
          :message="t(apiErrorTranslation.textKey)"
          :header="t(apiErrorTranslation.headerKey)"
        />
      </div>
    </div>
  </div>

  <!-- Normal Content -->
  <div
    v-if="
      !isInMaintenanceModeComputed &&
      !isInSystemFailureModeComputed &&
      !errorStates.errorStateMap.apiErrorRateLimitExceeded.value &&
      (appointments.length > 0 || !displayedOnDetailScreen)
    "
    :class="displayedOnDetailScreen ? 'details-background' : 'overview-padding'"
  >
    <div class="container">
      <div class="header">
        <div class="headline">
          <span class="header-icon">
            <muc-icon icon="calendar" />
          </span>
          <h2 tabindex="0">
            <span v-if="displayedOnDetailScreen">{{
              t("myFurtherAppointments")
            }}</span>
            <span v-else>{{ t("myAppointments") }}</span>

            <span
              v-if="
                appointments.length && !displayedOnDetailScreen && !loadingError
              "
            >
              ({{ appointments.length }})</span
            >
          </h2>
        </div>
        <muc-link
          v-if="!loadingError && appointments.length > 3 && !isMobile"
          :label="t('showAllAppointments')"
          icon="chevron-right"
          target="_self"
          no-underline
          :href="appointmentOverviewUrl"
        />
      </div>
      <error-alert
        v-if="loadingError"
        class="no-padding-top"
        :message="t('apiErrorLoadingAppointmentsText')"
        :header="t('apiErrorLoadingAppointmentsHeader')"
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
          :label="t('showAllAppointments')"
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
import { MucIcon, MucLink } from "@muenchen/muc-patternlab-vue";
import { computed, onMounted, onUnmounted, ref } from "vue";

import { AppointmentDTO } from "@/api/models/AppointmentDTO";
import { Office } from "@/api/models/Office";
import { fetchServicesAndProviders } from "@/api/ZMSAppointmentAPI";
import { getAppointments } from "@/api/ZMSAppointmentUserAPI";
import ErrorAlert from "@/components/Common/ErrorAlert.vue";
import SkeletonLoader from "@/components/Common/SkeletonLoader.vue";
import {
  getApiStatusState,
  handleApiResponseForDownTime,
  isInMaintenanceMode,
  isInSystemFailureMode,
} from "@/utils/apiStatusService";
import { QUERY_PARAM_APPOINTMENT_ID } from "@/utils/Constants";
import {
  createErrorStates,
  getApiErrorTranslation,
  handleApiResponse as handleErrorApiResponse,
} from "@/utils/errorHandler";
import AppointmentCardViewer from "./AppointmentCardViewer.vue";

const props = defineProps<{
  baseUrl?: string;
  appointmentDetailUrl: string;
  appointmentOverviewUrl: string;
  newAppointmentUrl: string;
  displayedOnDetailScreen: boolean;
  t: (key: string) => string;
}>();

const loading = ref(true);
const loadingError = ref(false);
const isMobile = ref(false);

const appointments = ref<AppointmentDTO[]>([]);
const offices = ref<Office[]>([]);

// API status state
const isInMaintenanceModeComputed = computed(() => isInMaintenanceMode());
const isInSystemFailureModeComputed = computed(() => isInSystemFailureMode());

// Error handling state
const errorStates = createErrorStates();
const currentErrorData = computed(() => errorStates.currentErrorData);
const apiErrorTranslation = computed(() =>
  getApiErrorTranslation(errorStates.errorStateMap, currentErrorData.value)
);

const checksMobile = () => {
  isMobile.value = window.matchMedia("(max-width: 767px)").matches;
};

onMounted(() => {
  loading.value = true;
  checksMobile();
  window.addEventListener("resize", checksMobile);

  fetchServicesAndProviders(undefined, undefined, props.baseUrl ?? undefined)
    .then((data) => {
      // Check if any error state should be activated
      if (handleApiResponseForDownTime(data, props.baseUrl)) {
        return; // Error state was activated, stop processing
      }

      // Handle normal errors (like rate limit)
      handleErrorApiResponse(
        data,
        errorStates.errorStateMap,
        currentErrorData.value
      );

      offices.value = data.offices;
      getAppointments("user").then((data) => {
        if (
          Array.isArray(data) &&
          data.every((item) => item.processId !== undefined)
        ) {
          appointments.value = data;
          if (props.displayedOnDetailScreen) {
            const urlParams = new URLSearchParams(window.location.search);
            const appointmentId = urlParams.get(QUERY_PARAM_APPOINTMENT_ID);
            appointments.value = appointments.value.filter(
              (appointment: AppointmentDTO) =>
                appointment.processId != appointmentId
            );
          }
        } else {
          loadingError.value = true;
        }
      });
    })
    .finally(() => {
      loading.value = false;
    });
});

onUnmounted(() => {
  window.removeEventListener("resize", checksMobile);
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
