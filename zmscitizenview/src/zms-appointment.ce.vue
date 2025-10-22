<template>
  <link
    href="https://assets.muenchen.de/mde/1.0.10/css/style.css"
    rel="stylesheet"
  />
  <main>
    <div>
      <div v-html="sanitizeHtml(mucIconsSprite)"></div>
      <div v-html="sanitizeHtml(customIconsSprit)"></div>
      <appointment-view
        :global-state="globalState"
        :service-id="serviceId"
        :location-id="locationId"
        :exclusive-location="exclusiveLocation"
        :appointment-hash="appointmentHash"
        :confirm-appointment-hash="confirmAppointmentHash"
        :appointment-detail-url="appointmentDetailUrl"
        :show-login-option="showLoginOption.toLowerCase() === 'true'"
        :t="t"
      />
    </div>
  </main>
</template>

<script lang="ts" setup>
import customIconsSprit from "@muenchen/muc-patternlab-vue/assets/icons/custom-icons.svg?raw";
import mucIconsSprite from "@muenchen/muc-patternlab-vue/assets/icons/muc-icons.svg?raw";
import { computed, onMounted } from "vue";
import { useI18n } from "vue-i18n";

import AppointmentView from "@/components/Appointment/AppointmentView.vue";
import { sanitizeHtml } from "@/utils/sanitizeHtml";
import { useGlobalState } from "./utils/useGlobalState";

// URL Parsing
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

const fallbackConfirmAppointmentHash = confirmHashMatch?.[1];
const fallbackAppointmentHash = appointmentHashMatch?.[1];
const fallbackServiceId = hashMatch?.[1] || pathMatch?.[1];
const fallbackLocationId = hashMatch?.[2] || pathMatch?.[2];

// Props
const props = withDefaults(
  defineProps<{
    baseUrl?: string;
    serviceId?: string;
    locationId?: string;
    exclusiveLocation?: string;
    appointmentHash?: string;
    confirmAppointmentHash?: string;
    appointmentDetailUrl?: string;
    showLoginOption?: string;
  }>(),
  {
    baseUrl: undefined,
    serviceId: undefined,
    locationId: undefined,
    exclusiveLocation: undefined,
    appointmentHash: undefined,
    confirmAppointmentHash: undefined,
    appointmentDetailUrl: "appointment-detail.html",
    showLoginOption: "false",
  }
);

const serviceId = computed(() => props.serviceId ?? fallbackServiceId);
const locationId = computed(() => props.locationId ?? fallbackLocationId);
const appointmentHash = computed(
  () => props.appointmentHash ?? fallbackAppointmentHash
);
const confirmAppointmentHash = computed(
  () => props.confirmAppointmentHash ?? fallbackConfirmAppointmentHash
);

// i18n & Global State
const { t } = useI18n();
const globalState = useGlobalState(props);

// Web Component Attributes
onMounted(() => {
  const element = document.querySelector("zms-appointment-wrapped");
  if (!element) return;

  const urlElements = window.location.hash.split("/");
  const params = new URLSearchParams(window.location.search);

  if (urlElements[1] === "services") {
    element.setAttribute("service-id", urlElements[2] || "");
    if (urlElements[3] === "locations") {
      element.setAttribute("location-id", urlElements[4] || "");
    }
  }

  if (urlElements[1] === "appointment") {
    if (urlElements[2] === "confirm") {
      element.setAttribute("confirm-appointment-hash", urlElements[3] || "");
    } else {
      element.setAttribute("appointment-hash", urlElements[2] || "");
    }
  }

  if (params.get("exclusiveLocation")) {
    element.setAttribute("exclusive-location", "1");
  }
});
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
