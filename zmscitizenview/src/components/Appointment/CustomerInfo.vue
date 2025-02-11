<template>
  <h2
    class="m-component-form__title"
    tabindex="0"
  >
    Kontaktdaten
  </h2>
  <form class="m-form m-form--default">
    <muc-input
      id="firstname"
      v-model="customerData.firstName"
      :error-msg="showErrorMessage ? errorMessageFirstName : undefined"
      :label="t('firstName')"
      required
    />
    <muc-input
      id="lastname"
      v-model="customerData.lastName"
      :error-msg="showErrorMessage ? errorMessageLastName : undefined"
      :label="t('lastName')"
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
      v-if="telephoneActivated"
      id="telephonenumber"
      v-model="customerData.telephoneNumber"
      :error-msg="showErrorMessage ? errorMessageTelephoneNumber : undefined"
      :label="t('telephoneNumber')"
      :required="selectedProvider.scope.telephoneRequired"
      placeholder="+49 151 1234567"
    />
    <muc-text-area
      v-if="customTextfieldActivated"
      id="remarks"
      v-model="customerData.customTextfield"
      :error-msg="showErrorMessage ? errorMessageCustomTextfield : undefined"
      :label="selectedProvider.scope.customTextfieldLabel"
      :required="selectedProvider.scope.customTextfieldRequired"
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
      icon="arrow-right"
      @click="nextStep"
    >
      <template #default>{{ t("next") }}</template>
    </muc-button>
  </div>
</template>

<script setup lang="ts">
import { MucButton, MucInput, MucTextArea } from "@muenchen/muc-patternlab-vue";
import { computed, inject, ref } from "vue";

import {
  CustomerDataProvider,
  SelectedTimeslotProvider,
} from "@/types/ProvideInjectTypes";

const props = defineProps<{
  t: any;
}>();

const emit = defineEmits<(e: "next" | "back") => void>();

const { customerData } = inject<CustomerDataProvider>(
  "customerData"
) as CustomerDataProvider;

const { selectedProvider } = inject<SelectedTimeslotProvider>(
  "selectedTimeslot"
) as SelectedTimeslotProvider;

const showErrorMessage = ref<boolean>(false);

const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const telephonPattern = /^\+?\d[\d\s]*$/;

const telephoneActivated = () =>
  selectedProvider.value &&
  selectedProvider.value.scope &&
  selectedProvider.value.scope.telephoneActivated;

const customTextfieldActivated = () =>
  selectedProvider.value &&
  selectedProvider.value.scope &&
  selectedProvider.value.scope.customTextfieldActivated;

const errorMessageFirstName = computed(() =>
  customerData.value.firstName ? undefined : props.t("errorMessageFirstName")
);

const errorMessageLastName = computed(() =>
  customerData.value.lastName ? undefined : props.t("errorMessageLastName")
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

const validForm = computed(
  () =>
    !errorMessageFirstName.value &&
    !errorMessageLastName.value &&
    !errorMessageMailAddress.value &&
    !errorMessageTelephoneNumber.value &&
    !errorMessageCustomTextfield.value
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
