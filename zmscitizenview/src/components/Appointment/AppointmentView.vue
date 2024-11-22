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
        @next="setServices"
      />
      <calendar-view
        v-if="currentView === 1"
        :selected-service-map="selectedServiceMap"
        :t="t"
        @back="decreaseCurrentView"
        @next="nextReserveAppointment"
      />
      <customer-info
        v-if="currentView === 2"
        :t="t"
        @back="decreaseCurrentView"
        @next="nextUpdateAppointment"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { MucStepper } from "@muenchen/muc-patternlab-vue";
import { provide, ref, watch } from "vue";

import { AppointmentDTO } from "@/api/models/AppointmentDTO";
import { reserveAppointment, updateAppointment } from "@/api/ZMSAppointmentAPI";
import CalendarView from "@/components/Appointment/CalendarView.vue";
import CustomerInfo from "@/components/Appointment/CustomerInfo.vue";
import ServiceFinder from "@/components/Appointment/ServiceFinder.vue";
import { AppointmentImpl } from "@/types/AppointmentImpl";
import { CustomerData } from "@/types/CustomerData";
import { OfficeImpl } from "@/types/OfficeImpl";
import {
  CustomerDataProvider,
  SelectedAppointmentProvider,
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

const selectedServiceMap = ref<Map<string, number>>(new Map<string, number>());

const selectedProvider = ref<OfficeImpl>();
const selectedTimeslot = ref<number>(0);

const customerData = ref<CustomerData>(new CustomerData("", "", "", "", ""));
const appointment = ref<AppointmentImpl>();

provide<SelectedServiceProvider>("selectedServiceProvider", {
  selectedService,
  updateSelectedService,
} as SelectedServiceProvider);

provide<SelectedTimeslotProvider>("selectedTimeslot", {
  selectedProvider,
  selectedTimeslot,
} as SelectedTimeslotProvider);

provide<CustomerDataProvider>("customerData", {
  customerData: customerData,
} as CustomerDataProvider);

provide<SelectedAppointmentProvider>("appointment", {
  appointment,
} as SelectedAppointmentProvider);

const increaseCurrentView = () => currentView.value++;

const decreaseCurrentView = () => currentView.value--;

const changeStep = (step: string) => {
  if (parseInt(step) < parseInt(activeStep.value)) {
    currentView.value = parseInt(step);
  }
};

const setServices = () => {
  if (selectedService.value) {
    if (selectedService.value.count) {
      selectedServiceMap.value.set(
        selectedService.value.id,
        selectedService.value.count
      );
    }

    if (selectedService.value.subServices) {
      selectedService.value.subServices.forEach((subservice) => {
        if (subservice.count > 0) {
          selectedServiceMap.value.set(
            subservice.id.toString(),
            subservice.count
          );
        }
      });
    }
    increaseCurrentView();
  }
};

const nextReserveAppointment = () => {
  increaseCurrentView();
  reserveAppointment(
    selectedTimeslot.value,
    Array.from(selectedServiceMap.value.keys()),
    Array.from(selectedServiceMap.value.values()),
    selectedProvider.value.id
  ).then((data) => {
    if ((data as AppointmentDTO).processId !== undefined) {
      appointment.value = data as AppointmentDTO;
    } else {
      // error.value = true;
    }
  });
};

const nextUpdateAppointment = () => {
  if (appointment.value) {
    appointment.value.familyName =
      customerData.value.firstName + " " + customerData.value.lastName;
    appointment.value.email = customerData.value.mailAddress;
    appointment.value.telephone = customerData.value.telephoneNumber
      ? customerData.value.telephoneNumber
      : undefined;
    appointment.value.customTextfield = customerData.value.remarks
      ? customerData.value.remarks
      : undefined;

    increaseCurrentView();
    updateAppointment(appointment.value).then((data) => {
      if ((data as AppointmentDTO).processId !== undefined) {
        appointment.value = data as AppointmentDTO;
        console.log("Appointment: ", appointment.value);
      } else {
        // error.value = true;
      }
    });
  }
};

watch(currentView, (newCurrentView) => {
  activeStep.value = newCurrentView.toString();
});
</script>
