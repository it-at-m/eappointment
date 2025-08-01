<template>
  <link
    href="https://assets.muenchen.de/mde/1.0.10/css/style.css"
    rel="stylesheet"
  />
  <main>
    <div>
      <div v-html="mucIconsSprite"></div>
      <div v-html="customIconsSprit"></div>
      <appointment-view
        :base-url="baseUrl"
        :service-id="serviceId"
        :location-id="locationId"
        :exclusive-location="exclusiveLocation"
        :appointment-hash="appointmentHash"
        :confirm-appointment-hash="confirmAppointmentHash"
        :t="t"
      />
    </div>
  </main>
</template>

<script lang="ts">
const hash = window.location.hash || "";
const path = window.location.pathname || "";

const confirmHashMatch =
  hash.match(/#\/appointment\/confirm\/(.+)/) ||
  path.match(/\/appointment\/confirm\/(.+)/);
const appointmentHashMatch =
  hash.match(/#\/appointment\/([^/]+)$/) ||
  path.match(/\/appointment\/([^/]+)$/);

const hashMatch = hash.match(/services\/([^/]+)(?:\/locations\/([^/]+))?/);
const pathMatch = path.match(/services\/([^/]+)(?:\/locations\/([^/]+))?/);

export const fallbackConfirmAppointmentHash = confirmHashMatch?.[1];
export const fallbackAppointmentHash = appointmentHashMatch?.[1];

export const fallbackServiceId = hashMatch?.[1] || pathMatch?.[1];
export const fallbackLocationId = hashMatch?.[2] || pathMatch?.[2];
</script>

<script lang="ts" setup>
import customIconsSprit from "@muenchen/muc-patternlab-vue/assets/icons/custom-icons.svg?raw";
import mucIconsSprite from "@muenchen/muc-patternlab-vue/assets/icons/muc-icons.svg?raw";
import { useI18n } from "vue-i18n";

import AppointmentView from "@/components/Appointment/AppointmentView.vue";

defineProps({
  baseUrl: {
    type: String,
    required: false,
    default: undefined,
  },
  serviceId: {
    type: String,
    required: false,
    default: fallbackServiceId,
  },
  locationId: {
    type: String,
    required: false,
    default: fallbackLocationId,
  },
  exclusiveLocation: {
    type: String,
    required: false,
    default: undefined,
  },
  appointmentHash: {
    type: String,
    required: false,
    default: fallbackAppointmentHash,
  },
  confirmAppointmentHash: {
    type: String,
    required: false,
    default: fallbackConfirmAppointmentHash,
  },
});

const { t } = useI18n();
</script>

<style>
@import "@muenchen/muc-patternlab-vue/assets/css/custom-style.css";
@import "@muenchen/muc-patternlab-vue/style.css";

:host {
  font-family:
    Open Sans,
    sans-serif;
}

main {
  padding-bottom: 32px;
}
</style>
