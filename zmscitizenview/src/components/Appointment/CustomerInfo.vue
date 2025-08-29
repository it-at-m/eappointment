<template>
  <div v-if="isExpired">
    <muc-callout type="error">
      <template #content>
        {{ t("apiErrorSessionTimeoutText") }}
      </template>
      <template #header>{{ t("apiErrorSessionTimeoutHeader") }}</template>
    </muc-callout>
  </div>
  <h2
    v-if="!isExpired"
    class="m-component-form__title"
    tabindex="0"
  >
    {{ t("contactDetails") }}
  </h2>
  <form
    v-if="!isExpired"
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
      v-if="!isExpired"
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
import type {
  SelectedAppointmentProvider,
  SelectedTimeslotProvider,
} from "@/types/ProvideInjectTypes";
import type { Ref } from "vue";

import {
  MucButton,
  MucCallout,
  MucInput,
  MucTextArea,
} from "@muenchen/muc-patternlab-vue";
import { computed, inject, onBeforeUnmount, onMounted, ref, watch } from "vue";

import { CustomerDataProvider } from "@/types/ProvideInjectTypes";
import { useReservationTimer } from "@/utils/useReservationTimer";

const props = defineProps<{ t: (key: string) => string }>();

const emit = defineEmits<(e: "next" | "back") => void>();

const { customerData } = inject<CustomerDataProvider>(
  "customerData"
) as CustomerDataProvider;

const { selectedProvider } = inject<SelectedTimeslotProvider>(
  "selectedTimeslot"
) as SelectedTimeslotProvider;

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

const { isExpired, timeLeftString } = useReservationTimer();

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
const previousStep = () => emit("back");
</script>

<style scoped>
.m-button-group {
  margin-top: 48px;
}
</style>
