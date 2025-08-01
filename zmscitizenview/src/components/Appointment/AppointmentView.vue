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
                @captchaTokenChanged="captchaToken = $event ?? undefined"
              />
            </div>

            <div v-if="currentView === 1">
              <calendar-view
                :base-url="baseUrl"
                :is-rebooking="isRebooking"
                :exclusive-location="exclusiveLocation"
                :preselected-office-id="preselectedLocationId"
                :selected-service-map="selectedServiceMap"
                :captcha-token="captchaToken ?? null"
                :t="t"
                :booking-error="captchaError || appointmentNotAvailableError"
                :booking-error-key="bookingErrorKey"
                @back="decreaseCurrentView"
                @next="nextReserveAppointment"
              />
            </div>
            <div v-if="currentView === 2">
              <customer-info
                :t="t"
                @back="decreaseCurrentView"
                @next="nextUpdateAppointment"
              />
            </div>
            <div v-if="currentView === 3">
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
            v-if="confirmAppointmentActivationExpiredError"
            type="error"
          >
            <template #content>
              {{ t("appointmentActivationExpiredErrorText") }}
            </template>

            <template #header>
              {{ t("appointmentActivationExpiredErrorHeader") }}
            </template>
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
import { computed, nextTick, onMounted, provide, ref, watch } from "vue";

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
  t: (key: string) => string;
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

const customerData = ref<CustomerData>(
  new CustomerData("", "", "", "", "", "")
);
const appointment = ref<AppointmentImpl>();
const rebookedAppointment = ref<AppointmentImpl>();

const services = ref<Service[]>([]);
const relations = ref<Relation[]>([]);
const offices = ref<Office[]>([]);

const rebookOrCanelDialog = ref<boolean>(false);
const isRebooking = ref<boolean>(false);
const captchaToken = ref<string | undefined>(undefined);
const captchaError = ref<boolean>(false);

const bookingErrorKey = computed(() => {
  if (captchaError.value) return "altcha.invalidCaptcha";
  if (appointmentNotAvailableError.value) return "noAppointmentsAvailable";
  return "";
});
const appointmentNotAvailableError = ref<boolean>(false);
const updateAppointmentError = ref<boolean>(false);
const tooManyAppointmentsWithSameMailError = ref<boolean>(false);
const appointmentNotFoundError = ref<boolean>(false);
const confirmAppointmentActivationExpiredError = ref<boolean>(false);

const confirmAppointmentSuccess = ref<boolean>(false);
const confirmAppointmentError = ref<boolean>(false);

const cancelAppointmentSuccess = ref<boolean>(false);
const cancelAppointmentError = ref<boolean>(false);

const isReservingAppointment = ref<boolean>(false);
const isUpdatingAppointment = ref<boolean>(false);
const isBookingAppointment = ref<boolean>(false);
const isCancelingAppointment = ref<boolean>(false);

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

provide("loadingStates", {
  isReservingAppointment,
  isUpdatingAppointment,
  isBookingAppointment,
  isCancelingAppointment,
});

const increaseCurrentView = () => currentView.value++;

const decreaseCurrentView = () => currentView.value--;

/**
 * Adjusts the current view to the active step in the stepper
 */
const changeStep = (step: string) => {
  if (parseInt(step) < parseInt(activeStep.value)) {
    currentView.value = parseInt(step);
  }
};

/**
 * Creation of a map that prepares the services and their counts for the backend call.
 */
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
    appointment.value.customTextfield2 =
      rebookedAppointment.value.customTextfield2;
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
  if (isReservingAppointment.value) {
    return;
  }

  isReservingAppointment.value = true;
  appointmentNotAvailableError.value = false;
  captchaError.value = false;
  rebookOrCanelDialog.value = false;

  reserveAppointment(
    selectedTimeslot.value,
    Array.from(selectedServiceMap.value.keys()),
    Array.from(selectedServiceMap.value.values()),
    selectedProvider.value?.id ?? "",
    props.baseUrl ?? undefined,
    captchaToken.value ?? undefined
  )
    .then((data) => {
      if ((data as AppointmentDTO).processId !== undefined) {
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
        const firstErrorCode = (data as any).errors?.[0]?.errorCode ?? "";
        if (firstErrorCode === "appointmentNotAvailable") {
          appointmentNotAvailableError.value = true;
        } else if (
          ["captchaMissing", "captchaExpired", "captchaInvalid"].includes(
            firstErrorCode
          )
        ) {
          captchaError.value = true;
        }
      }
    })
    .finally(() => {
      isReservingAppointment.value = false;
    });
};

const nextUpdateAppointment = () => {
  if (isUpdatingAppointment.value) {
    return;
  }

  if (appointment.value) {
    isUpdatingAppointment.value = true;
    appointment.value.familyName =
      customerData.value.firstName + " " + customerData.value.lastName;
    appointment.value.email = customerData.value.mailAddress;
    appointment.value.telephone = customerData.value.telephoneNumber
      ? customerData.value.telephoneNumber
      : undefined;
    appointment.value.customTextfield = customerData.value.customTextfield
      ? customerData.value.customTextfield
      : undefined;
    appointment.value.customTextfield2 = customerData.value.customTextfield2
      ? customerData.value.customTextfield2
      : undefined;

    updateAppointment(appointment.value, props.baseUrl ?? undefined)
      .then((data) => {
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
      })
      .finally(() => {
        isUpdatingAppointment.value = false;
      });
  }
};

