<template>
  <div class="m-component">
    <div class="m-contact">
      <div class="m-contact__body">
        <div class="m-contact__section">
          <div class="m-content">
            <h2>{{ t("your") }} {{ t("appointment") }}</h2>
          </div>
          <div class="m-content">
            <h3 tabindex="0">{{ t("service") }}</h3>
          </div>
          <div class="m-content border-bottom">
            <p tabindex="0">
              {{ selectedService.count }}x {{ selectedService.name }}
              <br />
            </p>
            <div v-if="selectedService && selectedService.subServices">
              <div
                v-for="subService in selectedService.subServices"
                :key="subService.id"
              >
                <p
                  v-if="subService.count > 0"
                  tabindex="0"
                >
                  {{ subService.count }}x {{ subService.name }} <br />
                </p>
              </div>
            </div>
          </div>
          <div class="m-content">
            <h3 tabindex="0">{{ t("location") }}</h3>
          </div>
          <div
            v-if="selectedProvider"
            class="m-content border-bottom"
          >
            <p tabindex="0">
              <strong> {{ selectedProvider.organization }}</strong
              ><br />
              {{ selectedProvider.name }}<br />
            </p>
            <p tabindex="0">
              <strong>{{ t("address") }}</strong>
              <br />
            </p>
            <p tabindex="0">
              {{ selectedProvider.address.street }}
              {{ selectedProvider.address.house_number }}<br />
              {{ selectedProvider.address.postal_code }}
              {{ selectedProvider.address.city }}<br />
            </p>
          </div>
          <div class="m-content">
            <h3 tabindex="0">{{ t("time") }}</h3>
          </div>
          <div
            v-if="appointment"
            class="m-content border-bottom"
          >
            <p tabindex="0">
              {{ formatTime(appointment.timestamp) }} <br />
              {{ t("estimatedDuration") }} {{ estimatedDuration() }} {{ t("minutes") }}<br />
            </p>
          </div>
          <div class="m-content">
            <h3 tabindex="0">{{ t("contact") }}</h3>
          </div>
          <div
            v-if="appointment"
            class="m-content border-bottom"
          >
            <p tabindex="0">
              {{ appointment.familyName }}
              <br />
              {{ appointment.email }}<br />
              {{ appointment.telephone }}<br />
            </p>
            <div
              v-if="appointment.customTextfield"
              tabindex="0"
            >
              <strong>{{ t("remarks") }}</strong
              ><br />
              <p>{{ appointment.customTextfield }}</p>
              <br />
            </div>
          </div>
          <div v-if="!rebookOrCancelDialog">
            <div class="m-content">
              <h3 tabindex="0">{{ t("termsOfUse") }}</h3>
            </div>
            <div class="m-content">
              <p tabindex="0">
                <strong>{{ t("privacyCheckboxLabel") }}</strong
                ><br />
              </p>
            </div>
            <div class="m-content">
              <div class="m-checkboxes">
                <div class="m-checkboxes__item">
                  <input
                    id="checkbox-privacy-policy"
                    class="m-checkboxes__input"
                    name="checkbox-privacy-policy"
                    type="checkbox"
                    @click="clickPrivacyPolicy"
                  />
                  <label
                    class="m-label m-checkboxes__label"
                    for="checkbox-privacy-policy"
                    v-html="t('privacyCheckboxText')"
                  />
                </div>
              </div>
            </div>
            <div class="m-content">
              <p tabindex="0">
                <strong>{{ t("communicationCheckboxLabel") }}</strong
                ><br />
              </p>
            </div>
            <div class="m-content">
              <div class="m-checkboxes">
                <div class="m-checkboxes__item">
                  <input
                    id="checkbox-electronic-communication"
                    class="m-checkboxes__input"
                    name="checkbox-electronic-communication"
                    type="checkbox"
                    @click="clickElectronicCommunication"
                  />
                  <label
                    class="m-label m-checkboxes__label"
                    for="checkbox-electronic-communication"
                    v-html="t('communicationCheckboxText')"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div
    v-if="rebookOrCancelDialog"
    class="m-button-group"
  >
    <muc-button
      icon="arrow-right"
      @click="rescheduleAppointment"
    >
      <template #default>{{ t("rescheduleAppointment") }}</template>
    </muc-button>
    <muc-button
      icon="close"
      variant="secondary"
      @click="cancelAppointment"
    >
      <template #default>{{ t("cancelAppointment") }}</template>
    </muc-button>
  </div>
  <div
    v-if="isRebooking"
    class="m-button-group"
  >
    <muc-button
      :disabled="!validForm"
      icon="check"
      @click="bookAppointment"
    >
      <template #default>{{ t("rescheduleAppointment") }}</template>
    </muc-button>
    <muc-button
      icon="close"
      variant="secondary"
      @click="cancelReschedule"
    >
      <template #default>{{ t("cancelReschedule") }}</template>
    </muc-button>
  </div>
  <div
    v-if="!rebookOrCancelDialog && !isRebooking"
    class="m-button-group"
  >
    <muc-button
      icon="arrow-left"
      icon-shown-left
      variant="secondary"
      @click="previousStep"
    >
      <template #default>{{ t("back") }}</template>
    </muc-button>
    <muc-button
      :disabled="!validForm"
      icon="check"
      @click="bookAppointment"
    >
      <template #default>{{ t("bookAppointment") }}</template>
    </muc-button>
  </div>
