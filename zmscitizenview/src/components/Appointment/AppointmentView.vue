<template>
  <div class="m-component">
    <div class="container">
      <muc-stepper
        :step-items="STEPPER_ITEMS"
        :active-item="activeStep"
        @change-step="changeStep"
      />
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
      <calendar-view
        v-if="currentView === 1"
        :t="t"
      />
      <customer-info v-if="currentView === 2" />
      <div class="m-submit-group">
        <muc-button
          v-if="showDecreaseViewButton"
          variant="secondary"
          @click="decreaseCurrentView"
        >
          <template #default>{{ t("back") }}</template>
        </muc-button>
        <muc-button
          v-if="showIncreaseViewButton"
          :disabled="disableIncreaseViewButton"
          @click="increaseCurrentView"
        >
          <template #default>{{ t("next") }}</template>
        </muc-button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { MucButton, MucStepper } from "@muenchen/muc-patternlab-vue";
import { onMounted, provide, ref, watch } from "vue";

import { Office } from "@/api/models/Office";
import { Relation } from "@/api/models/Relation";
import { Service } from "@/api/models/Service";
import { fetchServicesAndProviders } from "@/api/ZMSAppointmentAPI";
import CalendarView from "@/components/Appointment/CalendarView.vue";
import CustomerInfo from "@/components/Appointment/CustomerInfo.vue";
import ServiceFinder from "@/components/Appointment/ServiceFinder.vue";
import { ServiceImpl } from "@/types/ServiceImpl";
import { SelectedServiceProvider, SelectedTimeslotProvider } from "@/types/ProvideInjectTypes";
import { StepperItem } from "@/types/StepperTypes";

const props = defineProps<{
  baseUrl: any;
  serviceId?: string;
  locationId?: string;
  appointmentHash?: any;
  confirmAppointmentHash?: any;
  t: any;
}>();

const STEPPER_ITEMS: StepperItem[] = [
  {
    id: "0",
    label: props.t("service"),
    icon: "shopping-cart",
  },
  {
    id: "1",
    label: props.t("appointment"),
    icon: "calendar",
  },
  {
    id: "2",
    label: props.t("contact"),
    icon: "mail",
  },
  {
    id: "3",
    label: props.t("overview"),
    icon: "information",
  },
];

const activeStep = ref<string>("0");

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

const selectedTimeslot = ref<number>(0);

provide<SelectedServiceProvider>("selectedServiceProvider", {
  selectedService,
  updateSelectedService,
} as SelectedServiceProvider);

provide<SelectedTimeslotProvider>("selectedTimeslot", {selectedTimeslot} as SelectedTimeslotProvider);

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

const changeStep = (step: string) => {
  if (parseInt(step) < parseInt(activeStep.value)) {
    currentView.value = parseInt(step);
  }
};

watch(currentView, (newCurrentView) => {
  activeStep.value = newCurrentView.toString();
  showDecreaseViewButton.value = newCurrentView > 0;
  disableIncreaseViewButton.value = newCurrentView === 1 && selectedTimeslot.value === 0;
});

watch(selectedTimeslot, (newTimeslot) => {
  disableIncreaseViewButton.value = currentView.value === 1 && selectedTimeslot.value === 0;
  console.log("TimeSlot: ", newTimeslot);
});
</script>
