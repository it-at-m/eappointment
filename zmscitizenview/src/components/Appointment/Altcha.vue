<template>
  <div v-if="captchaEnabled && captchaChallengeUrl && captchaVerifyUrl">
    <div class="debug-info">
      <p>captchaEnabled: {{ captchaEnabled }}</p>
      <p>captchaChallengeUrl: {{ captchaChallengeUrl }}</p>
      <p>captchaVerifyUrl: {{ captchaVerifyUrl }}</p>
      <p>widgetLoaded: {{ widgetLoaded }}</p>
      <p>widgetConfigured: {{ widgetConfigured }}</p>
    </div>
    <altcha-widget
      :challengeurl="captchaChallengeUrl"
      :verifyurl="captchaVerifyUrl"
      ref="altchaWidget"
    />
  </div>
  <div v-else>
    <p>Das Captcha konnte nicht geladen werden.</p>
    <div class="debug-info">
      <p>captchaEnabled: {{ captchaEnabled }}</p>
      <p>captchaChallengeUrl: {{ captchaChallengeUrl }}</p>
      <p>captchaVerifyUrl: {{ captchaVerifyUrl }}</p>
      <p>widgetLoaded: {{ widgetLoaded }}</p>
      <p>widgetConfigured: {{ widgetConfigured }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { AltchaWidget } from "@/types/AltchaTypes";

import { nextTick, onMounted, onUnmounted, ref } from "vue";

import {
  getAPIBaseURL,
  VUE_APP_ZMS_API_CAPTCHA_CHALLENGE_ENDPOINT,
  VUE_APP_ZMS_API_CAPTCHA_DETAILS_ENDPOINT,
  VUE_APP_ZMS_API_CAPTCHA_VERIFY_ENDPOINT,
} from "@/utils/Constants";

import "altcha";

console.log("Altcha component script executing");

const altchaWidget = ref<Partial<AltchaWidget> | null>(null);
const getWidget = () => altchaWidget.value as AltchaWidget;

const captchaChallengeUrl = ref<string | null>(null);
const captchaVerifyUrl = ref<string | null>(null);
const captchaEnabled = ref<boolean>(true);
const widgetLoaded = ref<boolean>(false);
const widgetConfigured = ref<boolean>(false);

console.log("Altcha component refs initialized");

const emit = defineEmits<{
  (e: "validationResult", value: boolean): void;
}>();

const fetchCaptchaDetails = async () => {
  try {
    console.log("Starting fetchCaptchaDetails...");
    console.log(
      "API Base URL:",
      getAPIBaseURL(import.meta.env.VITE_VUE_APP_API_URL)
    );
    console.log("Environment:", import.meta.env);

    const response = await fetch(
      `${getAPIBaseURL(import.meta.env.VITE_VUE_APP_API_URL)}${VUE_APP_ZMS_API_CAPTCHA_DETAILS_ENDPOINT}`
    );
    if (!response.ok) throw new Error("Fehler beim Laden der Captcha-Daten");
    const data = await response.json();
    console.log("Captcha Details Response:", data);

    captchaChallengeUrl.value = `${getAPIBaseURL(import.meta.env.VITE_VUE_APP_API_URL)}${VUE_APP_ZMS_API_CAPTCHA_CHALLENGE_ENDPOINT}`;
    captchaVerifyUrl.value = `${getAPIBaseURL(import.meta.env.VITE_VUE_APP_API_URL)}${VUE_APP_ZMS_API_CAPTCHA_VERIFY_ENDPOINT}`;
    captchaEnabled.value = data.captchaEnabled;

    console.log("Updated values:", {
      captchaEnabled: captchaEnabled.value,
      captchaChallengeUrl: captchaChallengeUrl.value,
      captchaVerifyUrl: captchaVerifyUrl.value,
    });
  } catch (error) {
    console.error("Fehler beim Abrufen der Captcha-Details:", error);
    captchaEnabled.value = false;
  }
};

const handleStateChange = (ev: CustomEvent | Event) => {
  if ("detail" in ev) {
    console.log("EVENT:", ev);
    const state = ev.detail.state;
    console.log("STATE:", state);
    if (state === "verified") {
      emit("validationResult", true);
    } else {
      emit("validationResult", false);
    }
  }
};

const configureWidget = () => {
  const widget = getWidget();
  console.log("Configuring widget:", widget);
  if (widget) {
    try {
      widget.configure({
        strings: {
          error:
            "Verifizierung fehlgeschlagen. Versuche es später noch einmal.",
          expired: "Verifizierung abgelaufen. Versuche es erneut.",
          footer:
            'Geschützt durch <a href=\"https://altcha.org/\" target=\"_blank\" aria-label=\"Besuche Altcha.org\">ALTCHA</a>',
          label: "Ich bin kein Bot.",
          verified: "Erfolgreich verifiziert!",
          verifying: "Überprüfe...",
          waitAlert: "Überprüfung läuft... bitte warten.",
        },
      });
      widgetConfigured.value = true;
      console.log("Widget configured successfully");
    } catch (error) {
      console.error("Error configuring widget:", error);
    }
  } else {
    console.warn("Widget not available for configuration");
  }
};

onMounted(async () => {
  console.log("Component mounted");
  console.log(
    "Checking if altcha is available:",
    typeof window.altcha !== "undefined"
  );
  widgetLoaded.value = typeof window.altcha !== "undefined";

  await fetchCaptchaDetails();
  getWidget()?.addEventListener("statechange", handleStateChange);
  nextTick(() => {
    configureWidget();
  });
});

onUnmounted(() => {
  getWidget()?.removeEventListener("statechange", handleStateChange);
});
</script>
