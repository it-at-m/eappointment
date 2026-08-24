<template>
  <div v-if="isExpired">
    <muc-callout type="error">
      <template #content>
        <p>{{ t("apiErrorSessionTimeoutText") }}</p>
      </template>
      <template #header>{{ t("apiErrorSessionTimeoutHeader") }}</template>
    </muc-callout>
  </div>
  <div
    v-if="showLoginOption && !isExpired"
    class="login-option-block"
    :class="{ 'login-option-block--with-error': showLoginErrorBanner }"
  >
    <!--Can be replaced if MucCallout has been extended with buttons in @muenchen/muc-patternlab-vue https://github.com/it-at-m/muc-patternlab-vue/pull/573 -->
    <div
      v-if="!globalState.isLoggedIn"
      class="m-callout m-callout--default"
      aria-label="Information"
    >
      <div class="m-callout__inner">
        <div class="m-callout__icon">
          <muc-icon icon="user" />
        </div>
        <div class="m-callout__body">
          <div class="m-callout__body__inner">
            <h2 class="m-callout__headline">
              {{ t("optionalLoginHeader") }}
            </h2>
            <div class="m-callout__content">
              <p>
                {{ t("optionalLoginText") }}
              </p>
              <muc-button
                :icon="'sign-in'"
                @click="login"
              >
                <template #default>
                  <span>{{ t("login") }}</span>
                </template>
              </muc-button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <muc-callout
      v-else
      type="success"
    >
      <template #content>
        <p>{{ t("loggedinText") }}</p>
      </template>
      <template #header>{{ t("loggedinHeader") }}</template>
      <template #icon><muc-icon icon="user-fill" /></template>
    </muc-callout>
    <muc-banner
      v-if="showLoginErrorBanner"
      type="danger"
      variant="content"
      class="login-failed-banner"
    >
      {{ t("loginFailedBefore") }}
      <strong>{{ t("loginFailedHeader") }}</strong
      >.
      {{ t("loginFailedText") }}
    </muc-banner>
  </div>
  <h2
    v-if="!isExpired"
    class="m-component-form__title"
    :class="{
      'm-component-form__title--after-login-error': showLoginErrorBanner,
    }"
  >
    {{ t("contactDetails") }}
  </h2>
  <form
    v-if="!isExpired"
    ref="contactForm"
    class="m-form m-form--default"
  >
    <!--
      muc-input / muc-text-area have no disabled prop (fallthrough lands on a
      wrapper div). A fieldset is what actually disables the native control.
    -->
    <fieldset
      class="contact-lock"
      data-contact-lock="firstname"
      :disabled="lockFirstName"
    >
      <muc-input
        id="firstname"
        v-model="customerData.firstName"
        autocomplete="given-name"
        :error-msg="errorDisplayFirstName"
        :label="t('firstName')"
        max="50"
        required
      />
    </fieldset>
    <fieldset
      class="contact-lock"
      data-contact-lock="lastname"
      :disabled="lockLastName"
    >
      <muc-input
        id="lastname"
        v-model="customerData.lastName"
        autocomplete="family-name"
        :error-msg="errorDisplayLastName"
        :label="t('lastName')"
        max="50"
        required
      />
    </fieldset>
    <fieldset
      class="contact-lock"
      data-contact-lock="mailaddress"
      :disabled="lockMailAddress"
    >
      <muc-input
        id="mailaddress"
        v-model="customerData.mailAddress"
        autocomplete="email"
        :error-msg="errorDisplayMailAddress"
        :label="t('mailAddress')"
        max="50"
        required
      />
    </fieldset>
    <fieldset
      v-if="providerScope?.telephoneActivated"
      class="contact-lock"
      data-contact-lock="telephonenumber"
      :disabled="lockTelephoneNumber"
    >
      <muc-input
        id="telephonenumber"
        v-model="customerData.telephoneNumber"
        autocomplete="tel"
        :error-msg="errorDisplayTelephoneNumber"
        :label="t('telephoneNumber')"
        :required="!!providerScope?.telephoneRequired"
        max="50"
        placeholder="+491511234567"
      />
    </fieldset>
    <fieldset
      v-if="providerScope?.customTextfieldActivated"
      class="contact-lock"
      data-contact-lock="remarks"
      :disabled="lockCustomTextfield"
    >
      <muc-text-area
        id="remarks"
        v-model="customerData.customTextfield"
        :error-msg="errorDisplayCustomTextfield"
        :label="providerScope?.customTextfieldLabel ?? undefined"
        :required="providerScope?.customTextfieldRequired ?? undefined"
        :maxlength="MAX_CUSTOM_TEXT_CHARS"
        :rows="textfieldRows1"
        @input="handleInput1"
      />
    </fieldset>
    <fieldset
      v-if="providerScope?.customTextfield2Activated"
      class="contact-lock"
      data-contact-lock="remarks2"
      :disabled="lockCustomTextfield2"
    >
      <muc-text-area
        id="remarks2"
        v-model="customerData.customTextfield2"
        :error-msg="errorDisplayCustomTextfield2"
        :label="providerScope?.customTextfield2Label ?? undefined"
        :required="providerScope?.customTextfield2Required ?? undefined"
        :maxlength="MAX_CUSTOM_TEXT_CHARS"
        :rows="textfieldRows2"
        @input="handleInput2"
      />
    </fieldset>
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
      :disabled="
        loadingStates.isUpdatingAppointment.value || !hasReservedAppointment
      "
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
import type { AppointmentDTO } from "@/api/models/AppointmentDTO";
import type {
  CustomerDataProvider,
  SelectedAppointmentProvider,
  SelectedTimeslotProvider,
} from "@/types/ProvideInjectTypes";
import type { Ref } from "vue";

