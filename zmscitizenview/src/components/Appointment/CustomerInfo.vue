<template>
  <h2 class="m-component-form__title">Kontaktdaten</h2>
  <form class="m-form m-form--default">
    <muc-input
      v-model="firstName"
      :error-msg="errorMessageFirstName"
      :label="t('firstName')"
      required
    />
    <muc-input
      v-model="lastName"
      :error-msg="errorMessageLastName"
      :label="t('lastName')"
      required
    />
    <muc-input
      v-model="mailAddress"
      :error-msg="errorMessagemMailAddress"
      :label="t('mailAddress')"
      required
    />
    <muc-input
      v-model="telephoneNumber"
      :error-msg="errorMessagemTelephoneNumber"
      :label="t('telephoneNumber')"
      placeholder="+49 151 1234567"
    />
    <muc-text-area
      v-model="remarks"
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
import { computed, ref } from "vue";

const props = defineProps<{
  t: any;
}>();

const emit = defineEmits<(e: "next" | "back") => void>();

const firstName = ref<string>();
const lastName = ref<string>();
const mailAddress = ref<string>();
const telephoneNumber = ref<string>();
const remarks = ref<string>();

const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const telephonPattern = /^\+?\d[\d\s]*$/;

const errorMessageFirstName = computed(() =>
  firstName.value ? undefined : props.t("errorMessageFirstName")
);

const errorMessageLastName = computed(() =>
  lastName.value ? undefined : props.t("errorMessageLastName")
);

const errorMessagemMailAddress = computed(() => {
  if (!mailAddress.value) {
    return props.t("errorMessageMailAddressRequired");
  } else if (!emailPattern.test(mailAddress.value)) {
    return props.t("errorMessageMailAddressValidation");
  } else {
    return undefined;
  }
});

const errorMessagemTelephoneNumber = computed(() => {
  if (!telephoneNumber.value && false) {
    return props.t("errorMessageTelephoneNumberRequired");
  } else if (
    telephoneNumber.value &&
    !telephonPattern.test(telephoneNumber.value)
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
    !errorMessagemMailAddress.value &&
    !errorMessagemTelephoneNumber.value
);

const nextStep = () => emit("next");
const previousStep = () => emit("back");
</script>

<style scoped></style>
