<template>
  <!-- <p>
    Verbleibende Zeit {{ countdownLabel }}.
  </p> -->
  <div v-if="sessionTimeoutError">
    <muc-callout type="error">
      <template #content>
        {{ t("apiErrorSessionTimeoutText") }}
      </template>

      <template #header>{{ t("apiErrorSessionTimeoutHeader") }}</template>
    </muc-callout>
  </div>
  <h2
    v-if="!sessionTimeoutError"
    class="m-component-form__title"
    tabindex="0"
  >
    {{ t("contactDetails") }}
  </h2>
  <form
    v-if="!sessionTimeoutError"
    class="m-form m-form--default"
  >
    <muc-input
      id="firstname"
      v-model="customerData.firstName"
      :error-msg="errorDisplayFirstName"
      :label="t('firstName')"
      max="50"
      required
    />
    <muc-input
      id="lastname"
      v-model="customerData.lastName"
      :error-msg="errorDisplayLastName"
      :label="t('lastName')"
      max="50"
      required
    />
    <muc-input
      id="mailaddress"
      v-model="customerData.mailAddress"
      :error-msg="errorDisplayMailAddress"
      :label="t('mailAddress')"
      max="50"
      required
    />
    <muc-input
      v-if="
        selectedProvider &&
        selectedProvider.scope &&
        selectedProvider.scope.telephoneActivated
      "
      id="telephonenumber"
      v-model="customerData.telephoneNumber"
      :error-msg="errorDisplayTelephoneNumber"
      :label="t('telephoneNumber')"
      :required="selectedProvider.scope.telephoneRequired"
      max="50"
      placeholder="+491511234567"
    />
    <muc-text-area
      v-if="
        selectedProvider &&
        selectedProvider.scope &&
        selectedProvider.scope.customTextfieldActivated
      "
      id="remarks"
      v-model="customerData.customTextfield"
      :error-msg="errorDisplayCustomTextfield"
      :label="selectedProvider.scope.customTextfieldLabel"
      :required="selectedProvider.scope.customTextfieldRequired"
      maxlength="100"
    />
    <muc-text-area
      v-if="
        selectedProvider &&
        selectedProvider.scope &&
        selectedProvider.scope.customTextfield2Activated
      "
      id="remarks2"
      v-model="customerData.customTextfield2"
      :error-msg="errorDisplayCustomTextfield2"
      :label="selectedProvider.scope.customTextfield2Label"
      :required="selectedProvider.scope.customTextfield2Required"
      maxlength="100"
    />
  </form>
  <div class="m-button-group">
    <muc-button
      icon="arrow-left"
      icon-shown-left
      variant="secondary"
      @click="previousStep"
    >
      <template #default>{{ t("back") }}</template>
    </muc-button>
    <muc-button
      v-if="!sessionTimeoutError"
      :disabled="loadingStates.isUpdatingAppointment.value"
      :icon="'arrow-right'"
      @click="nextStep"
    >
      <template #default>
        <span>{{ t("next") }}</span>
      </template>
    </muc-button>
  </div>
</template>

<script setup lang="ts">
import type { Ref } from "vue";

import {
  MucButton,
  MucCallout,
  MucInput,
  MucTextArea,
} from "@muenchen/muc-patternlab-vue";
import { computed, inject, ref, onMounted, onUnmounted, watch } from "vue";

import {
  CustomerDataProvider,
  SelectedTimeslotProvider,
  SelectedAppointmentProvider
} from "@/types/ProvideInjectTypes";

const props = defineProps<{
  t: (key: string) => string;
}>();

const emit = defineEmits<(e: "next" | "back") => void>();

const { customerData } = inject<CustomerDataProvider>(
  "customerData"
) as CustomerDataProvider;

const { selectedProvider } = inject<SelectedTimeslotProvider>(
  "selectedTimeslot"
) as SelectedTimeslotProvider;

const injectedAppointment = inject<SelectedAppointmentProvider>("appointment");
const appointmentRef = injectedAppointment?.appointment;

