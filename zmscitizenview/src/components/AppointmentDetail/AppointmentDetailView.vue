<template>
  <p>Detailansicht</p>
  <p>{{ selectedProvider?.address }}</p>
  <p>{{ appointment }}</p>
  <div class="m-content">
    <h3 tabindex="0">{{ t("time") }}</h3>
  </div>
  <div
    v-if="appointment"
    class="m-content border-bottom"
  >
    <p tabindex="0">
      {{ formatTime(appointment.timestamp) }}
      {{ t("timeStampSuffix") }} <br />
      {{ t("estimatedDuration") }} {{ estimatedDuration() }}
      {{ t("minutes") }}<br />
    </p>
  </div>
</template>

<script setup lang="ts">
import {onMounted, ref} from "vue";
import {AppointmentDTO} from "@/api/models/AppointmentDTO";
import {getAppointmentDetails} from "@/api/ZMSAppointmentUserAPI";
import {Service} from "@/api/models/Service";
import {Relation} from "@/api/models/Relation";
import {Office} from "@/api/models/Office";
import {fetchServicesAndProviders} from "@/api/ZMSAppointmentAPI";
import {OfficeImpl} from "@/types/OfficeImpl";
import {AppointmentImpl} from "@/types/AppointmentImpl";
import {ServiceImpl} from "@/types/ServiceImpl";
import {SubService} from "@/types/SubService";
import {getProviders} from "@/utils/getProviders";
import {formatTime} from "@/utils/formatTime";
import {calculateEstimatedDuration} from "@/utils/calculateEstimatedDuration";

const props = defineProps<{
  baseUrl?: string;
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

    getAppointmentDetails("1111").then(
      (data) => {
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
              appointment.value.subRequestCounts.forEach(
                (subRequestCount) => {
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
                }
              );
            }
          }
        } else {
          loadingError.value = true;
        }
      }
    );
  });
});
</script>
