<template>
  <h2 class="m-component-form__title">Kontaktdaten</h2>
  <form class="m-form m-form--default">
    <muc-input
      v-model="customerData.firstName"
      :error-msg="errorMessageFirstName"
      :label="t('firstName')"
      required
    />
    <muc-input
      v-model="customerData.lastName"
      :error-msg="errorMessageLastName"
      :label="t('lastName')"
      required
    />
    <muc-input
      v-model="customerData.mailAddress"
      :error-msg="errorMessageMailAddress"
      :label="t('mailAddress')"
      required
    />
    <muc-input
      v-if="telephoneActivated"
      v-model="customerData.telephoneNumber"
      :error-msg="errorMessageTelephoneNumber"
      :label="t('telephoneNumber')"
      placeholder="+49 151 1234567"
    />
    <muc-text-area
      v-if="customTextfieldActivated"
      v-model="customerData.remarks"
      :label="t('remarks')"
      :hint="t('remarkCompletionInstructions')"
    />
  </form>
  <div class="m-submit-group">
    <muc-button
      variant="secondary"
      @click="previousStep"
    >
      <template #default>{{ t("back") }}</template>
    </muc-button>
    <muc-button
      :disabled="!validForm"
      @click="nextStep"
    >
      <template #default>{{ t("next") }}</template>
    </muc-button>
  </div>
</template>

<script setup lang="ts">
import { MucButton, MucInput, MucTextArea } from "@muenchen/muc-patternlab-vue";
import {computed, inject} from "vue";
import {CustomerDataProvider, SelectedAppointmentProvider} from "@/types/ProvideInjectTypes";

const props = defineProps<{
  t: any;
}>();

const emit = defineEmits<(e: "next" | "back") => void>();

const { customerData } = inject<CustomerDataProvider>(
  "customerData"
) as CustomerDataProvider;

const { appointment } = inject<SelectedAppointmentProvider>(
  "appointment"
) as SelectedAppointmentProvider;

const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const telephonPattern = /^\+?\d[\d\s]*$/;

const telephoneActivated = () =>
  appointment.value
  && appointment.value.scope
  && appointment.value.scope.telephoneActivated == "1";

const customTextfieldActivated = () =>
  appointment.value
  && appointment.value.scope
  && appointment.value.scope.customTextfieldActivated == "1";

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
  if (!customerData.value.telephoneNumber && appointment.value && appointment.value.scope.telephoneRequired == "1") {
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

const validForm = computed(
  () =>
    !errorMessageFirstName.value &&
    !errorMessageLastName.value &&
    !errorMessageMailAddress.value &&
    !errorMessageTelephoneNumber.value
);

const nextStep = () => emit("next");
const previousStep = () => emit("back");

</script>

<style scoped></style>
