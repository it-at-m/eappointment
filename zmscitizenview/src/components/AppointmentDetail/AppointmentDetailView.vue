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
      loadingError
    "
  >
    <muc-intro
      :tagline="t('appointment')"
      :title="appointmentId ? appointmentId : ''"
    />
    <error-alert
      :message="t('apiErrorLoadingAppointmentsText')"
      :header="t('apiErrorLoadingSingleAppointmentHeader')"
    >
      <muc-button
        icon="arrow-right"
        @onclick="goToAppointmentOverviewLink"
      >
        {{ t("buttonBackToOverview") }}
      </muc-button>
    </error-alert>
  </div>
  <div
    v-if="
      !isInMaintenanceModeComputed &&
      !isInSystemFailureModeComputed &&
      !errorStates.errorStateMap.apiErrorRateLimitExceeded.value &&
      !loading
    "
  >
    <appointment-detail-header
      :appointment="appointment"
      :selected-provider="selectedProvider"
      :t="t"
      @cancel-appointment="cancelAppointment"
      @reschedule-appointment="rescheduleAppointment"
    />
    <div class="m-component m-component-form">
      <div class="container">
        <div class="m-component__grid">
          <div class="m-component__column">
            <div class="m-content">
              <h2 tabindex="0">{{ t("time") }}</h2>
            </div>
            <div
              v-if="appointment"
              class="m-content time-container-margin-bottom"
            >
              <div class="timeBox">
                <calendar-icon :timestamp="appointment.timestamp" />
                <p tabindex="0">
                  {{ formatAppointmentDateTime(appointment.timestamp) }}
                  {{ t("timeStampSuffix") }} <br />
                  {{ t("estimatedDuration") }} <br v-if="isMobile" />
                  {{ estimatedDuration() }}
                  {{ t("minutes") }}
                </p>
              </div>
              <p tabindex="0">
                {{ t("detailTimeHint") }}
              </p>
            </div>
            <div class="m-content">
              <h2 tabindex="0">{{ t("location") }}</h2>
            </div>
            <div
              v-if="selectedProvider"
              class="m-content location-text-margin-top"
            >
              <p tabindex="0">
                {{ selectedProvider.organization }}<br />
                <strong> {{ selectedProvider.name }} </strong><br />
              </p>
              <p tabindex="0">
                {{ selectedProvider.address.street }}
                {{ selectedProvider.address.house_number }}<br />
                {{ selectedProvider.address.postal_code }}
                {{ selectedProvider.address.city }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div
      v-if="appointment"
      class="m-component m-component-linklist--boxed"
    >
      <div class="container">
        <div class="m-component__grid">
          <div class="m-component__column">
            <h2
              class="m-component__title"
              tabindex="0"
            >
              {{ t("services") }}
            </h2>
            <div class="m-linklist">
              <ul class="m-linklist__list">
                <li class="m-linklist__list__item">
                  <a
                    class="m-linklist-element m-linklist-element--external"
                    :href="getServiceBaseURL() + appointment.serviceId"
                    target="_blank"
                  >
                    <div class="m-linklist-element__meta">
                      <span class="m-linklist-element__title">{{
                        appointment.serviceName
                      }}</span>
                    </div>
                    <svg
                      aria-hidden="true"
                      class="icon"
                    >
                      <use xlink:href="#icon-arrow-right"></use>
                    </svg>
                  </a>
                </li>
                <li
                  v-for="(subrequest, index) in appointment.subRequestCounts"
                  :key="index"
                  class="m-linklist__list__item"
                >
                  <a
                    class="m-linklist-element m-linklist-element"
                    :href="getServiceBaseURL() + subrequest.id"
                  >
                    <div class="m-linklist-element__meta">
                      <span class="m-linklist-element__title">{{
                        subrequest.name
                      }}</span>
                    </div>
                    <svg
                      aria-hidden="true"
                      class="icon"
                    >
                      <use xlink:href="#icon-arrow-right"></use>
                    </svg>
                  </a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { MucButton, MucIntro } from "@muenchen/muc-patternlab-vue";
import { computed, onMounted, ref } from "vue";

import { AppointmentDTO } from "@/api/models/AppointmentDTO";
import { Office } from "@/api/models/Office";
import { Relation } from "@/api/models/Relation";
import { Service } from "@/api/models/Service";
import { fetchServicesAndProviders } from "@/api/ZMSAppointmentAPI";
import { getAppointmentDetails } from "@/api/ZMSAppointmentUserAPI";
import AppointmentDetailHeader from "@/components/AppointmentDetail/AppointmentDetailHeader.vue";
import CalendarIcon from "@/components/Common/CalendarIcon.vue";
import ErrorAlert from "@/components/Common/ErrorAlert.vue";
import { AppointmentImpl } from "@/types/AppointmentImpl";
import { OfficeImpl } from "@/types/OfficeImpl";
import { ServiceImpl } from "@/types/ServiceImpl";
import { SubService } from "@/types/SubService";
import {
  getApiStatusState,
  handleApiResponse,
  isInMaintenanceMode,
  isInSystemFailureMode,
} from "@/utils/apiStatusService";
import { calculateEstimatedDuration } from "@/utils/calculateEstimatedDuration";
import {
  getServiceBaseURL,
  QUERY_PARAM_APPOINTMENT_ID,
} from "@/utils/Constants";
import {
  createErrorStates,
  getApiErrorTranslation,
  handleApiResponse as handleErrorApiResponse,
} from "@/utils/errorHandler";
import { formatAppointmentDateTime } from "@/utils/formatAppointmentDateTime";
import { getProviders } from "@/utils/getProviders";

const props = defineProps<{
  baseUrl?: string;
  appointmentOverviewUrl: string;
  rescheduleAppointmentUrl: string;
  t: (key: string) => string;
}>();

const services = ref<Service[]>([]);
const relations = ref<Relation[]>([]);
const offices = ref<Office[]>([]);

const appointment = ref<AppointmentImpl>();
const appointmentId = ref<string | null>();
const selectedService = ref<ServiceImpl>();
const selectedProvider = ref<OfficeImpl>();
const loading = ref(true);
const loadingError = ref(false);
const isMobile = ref(false);

// API status state
const apiStatusState = getApiStatusState();
const isInMaintenanceModeComputed = computed(() => isInMaintenanceMode());
const isInSystemFailureModeComputed = computed(() => isInSystemFailureMode());

// Error handling state
const errorStates = createErrorStates();
const currentErrorData = computed(() => errorStates.currentErrorData);
const apiErrorTranslation = computed(() =>
  getApiErrorTranslation(errorStates.errorStateMap, currentErrorData.value)
);

/**
 * This function determines the expected duration of the appointment.
 * The provider is queried for the service and each subservice because the slots for the respective service are stored in this provider.
 */
const estimatedDuration = () => {
  return calculateEstimatedDuration(
    selectedService.value,
    selectedProvider.value
  );
};

const rescheduleAppointment = () => {
  if (appointment.value)
    location.href = `${props.rescheduleAppointmentUrl}?${QUERY_PARAM_APPOINTMENT_ID}=${appointment.value.processId}`;
};

const cancelAppointment = () => {
  // TODO cancelAppointment(appointment.value, props.baseUrl ?? undefined)
};

const goToAppointmentOverviewLink = () => {
  location.href = props.appointmentOverviewUrl;
};

const checksMobile = () => {
  isMobile.value = window.matchMedia("(max-width: 767px)").matches;
};

onMounted(() => {
  loading.value = true;
  checksMobile();
  window.addEventListener("resize", checksMobile);

  const urlParams = new URLSearchParams(window.location.search);
  appointmentId.value = urlParams.get(QUERY_PARAM_APPOINTMENT_ID);
  fetchServicesAndProviders(
    undefined,
    undefined,
    props.baseUrl ?? undefined
  ).then((data) => {
    // Check if any error state should be activated
    if (handleApiResponse(data, props.baseUrl)) {
      return; // Error state was activated, stop processing
    }

    // Handle normal errors (like rate limit)
    handleErrorApiResponse(
      data,
      errorStates.errorStateMap,
      currentErrorData.value
    );

    services.value = data.services;
    relations.value = data.relations;
    offices.value = data.offices;

    if (appointmentId.value) {
      getAppointmentDetails(appointmentId.value).then((data) => {
        if ((data as AppointmentDTO).processId != undefined) {
          appointment.value = data;

          selectedService.value = services.value.find(
            (service) => service.id == appointment.value?.serviceId
          );
          if (selectedService.value) {
            selectedService.value.count = appointment.value.serviceCount;

            selectedService.value.providers = getProviders(
              selectedService.value?.id,
              null,
              relations.value,
              offices.value
            );

            const foundOffice = selectedService.value.providers.find(
              (office) => office.id == appointment.value?.officeId
            );

            if (foundOffice) {
              selectedProvider.value = foundOffice;
            }

            if (appointment.value.subRequestCounts.length > 0) {
              appointment.value.subRequestCounts.forEach((subRequestCount) => {
                const subRequest = services.value.find(
                  (service) => service.id == subRequestCount.id
                ) as Service;
                const subService = new SubService(
                  subRequest.id,
                  subRequest.name,
                  subRequest.maxQuantity,
                  getProviders(
                    subRequest.id,
                    null,
                    relations.value,
                    offices.value
                  ),
                  subRequestCount.count
                );
                if (
                  selectedService.value &&
                  !selectedService.value.subServices
                ) {
                  selectedService.value.subServices = [];
                }
                selectedService.value?.subServices?.push(subService);
              });
            }
          }
          loading.value = false;
        } else {
          loadingError.value = true;
        }
      });
    }
  });
});
</script>

<style scoped>
.m-content p {
  margin-bottom: 16px !important;
}

.location-text-margin-top {
  margin-top: 32px;
}

.time-container-margin-bottom {
  margin-bottom: 64px;
}

.timeBox {
  display: flex;
  align-items: center;
  margin-top: 32px;
  margin-bottom: 32px;
}
.timeBox p {
  margin-bottom: 0 !important;
}
</style>
