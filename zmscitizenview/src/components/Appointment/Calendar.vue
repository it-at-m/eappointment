<template>
  <h2 tabindex="0">{{ t("location") }}</h2>
  <!--  Add location selection-->
  <h2 tabindex="0">{{ t("time") }}</h2>
  <!--  Add calendar-->
  <MucCallout type="warning">
    <template #content>
      {{ t("noAppointmentsAvailable") }}
    </template>

    <template #header>{{ t("noAppointmentsAvailableHeader") }}</template>
  </MucCallout>
  <h3 tabindex="0">{{ t("availableTimes") }}</h3>


</template>

<script setup lang="ts">
import { MucCallout } from "@muenchen/muc-patternlab-vue";
import {inject, onMounted, ref} from "vue";
import {SelectedServiceProvider} from "@/types/ServiceTypes";
import {OfficeImpl} from "@/types/OfficeImpl";
import {fetchAvailableDays, fetchAvailableTimeSlots} from "@/api/ZMSAppointmentAPI";
import {AvailableDaysDTO} from "@/api/models/AvailableDaysDTO";
import {AvailableTimeSlotsDTO} from "@/api/models/AvailableTimeSlotsDTO";

defineProps<{
  t: any;
}>();

const { selectedService } =
  inject<SelectedServiceProvider>("selectedServiceProvider") as SelectedServiceProvider;

const selectableProviders = ref<Array<OfficeImpl>>();
const currentProvider = ref<OfficeImpl>();
const displayInfo = ref<string>();
const selectedServices = ref<Map<string, number>>(new Map<string, number>);
const availableDays = ref<Array<string>>();
const appointmentTimestamps = ref<Array<number>>();

const showSelectionForProvider = (provider: OfficeImpl) => {

  currentProvider.value = provider;

  if (provider.scope && provider.scope.displayInfo && provider.scope.displayInfo.length > 0) {
    displayInfo.value = provider.scope.displayInfo;
  } else {
    displayInfo.value = undefined;
  }

  fetchAvailableDays(
    currentProvider.value,
    Array.from(selectedServices.value.keys()),
    Array.from(selectedServices.value.values())
  ).then((data) => {
    if (data as AvailableDaysDTO) {
      availableDays.value = (data as AvailableDaysDTO).availableDays;
      getAppointmentsOfDay(availableDays.value[0]);
    }
  });
}

const getAppointmentsOfDay = (date: string) => {

  fetchAvailableTimeSlots(
    date,
    currentProvider.value,
    Array.from(selectedServices.value.keys()),
    Array.from(selectedServices.value.values())
  ).then((data) => {
    if (data as AvailableTimeSlotsDTO) {
      appointmentTimestamps.value = (data as AvailableTimeSlotsDTO).appointmentTimestamps;
    }
  });

}

onMounted(() => {

  if (selectedService.value) {
    if (selectedService.value.count) {
      selectedServices.value.set(selectedService.value.id, selectedService.value.count);
    }

    if (selectedService.value.subServices) {
      selectedService.value.subServices.forEach((subservice) => {
        if (subservice.count > 0) {
          selectedServices.value.set(subservice.id.toString(), subservice.count);
        }
      });
    }

    if (selectedService.value.providers && selectedService.value.subServices) {
      let choosenSubservices = selectedService.value.subServices.filter((subservice) => subservice.count > 0);
      selectableProviders.value = selectedService.value.providers.filter((provider) => {
        return choosenSubservices.every((subservice) => {
          return subservice.providers.some((subserviceProvider) => subserviceProvider.id === provider.id);
        });
      });
      if (selectableProviders.value.length > 0) showSelectionForProvider(selectableProviders.value[0]);
    }

  }

});
</script>

<style scoped></style>