import {
  MucBanner,
  MucButton,
  MucCallout,
  MucIcon,
  MucInput,
  MucTextArea,
} from "@muenchen/muc-patternlab-vue";
import {
  computed,
  inject,
  nextTick,
  onMounted,
  onUpdated,
  ref,
  watch,
} from "vue";

import { GlobalState } from "@/types/GlobalState";
import {
  normalizePlainText,
  plainTextCharCount,
} from "@/utils/processPlainText";
import {
  isFilledContactValue,
  splitFamilyName,
} from "@/utils/rebookingContact";
import { countLines, handleInput } from "@/utils/textfieldRows";
import { useReservationTimer } from "@/utils/useReservationTimer";

/**
 * UX pre-check limit aligned with backend ProcessPlainText max (250).
 * Backend entities validation is authoritative if client normalization differs.
 */
const MAX_CUSTOM_TEXT_CHARS = 250;

const inputLines1 = ref<number>(3);
const inputLines2 = ref<number>(3);
const textfieldRows1 = computed(() => inputLines1.value);
const textfieldRows2 = computed(() => inputLines2.value);
const handleInput1 = (event: Event) => {
  handleInput(inputLines1, event);
};
const handleInput2 = (event: Event) => {
  handleInput(inputLines2, event);
};

const props = defineProps<{
  globalState: GlobalState;
  showLoginOption: boolean;
  isRebooking?: boolean;
  loginFailed?: boolean;
  t: (key: string, params?: Record<string, unknown>) => string;
}>();

const emit = defineEmits<(e: "next" | "back" | "login") => void>();

const { customerData } = inject<CustomerDataProvider>(
  "customerData"
) as CustomerDataProvider;

const { selectedProvider } = inject<SelectedTimeslotProvider>(
  "selectedTimeslot"
) as SelectedTimeslotProvider;

const { appointment } = inject<SelectedAppointmentProvider>("appointment", {
  appointment: ref(undefined),
}) as SelectedAppointmentProvider;

const rebookedAppointment = inject<Ref<AppointmentDTO | undefined>>(
  "rebookedAppointment",
  ref(undefined)
);

/**
 * After Bürger-Login remount, appointment is re-fetched async. Weiter must stay
 * disabled until processId is back — nextUpdateAppointment no-ops without it.
 */
