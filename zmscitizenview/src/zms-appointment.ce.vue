<template>
  <link
    href="https://assets.muenchen.de/mde/1.1.15/css/style.css"
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
import { ref } from "vue";
import { useI18n } from "vue-i18n";

import AppointmentView from "@/components/Appointment/AppointmentView.vue";
import { sanitizeHtml } from "@/utils/sanitizeHtml";
import { useGlobalState } from "./utils/useGlobalState";

// Props
const props = withDefaults(
  defineProps<{
    baseUrl?: string;
    appointmentDetailUrl?: string;
    showLoginOption?: string;
  }>(),
  {
    baseUrl: undefined,
    appointmentDetailUrl: "appointment-detail.html",
    showLoginOption: "false",
  }
);

// START Routing
const rawHash = window.location.hash.startsWith("#")
  ? window.location.hash.substring(1)
  : window.location.hash;

let decodedHash: string;
try {
  decodedHash = decodeURIComponent(rawHash);
} catch {
  decodedHash = rawHash;
}
const normalized = decodedHash.startsWith("/")
  ? decodedHash
  : `/${decodedHash}`;
const urlElements = normalized.split("/");
const url = new URL(window.location.href);
const params = new URLSearchParams(url.search);

const serviceId = ref<string | undefined>(undefined);
if (urlElements.length >= 3 && urlElements[1] === "services") {
  serviceId.value = urlElements[2];
}

const locationId = ref<string | undefined>(undefined);
if (urlElements.length >= 5 && urlElements[3] === "locations") {
  locationId.value = urlElements[4];
}

const confirmAppointmentHash = ref<string | undefined>(undefined);
if (
  urlElements.length === 4 &&
  urlElements[1] === "appointment" &&
  urlElements[2] === "confirm"
) {
  confirmAppointmentHash.value = urlElements[3];
}

const appointmentHash = ref<string | undefined>(undefined);
if (urlElements.length === 3 && urlElements[1] === "appointment") {
  appointmentHash.value = urlElements[2];
}

const exclusiveLocation = ref<string | undefined>(undefined);
if (params.get("exclusiveLocation")) {
  exclusiveLocation.value = "1";
}
// END Routing

// i18n & Global State
const { t } = useI18n();
const globalState = useGlobalState(props);
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
