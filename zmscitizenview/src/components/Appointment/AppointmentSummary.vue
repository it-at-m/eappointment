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
            <p
              v-if="selectedService"
              tabindex="0"
            >
              {{ selectedService.count }}x
              <a
                :href="getServiceBaseURL() + selectedService.id"
                target="_blank"
                class="m-link"
                tabindex="0"
                >{{ selectedService.name }}</a
              >
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
                  {{ subService.count }}x
                  <a
                    :href="getServiceBaseURL() + subService.id"
                    target="_blank"
                    class="m-link"
                    tabindex="0"
                    >{{ subService.name }}</a
                  >
                  <br />
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
            <p tabindex="0">{{ selectedProvider.name }}<br /></p>
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
              {{ formatTime(appointment.timestamp) }}
              {{ t("timeStampSuffix") }} <br />
              {{ t("estimatedDuration") }} {{ estimatedDuration() }}
              {{ t("minutes") }}<br />
            </p>
          </div>
          <div
            v-if="
              selectedProvider &&
              selectedProvider.scope &&
              selectedProvider.scope.displayInfo
            "
          >
            <div class="m-content">
              <h3 tabindex="0">{{ t("hint") }}</h3>
            </div>
            <div class="m-content border-bottom">
              <div
                tabindex="0"
                v-html="selectedProvider.scope.displayInfo"
              ></div>
            </div>
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
              v-if="
                appointment &&
                selectedProvider &&
                selectedProvider.scope &&
                appointment.customTextfield
              "
              tabindex="0"
            >
              <strong>{{ selectedProvider.scope.customTextfieldLabel }}</strong
              ><br />
              <p>{{ appointment.customTextfield }}</p>
              <br />
            </div>
            <div
              v-if="
                appointment &&
                selectedProvider &&
                selectedProvider.scope &&
                appointment.customTextfield2
              "
              tabindex="0"
            >
              <strong>{{ selectedProvider.scope.customTextfield2Label }}</strong
              ><br />
              <p>{{ appointment.customTextfield2 }}</p>
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
      :disabled="loadingStates.isCancelingAppointment.value"
      :icon="'close'"
      variant="secondary"
      @click="cancelAppointment"
    >
      <template #default>
        <span>{{ t("cancelAppointment") }}</span>
      </template>
    </muc-button>
  </div>
  <div
    v-if="isRebooking"
    class="m-button-group"
  >
    <muc-button
      :disabled="!validForm || loadingStates.isBookingAppointment.value"
      :icon="'check'"
      @click="bookAppointment"
    >
      <template #default>
        <span>{{ t("rescheduleAppointment") }}</span>
      </template>
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
      :disabled="!validForm || loadingStates.isBookingAppointment.value"
      :icon="'check'"
      @click="bookAppointment"
    >
      <template #default>
        <span>{{ t("bookAppointment") }}</span>
      </template>
    </muc-button>
  </div>
</template>

<script setup lang="ts">
import type { Ref } from "vue";

import { MucButton } from "@muenchen/muc-patternlab-vue";
import { computed, inject, ref } from "vue";

import { OfficeImpl } from "@/types/OfficeImpl";
import {
  SelectedAppointmentProvider,
  SelectedServiceProvider,
  SelectedTimeslotProvider,
} from "@/types/ProvideInjectTypes";
import { SubService } from "@/types/SubService";
import { calculateEstimatedDuration } from "@/utils/calculateEstimatedDuration";
import { getServiceBaseURL } from "@/utils/Constants";

defineProps<{
  isRebooking: boolean;
  rebookOrCancelDialog: boolean;
  t: (key: string) => string;
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

/**
 * This function determines the expected duration of the appointment.
 * The provider is queried for the service and each subservice because the slots for the respective service are stored in this provider.
 */
const estimatedDuration = () => {
  return calculateEstimatedDuration(
    selectedService.value,
    selectedProvider.value
  );
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
