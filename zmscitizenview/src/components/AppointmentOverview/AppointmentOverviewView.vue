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
          :type="apiErrorTranslation.errorType"
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
      isAuthenticated()
    "
    class="container"
  >
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
import { computed, onMounted, ref } from "vue";

import { AppointmentDTO } from "@/api/models/AppointmentDTO";
import { Office } from "@/api/models/Office";
import { fetchServicesAndProviders } from "@/api/ZMSAppointmentAPI";
import { getAppointments } from "@/api/ZMSAppointmentUserAPI";
import AddAppointmentCard from "@/components/AppointmentOverview/AddAppointmentCard.vue";
import AddAppointmentSvg from "@/components/AppointmentOverview/AddAppointmentSvg.vue";
import ErrorAlert from "@/components/Common/ErrorAlert.vue";
import SkeletonLoader from "@/components/Common/SkeletonLoader.vue";
import {
  handleApiResponseForDownTime,
  isInMaintenanceMode,
  isInSystemFailureMode,
} from "@/utils/apiStatusService";
import { isAuthenticated } from "@/utils/auth";
import {
  createErrorStates,
  getApiErrorTranslation,
  handleApiResponse as handleErrorApiResponse,
} from "@/utils/errorHandler";
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

// API status state
const isInMaintenanceModeComputed = computed(() => isInMaintenanceMode());
const isInSystemFailureModeComputed = computed(() => isInSystemFailureMode());

// Error handling state
const errorStates = createErrorStates();
const currentErrorData = computed(() => errorStates.currentErrorData);
const apiErrorTranslation = computed(() =>
  getApiErrorTranslation(errorStates.errorStateMap, currentErrorData.value)
);

const goToOverviewLink = () => {
  location.href = props.overviewUrl;
};

onMounted(() => {
  loading.value = true;
  fetchServicesAndProviders(undefined, undefined, props.baseUrl ?? undefined)
    .then((data) => {
      // Check if any error state should be activated
      if (handleApiResponseForDownTime(data, props.baseUrl)) {
        return;
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
