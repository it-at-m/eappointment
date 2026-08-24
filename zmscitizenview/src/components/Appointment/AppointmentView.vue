<template>
  <div class="m-component m-component-form">
    <!-- Maintenance Page -->
    <div
      v-if="isInMaintenanceModeComputed"
      class="container"
    >
      <div class="m-component__grid">
        <div class="m-component__column">
          <error-alert
            :message="t('maintenancePageText')"
            :header="t('maintenancePageHeader')"
            type="warning"
          />
        </div>
      </div>
    </div>

    <!-- System Failure Page -->
    <div
      v-if="isInSystemFailureModeComputed"
      class="container"
    >
      <div class="m-component__grid">
        <div class="m-component__column">
          <error-alert
            :message="t('systemFailurePageText')"
            :header="t('systemFailurePageHeader')"
            type="error"
          />
        </div>
      </div>
    </div>

    <!-- Error Alert (for rate limit, etc.) -->
    <div
      v-if="
        !isInMaintenanceModeComputed &&
        !isInSystemFailureModeComputed &&
        errorStates.errorStateMap.apiErrorRateLimitExceeded.value
      "
      class="container"
    >
      <div class="m-component__grid">
        <div class="m-component__column">
          <error-alert
            :message="t(apiErrorTranslation.textKey)"
            :header="t(apiErrorTranslation.headerKey)"
            :type="apiErrorTranslation.errorType"
          />
        </div>
      </div>
    </div>

    <!-- ServiceFinder-specific rate limit alert removed; centralized via errorStates -->

    <!-- Normal Application Content -->
    <div
      v-if="
        !isInMaintenanceModeComputed &&
        !isInSystemFailureModeComputed &&
        !errorStates.errorStateMap.apiErrorRateLimitExceeded.value &&
        (!confirmAppointmentHash || appointmentAlreadyActivated) &&
        !apiErrorAppointmentNotFound &&
        !apiErrorInvalidJumpinLink &&
        currentView < 4
      "
    >
      <muc-stepper
        v-if="!isAppointmentInPast"
        ref="stepperRef"
        :step-items="STEPPER_ITEMS"
        :active-item="activeStep"
        :disable-previous-steps="
          !!appointmentHash || appointmentAlreadyActivated
        "
        @change-step="changeStep"
      />
      <div class="container">
        <div class="m-component__grid">
          <div class="m-component__column">
            <div
              v-if="
                currentView === 0 &&
                !appointmentHash &&
                !appointmentAlreadyActivated
              "
            >
              <service-finder
                :global-state="globalState"
                :preselected-service-id="serviceId"
                :preselected-office-id="locationId"
                :exclusive-location="exclusiveLocation"
                :t="t"
                @next="setServices"
                @captchaTokenChanged="handleCaptchaTokenChanged"
                @invalidJumpinLink="handleInvalidJumpinLink"
                @rateLimitError="handleServiceFinderRateLimitError"
              />
            </div>

            <!-- Keep mounted across customer-info and summary so back/login do not remount/refetch. -->
            <div v-show="currentView === 1">
              <AppointmentSelection
                v-if="
                  currentView === 1 || currentView === 2 || currentView === 3
                "
                :key="appointmentSelectionKey"
                :global-state="globalState"
                :is-rebooking="isRebooking"
                :exclusive-location="exclusiveLocation"
                :preselected-office-id="preselectedLocationId"
                :selected-service-map="selectedServiceMap"
                :captcha-token="captchaToken ?? null"
                :t="t"
                :booking-error="
                  captchaError ||
                  apiErrorAppointmentNotAvailable ||
                  errorStates.errorStateMap.apiErrorCaptchaExpired.value ||
                  errorStates.errorStateMap.apiErrorCaptchaMissing.value ||
                  errorStates.errorStateMap.apiErrorCaptchaInvalid.value
                "
                :booking-error-key="bookingErrorKey"
                @back="decreaseCurrentView"
                @clearBookingError="clearBookingError"
                @next="nextReserveAppointment"
              />
            </div>
            <div v-if="currentView === 2">
              <div v-if="hasUpdateAppointmentError">
                <muc-callout
                  :type="toCalloutType(apiErrorTranslation.errorType)"
                >
                  <template #content>
                    <p>{{ t(apiErrorTranslation.textKey) }}</p>
                  </template>

                  <template #header>
                    {{ t(apiErrorTranslation.headerKey) }}
                  </template>
                </muc-callout>
              </div>
              <customer-info
                :global-state="globalState"
                :show-login-option="showLoginOption && !isRebooking"
                :is-rebooking="isRebooking"
                :login-failed="loginFailed"
                :t="t"
                @back="decreaseCurrentView"
                @next="nextUpdateAppointment"
                @login="requestLogin"
              />
            </div>
            <div v-if="currentView === 3">
              <appointment-summary
                v-if="
                  !hasUpdateAppointmentError &&
                  !hasPreconfirmAppointmentError &&
                  !isAppointmentInPast
                "
                :appointment-already-activated="appointmentAlreadyActivated"
                :is-rebooking="isRebooking"
                :rebook-or-cancel-dialog="rebookOrCancelDialog"
                :t="t"
                @back="decreaseCurrentView"
                @book-appointment="nextBookAppointment"
                @cancel-appointment="nextCancelAppointment"
                @cancel-reschedule="nextCancelReschedule"
                @reschedule-appointment="nextRescheduleAppointment"
              />
              <div v-if="isAppointmentInPast">
                <muc-callout type="error">
                  <template #content>
                    <p>{{ t("rescheduleErrorText") }}</p>
                  </template>

                  <template #header>
                    {{ t("rescheduleErrorHeader") }}
                  </template>
                </muc-callout>
                <muc-button
                  icon="arrow-right"
                  @click="redirectToAppointmentStart"
                >
                  {{ t("newAppointmentButton") }}
                </muc-button>
              </div>
              <div v-if="hasUpdateAppointmentError">
                <muc-callout
                  :type="toCalloutType(apiErrorTranslation.errorType)"
                >
                  <template #content>
                    <p>{{ t(apiErrorTranslation.textKey) }}</p>
                  </template>

                  <template #header>
                    {{ t(apiErrorTranslation.headerKey) }}
                  </template>
                </muc-callout>
              </div>
              <div v-if="hasPreconfirmAppointmentError">
                <muc-callout
                  :type="toCalloutType(apiErrorTranslation.errorType)"
                >
                  <template #content>
                    <p>{{ t(apiErrorTranslation.textKey) }}</p>
                  </template>

                  <template #header>
                    {{ t(apiErrorTranslation.headerKey) }}
                  </template>
                </muc-callout>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div
      v-if="
        !isInMaintenanceModeComputed &&
        !isInSystemFailureModeComputed &&
        !errorStates.errorStateMap.apiErrorRateLimitExceeded.value
      "
    >
      <div class="container">
        <div class="m-component__grid">
          <div class="m-component__column">
            <template v-if="currentView === 4">
              <muc-callout
                v-if="!cancelAppointmentSuccess"
                type="warning"
              >
                <template #content>
                  <p>{{ confirmText }}</p>
                </template>
                <template #header>
                  {{ t("confirmAppointmentHeader") }}
                </template>
              </muc-callout>

              <muc-callout
                v-if="cancelAppointmentSuccess"
                type="success"
              >
                <template #content>
                  <p>{{ t("appointmentSuccessfullyCanceledText") }}</p>
                </template>
                <template #header>
                  {{ t("appointmentSuccessfullyCanceledHeader") }}
                </template>
              </muc-callout>

              <muc-callout
                v-if="hasCancelAppointmentError"
                :type="toCalloutType(apiErrorTranslation.errorType)"
              >
                <template #content>
                  <p>{{ t(apiErrorTranslation.textKey) }}</p>
                </template>
                <template #header>
                  {{ t(apiErrorTranslation.headerKey) }}
                </template>
              </muc-callout>
            </template>

            <template v-else>
              <muc-callout
                v-if="confirmAppointmentSuccess"
                type="success"
              >
                <template #content>
                  <p>{{ t("appointmentSuccessfullyBookedText") }}</p>
                </template>
                <template #header>
                  {{ t("appointmentSuccessfullyBookedHeader") }}
                </template>
              </muc-callout>

              <div
                v-if="confirmAppointmentSuccess"
                class="m-button-group"
              >
                <div v-if="globalState.isLoggedIn && appointmentDetailUrl">
                  <muc-button
                    icon="arrow-right"
                    @click="viewAppointment"
                  >
                    {{ t("viewAppointment") }}
                  </muc-button>
                </div>
                <muc-button
                  v-if="!globalState.isLoggedIn && appointment?.icsContent"
                  icon="download"
                  @click="downloadIcsAppointment"
                >
                  {{ t("downloadAppointment") }}
                </muc-button>
                <muc-button
                  @click="redirectToAppointmentStart"
                  variant="secondary"
                >
                  {{ t("bookAnotherAppointment") }}
                </muc-button>
              </div>

              <muc-callout
                v-if="
                  !confirmAppointmentSuccess &&
                  !appointmentAlreadyActivated &&
                  hasConfirmAppointmentError
                "
                :type="toCalloutType(apiErrorTranslation.errorType)"
              >
                <template #content>
                  <p>{{ t(apiErrorTranslation.textKey) }}</p>
                </template>
                <template #header>
                  {{ t(apiErrorTranslation.headerKey) }}
                </template>
              </muc-callout>

              <muc-callout
                v-if="hasInitializationError"
                :type="toCalloutType(apiErrorTranslation.errorType)"
              >
                <template #content>
                  <p>{{ t(apiErrorTranslation.textKey) }}</p>
                </template>
                <template #header>
                  {{ t(apiErrorTranslation.headerKey) }}
                </template>
              </muc-callout>

              <muc-callout
                v-if="apiErrorInvalidJumpinLink"
                type="error"
              >
                <template #content>
                  <p>{{ t("apiErrorInvalidJumpinLinkText") }}</p>
                  <div
                    class="m-button-group"
                    style="margin-top: 1rem"
                  >
                    <p>
                      <muc-button
                        icon="arrow-right"
                        @click="redirectToAppointmentStart"
                        style="margin-bottom: 0; margin-right: 0"
                      >
                        {{ t("bookAppointmentStart") }}
                      </muc-button>
                    </p>
                  </div>
                </template>
                <template #header>
                  {{ t("apiErrorInvalidJumpinLinkHeader") }}
                </template>
              </muc-callout>
            </template>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { ApiErrorTranslation, ErrorStateMap } from "@/utils/errorHandler";