const hasReservedAppointment = computed(
  () => !!appointment.value?.processId && !!appointment.value?.authKey
);

/** Prefer selected office scope; fall back to reserved appointment scope (login/back). */
const providerScope = computed(
  () => selectedProvider.value?.scope ?? appointment.value?.scope
);

/**
 * Lock from the previous appointment, not from live customerData. Otherwise
 * typing into an empty required field would disable it, and a mount snapshot
 * misses values that are copied onto customerData a tick later.
 * muc-input has no disabled prop — fieldsets disable the native control.
 */
const previousAppointment = computed(
  () => rebookedAppointment.value ?? appointment.value
);

const previousName = computed(() =>
  splitFamilyName(previousAppointment.value?.familyName)
);

const lockFirstName = computed(
  () =>
    Boolean(props.isRebooking) &&
    isFilledContactValue(previousName.value.firstName)
);
const lockLastName = computed(
  () =>
    Boolean(props.isRebooking) &&
    isFilledContactValue(previousName.value.lastName)
);
const lockMailAddress = computed(
  () =>
    Boolean(props.isRebooking) &&
    isFilledContactValue(previousAppointment.value?.email)
);
const lockTelephoneNumber = computed(
  () =>
    Boolean(props.isRebooking) &&
    isFilledContactValue(previousAppointment.value?.telephone)
);
const lockCustomTextfield = computed(
  () =>
    Boolean(props.isRebooking) &&
    isFilledContactValue(previousAppointment.value?.customTextfield)
);
const lockCustomTextfield2 = computed(
  () =>
    Boolean(props.isRebooking) &&
    isFilledContactValue(previousAppointment.value?.customTextfield2)
);

const contactForm = ref<HTMLFormElement | null>(null);

const LOCKED_CONTROL_BG = "var(--color-neutral-100, #f2f2f2)";
const LOCKED_CONTROL_FG = "var(--color-neutral-600, #6d6d6d)";

function applyNativeDisabledOnFieldset(fieldset: HTMLFieldSetElement): void {
  const locked = fieldset.disabled;
  const control = fieldset.querySelector<
    HTMLInputElement | HTMLTextAreaElement
  >(
    "input:not([type=hidden]):not([type=checkbox]):not([type=radio]), textarea"
  );
  if (!control) {
    return;
  }
  control.disabled = locked;
  if (locked) {
    control.setAttribute("disabled", "");
    control.style.setProperty(
      "background-color",
      LOCKED_CONTROL_BG,
      "important"
    );
    control.style.setProperty("color", LOCKED_CONTROL_FG, "important");
    control.style.setProperty("cursor", "not-allowed", "important");
  } else {
    control.removeAttribute("disabled");
    control.style.removeProperty("background-color");
    control.style.removeProperty("color");
    control.style.removeProperty("cursor");
  }
}

const applyContactLocksToNativeControls = async () => {
  await nextTick();
  const form = contactForm.value;
  if (!form) {
    return;
  }
  form
    .querySelectorAll<HTMLFieldSetElement>("fieldset[data-contact-lock]")
    .forEach(applyNativeDisabledOnFieldset);
};

watch(
  [
    lockFirstName,
    lockLastName,
    lockMailAddress,
    lockTelephoneNumber,
    lockCustomTextfield,
    lockCustomTextfield2,
  ],
  () => {
    void applyContactLocksToNativeControls();
  },
  { immediate: true, flush: "post" }
);

onUpdated(() => {
  void applyContactLocksToNativeControls();
});

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

const { isExpired } = useReservationTimer();

const showLoginErrorBanner = computed(
  () => Boolean(props.loginFailed) && !isExpired.value
);

const showErrorMessage = ref<boolean>(false);

const emailPattern =
  /^(?!.*\.\.)(?!\.)(?!.*\.$)[^\s@+]+(?<!\.)@(?!\.)[^\s@+]+\.[^\s@]{2,}$/;
const telephonPattern = /^\+?[0-9]\d{6,14}$/;

