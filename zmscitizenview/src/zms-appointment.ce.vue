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
import { onMounted, ref } from "vue";
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
// Helper function to parse the URL hash with Safari iOS encoding support
const parseUrlHash = () => {
  const rawHash = window.location.hash.startsWith("#")
    ? window.location.hash.substring(1)
    : window.location.hash;

  // Decode repeatedly to handle double/triple URL encoding (Safari iOS issue)
  // Some browsers/email clients may encode the hash multiple times
  let decodedHash: string = rawHash;
  let prevHash: string;
  let iterations = 0;
  const maxIterations = 5; // Safety limit to prevent infinite loops

  do {
    prevHash = decodedHash;
    try {
      decodedHash = decodeURIComponent(decodedHash);
    } catch {
      // Stop if decoding fails (e.g., malformed URI)
      break;
    }
    iterations++;
  } while (decodedHash !== prevHash && iterations < maxIterations);

  // Remove trailing = (URL artifact) and normalize
  const cleanedHash = decodedHash.replace(/=+$/, "");
  const normalized = cleanedHash.startsWith("/")
    ? cleanedHash
    : `/${cleanedHash}`;

  return normalized.split("/");
};

const url = new URL(window.location.href);
const params = new URLSearchParams(url.search);

// Initialize refs
const serviceId = ref<string | undefined>(undefined);
const locationId = ref<string | undefined>(undefined);
const confirmAppointmentHash = ref<string | undefined>(undefined);
const appointmentHash = ref<string | undefined>(undefined);
const exclusiveLocation = ref<string | undefined>(undefined);

// Function to extract route parameters from URL elements
const extractRouteParams = (urlElements: string[]) => {
  if (urlElements.length >= 3 && urlElements[1] === "services") {
    serviceId.value = urlElements[2];
  }
  if (urlElements.length >= 5 && urlElements[3] === "locations") {
    locationId.value = urlElements[4];
  }
  if (
    urlElements.length === 4 &&
    urlElements[1] === "appointment" &&
    urlElements[2] === "confirm"
  ) {
    confirmAppointmentHash.value = urlElements[3];
  }
  if (urlElements.length === 3 && urlElements[1] === "appointment") {
    appointmentHash.value = urlElements[2];
  }
  if (params.get("exclusiveLocation")) {
    exclusiveLocation.value = "1";
  }
};

// Parse hash immediately (works most of the time)
const initialUrlElements = parseUrlHash();
console.log("[ZMS] Initial parse:", window.location.hash, initialUrlElements);
extractRouteParams(initialUrlElements);
console.log("[ZMS] After initial:", appointmentHash.value, confirmAppointmentHash.value);

// Re-parse in onMounted to handle Safari timing issues
// This ensures the hash is read after the component is fully connected to the DOM
onMounted(() => {
  // Only re-parse if we didn't get appointment hash but URL contains "appointment"
  const hash = window.location.hash;
  if (
    !appointmentHash.value &&
    !confirmAppointmentHash.value &&
    hash.includes("appointment")
  ) {
    console.log("[ZMS] onMounted re-parse triggered");
    const urlElements = parseUrlHash();
    console.log("[ZMS] onMounted parse:", hash, urlElements);
    extractRouteParams(urlElements);
    console.log("[ZMS] After onMounted:", appointmentHash.value, confirmAppointmentHash.value);
  }
});
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
