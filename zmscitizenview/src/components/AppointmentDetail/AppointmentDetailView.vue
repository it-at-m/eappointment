<template>
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
                {{ formatTime(appointment.timestamp) }}
                {{ t("timeStampSuffix") }} <br />
                {{ t("estimatedDuration") }} {{ estimatedDuration() }}
                {{ t("minutes") }}
              </p>
            </div>
            <p>
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
            <p tabindex="0">{{ selectedProvider.organization }}<br /></p>
            <p tabindex="0">
              <strong> {{ selectedProvider.name }} </strong><br />
            </p>
            <p tabindex="0">
              {{ selectedProvider.address.street }}
              {{ selectedProvider.address.house_number }}<br />
              {{ selectedProvider.address.postal_code }}
              {{ selectedProvider.address.city }}<br />
            </p>
            <p tabindex="0">
              {{ t("detailLocationHint") }}
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
          <h2 class="m-component__title">{{ t("services") }}</h2>
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
                    <use xlink:href="#icon-ext-link"></use>
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
</template>

<script setup lang="ts">
import { onMounted, ref } from "vue";

import { AppointmentDTO } from "@/api/models/AppointmentDTO";
import { Office } from "@/api/models/Office";
import { Relation } from "@/api/models/Relation";
import { Service } from "@/api/models/Service";
import { fetchServicesAndProviders } from "@/api/ZMSAppointmentAPI";
import { getAppointmentDetails } from "@/api/ZMSAppointmentUserAPI";
import AppointmentDetailHeader from "@/components/AppointmentDetail/AppointmentDetailHeader.vue";
import CalendarIcon from "@/components/Common/CalendarIcon.vue";
import { AppointmentImpl } from "@/types/AppointmentImpl";
import { OfficeImpl } from "@/types/OfficeImpl";
import { ServiceImpl } from "@/types/ServiceImpl";
import { SubService } from "@/types/SubService";
import { calculateEstimatedDuration } from "@/utils/calculateEstimatedDuration";
import {
  getServiceBaseURL,
  QUERY_PARAM_APPOINTMENT_ID,
} from "@/utils/Constants";
import { formatTime } from "@/utils/formatTime";
import { getProviders } from "@/utils/getProviders";

const props = defineProps<{
  baseUrl?: string;
  rescheduleAppointmentUrl: string;
  t: (key: string) => string;
}>();

const services = ref<Service[]>([]);
const relations = ref<Relation[]>([]);
const offices = ref<Office[]>([]);

const appointment = ref<AppointmentImpl>();
const selectedService = ref<ServiceImpl>();
const selectedProvider = ref<OfficeImpl>();
const loading = ref(true);
const loadingError = ref(false);

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
  // cancelAppointment(appointment.value, props.baseUrl ?? undefined)
  //   .then((data) => {
  //     if ((data as any)?.errors?.length > 0) {
  //       handleApiResponse(data, errorStateMap.value);
  //       return;
  //     }
  //
  //     if ((data as AppointmentDTO).processId != undefined) {
  //       cancelAppointmentSuccess.value = true;
  //     } else {
  //       cancelAppointmentError.value = true;
  //     }
  //     increaseCurrentView();
  //   })
  //   .finally(() => {
  //     isCancelingAppointment.value = false;
  //   });
};

onMounted(() => {
  loading.value = true;
  fetchServicesAndProviders(
    undefined,
    undefined,
    props.baseUrl ?? undefined
  ).then((data) => {
    services.value = data.services;
    relations.value = data.relations;
    offices.value = data.offices;

    const urlParams = new URLSearchParams(window.location.search);
    const appointmentId = urlParams.get(QUERY_PARAM_APPOINTMENT_ID);
    if (appointmentId) {
      getAppointmentDetails(appointmentId).then((data) => {
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
