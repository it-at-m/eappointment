<template>
  <div class="m-component">
    <div
      v-if="!confirmAppointmentHash && currentView < 4"
      class="container"
    >
      <muc-stepper
        :step-items="STEPPER_ITEMS"
        :active-item="activeStep"
        @change-step="changeStep"
      />
      <div v-if="currentView === 0">
        <service-finder
          :preselected-service-id="serviceId"
          :preselected-offive-id="locationId"
          :t="t"
          @next="setServices"
        />
      </div>
      <div v-if="currentView === 1">
        <calendar-view
          :selected-service-map="selectedServiceMap"
          :t="t"
          @back="decreaseCurrentView"
          @next="nextReserveAppointment"
        />
        <div v-if="appointmentNotAvailableError">
          <muc-callout type="error">
            <template #content>
              {{ t("selectedDateNoLongerAvailableText") }}
            </template>

            <template #header>{{
              t("selectedDateNoLongerAvailableHeader")
            }}</template>
          </muc-callout>
        </div>
      </div>
      <div v-if="currentView === 2">
        <customer-info
          :t="t"
          @back="decreaseCurrentView"
          @next="nextUpdateAppointment"
        />
      </div>
      <div v-if="currentView === 3">
        <!-- Delete tooManyAppointmentsWithSameMailError if contact is transferred from backend call offices-and-services    -->
        <appointment-summary
          v-if="
            !updateAppointmentError && !tooManyAppointmentsWithSameMailError
          "
          :t="t"
          @back="decreaseCurrentView"
          @book-appointment="nextBookAppointment"
        />
        <div v-if="tooManyAppointmentsWithSameMailError">
          <muc-callout type="error">
            <template #content>
              {{ t("tooManyAppointmentsWithSameMailErrorText") }}
            </template>

            <template #header>{{
              t("tooManyAppointmentsWithSameMailErrorHeader")
            }}</template>
          </muc-callout>
        </div>
        <div v-if="updateAppointmentError">
          <muc-callout type="error">
            <template #content>
              {{ t("updateAppointmentErrorText") }}
            </template>

            <template #header>{{ t("updateAppointmentErrorHeader") }}</template>
          </muc-callout>
        </div>
      </div>
    </div>
    <div
      v-if="currentView === 4"
      class="container"
    >
      <muc-callout type="warning">
        <template #content>
          {{ t("confirmAppointmentText") }}
        </template>

        <template #header>{{ t("confirmAppointmentHeader") }}</template>
      </muc-callout>
    </div>
    <div
      v-else
      class="container"
    >
      <muc-callout
        v-if="confirmAppointmentSuccess"
        type="success"
      >
        <template #content>
          {{ t("appointmentSuccessfullyBookedText") }}
        </template>

        <template #header>{{
          t("appointmentSuccessfullyBookedHeader")
        }}</template>
      </muc-callout>
      <muc-callout
        v-if="confirmAppointmentError"
        type="error"
      >
        <template #content>
          {{ t("appointmentBookingErrorText") }}
        </template>

        <template #header>{{ t("appointmentBookingErrorHeader") }}</template>
      </muc-callout>
    </div>
  </div>
</template>

<script setup lang="ts">
import { MucCallout, MucStepper } from "@muenchen/muc-patternlab-vue";
import { onMounted, provide, ref, watch } from "vue";

import { AppointmentDTO } from "@/api/models/AppointmentDTO";
import { ErrorDTO } from "@/api/models/ErrorDTO";
import { Service } from "@/api/models/Service";
import {
  confirmAppointment,
  fetchAppointment,
  fetchServicesAndProviders,
  preconfirmAppointment,
  reserveAppointment,
  updateAppointment,
} from "@/api/ZMSAppointmentAPI";
import AppointmentSummary from "@/components/Appointment/AppointmentSummary.vue";
import CalendarView from "@/components/Appointment/CalendarView.vue";
import CustomerInfo from "@/components/Appointment/CustomerInfo.vue";
import ServiceFinder from "@/components/Appointment/ServiceFinder.vue";
import { AppointmentHash } from "@/types/AppointmentHashTypes";
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
import { SubService } from "@/types/SubService";

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