import {
  MucButton,
  MucCallout,
  MucStepper,
} from "@muenchen/muc-patternlab-vue";
import {
  ComponentPublicInstance,
  computed,
  nextTick,
  onMounted,
  provide,
  ref,
  watch,
} from "vue";

import { AppointmentDTO } from "@/api/models/AppointmentDTO";
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
import AppointmentSelection from "@/components/Appointment/AppointmentSelection.vue";
import AppointmentSummary from "@/components/Appointment/AppointmentSummary.vue";
import CustomerInfo from "@/components/Appointment/CustomerInfo.vue";
import ServiceFinder from "@/components/Appointment/ServiceFinder.vue";
import ErrorAlert from "@/components/Common/ErrorAlert.vue";
import { AppointmentHash } from "@/types/AppointmentHashTypes";
import { CustomerData } from "@/types/CustomerData";
import { GlobalState } from "@/types/GlobalState";
import { LocalStorageUiData } from "@/types/LocalStorageAppointmentData";
import { OfficeImpl } from "@/types/OfficeImpl";
import {
  CustomerDataProvider,
  SelectedAppointmentProvider,
  SelectedServiceProvider,
  SelectedTimeslotProvider,
  ServiceLinkProvider,
} from "@/types/ProvideInjectTypes";
import { ServiceImpl } from "@/types/ServiceImpl";
import { StepperItem } from "@/types/StepperTypes";
import { SubService } from "@/types/SubService";
import {
  getApiStatusState,
  handleApiResponseForDownTime,
  isInMaintenanceMode,
  isInSystemFailureMode,
} from "@/utils/apiStatusService";
import {
  clearAppointmentAuthHashSession,
  clearAppointmentLocalStorage,
  getFreshLocalStorageUiData,
  parseAppointmentHash,
  resolveAppointmentAuthHash,
  saveUiToLocalStorage,
  setAppointmentAuthHashForLogin,
} from "@/utils/appointmentLoginStorage";
import { getTokenData } from "@/utils/auth";
import { toCalloutType } from "@/utils/callout";
import {
  APPOINTMENT_ACTION_TYPE,
  QUERY_PARAM_APPOINTMENT_DISPLAY_NUMBER,
  QUERY_PARAM_APPOINTMENT_ID,
  resolveAgainstCurrentPage,
} from "@/utils/Constants";
import { downloadIcsFile } from "@/utils/downloadIcsFile";
import {
  clearContextErrors,
  createErrorStates,
  getApiErrorTranslation,
  handleApiError,
  handleApiResponse as handleErrorApiResponse,
  hasCancelContextError,
  hasConfirmContextError,
  hasInitializationContextError,
  hasPreconfirmContextError,
  hasUpdateContextError,
} from "@/utils/errorHandler";
import {
  applyAppointmentContactToCustomerData,
  hasMissingRequiredContact,
} from "@/utils/rebookingContact";
import { isExpired } from "@/utils/timestampInPast";