</template>

<script setup lang="ts">
import { MucButton } from "@muenchen/muc-patternlab-vue";
import { computed, inject, ref } from "vue";

import {
  SelectedAppointmentProvider,
  SelectedServiceProvider,
  SelectedTimeslotProvider,
} from "@/types/ProvideInjectTypes";

defineProps<{
  isRebooking: boolean;
  rebookOrCancelDialog: boolean;
  t: any;
}>();

const emit =
  defineEmits<
    (
      e:
        | "bookAppointment"
        | "back"
        | "cancelAppointment"
        | "cancelReschedule"
        | "rescheduleAppointment"
    ) => void
  >();

const { selectedService } = inject<SelectedServiceProvider>(
  "selectedServiceProvider"
) as SelectedServiceProvider;

const { selectedProvider } = inject<SelectedTimeslotProvider>(
  "selectedTimeslot"
) as SelectedTimeslotProvider;

const { appointment } = inject<SelectedAppointmentProvider>(
  "appointment"
) as SelectedAppointmentProvider;

const privacyPolicy = ref<boolean>(false);
const electronicCommunication = ref<boolean>(false);

const formatterDate = new Intl.DateTimeFormat("de-DE", {
  weekday: "long",
  year: "numeric",
  month: "numeric",
  day: "numeric",
});

const formatterTime = new Intl.DateTimeFormat("de-DE", {
  timeZone: "Europe/Berlin",
  hour: "numeric",
  minute: "numeric",
  hour12: false,
});

const formatTime = (time: any) => {
  const date = new Date(time * 1000);
  return formatterDate.format(date) + ", " + formatterTime.format(date);
};

const clickPrivacyPolicy = () => (privacyPolicy.value = !privacyPolicy.value);
const clickElectronicCommunication = () =>
  (electronicCommunication.value = !electronicCommunication.value);

const validForm = computed(
  () => privacyPolicy.value && electronicCommunication.value
);

const bookAppointment = () => emit("bookAppointment");
const previousStep = () => emit("back");
const cancelAppointment = () => emit("cancelAppointment");
const cancelReschedule = () => emit("cancelReschedule");
const rescheduleAppointment = () => emit("rescheduleAppointment");

const estimatedDuration = () => {
  let time = 0;
  const serviceProvider = selectedService.value?.providers?.find(
    (provider) => provider.id === selectedProvider.value?.id
  );
  if (
    serviceProvider &&
    selectedService.value?.count &&
    serviceProvider?.slots
  ) {
    time =
      selectedService.value.count *
      serviceProvider.slots *
      serviceProvider.slotTimeInMinutes;
  }

  if (selectedService.value?.subServices) {
    selectedService.value?.subServices?.forEach((subservice) => {
      const subserviceProvider = subservice.providers?.find(
        (provider) => provider.id === selectedProvider.value?.id
      );
      if (subserviceProvider && subservice.count && subserviceProvider.slots) {
        time =
          subservice.count *
          subserviceProvider.slots *
          subserviceProvider.slotTimeInMinutes;
      }
    });
  }
  return time;
};
</script>

<style scoped>
.border-bottom {
  border-bottom: 1px solid var(--color-neutrals-blue);
}

.m-component {
  padding-top: 0;
}
</style>