const sessionTimeoutErrorRef = inject<Ref<boolean>>("sessionTimeoutErrorRef", ref(false));
const sessionTimeoutError = sessionTimeoutErrorRef;

const remainingMs = ref<number | null>(null);
const expiresAtMs = ref<number | null>(null);
let tickTimer: number | null = null;

const loadingStates = inject("loadingStates", {
  isReservingAppointment: ref(false),
  isUpdatingAppointment: ref(false),
  isBookingAppointment: ref(false),
  isCancelingAppointment: ref(false),
}) as {
  isReservingAppointment: Ref<boolean>;
  isUpdatingAppointment: Ref<boolean>;
  isBookingAppointment: Ref<boolean>;
  isCancelingAppointment: Ref<boolean>;
};

function parseCreatedMs(created: unknown): number | null {
  if (created == null) return null;

  if (typeof created === "number" || (typeof created === "string" && created.trim() !== "" && !isNaN(Number(created)))) {
    const n = Number(created);
    return n < 1e12 ? n * 1000 : n;
  }
  const parsed = Date.parse(String(created));
  return isNaN(parsed) ? null : parsed;
}

function recomputeFromAppointment() {
  clearTick();

  const appt: any = appointmentRef?.value;
  if (!appt) {
    remainingMs.value = null;
    expiresAtMs.value = null;
    return;
  }
  let createdMs = parseCreatedMs(appt.createTimestamp);
  
  if (createdMs == null) {
      createdMs = Date.now();
      console.warn("[CustomerInfo] createTimestamp fehlt – Fallback auf Date.now()");
    }

    console.log("[CustomerInfo] appt snapshot", {
      createTimestamp: appt?.createTimestamp,
      topLevelReservationDuration: appt?.reservationDuration,
      scopeReservationDuration: appt?.scope?.reservationDuration,
      scope: appt?.scope
    });
  const durationMin = appt?.reservationDuration ?? appt?.scope?.reservationDuration ?? null;

  if (durationMin == null || isNaN(Number(durationMin))) {
    remainingMs.value = null;
    expiresAtMs.value = null;
    console.warn("[CustomerInfo] reservationDuration fehlt oder ist ungültig:", durationMin, appt?.scope);
    return;
  }
  
  const expires = createdMs + Number(durationMin) * 60 * 1000;
  expiresAtMs.value = expires;

  tick();
  tickTimer = window.setInterval(tick, 1000) as unknown as number;
}
function tick() {
  if (!expiresAtMs.value) {
    remainingMs.value = null;
    return;
  }
  const now = Date.now();
  const diff = Math.max(0, expiresAtMs.value - now);
  remainingMs.value = diff;

  if (diff === 0) {
    if (sessionTimeoutErrorRef) {
      sessionTimeoutErrorRef.value = true;
    }
    clearTick();
  }
}

function clearTick() {
  if (tickTimer != null) {
    clearInterval(tickTimer as unknown as number);
    tickTimer = null;
  }
}

const countdownLabel = computed(() => {
  if (remainingMs.value == null) return "";
  const total = Math.ceil(remainingMs.value / 1000);
  const m = Math.floor(total / 60);
  const s = total % 60;
  return `${m}:${s.toString().padStart(2, "0")}`;
});

watch(appointmentRef ?? ref(null), () => {
  if (sessionTimeoutErrorRef) {
    sessionTimeoutErrorRef.value = false;
  }
  recomputeFromAppointment();
}, { immediate: true });

onMounted(() => {
  recomputeFromAppointment();
});

onUnmounted(() => {
  clearTick();
});

const showErrorMessage = ref<boolean>(false);

const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const telephonPattern = /^\+?[0-9]\d{6,14}$/;

const errorMessageFirstName = computed(() => {
  if (!showErrorMessage.value) return undefined;

  return customerData.value.firstName?.trim()
    ? undefined
    : props.t("errorMessageFirstName");
});

const maxLengthMessageFirstName = computed(() =>
  customerData.value.firstName?.length >= 50
    ? props.t("errorMessageMaxLength", { max: 50 })
    : undefined
);