const props = defineProps<{
  globalState: GlobalState;
  serviceId?: string;
  locationId?: string;
  exclusiveLocation?: string;
  appointmentHash?: string;
  confirmAppointmentHash?: string;
  appointmentDetailUrl?: string;
  showLoginOption: boolean;
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

/** Remount AppointmentSelection only when the chosen services change. */
const appointmentSelectionKey = computed(() => {
  const entries = Array.from(selectedServiceMap.value.entries())
    .map(([id, count]) => `${id}:${count}`)
    .sort();
  return entries.length > 0 ? entries.join("|") : "none";
});

const selectedProvider = ref<OfficeImpl>();
const selectedTimeslot = ref<number>(0);

const customerData = ref<CustomerData>(
  new CustomerData("", "", "", "", "", "")
);

const serviceLinkId = ref<string | null>(null);
const updateServiceLinkId = (id: string | null) => {
  serviceLinkId.value = id;
};

const loginFailed = ref(false);

watch(
  () => props.globalState.accessToken,
  (newAccessToken) => {
    if (!newAccessToken) {
      loginFailed.value = false;
      return;
    }

    try {
      const tokenData = getTokenData(newAccessToken);
      loginFailed.value = false;
      customerData.value.firstName =
        customerData.value.firstName || tokenData.given_name || "";
      customerData.value.lastName =
        customerData.value.lastName || tokenData.family_name || "";
      customerData.value.mailAddress =
        customerData.value.mailAddress || tokenData.email || "";
    } catch {
      loginFailed.value = true;
    }
  },
  { immediate: true }
);

const appointment = ref<AppointmentDTO>();
const rebookedAppointment = ref<AppointmentDTO>();

const services = ref<Service[]>([]);
const relations = ref<Relation[]>([]);
const offices = ref<Office[]>([]);

const rebookOrCancelDialog = ref<boolean>(false);
const isRebooking = ref<boolean>(false);
const captchaToken = ref<string | undefined>(undefined);
const captchaError = ref<boolean>(false);
const forcedPast = ref(false);
const isAppointmentInPast = computed(() => {
  return forcedPast.value || isExpired((appointment.value as any)?.timestamp);
});

const bookingErrorKey = computed(() => {
  if (captchaError.value) return "altcha.invalidCaptcha";
  if (apiErrorAppointmentNotAvailable.value)
    return "apiErrorAppointmentNotAvailable";
  if (errorStateMap.value.apiErrorCaptchaExpired.value)
    return "apiErrorCaptchaExpired";
  if (errorStateMap.value.apiErrorCaptchaMissing.value)
    return "apiErrorCaptchaMissing";
  if (errorStateMap.value.apiErrorCaptchaInvalid.value)
    return "apiErrorCaptchaInvalid";
  return "";
});

const confirmAppointmentSuccess = ref<boolean>(false);
const appointmentAlreadyActivated = ref<boolean>(false);
const confirmedAppointmentHash = ref<string | null>(null);
const loadedAppointmentHash = ref<string | null>(null);
const isLoadingAppointmentFromHash = ref<boolean>(false);
const cancelAppointmentSuccess = ref<boolean>(false);
const cancelAppointmentError = ref<boolean>(false);

// Create centralized error states
const errorStates = createErrorStates();
const errorStateMap = computed<ErrorStateMap>(() => errorStates.errorStateMap);
const currentErrorData = computed(() => errorStates.currentErrorData);

// API status state
const apiStatusState = getApiStatusState();
const isInMaintenanceModeComputed = computed(() => isInMaintenanceMode());
const isInSystemFailureModeComputed = computed(() => isInSystemFailureMode());

// Access individual error refs from the error state map
const apiErrorAppointmentNotAvailable =
  errorStateMap.value.apiErrorAppointmentNotAvailable;
const apiErrorAppointmentNotFound =
  errorStateMap.value.apiErrorAppointmentNotFound;
const apiErrorInvalidJumpinLink = errorStateMap.value.apiErrorInvalidJumpinLink;

const isReservingAppointment = ref<boolean>(false);
const isUpdatingAppointment = ref<boolean>(false);
const isBookingAppointment = ref<boolean>(false);
const isCancelingAppointment = ref<boolean>(false);

const preselectedLocationId = ref<string | undefined>(props.locationId);

const reservationStartMs = ref<number | null>(null);

const activationMinutes = computed<number | undefined>(() => {
  const fromAppt = (appointment.value as any)?.scope?.activationDuration;
  const fromProv = (selectedProvider.value as any)?.scope?.activationDuration;
  const raw = fromAppt ?? fromProv;
  const n = typeof raw === "string" ? Number.parseInt(raw, 10) : raw;
  return Number.isFinite(n as number) ? (n as number) : undefined;
});

const confirmText = computed<string>(() => {
  const minutes = String(activationMinutes.value ?? 30);
  return (props.t as any)("confirmAppointmentText", {
    activationMinutes: minutes,
  });
});

const apiErrorTranslation = computed<ApiErrorTranslation>(() => {
  return getApiErrorTranslation(
    errorStates.errorStateMap,
    currentErrorData.value
  );
});

type StepperInstance = ComponentPublicInstance | HTMLElement | null;
const stepperRef = ref<StepperInstance>(null);

const focusActiveStepperItem = async () => {
  await nextTick();

  // Zugriff auf das gerenderte DOM des Steppers
  const rootEl =
    (stepperRef.value as ComponentPublicInstance | null)?.$el ??
    (stepperRef.value as HTMLElement | null);

  if (!rootEl) return;

  const activeIcon = rootEl.querySelector<HTMLElement>(
    ".m-form-step__icon[aria-current='step']"
  );

  activeIcon?.focus();
};

// Track the current context based on API calls and props
const currentContext = ref<string>("update");

// Computed property to determine the active context
const activeContext = computed<string>(() => {
  if (props.confirmAppointmentHash) {
    return "confirm";
  }
  // During rebooking, use the current context instead of initialization
  if (props.appointmentHash && isRebooking.value) {
    return currentContext.value;
  }
  if (props.appointmentHash) {
    return "initialization";
  }
  return currentContext.value;
});

// Computed property to check if any update appointment error is active
const hasUpdateAppointmentError = computed<boolean>(() => {
  return hasUpdateContextError(errorStateMap.value, activeContext.value);
});

// Computed property to check if any confirm appointment error is active
const hasConfirmAppointmentError = computed<boolean>(() => {
  return hasConfirmContextError(errorStateMap.value, activeContext.value);
});

// Computed property to check if any initialization error is active
const hasInitializationError = computed<boolean>(() => {
  return hasInitializationContextError(
    errorStateMap.value,
    activeContext.value
  );
});

// Computed property to check if any preconfirm appointment error is active
const hasPreconfirmAppointmentError = computed<boolean>(() => {
  return hasPreconfirmContextError(errorStateMap.value, activeContext.value);
});

// Computed property to check if any cancel appointment error is active
const hasCancelAppointmentError = computed<boolean>(() => {
  return hasCancelContextError(errorStateMap.value, activeContext.value);
});

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

provide("rebookedAppointment", rebookedAppointment);

provide<ServiceLinkProvider>("serviceLinkProvider", {
  serviceLinkId,
  updateServiceLinkId,
} as ServiceLinkProvider);

provide("reservationStartMs", reservationStartMs);

provide("loadingStates", {
  isReservingAppointment,
  isUpdatingAppointment,
  isBookingAppointment,
  isCancelingAppointment,
});

const increaseCurrentView = () => currentView.value++;

const decreaseCurrentView = (): void => {
  clearAllErrors();
  currentView.value--;
};

/**
 * Adjusts the current view to the active step in the stepper
 */
const changeStep = (step: string) => {
  if (parseInt(step) < parseInt(activeStep.value)) {
    clearAllErrors();
    currentView.value = parseInt(step);
  }
};

/**
 * Creation of a map that prepares the services and their counts for the backend call.
 */
const setServices = () => {
  clearAllErrors();
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

const copyRebookedContactOntoAppointment = () => {
  if (!appointment.value || !rebookedAppointment.value) {
    return;
  }
  appointment.value.familyName = rebookedAppointment.value.familyName;
  appointment.value.email = rebookedAppointment.value.email;
  appointment.value.telephone = rebookedAppointment.value.telephone;
  appointment.value.customTextfield = rebookedAppointment.value.customTextfield;
  appointment.value.customTextfield2 =
    rebookedAppointment.value.customTextfield2;
};

const fillCustomerDataFromRebookedAppointment = () => {
  if (!rebookedAppointment.value) {
    return;
  }
  applyAppointmentContactToCustomerData(
    customerData.value,
    rebookedAppointment.value
  );
};

const targetScopeForRebooking = () =>
  selectedProvider.value?.scope ?? appointment.value?.scope;

const continueRebookingAfterReserve = () => {
  fillCustomerDataFromRebookedAppointment();
  copyRebookedContactOntoAppointment();
  if (
    rebookedAppointment.value &&
    hasMissingRequiredContact(
      rebookedAppointment.value,
      targetScopeForRebooking()
    )
  ) {
    currentView.value = 2;
    return;
  }
  return setRebookData();
};

const setRebookData = () => {
  if (appointment.value && rebookedAppointment.value) {
    copyRebookedContactOntoAppointment();
    clearContextErrors(errorStateMap.value);
    currentContext.value = "update";
    return updateAppointment(props.globalState, appointment.value).then(
      (data) => {
        if ((data as AppointmentDTO).processId != undefined) {
          appointment.value = data as AppointmentDTO;
          currentView.value = 3;
        } else {
          handleErrorApiResponse(
            data,
            errorStates.errorStateMap,
            currentErrorData.value
          );
          currentView.value = 2;
        }
      }
    );
  }
};

const nextReserveAppointment = () => {
  if (isReservingAppointment.value) {
    return;
  }

  isReservingAppointment.value = true;
  clearAllErrors();
  rebookOrCancelDialog.value = false;

  reserveAppointment(
    props.globalState,
    selectedTimeslot.value,
    Array.from(selectedServiceMap.value.keys()),
    Array.from(selectedServiceMap.value.values()),
    selectedProvider.value?.id ?? "",
    captchaToken.value ?? undefined
  )
    .then((data) => {
      if ((data as AppointmentDTO).processId !== undefined) {
        if (appointment.value && !isRebooking.value) {
          currentContext.value = "cancel";
          cancelAppointment(props.globalState, appointment.value);
        }
        appointment.value = data as AppointmentDTO;
        reservationStartMs.value = Date.now();
        if (isRebooking.value) {
          continueRebookingAfterReserve();
        } else {
          increaseCurrentView();
        }
      } else {
        handleErrorApiResponse(
          data,
          errorStates.errorStateMap,
          currentErrorData.value
        );
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
    clearContextErrors(errorStateMap.value);
    copyCustomerDataOntoAppointment();

    currentContext.value = "update";
    return updateAppointment(props.globalState, appointment.value)
      .then((data) => {
        if ((data as AppointmentDTO).processId != undefined) {
          appointment.value = data as AppointmentDTO;
          increaseCurrentView();
        } else {
          handleErrorApiResponse(
            data,
            errorStates.errorStateMap,
            currentErrorData.value
          );
        }
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
    clearContextErrors(errorStateMap.value);

    const canDirectConfirm =
      !!appointment.value?.processId && !!appointment.value?.authKey;

    if (
      canDirectConfirm &&
      (isRebooking.value || props.globalState.isLoggedIn)
    ) {
      nextConfirmAppointment({
        id: appointment.value.processId,
        authKey: appointment.value.authKey,
      });
      return;
    }

    currentContext.value = "preconfirm";
    preconfirmAppointment(props.globalState, appointment.value)
      .then((data) => {
        if ((data as any)?.errors?.length > 0) {
          handleErrorApiResponse(
            data,
            errorStates.errorStateMap,
            currentErrorData.value
          );
          return;
        }

        if ((data as AppointmentDTO).processId != undefined) {
          appointment.value = data as AppointmentDTO;
          if (isRebooking.value && rebookedAppointment.value) {
            currentContext.value = "cancel";
            cancelAppointment(props.globalState, rebookedAppointment.value);
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
    clearContextErrors(errorStateMap.value);
    currentContext.value = "cancel";
    cancelAppointment(props.globalState, appointment.value)
      .then((data) => {
        if ((data as any)?.errors?.length > 0) {
          handleErrorApiResponse(
            data,
            errorStates.errorStateMap,
            currentErrorData.value
          );
          return;
        }

        if ((data as AppointmentDTO).processId != undefined) {
          cancelAppointmentSuccess.value = true;
        } else {
          cancelAppointmentError.value = true;
        }
        currentView.value = 4;
      })
      .finally(() => {
        isCancelingAppointment.value = false;
      });
  }
};

const nextRescheduleAppointment = () => {
  clearContextErrors(errorStateMap.value);

  if (isExpired((appointment.value as any)?.timestamp)) {
    forcedPast.value = true;
    currentView.value = 3;
    goToTop();
    return;
  }

  // normal rebooking flow
  isRebooking.value = true;
  rebookedAppointment.value = appointment.value;
  setServices();
  currentView.value = 1;
};

const nextCancelReschedule = () => {
  clearContextErrors(errorStateMap.value);
  isRebooking.value = false;
  rebookOrCancelDialog.value = true;
};

/**
 * Adjusts the active step in the stepper to the current view
 */
watch(currentView, (newCurrentView) => {
  activeStep.value = newCurrentView.toString();
  goToTop();
  focusActiveStepperItem();
});

/**
 * Sets the view to the top of the page after change the current view
 */
const goToTop = async () => {
  await nextTick();
  window.scrollTo({ top: 0, behavior: "instant" });
};

const copyCustomerDataOntoAppointment = () => {
  if (!appointment.value) {
    return;
  }
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
};

const applyContactFromAppointment = (booked: AppointmentDTO) => {
  if (booked.telephone) {
    customerData.value.telephoneNumber =
      customerData.value.telephoneNumber || booked.telephone;
  }
  if (booked.customTextfield) {
    customerData.value.customTextfield =
      customerData.value.customTextfield || booked.customTextfield;
  }
  if (booked.customTextfield2) {
    customerData.value.customTextfield2 =
      customerData.value.customTextfield2 || booked.customTextfield2;
  }
};

const persistUiIdsForLogin = () => {
  if (selectedService.value && selectedProvider.value) {
    saveUiToLocalStorage({
      timestamp: Date.now(),
      currentView: currentView.value,
      selectedServiceId: String(selectedService.value.id),
      selectedServiceMap: Object.fromEntries(selectedServiceMap.value),
      selectedProviderId: String(selectedProvider.value.id),
      selectedTimeslot: selectedTimeslot.value,
      ...(reservationStartMs.value != null
        ? { reservationStartMs: reservationStartMs.value }
        : {}),
    });
  }
};

const startOidcLogin = () => {
  persistUiIdsForLogin();
  setAppointmentAuthHashForLogin(
    appointment.value?.processId,
    appointment.value?.authKey
  );
  document.dispatchEvent(
    new CustomEvent("authorization-request", {
      detail: {
        loginProvider: undefined,
        authLevel: undefined,
      },
    })
  );
};

const requestLogin = () => {
  // Persist phone / Zusatzfelder on the reserved appointment so they survive
  // OAuth remount without writing PII to localStorage (ZMSKVR-1002 / CodeQL).
  if (appointment.value?.processId && appointment.value?.authKey) {
    copyCustomerDataOntoAppointment();
    return updateAppointment(props.globalState, appointment.value)
      .catch(() => undefined)
      .finally(startOidcLogin);
  }
  startOidcLogin();
};

const viewAppointment = () => {
  const url = resolveAgainstCurrentPage(
    props.appointmentDetailUrl || "appointment-detail.html"
  );
  url.searchParams.set(
    QUERY_PARAM_APPOINTMENT_ID,
    appointment.value?.processId || ""
  );
  if (appointment.value?.displayNumber) {
    url.searchParams.set(
      QUERY_PARAM_APPOINTMENT_DISPLAY_NUMBER,
      appointment.value.displayNumber
    );
  }
  location.href = url.toString();
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
          office.allowDisabledServicesMix,
          office.scope,
          office.slotsPerAppointment,
          office.slots,
          office.priority || 1,
          office.parentId,
          office.sharedBookingOfficeIds
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

const applyLocalStorageUiData = (uiData: LocalStorageUiData) => {
  selectedServiceMap.value = new Map(
    Object.entries(uiData.selectedServiceMap ?? {})
  );

  const foundService = services.value.find(
    (service) => String(service.id) === String(uiData.selectedServiceId)
  );
  if (foundService) {
    selectedService.value = foundService as ServiceImpl;
    const count = selectedServiceMap.value.get(String(foundService.id));
    if (count != undefined) {
      selectedService.value.count = count;
    }
    selectedService.value.providers = getProviders(
      selectedService.value.id,
      null
    );
  }

  const foundOffice = offices.value.find(
    (office) => String(office.id) === String(uiData.selectedProviderId)
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
      foundOffice.disabledByServices,
      foundOffice.allowDisabledServicesMix,
      foundOffice.scope,
      foundOffice.slotsPerAppointment,
      foundOffice.slots,
      foundOffice.priority || 1,
      foundOffice.parentId,
      foundOffice.sharedBookingOfficeIds
    );
  }

  selectedTimeslot.value = uiData.selectedTimeslot;
  currentView.value = isAppointmentInPast.value ? 3 : uiData.currentView;
  if (typeof uiData.reservationStartMs === "number") {
    reservationStartMs.value = uiData.reservationStartMs;
  }
};

const runLoginResumeFromHashAndLocalStorage = (
  hash: string,
  uiData: LocalStorageUiData
): void => {
  const appointmentData = parseAppointmentHash(hash);
  if (!appointmentData) {
    handleApiError(
      "appointmentNotFound",
      errorStateMap.value,
      currentErrorData.value
    );
    clearAppointmentLocalStorage();
    clearAppointmentAuthHashSession();
    return;
  }

  loadedAppointmentHash.value = hash;
  clearContextErrors(errorStateMap.value);

  fetchServicesAndProviders(
    props.serviceId ?? undefined,
    props.locationId ?? undefined,
    props.globalState?.baseUrl ?? undefined
  )
    .then((data) => {
      handleErrorApiResponse(
        data,
        errorStates.errorStateMap,
        currentErrorData.value
      );

      if (handleApiResponseForDownTime(data, props.globalState?.baseUrl)) {
        return;
      }

      if ((data as any)?.errors?.length) {
        return;
      }

      services.value = (data as any).services;
      relations.value = (data as any).relations;
      offices.value = (data as any).offices;

      applyLocalStorageUiData(uiData);

      return fetchAppointment(props.globalState, appointmentData).then(
        (response) => {
          if ((response as AppointmentDTO).processId != undefined) {
            appointment.value = response as AppointmentDTO;
            applyContactFromAppointment(appointment.value);
            if (reservationStartMs.value == null) {
              reservationStartMs.value = Date.now();
            }
            if ("captchaToken" in response && (response as any).captchaToken) {
              captchaToken.value =
                captchaToken.value ||
                ((response as any).captchaToken as string);
            }
            // Keep stepper step from UI localStorage (do not open reschedule/cancel dialog).
            currentView.value = isAppointmentInPast.value
              ? 3
              : uiData.currentView;
            clearAppointmentLocalStorage();
            clearAppointmentAuthHashSession();
          } else {
            handleErrorApiResponse(
              response,
              errorStates.errorStateMap,
              currentErrorData.value
            );
          }
        }
      );
    })
    .catch(() => {
      handleApiError(
        "appointmentNotFound",
        errorStateMap.value,
        currentErrorData.value
      );
    });
};

const handleInvalidJumpinLink = () => {
  handleApiError(
    "invalidJumpinLink",
    errorStateMap.value,
    currentErrorData.value
  );
};

const handleServiceFinderRateLimitError = () => {
  handleApiError(
    "rateLimitExceeded",
    errorStateMap.value,
    currentErrorData.value,
    "warning"
  );
};

const clearBookingError = (): void => {
  captchaError.value = false;

  errorStateMap.value.apiErrorAppointmentNotAvailable.value = false;
  errorStateMap.value.apiErrorCaptchaExpired.value = false;
  errorStateMap.value.apiErrorCaptchaMissing.value = false;
  errorStateMap.value.apiErrorCaptchaInvalid.value = false;

  errorStates.currentErrorData.value = null;
};

const clearAllErrors = (): void => {
  clearContextErrors(errorStateMap.value);
  clearBookingError();
};

const handleCaptchaTokenChanged = (token?: string | null): void => {
  captchaToken.value = token ?? undefined;
  clearBookingError();
};

const redirectToAppointmentStart = () => {
  // Clear jump-in link parameters and reset to clean start state
  // This keeps users within our application instead of redirecting to external site
  const baseUrl = window.location.origin + window.location.pathname;
  window.location.href = baseUrl;
};

const downloadIcsAppointment = () => {
  downloadIcsFile(appointment.value?.icsContent);
};

const resetConfirmRouteState = (): void => {
  confirmAppointmentSuccess.value = false;
  appointmentAlreadyActivated.value = false;
  confirmedAppointmentHash.value = null;
  isBookingAppointment.value = false;
};

const runAppointmentFromHash = (hash: string | undefined): void => {
  if (
    !hash ||
    hash === loadedAppointmentHash.value ||
    isLoadingAppointmentFromHash.value
  ) {
    return;
  }

  const appointmentData = parseAppointmentHash(hash);
  if (!appointmentData) {
    handleApiError(
      "appointmentNotFound",
      errorStateMap.value,
      currentErrorData.value
    );
    return;
  }

  resetConfirmRouteState();
  loadedAppointmentHash.value = hash;
  isLoadingAppointmentFromHash.value = true;
  clearContextErrors(errorStateMap.value);
  rebookOrCancelDialog.value = true;

  fetchServicesAndProviders(
    props.serviceId ?? undefined,
    props.locationId ?? undefined,
    props.globalState?.baseUrl ?? undefined
  )
    .then((data) => {
      handleErrorApiResponse(
        data,
        errorStates.errorStateMap,
        currentErrorData.value
      );

      if (handleApiResponseForDownTime(data, props.globalState?.baseUrl)) {
        return;
      }

      services.value = (data as any).services;
      relations.value = (data as any).relations;
      offices.value = (data as any).offices;

      fetchAppointment(props.globalState, appointmentData).then((data) => {
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

            updateServiceLinkId(
              String(
                selectedService.value.rootParentId ??
                  selectedService.value.id ??
                  ""
              )
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
                undefined,
                foundOffice.allowDisabledServicesMix,
                foundOffice.scope,
                foundOffice.slotsPerAppointment,
                undefined,
                foundOffice.priority || 1,
                foundOffice.parentId,
                foundOffice.sharedBookingOfficeIds
              );
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
              });
            }
            if (!appointmentData.action || isAppointmentInPast.value) {
              currentView.value = 3;
            } else if (
              appointmentData.action === APPOINTMENT_ACTION_TYPE.RESCHEDULE
            ) {
              nextRescheduleAppointment();
            } else {
              nextCancelAppointment();
            }
          }
        } else {
          handleApiError(
            "appointmentNotFound",
            errorStateMap.value,
            currentErrorData.value
          );
        }
      });
    })
    .finally(() => {
      isLoadingAppointmentFromHash.value = false;
    });
};

const runConfirmFromHash = (hash: string | undefined): void => {
  if (!hash || isBookingAppointment.value) {
    return;
  }

  // Already showing the activated overview for this confirm link.
  if (
    appointmentAlreadyActivated.value &&
    hash === confirmedAppointmentHash.value
  ) {
    return;
  }

  // Same confirm link opened again after a successful activation in this session
  // (hash watch re-fired without a full remount — e.g. leave route then reopen).
  if (
    confirmAppointmentSuccess.value &&
    hash === confirmedAppointmentHash.value
  ) {
    showAlreadyActivatedAppointment(hash);
    return;
  }

  // Duplicate in-flight confirm for the same hash.
  if (hash === confirmedAppointmentHash.value) {
    return;
  }

  const appointmentData = parseAppointmentHash(hash);
  if (!appointmentData) {
    handleApiError(
      "appointmentNotFound",
      errorStateMap.value,
      currentErrorData.value
    );
    return;
  }

  confirmedAppointmentHash.value = hash;
  loadedAppointmentHash.value = null;
  clearAllErrors();
  currentView.value = 5;
  isBookingAppointment.value = true;
  nextConfirmAppointment(appointmentData, hash);
};

function showAlreadyActivatedAppointment(hash: string): void {
  clearContextErrors(errorStateMap.value);
  // runAppointmentFromHash resets confirm route state; re-apply afterwards.
  runAppointmentFromHash(hash);
  appointmentAlreadyActivated.value = true;
  confirmedAppointmentHash.value = hash;
}

function nextConfirmAppointment(
  appointmentData: AppointmentHash,
  hash: string
) {
  confirmAppointment(props.globalState, appointmentData)
    .then((data) => {
      currentView.value = 5;

      if ((data as AppointmentDTO).processId != undefined) {
        confirmAppointmentSuccess.value = true;
        appointment.value = data as AppointmentDTO;
        clearContextErrors(errorStateMap.value);
        if (isRebooking.value && rebookedAppointment.value) {
          currentContext.value = "cancel";
          cancelAppointment(props.globalState, rebookedAppointment.value);
        }
      } else {
        const firstErrorCode = (data as any).errors?.[0]?.errorCode ?? "";

        if (firstErrorCode === "processNotPreconfirmedAnymore") {
          Promise.resolve(
            fetchAppointment(props.globalState, appointmentData)
          ).then((fetched) => {
            if ((fetched as AppointmentDTO)?.processId != undefined) {
              showAlreadyActivatedAppointment(hash);
              return;
            }
            handleApiError(
              "preconfirmationExpired",
              errorStateMap.value,
              currentErrorData.value
            );
          });
        } else if (firstErrorCode === "appointmentNotFound") {
          handleApiError(
            "preconfirmationExpired",
            errorStateMap.value,
            currentErrorData.value
          );
        } else {
          handleErrorApiResponse(
            data,
            errorStates.errorStateMap,
            currentErrorData.value
          );
        }
      }
    })
    .finally(() => {
      isBookingAppointment.value = false;
    });
}

watch(
  () => props.confirmAppointmentHash,
  (hash) => {
    runConfirmFromHash(hash);
  }
);

watch(
  () => props.appointmentHash,
  (hash) => {
    if (!hash) {
      loadedAppointmentHash.value = null;
      return;
    }
    const uiData = getFreshLocalStorageUiData();
    if (uiData) {
      runLoginResumeFromHashAndLocalStorage(hash, uiData);
      return;
    }
    runAppointmentFromHash(hash);
  }
);

onMounted(() => {
  runConfirmFromHash(props.confirmAppointmentHash);

  if (props.confirmAppointmentHash) {
    clearAppointmentLocalStorage();
    clearAppointmentAuthHashSession();
    focusActiveStepperItem();
    return;
  }

  const authHash = resolveAppointmentAuthHash(props.appointmentHash);
  const uiData = getFreshLocalStorageUiData();

  if (authHash && uiData) {
    runLoginResumeFromHashAndLocalStorage(authHash, uiData);
    focusActiveStepperItem();
    return;
  }

  if (authHash) {
    runAppointmentFromHash(authHash);
    clearAppointmentLocalStorage();
    clearAppointmentAuthHashSession();
    focusActiveStepperItem();
    return;
  }

  if (uiData) {
    clearContextErrors(errorStateMap.value);

    fetchServicesAndProviders(
      props.serviceId ?? undefined,
      props.locationId ?? undefined,
      props.globalState?.baseUrl ?? undefined
    )
      .then((data) => {
        handleErrorApiResponse(
          data,
          errorStates.errorStateMap,
          currentErrorData.value
        );

        if (handleApiResponseForDownTime(data, props.globalState?.baseUrl)) {
          return;
        }

        if ((data as any)?.errors?.length) {
          return;
        }

        services.value = (data as any).services;
        relations.value = (data as any).relations;
        offices.value = (data as any).offices;

        // UI-only restore — never apply authKey from localStorage (legacy or new).
        applyLocalStorageUiData(uiData);
        clearAppointmentLocalStorage();
        clearAppointmentAuthHashSession();
      })
      .catch(() => {
        handleApiError(
          "appointmentNotFound",
          errorStateMap.value,
          currentErrorData.value
        );
      });

    focusActiveStepperItem();
    return;
  }

  clearAppointmentLocalStorage();
  clearAppointmentAuthHashSession();
  focusActiveStepperItem();
});
</script>
<style lang="scss" scoped>
@use "@/styles/breakpoints.scss" as *;

.m-button-group {
  display: flex;
  flex-direction: row;
  margin-top: 1rem;
}

.button-item {
  margin-bottom: 0;
  margin-right: 1rem;
}

@include xs-down {
  .m-button-group {
    flex-direction: column;
    align-items: flex-start;
  }

  .button-item {
    margin-right: 0;
    margin-bottom: 1rem;
    width: auto;
    flex: none;
  }
}
</style>
