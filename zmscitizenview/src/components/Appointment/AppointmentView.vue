<template>
  <div class="m-component">
    <div class="container">
      <service-finder
        v-if="currentView === 0"
        :services="services"
        :relations="relations"
        :offices="offices"
        :preselected-service-id="serviceId"
        :preselected-offive-id="locationId"
        :t="t"
        @set-service="setShowIncreaseViewButton"
      />
      <calendar
        v-if="currentView === 1"
        :t="t"
      />
      <customer-info v-if="currentView === 2" />
      <div class="m-submit-group">
        <muc-button
          v-if="showDecreaseViewButton"
          :disabled="disableIncreaseViewButton"
          variant="secondary"
          @click="decreaseCurrentView"
        >
          <template #default>{{ t("back") }}</template>
        </muc-button>
        <muc-button
          v-if="showIncreaseViewButton"
          @click="increaseCurrentView"
        >
          <template #default>{{ t("next") }}</template>
        </muc-button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { MucButton } from "@muenchen/muc-patternlab-vue";
import { onMounted, provide, ref, watch } from "vue";

import { Office } from "@/api/models/Office";
import { Relation } from "@/api/models/Relation";
import { Service } from "@/api/models/Service";
import { fetchServicesAndProviders } from "@/api/ZMSAppointmentAPI";
import Calendar from "@/components/Appointment/CalendarView.vue";
import CustomerInfo from "@/components/Appointment/CustomerInfo.vue";
import ServiceFinder from "@/components/Appointment/ServiceFinder.vue";
import { ServiceImpl } from "@/types/ServiceImpl";
import { SelectedServiceProvider } from "@/types/ServiceTypes";

const props = defineProps<{
  baseUrl: any;
  serviceId?: string;
  locationId?: string;
  appointmentHash?: any;
  confirmAppointmentHash?: any;
  t: any;
}>();

const services = ref<Service[]>([]);
const relations = ref<Relation[]>([]);
const offices = ref<Office[]>([]);

const currentView = ref<number>(0);
const disableIncreaseViewButton = ref<boolean>(false);
const showIncreaseViewButton = ref<boolean>(false);
const showDecreaseViewButton = ref<boolean>(false);

const selectedService = ref<ServiceImpl>();
const updateSelectedService = (newService: ServiceImpl): void => {
  selectedService.value = newService;
};

provide<SelectedServiceProvider>("selectedServiceProvider", {
  selectedService,
  updateSelectedService,
} as SelectedServiceProvider);

onMounted(() => {
  loadData();
});

function loadData() {
  fetchServicesAndProviders(
    props.serviceId ?? undefined,
    props.locationId ?? undefined
  ).then((data) => {
    services.value = data.services;
    relations.value = data.relations;
    offices.value = data.offices;
  });
}

const increaseCurrentView = () => currentView.value++;

const decreaseCurrentView = () => currentView.value--;

const setShowIncreaseViewButton = () => (showIncreaseViewButton.value = true);

watch(currentView, (newCurrentView) =>
  newCurrentView > 0
    ? (showDecreaseViewButton.value = true)
    : (showDecreaseViewButton.value = false)
);
</script>