const errorMessageFirstName = computed(() => {
  if (!showErrorMessage.value) return undefined;

  return customerData.value.firstName?.trim()
    ? undefined
    : props.t("errorMessageFirstName");
});

const maxLengthMessageFirstName = computed(() =>
  (customerData.value.firstName ?? "").length >= 50
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
  (customerData.value.lastName ?? "").length >= 50
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
  (customerData.value.mailAddress ?? "").length >= 50
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
    providerScope.value?.telephoneRequired
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
  (customerData.value.telephoneNumber ?? "").length >= 50
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
    providerScope.value?.customTextfieldRequired &&
    !normalizePlainText(customerData.value.customTextfield).trim()
  ) {
    return props.t("errorMessageCustomTextfield");
  }
  return undefined;
});

const maxLengthMessageCustomTextfield = computed(() =>
  plainTextCharCount(customerData.value.customTextfield) > MAX_CUSTOM_TEXT_CHARS
    ? props.t("errorMessageMaxLength", { max: MAX_CUSTOM_TEXT_CHARS })
    : undefined
);

const errorDisplayCustomTextfield = computed(
  () =>
    errorMessageCustomTextfield.value ?? maxLengthMessageCustomTextfield.value
);

const errorMessageCustomTextfield2 = computed(() => {
  if (!showErrorMessage.value) return undefined;

  if (
    providerScope.value?.customTextfield2Required &&
    !normalizePlainText(customerData.value.customTextfield2).trim()
  ) {
    return props.t("errorMessageCustomTextfield2");
  }
  return undefined;
});

const maxLengthMessageCustomTextfield2 = computed(() =>
  plainTextCharCount(customerData.value.customTextfield2) >
  MAX_CUSTOM_TEXT_CHARS
    ? props.t("errorMessageMaxLength", { max: MAX_CUSTOM_TEXT_CHARS })
    : undefined
);

const errorDisplayCustomTextfield2 = computed(
  () =>
    errorMessageCustomTextfield2.value ?? maxLengthMessageCustomTextfield2.value
);

onMounted(() => {
  inputLines1.value = countLines(customerData.value.customTextfield ?? "");
  inputLines2.value = countLines(customerData.value.customTextfield2 ?? "");
});

const validForm = computed(
  () =>
    !errorMessageFirstName.value &&
    !errorMessageLastName.value &&
    !errorMessageMailAddress.value &&
    !errorMessageTelephoneNumber.value &&
    !errorMessageCustomTextfield.value &&
    !errorMessageCustomTextfield2.value &&
    !maxLengthMessageCustomTextfield.value &&
    !maxLengthMessageCustomTextfield2.value
);

const login = () => {
  emit("login");
};

const nextStep = () => {
  showErrorMessage.value = true;
  if (validForm.value) {
    emit("next");
  }
};
const previousStep = () => emit("back");
</script>

<style lang="scss" scoped>
@use "@/styles/breakpoints.scss" as *;

.m-button-group {
  margin-top: 48px;
}

.contact-lock {
  border: 0;
  margin: 0;
  min-inline-size: 0;
  padding: 0;
}

.contact-lock:disabled {
  pointer-events: none;

  :deep(.m-input),
  :deep(.m-textarea),
  :deep(input),
  :deep(textarea) {
    background-color: var(--color-neutral-100, #f2f2f2) !important;
    color: var(--color-neutral-600, #6d6d6d) !important;
    cursor: not-allowed;
    opacity: 1;
  }
}

.login-option-block--with-error {
  display: flex;
  flex-direction: column;
  gap: 24px;
  margin-bottom: 24px;

  > .m-callout,
  :deep(> .m-callout) {
    margin-bottom: 0;
  }

  .login-failed-banner,
  :deep(.login-failed-banner) {
    margin: 0;
  }
}

.m-component-form__title--after-login-error {
  margin-top: 0;
}

@include sm-up {
  :deep(.m-character-count) {
    font-size: 0.875rem;
  }
}
</style>
