<template>
  <link
    href="https://assets.muenchen.de/mde/1.1.15/css/style.css"
    rel="stylesheet"
    @load="onStylesheetLoaded"
    @error="onStylesheetLoaded"
  />
  <main :class="{ 'styles-loading': !stylesLoaded }">
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
import { onMounted, onUnmounted, ref } from "vue";
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

// Initialize refs
const serviceId = ref<string | undefined>(undefined);
const locationId = ref<string | undefined>(undefined);
const confirmAppointmentHash = ref<string | undefined>(undefined);
const appointmentHash = ref<string | undefined>(undefined);
const exclusiveLocation = ref<string | undefined>(undefined);

// Function to extract route parameters from URL elements
const extractRouteParams = (urlElements: string[]) => {
  const searchParams = new URLSearchParams(
    new URL(window.location.href).search
  );
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
  if (searchParams.get("exclusiveLocation")) {
    exclusiveLocation.value = "1";
  }
};

/** Apply hash (and query) routing; clears stale refs so same-tab hash changes update the UI. */
const syncRouteFromLocation = () => {
  serviceId.value = undefined;
  locationId.value = undefined;
  confirmAppointmentHash.value = undefined;
  appointmentHash.value = undefined;
  exclusiveLocation.value = undefined;
  const urlElements = parseUrlHash();
  console.debug(
    "[ZMS] syncRouteFromLocation:",
    window.location.hash,
    urlElements
  );
  extractRouteParams(urlElements);
  console.debug(
    "[ZMS] After sync:",
    appointmentHash.value,
    confirmAppointmentHash.value
  );
};

// Parse hash immediately (works most of the time)
syncRouteFromLocation();

// Same-tab hash navigations (e.g. Selenium get(), mailto link flow) do not remount the app — listen for hashchange.
// onMounted also re-syncs for Safari timing where the hash is not ready on first script parse.
onMounted(() => {
  syncRouteFromLocation();
  window.addEventListener("hashchange", syncRouteFromLocation);
});

onUnmounted(() => {
  window.removeEventListener("hashchange", syncRouteFromLocation);
});
// END Routing

// i18n & Global State
const { t } = useI18n();
const globalState = useGlobalState(props);

// Prevent flickering: hide content until external stylesheet is loaded
// This prevents icons from appearing large/unstyled before CSS applies
const stylesLoaded = ref(false);
const onStylesheetLoaded = () => {
  stylesLoaded.value = true;
};

// Fallback: reveal content after 3s even if stylesheet fails or never loads
onMounted(() => {
  setTimeout(() => {
    stylesLoaded.value = true;
  }, 3000);
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

/* Hide content until external stylesheet is loaded to prevent icon flickering */
main.styles-loading {
  visibility: hidden;
}
</style>
