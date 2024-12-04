<template>
  <div class="m-component m-component-dataset">
    <div class="container">
      <div class="m-component__grid">
        <div class="m-component__column">
          <div class="m-contact">
            <div class="m-contact__body">
              <div class="m-contact__section">
                <div class="m-content">
                  <h2>{{ t("your") }} {{ t("appointment") }}</h2>
                </div>
                <div class="m-content">
                  <h3>{{ t("service") }}</h3>
                </div>
                <div class="m-content">
                  <p>
                    {{ selectedService.count }}x {{ selectedService.name }}
                    <br />
                  </p>
                  <div v-if="selectedService && selectedService.subServices">
                    <div
                      v-for="subService in selectedService.subServices"
                      :key="subService.id"
                    >
                      <p v-if="subService.count > 0">
                        {{ subService.count }}x {{ subService.name }} <br />
                      </p>
                    </div>
                  </div>
                </div>
                <div class="m-content">
                  <h3>{{ t("location") }}</h3>
                </div>
                <div class="m-content">
                  <p>
                    <strong>Landeshauptstadt München</strong><br />
                    {{ appointment.scope.provider.contact.name }}<br />
                  </p>
                  <p>
                    <strong>{{ t("address") }}</strong>
                    <br />
                  </p>
                  <p>
                    {{ appointment.scope.provider.contact.street }}
                    {{ appointment.scope.provider.contact.streetNumber }}<br />
                    {{ appointment.scope.provider.contact.postalCode }}
                    {{ appointment.scope.provider.contact.region }}<br />
                  </p>
                </div>
                <div class="m-content">
                  <h3>{{ t("time") }}</h3>
                </div>
                <div class="m-content">
                  <p>
                    {{ formatTime(appointment.timestamp) }} <br />
                    {{ t("estimatedDuration") }} {{ t("minutes") }}<br />
                  </p>
                </div>
                <div class="m-content">
                  <h3>{{ t("contact") }}</h3>
                </div>
                <div class="m-content">
                  <p>
                    {{ appointment.familyName }}
                    <br />
                    {{ appointment.email }}<br />
                    {{ appointment.telephone }}<br />
                  </p>
                  <div v-if="appointment.customTextfield">
                    <strong>{{ t("remarks") }}</strong
                    ><br />
                    <p>{{ appointment.customTextfield }}</p>
                    <br />
                  </div>
                </div>
                <div v-if="!rebookOrCancelDialog">
                  <div class="m-content">
                    <h3>{{ t("termsOfUse") }}</h3>
                  </div>
                  <div class="m-content">
                    <p>
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
                    <p>
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
      </div>
    </div>
  </div>
  <div
    v-if="rebookOrCancelDialog"
    class="m-submit-group"
  >
    <muc-button @click="rescheduleAppointment">
      <template #default>{{ t("rescheduleAppointment") }}</template>
    </muc-button>
    <muc-button
      variant="secondary"
      @click="cancelAppointment"
    >
      <template #default>{{ t("cancelAppointment") }}</template>
    </muc-button>
  </div>
  <div
    v-if="isRebooking"
    class="m-submit-group"
  >
    <muc-button
      :disabled="!validForm"
      @click="bookAppointment"
    >
      <template #default>{{ t("rescheduleAppointment") }}</template>
    </muc-button>
    <muc-button
      variant="secondary"
      @click="cancelReschedule"
    >
      <template #default>{{ t("cancelReschedule") }}</template>
    </muc-button>
  </div>
  <div
    v-if="!rebookOrCancelDialog && !isRebooking"
    class="m-submit-group"
  >
    <muc-button
      variant="secondary"
      @click="previousStep"
    >
      <template #default>{{ t("back") }}</template>
    </muc-button>
    <muc-button
      :disabled="!validForm"
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
</script>

<style scoped>
.list-item {
  margin-bottom: 1.75rem;
}

.m-component {
  padding-top: 0;
}
</style>
