<template>
  <div class="m-component m-component-form">
    <div
      v-if="
        !confirmAppointmentHash && !appointmentNotFoundError && currentView < 4
      "
    >
      <muc-stepper
        :step-items="STEPPER_ITEMS"
        :active-item="activeStep"
        :disable-previous-steps="!!appointmentHash"
        @change-step="changeStep"
      />
      <div class="container">
        <div class="m-component__grid">
          <div class="m-component__column">
            <div v-if="currentView === 0 && !appointmentHash">
              <service-finder
                :base-url="baseUrl"
                :preselected-service-id="serviceId"
                :preselected-office-id="locationId"
                :exclusive-location="exclusiveLocation"
                :t="t"
                @next="setServices"
              />
            </div>
            <div v-if="currentView === 1">
              <calendar-view
                :base-url="baseUrl"
                :is-rebooking="isRebooking"
                :exclusive-location="exclusiveLocation"
                :preselected-office-id="preselectedLocationId"
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
                  !updateAppointmentError &&
                  !tooManyAppointmentsWithSameMailError
                "
                :is-rebooking="isRebooking"
                :rebook-or-cancel-dialog="rebookOrCanelDialog"
                :t="t"
                @back="decreaseCurrentView"
                @book-appointment="nextBookAppointment"
                @cancel-appointment="nextCancelAppointment"
                @cancel-reschedule="nextCancelReschedule"
                @reschedule-appointment="nextRescheduleAppointment"
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

                  <template #header>{{
                    t("updateAppointmentErrorHeader")
                  }}</template>
                </muc-callout>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="m-component__grid">
      <div class="m-component__column">
        <div
          v-if="currentView === 4"
          class="container"
        >
          <muc-callout
            v-if="!cancelAppointmentSuccess"
            type="warning"
          >
            <template #content>
              {{ t("confirmAppointmentText") }}
            </template>

            <template #header>{{ t("confirmAppointmentHeader") }}</template>
          </muc-callout>
          <muc-callout
            v-if="cancelAppointmentSuccess"
            type="success"
          >
            <template #content>
              {{ t("appointmentSuccessfullyCanceledText") }}
            </template>

            <template #header>{{
              t("appointmentSuccessfullyCanceledHeader")
            }}</template>
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

            <template #header>{{
              t("appointmentBookingErrorHeader")
            }}</template>
          </muc-callout>
          <muc-callout
            v-if="appointmentNotFoundError"
            type="error"
          >
            <template #content>
              {{ t("appointmentNotFoundErrorText") }}
            </template>

            <template #header>{{
              t("appointmentNotFoundErrorHeader")
            }}</template>
          </muc-callout>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { MucCallout, MucStepper } from "@muenchen/muc-patternlab-vue";
import { onMounted, provide, ref, watch } from "vue";

