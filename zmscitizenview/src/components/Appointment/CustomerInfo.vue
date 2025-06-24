<template>
  <h2
    class="m-component-form__title"
    tabindex="0"
  >
    {{ t("contactDetails") }}
  </h2>
  <form class="m-form m-form--default">
    <muc-input
      id="firstname"
      v-model="customerData.firstName"
      :error-msg="showErrorMessage ? errorMessageFirstName : undefined"
      :label="t('firstName')"
      max="60"
      required
    />
    <muc-input
      id="lastname"
      v-model="customerData.lastName"
      :error-msg="showErrorMessage ? errorMessageLastName : undefined"
      :label="t('lastName')"
      max="60"
      required
    />
    <muc-input
      id="mailaddress"
      v-model="customerData.mailAddress"
      :error-msg="showErrorMessage ? errorMessageMailAddress : undefined"
      :label="t('mailAddress')"
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
      :error-msg="showErrorMessage ? errorMessageTelephoneNumber : undefined"
      :label="t('telephoneNumber')"
      :required="selectedProvider.scope.telephoneRequired"
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
      :error-msg="showErrorMessage ? errorMessageCustomTextfield : undefined"
      :label="selectedProvider.scope.customTextfieldLabel"
      :required="selectedProvider.scope.customTextfieldRequired"
    />
    <muc-text-area
      v-if="
        selectedProvider &&
        selectedProvider.scope &&
        selectedProvider.scope.customTextfield2Activated
      "
      id="remarks2"
      v-model="customerData.customTextfield2"
      :error-msg="showErrorMessage ? errorMessageCustomTextfield2 : undefined"
      :label="selectedProvider.scope.customTextfield2Label"
      :required="selectedProvider.scope.customTextfield2Required"
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

import { MucButton, MucInput, MucTextArea } from "@muenchen/muc-patternlab-vue";
import { computed, inject, ref } from "vue";

import {
  CustomerDataProvider,
  SelectedTimeslotProvider,
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

// Inject loading states
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

const showErrorMessage = ref<boolean>(false);

const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const telephonPattern = /^\+?[0-9]\d{6,14}$/;

const errorMessageFirstName = computed(() =>
  customerData.value.firstName?.trim()
    ? undefined
    : props.t("errorMessageFirstName")
);

const errorMessageLastName = computed(() =>
  customerData.value.lastName?.trim()
    ? undefined
    : props.t("errorMessageLastName")
);

const errorMessageMailAddress = computed(() => {
  if (!customerData.value.mailAddress) {
    return props.t("errorMessageMailAddressRequired");
  } else if (!emailPattern.test(customerData.value.mailAddress)) {
    return props.t("errorMessageMailAddressValidation");
  } else {
    return undefined;
  }
});

const errorMessageTelephoneNumber = computed(() => {
  if (
    !customerData.value.telephoneNumber &&
    selectedProvider.value &&
    selectedProvider.value.scope &&
    selectedProvider.value.scope.telephoneRequired
  ) {
    return props.t("errorMessageTelephoneNumberRequired");
  } else if (
    customerData.value.telephoneNumber &&
    !telephonPattern.test(customerData.value.telephoneNumber)
  ) {
    return props.t("errorMessageTelephoneNumberValidation");
  } else {
    return undefined;
  }
});

const errorMessageCustomTextfield = computed(() => {
  if (
    !customerData.value.customTextfield &&
    selectedProvider.value &&
    selectedProvider.value.scope &&
    selectedProvider.value.scope.customTextfieldRequired
  ) {
    return props.t("errorMessageCustomTextfield");
  } else {
    return undefined;
  }
});

const errorMessageCustomTextfield2 = computed(() => {
  if (
    !customerData.value.customTextfield2 &&
    selectedProvider.value &&
    selectedProvider.value.scope &&
    selectedProvider.value.scope.customTextfield2Required
  ) {
    return props.t("errorMessageCustomTextfield2");
  } else {
    return undefined;
  }
});

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

<style scoped></style>