const errorDisplayFirstName = computed(
  () => errorMessageFirstName.value ?? maxLengthMessageFirstName.value
);

const errorMessageLastName = computed(() => {
  if (!showErrorMessage.value) return undefined;

  return customerData.value.lastName?.trim()
    ? undefined
    : props.t("errorMessageLastName");
});

const maxLengthMessageLastName = computed(() =>
  customerData.value.lastName?.length >= 50
    ? props.t("errorMessageMaxLength", { max: 50 })
    : undefined
);

const errorDisplayLastName = computed(
  () => errorMessageLastName.value ?? maxLengthMessageLastName.value
);

const errorMessageMailAddress = computed(() => {
  if (!showErrorMessage.value) return undefined;

  if (!customerData.value.mailAddress) {
    return props.t("errorMessageMailAddressRequired");
  } else if (!emailPattern.test(customerData.value.mailAddress)) {
    return props.t("errorMessageMailAddressValidation");
  }
  return undefined;
});

const maxLengthMessageMailAddress = computed(() =>
  customerData.value.mailAddress?.length >= 50
    ? props.t("errorMessageMaxLength", { max: 50 })
    : undefined
);

const errorDisplayMailAddress = computed(
  () => errorMessageMailAddress.value ?? maxLengthMessageMailAddress.value
);

const errorMessageTelephoneNumber = computed(() => {
  if (!showErrorMessage.value) return undefined;

  if (
    !customerData.value.telephoneNumber &&
    selectedProvider.value?.scope?.telephoneRequired
  ) {
    return props.t("errorMessageTelephoneNumberRequired");
  } else if (
    customerData.value.telephoneNumber &&
    !telephonPattern.test(customerData.value.telephoneNumber)
  ) {
    return props.t("errorMessageTelephoneNumberValidation");
  }
  return undefined;
});

const maxLengthMessageTelephoneNumber = computed(() =>
  customerData.value.telephoneNumber?.length >= 50
    ? props.t("errorMessageMaxLength", { max: 50 })
    : undefined
);

const errorDisplayTelephoneNumber = computed(
  () =>
    errorMessageTelephoneNumber.value ?? maxLengthMessageTelephoneNumber.value
);

const errorMessageCustomTextfield = computed(() => {
  if (!showErrorMessage.value) return undefined;

  if (
    !customerData.value.customTextfield &&
    selectedProvider.value?.scope?.customTextfieldRequired
  ) {
    return props.t("errorMessageCustomTextfield");
  }
  return undefined;
});

const maxLengthMessageCustomTextfield = computed(() =>
  customerData.value.customTextfield?.length >= 100
    ? props.t("errorMessageMaxLength", { max: 100 })
    : undefined
);

const errorDisplayCustomTextfield = computed(
  () =>
    errorMessageCustomTextfield.value ?? maxLengthMessageCustomTextfield.value
);

const errorMessageCustomTextfield2 = computed(() => {
  if (!showErrorMessage.value) return undefined;

  if (
    !customerData.value.customTextfield2 &&
    selectedProvider.value?.scope?.customTextfield2Required
  ) {
    return props.t("errorMessageCustomTextfield2");
  }
  return undefined;
});

const maxLengthMessageCustomTextfield2 = computed(() =>
  customerData.value.customTextfield2?.length >= 100
    ? props.t("errorMessageMaxLength", { max: 100 })
    : undefined
);

const errorDisplayCustomTextfield2 = computed(
  () =>
    errorMessageCustomTextfield2.value ?? maxLengthMessageCustomTextfield2.value
);

const validForm = computed(
  () =>
    !errorMessageFirstName.value &&
    !errorMessageLastName.value &&
    !errorMessageMailAddress.value &&
    !errorMessageTelephoneNumber.value &&
    !errorMessageCustomTextfield.value &&
    !errorMessageCustomTextfield2.value
);

const nextStep = () => {
  showErrorMessage.value = true;
  if (validForm.value) {
    emit("next");
  }
};
const previousStep = () => {
  clearTick();
  emit("back")
};
</script>

<style scoped></style>