import { AppointmentDTO } from "@/api/models/AppointmentDTO";
import { ErrorDTO } from "@/api/models/ErrorDTO";
import { Office } from "@/api/models/Office";
import { Relation } from "@/api/models/Relation";
import { Service } from "@/api/models/Service";
import {
  cancelAppointment,
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
  baseUrl?: string;
  serviceId?: string;
  locationId?: string;
  exclusiveLocation?: string;
  appointmentHash?: string;
  confirmAppointmentHash?: string;
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
const rebookedAppointment = ref<AppointmentImpl>();

const services = ref<Service[]>([]);
const relations = ref<Relation[]>([]);
const offices = ref<Office[]>([]);

const rebookOrCanelDialog = ref<boolean>(false);
const isRebooking = ref<boolean>(false);

const appointmentNotAvailableError = ref<boolean>(false);
const updateAppointmentError = ref<boolean>(false);
const tooManyAppointmentsWithSameMailError = ref<boolean>(false);
const appointmentNotFoundError = ref<boolean>(false);

const confirmAppointmentSuccess = ref<boolean>(false);
const confirmAppointmentError = ref<boolean>(false);

const cancelAppointmentSuccess = ref<boolean>(false);
const cancelAppointmentError = ref<boolean>(false);

const preselectedLocationId = ref<string | undefined>(props.locationId);

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
  selectedServiceMap.value = new Map<string, number>();
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

const setRebookData = () => {
  if (appointment.value && rebookedAppointment.value) {
    appointment.value.familyName = rebookedAppointment.value.familyName;
    appointment.value.email = rebookedAppointment.value.email;
    appointment.value.telephone = rebookedAppointment.value.telephone;
    appointment.value.customTextfield =
      rebookedAppointment.value.customTextfield;
    updateAppointment(appointment.value, props.baseUrl ?? undefined).then(
      (data) => {
        if ((data as AppointmentDTO).processId != undefined) {
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
        currentView.value = 3;
      }
    );
  }
};

const nextReserveAppointment = () => {
  rebookOrCanelDialog.value = false;
  reserveAppointment(
    selectedTimeslot.value,
    Array.from(selectedServiceMap.value.keys()),
    Array.from(selectedServiceMap.value.values()),
    selectedProvider.value.id,
    props.baseUrl ?? undefined
  ).then((data) => {
    if ((data as AppointmentDTO).processId != undefined) {
      if (appointment.value && !isRebooking.value) {
        cancelAppointment(appointment.value, props.baseUrl ?? undefined);
      }
      appointment.value = data as AppointmentDTO;
      if (isRebooking.value) {
        setRebookData();
      } else {
        increaseCurrentView();
      }
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
    appointment.value.customTextfield = customerData.value.customTextfield
      ? customerData.value.customTextfield
      : undefined;

    updateAppointment(appointment.value, props.baseUrl ?? undefined).then(
      (data) => {
        if ((data as AppointmentDTO).processId != undefined) {
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
      }
    );
  }
};

const nextBookAppointment = () => {
  if (appointment.value) {
    preconfirmAppointment(appointment.value, props.baseUrl ?? undefined).then(
      (data) => {
        if ((data as AppointmentDTO).processId != undefined) {
          appointment.value = data as AppointmentDTO;
          if (isRebooking.value && rebookedAppointment.value) {
            cancelAppointment(
              rebookedAppointment.value,
              props.baseUrl ?? undefined
            );
          }
          increaseCurrentView();
        }
      }
    );
  }
};

const nextCancelAppointment = () => {
  if (appointment.value) {
    cancelAppointment(appointment.value, props.baseUrl ?? undefined).then(
      (data) => {
        if ((data as AppointmentDTO).processId != undefined) {
          cancelAppointmentSuccess.value = true;
        } else {
          cancelAppointmentError.value = true;
        }
        increaseCurrentView();
      }
    );
  }
};

const nextRescheduleAppointment = () => {
  isRebooking.value = true;
  rebookedAppointment.value = appointment.value;
  setServices();
  currentView.value = 1;
};

const nextCancelReschedule = () => {
  isRebooking.value = false;
  rebookOrCanelDialog.value = true;
};

watch(currentView, (newCurrentView) => {
  activeStep.value = newCurrentView.toString();
});

const getProviders = (serviceId: string, providers: string[] | null) => {
  const officesAtService = new Array<OfficeImpl>();
  relations.value.forEach((relation) => {
    if (relation.serviceId == serviceId) {
      const foundOffice: OfficeImpl = offices.value.filter((office) => {
        return office.id == relation.officeId;
      })[0];

      if (!providers || providers.includes(foundOffice.id.toString())) {
        foundOffice.slots = relation.slots;
        officesAtService.push(foundOffice);
      }
    }
  });

  return officesAtService;
};

onMounted(() => {
  if (props.confirmAppointmentHash) {
    let appointmentData: AppointmentHash;
    try {
      appointmentData = JSON.parse(window.atob(props.confirmAppointmentHash));
      if (
        appointmentData.id == undefined ||
        appointmentData.authKey == undefined
      ) {
        confirmAppointmentError.value = true;
        return;
      }
    } catch {
      confirmAppointmentError.value = true;
      return;
    }
    confirmAppointment(appointmentData, props.baseUrl ?? undefined).then(
      (data) => {
        if ((data as AppointmentDTO).processId != undefined) {
          confirmAppointmentSuccess.value = true;
        } else {
          confirmAppointmentError.value = true;
        }
      }
    );
  }

  if (props.appointmentHash) {
    rebookOrCanelDialog.value = true;
    fetchServicesAndProviders(
      props.serviceId ?? undefined,
      props.locationId ?? undefined,
      props.baseUrl ?? undefined
    ).then((data) => {
      services.value = data.services;
      relations.value = data.relations;
      offices.value = data.offices;

      let appointmentData: AppointmentHash;
      try {
        appointmentData = JSON.parse(window.atob(props.appointmentHash));
        if (
          appointmentData.id == undefined ||
          appointmentData.authKey == undefined
        ) {
          confirmAppointmentError.value = true;
          return;
        }
      } catch {
        confirmAppointmentError.value = true;
        return;
      }
      fetchAppointment(appointmentData, props.baseUrl ?? undefined).then(
        (data) => {
          if ((data as AppointmentDTO).processId != undefined) {
            appointment.value = data as AppointmentDTO;
            selectedService.value = services.value.find(
              (service) => service.id == appointment.value.serviceId
            );
            if (selectedService.value) {
              selectedService.value.count = appointment.value.serviceCount;
              selectedService.value.providers = getProviders(
                selectedService.value.id,
                null
              );

              preselectedLocationId.value = appointment.value.officeId;
              selectedProvider.value = offices.value.find(
                (office) => office.id == appointment.value?.officeId
              );

              if (appointment.value.subRequestCounts.length > 0) {
                appointment.value.subRequestCounts.forEach(
                  (subRequestCount) => {
                    const subRequest: Service = services.value.find(
                      (service) => service.id == subRequestCount.id
                    );
                    const subService = new SubService(
                      subRequest.id,
                      subRequest.name,
                      subRequest.maxQuantity,
                      getProviders(subRequest.id, null),
                      subRequestCount.count
                    );
                    if (!selectedService.value.subServices) {
                      selectedService.value.subServices = [];
                    }
                    selectedService.value.subServices.push(subService);
                  }
                );
              }
              currentView.value = 3;
            }
          } else {
            appointmentNotFoundError.value = true;
          }
        }
      );
    });
  }
});
</script>
