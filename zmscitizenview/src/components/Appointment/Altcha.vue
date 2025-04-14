<template>
  <div v-if="captchaEnabled && captchaChallengeUrl && captchaVerifyUrl">
    <altcha-widget
      :challengeurl="captchaChallengeUrl"
      :verifyurl="captchaVerifyUrl"
      ref="altchaWidget"
    />
  </div>
  <div v-else>
    <p>Das Captcha konnte nicht geladen werden.</p>
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

const altchaWidget = ref<Partial<AltchaWidget> | null>(null);
const getWidget = () => altchaWidget.value as AltchaWidget;

const captchaChallengeUrl = ref<string | null>(null);
const captchaVerifyUrl = ref<string | null>(null);
const captchaEnabled = ref<boolean>(true);

const emit = defineEmits<{
  (e: "validationResult", value: boolean): void;
}>();

const fetchCaptchaDetails = async () => {
  try {
    const response = await fetch(
      `${getAPIBaseURL(import.meta.env.VITE_VUE_APP_API_URL)}${VUE_APP_ZMS_API_CAPTCHA_DETAILS_ENDPOINT}`
    );
    if (!response.ok) throw new Error("Fehler beim Laden der Captcha-Daten");
    const data = await response.json();
    console.log("DATA:", data);
    captchaChallengeUrl.value = `${getAPIBaseURL(import.meta.env.VITE_VUE_APP_API_URL)}${VUE_APP_ZMS_API_CAPTCHA_CHALLENGE_ENDPOINT}`;
    captchaVerifyUrl.value = `${getAPIBaseURL(import.meta.env.VITE_VUE_APP_API_URL)}${VUE_APP_ZMS_API_CAPTCHA_VERIFY_ENDPOINT}`;
    console.log("CHALLENGE-URL:", captchaChallengeUrl.value);
    console.log("VERIFY-URL:", captchaVerifyUrl.value);
    captchaEnabled.value = data.captchaEnabled;
  } catch (error) {
    console.error("Fehler beim Abrufen der Captcha-Details:", error);
    captchaEnabled.value = false;
  }
};

const handleStateChange = (ev: CustomEvent | Event) => {
  if ("detail" in ev) {
    const state = ev.detail.state;
    console.log("EV:", ev);
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
    } catch (error) {
      console.error("Error configuring widget:", error);
    }
  } else {
    console.warn("Widget not available for configuration");
  }
};

onMounted(async () => {
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
