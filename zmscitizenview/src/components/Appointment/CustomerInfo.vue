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
      v-model="firstNameComputed"
      :error-msg="showErrorMessage ? errorMessageFirstName : undefined"
      :label="t('firstName')"
      max="50"
      required
    />
    <muc-input
      id="lastname"
      v-model="lastNameComputed"
      :error-msg="showErrorMessage ? errorMessageLastName : undefined"
      :label="t('lastName')"
      max="50"
      required
    />
    <muc-input
      id="mailaddress"
      v-model="mailAddressComputed"
      :error-msg="showErrorMessage ? errorMessageMailAddress : undefined"
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
      v-model="telephoneNumberComputed"
      :error-msg="showErrorMessage ? errorMessageTelephoneNumber : undefined"
      :label="t('telephoneNumber')"
      :required="selectedProvider.scope.telephoneRequired"
      max="50"
      placeholder="+491511234567"
    />
    <div
      v-if="
        selectedProvider &&
        selectedProvider.scope &&
        selectedProvider.scope.customTextfieldActivated
      "
      class="textarea-wrapper"
    >
      <muc-text-area
        id="remarks"
        v-model="customTextfieldComputed"
        :error-msg="showErrorMessage ? errorMessageCustomTextfield : undefined"
        :label="selectedProvider.scope.customTextfieldLabel"
        :required="selectedProvider.scope.customTextfieldRequired"
        max="100"
      />
      <span class="char-counter"> {{ customTextfieldCount }}/100 </span>
    </div>
    <div
      v-if="
        selectedProvider &&
        selectedProvider.scope &&
        selectedProvider.scope.customTextfield2Activated
      "
      class="textarea-wrapper"
    >
      <muc-text-area
        id="remarks2"
        v-model="customTextfield2Computed"
        :error-msg="showErrorMessage ? errorMessageCustomTextfield2 : undefined"
        :label="selectedProvider.scope.customTextfield2Label"
        :required="selectedProvider.scope.customTextfield2Required"
        max="100"
      />
      <span class="char-counter"> {{ customTextfield2Count }}/100 </span>
    </div>
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

const customTextfieldCount = computed(
  () => customerData.value.customTextfield?.length || 0
);
const customTextfield2Count = computed(
  () => customerData.value.customTextfield2?.length || 0
);

function useLimitedComputed(
  fieldName: keyof typeof customerData.value,
  maxLength: number
) {
  return computed<string>({
    get: () => customerData.value[fieldName] || "",
    set: (val: string) => {
      customerData.value[fieldName] = val.slice(0, maxLength);
    },
  });
}

const firstNameComputed = useLimitedComputed("firstName", 50);
const lastNameComputed = useLimitedComputed("lastName", 50);
const mailAddressComputed = useLimitedComputed("mailAddress", 50);
const telephoneNumberComputed = useLimitedComputed("telephoneNumber", 50);
const customTextfieldComputed = useLimitedComputed("customTextfield", 100);
const customTextfield2Computed = useLimitedComputed("customTextfield2", 100);
</script>

<style scoped>
.textarea-wrapper {
  position: relative;
}

.char-counter {
  position: absolute;
  bottom: 0.2rem;
  right: 0.2rem;
  font-size: 1rem;
  color: #617586;
  padding: 0 0.25rem;
  pointer-events: none;
  z-index: 10;
}
</style>