const services = ref<Service[]>([]);

const appointmentNotAvailableError = ref<boolean>(false);
const updateAppointmentError = ref<boolean>(false);
const tooManyAppointmentsWithSameMailError = ref<boolean>(false);

const confirmAppointmentSuccess = ref<boolean>(false);
const confirmAppointmentError = ref<boolean>(false);

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
  reserveAppointment(
    selectedTimeslot.value,
    Array.from(selectedServiceMap.value.keys()),
    Array.from(selectedServiceMap.value.values()),
    selectedProvider.value.id
  ).then((data) => {
    if ((data as AppointmentDTO).processId !== undefined) {
      appointment.value = data as AppointmentDTO;
      increaseCurrentView();
    } else {
      if ((data as ErrorDTO).errorCode === "appointmentNotAvailable") {
        appointmentNotAvailableError.value = true;
      }
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

    updateAppointment(appointment.value).then((data) => {
      if ((data as AppointmentDTO).processId !== undefined) {
        appointment.value = data as AppointmentDTO;
      } else {
        if (
          (data as ErrorDTO).errorCode === "tooManyAppointmentsWithSameMail"
        ) {
          tooManyAppointmentsWithSameMailError.value = true;
        } else {
          updateAppointmentError.value = true;
        }
      }
      increaseCurrentView();
    });
  }
};

const nextBookAppointment = () => {
  if (appointment.value) {
    preconfirmAppointment(appointment.value).then((data) => {
      if ((data as AppointmentDTO).processId !== undefined) {
        appointment.value = data as AppointmentDTO;
      } else {
        // error.value = true;
      }
      increaseCurrentView();
    });
  }
};

watch(currentView, (newCurrentView) => {
  activeStep.value = newCurrentView.toString();
});

onMounted(() => {
  if (props.confirmAppointmentHash) {
    let appointmentData: AppointmentHash;
    try {
      appointmentData = JSON.parse(window.atob(props.confirmAppointmentHash));
      if (
        appointmentData.id === undefined ||
        appointmentData.authKey === undefined
      ) {
        confirmAppointmentError.value = true;
        return;
      }
    } catch {
      confirmAppointmentError.value = true;
      return;
    }
    confirmAppointment(appointmentData).then((data) => {
      if ((data as AppointmentDTO).processId !== undefined) {
        confirmAppointmentSuccess.value = true;
      } else {
        confirmAppointmentError.value = true;
      }
    });
  }

  if (props.appointmentHash) {
    fetchServicesAndProviders(
      props.serviceId ?? undefined,
      props.locationId ?? undefined
    ).then((data) => {
      services.value = data.services;
      let appointmentData: AppointmentHash;
      try {
        appointmentData = JSON.parse(window.atob(props.appointmentHash));
        if (
          appointmentData.id === undefined ||
          appointmentData.authKey === undefined
        ) {
          confirmAppointmentError.value = true;
          return;
        }
      } catch {
        confirmAppointmentError.value = true;
        return;
      }
      fetchAppointment(appointmentData).then((data) => {
        if ((data as AppointmentDTO).processId !== undefined) {
          appointment.value = data as AppointmentDTO;
          selectedService.value = services.value.find(
            (service) => service.id === appointment.value.serviceId
          );
          selectedService.value.count = appointment.value.serviceCount;

          if (appointment.value.subRequestCounts.length > 0) {
            appointment.value.subRequestCounts.forEach((subRequestCount) => {
              const subRequest: Service = services.value.find(
                (service) => service.id === subRequestCount.id
              );
              const subService = new SubService(
                subRequest.id,
                subRequest.name,
                subRequest.maxQuantity,
                [],
                subRequestCount.count
              );
              if (!selectedService.value.subServices) {
                selectedService.value.subServices = [];
              }
              selectedService.value.subServices.push(subService);
            });
          }
          currentView.value = 3;
        } else {
          //confirmAppointmentError.value = true;
        }
      });
    });
  }
});
</script>