const nextBookAppointment = () => {
  if (isBookingAppointment.value) {
    return;
  }

  if (appointment.value) {
    isBookingAppointment.value = true;
    preconfirmAppointment(appointment.value, props.baseUrl ?? undefined)
      .then((data) => {
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
      })
      .finally(() => {
        isBookingAppointment.value = false;
      });
  }
};

const nextCancelAppointment = () => {
  if (isCancelingAppointment.value) {
    return;
  }

  if (appointment.value) {
    isCancelingAppointment.value = true;
    cancelAppointment(appointment.value, props.baseUrl ?? undefined)
      .then((data) => {
        if ((data as AppointmentDTO).processId != undefined) {
          cancelAppointmentSuccess.value = true;
        } else {
          cancelAppointmentError.value = true;
        }
        increaseCurrentView();
      })
      .finally(() => {
        isCancelingAppointment.value = false;
      });
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

/**
 * Adjusts the active step in the stepper to the current view
 */
watch(currentView, (newCurrentView) => {
  activeStep.value = newCurrentView.toString();
  goToTop();
});

/**
 * Sets the view to the top of the page after change the current view
 */
const goToTop = async () => {
  await nextTick();
  window.scrollTo({ top: 0, behavior: "instant" });
};

const getProviders = (serviceId: string, providers: string[] | null) => {
  const officesAtService = new Array<OfficeImpl>();
  relations.value.forEach((relation) => {
    if (relation.serviceId == serviceId) {
      const office = offices.value.find(
        (office) => office.id == relation.officeId
      );
      if (office) {
        const foundOffice: OfficeImpl = new OfficeImpl(
          office.id,
          office.name,
          office.address,
          office.showAlternativeLocations,
          office.displayNameAlternatives,
          office.organization,
          office.organizationUnit,
          office.slotTimeInMinutes,
          office.disabledByServices,
          office.scope,
          office.maxSlotsPerAppointment,
          office.slots,
          office.priority || 1
        );

        if (!providers || providers.includes(foundOffice.id.toString())) {
          foundOffice.slots = relation.slots;
          officesAtService.push(foundOffice);
        }
      }
    }
  });

  return officesAtService;
};

const parseAppointmentHash = (hash: string): AppointmentHash | null => {
  try {
    const appointmentData = JSON.parse(window.atob(hash));
    if (
      appointmentData.id == undefined ||
      appointmentData.authKey == undefined
    ) {
      return null;
    }
    return appointmentData;
  } catch {
    return null;
  }
};

onMounted(() => {
  console.log("Service ID:", props.serviceId);
  console.log("Location ID:", props.locationId);
  if (props.confirmAppointmentHash) {
    const appointmentData = parseAppointmentHash(props.confirmAppointmentHash);
    if (!appointmentData) {
      confirmAppointmentError.value = true;
      return;
    }

    confirmAppointment(appointmentData, props.baseUrl ?? undefined).then(
      (data) => {
        if ((data as AppointmentDTO).processId != undefined) {
          confirmAppointmentSuccess.value = true;
        } else {
          const firstErrorCode = (data as any).errors?.[0]?.errorCode ?? "";
          if (
            firstErrorCode === "processNotPreconfirmedAnymore" ||
            firstErrorCode === "appointmentNotFound"
          ) {
            confirmAppointmentActivationExpiredError.value = true;
          } else {
            confirmAppointmentError.value = true;
          }
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

      const appointmentData = parseAppointmentHash(props.appointmentHash ?? "");
      if (!appointmentData) {
        appointmentNotFoundError.value = true;
        return;
      }

      fetchAppointment(appointmentData, props.baseUrl ?? undefined).then(
        (data) => {
          if ((data as AppointmentDTO).processId != undefined) {
            if ("captchaToken" in data && data.captchaToken) {
              captchaToken.value = data.captchaToken as string;
            }
            appointment.value = data as AppointmentDTO;
            selectedService.value = services.value.find(
              (service) => service.id == appointment.value?.serviceId
            );
            if (selectedService.value) {
              selectedService.value.count = appointment.value.serviceCount;
              selectedService.value.providers = getProviders(
                selectedService.value.id,
                null
              );

              preselectedLocationId.value = appointment.value.officeId;
              const foundOffice = offices.value.find(
                (office) => office.id == appointment.value?.officeId
              );
              if (foundOffice) {
                selectedProvider.value = new OfficeImpl(
                  foundOffice.id,
                  foundOffice.name,
                  foundOffice.address,
                  foundOffice.showAlternativeLocations,
                  foundOffice.displayNameAlternatives,
                  foundOffice.organization,
                  foundOffice.organizationUnit,
                  foundOffice.slotTimeInMinutes,
                  undefined, // disabledByServices
                  foundOffice.scope,
                  foundOffice.maxSlotsPerAppointment,
                  undefined, // slots
                  foundOffice.priority || 1
                );
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
                      getProviders(subRequest.id, null),
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
