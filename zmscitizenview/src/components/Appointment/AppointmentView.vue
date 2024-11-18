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
        :preselected-service-id="serviceId"
        :preselected-offive-id="locationId"
        :t="t"
        @next="increaseCurrentView"
      />
      <calendar-view
        v-if="currentView === 1"
        :t="t"
        @back="decreaseCurrentView"
        @next="increaseCurrentView"
      />
      <customer-info
        v-if="currentView === 2"
        :t="t"
        @back="decreaseCurrentView"
        @next="increaseCurrentView"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { MucStepper } from "@muenchen/muc-patternlab-vue";
import { provide, ref, watch } from "vue";

import CalendarView from "@/components/Appointment/CalendarView.vue";
import CustomerInfo from "@/components/Appointment/CustomerInfo.vue";
import ServiceFinder from "@/components/Appointment/ServiceFinder.vue";
import {
  SelectedServiceProvider,
  SelectedTimeslotProvider,
} from "@/types/ProvideInjectTypes";
import { ServiceImpl } from "@/types/ServiceImpl";
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

const currentView = ref<number>(0);

const selectedService = ref<ServiceImpl>();
const updateSelectedService = (newService: ServiceImpl): void => {
  selectedService.value = newService;
};

const selectedTimeslot = ref<number>(0);

provide<SelectedServiceProvider>("selectedServiceProvider", {
  selectedService,
  updateSelectedService,
} as SelectedServiceProvider);

provide<SelectedTimeslotProvider>("selectedTimeslot", {
  selectedTimeslot,
} as SelectedTimeslotProvider);

const increaseCurrentView = () => currentView.value++;

const decreaseCurrentView = () => currentView.value--;

const changeStep = (step: string) => {
  if (parseInt(step) < parseInt(activeStep.value)) {
    currentView.value = parseInt(step);
  }
};

watch(currentView, (newCurrentView) => {
  activeStep.value = newCurrentView.toString();
});

watch(selectedTimeslot, (newTimeslot) => {
  console.log("TimeSlot: ", newTimeslot);
});
</script>
