<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from "vue";

import {
  getAPIBaseURL,
  VUE_APP_ZMS_API_CAPTCHA_DETAILS_ENDPOINT,
} from "@/utils/Constants";

import "altcha";

const altchaWidget = ref<HTMLElement | null>(null);
const captchaChallengeUrl = ref<string | null>(null);
const captchaVerifyUrl = ref<string | null>(null);
const captchaEnabled = ref<boolean>(true);

const props = defineProps({
  payload: {
    type: String,
    required: false,
  },
});
const emit = defineEmits<{
  (e: "update:payload", value: string): void;
  (e: "validationResult", value: boolean): void;
}>();

const internalValue = ref(props.payload);

watch(internalValue, (v) => {
  emit("update:payload", v || "");
});

const fetchCaptchaDetails = async () => {
  try {
    const response = await fetch(
      `${getAPIBaseURL(import.meta.env.VITE_VUE_APP_API_URL)}${VUE_APP_ZMS_API_CAPTCHA_DETAILS_ENDPOINT}` // corresponds to https://zms.ddev.site/terminvereinbarung/api/citizen/captcha-details/
    );
    if (!response.ok) throw new Error("Fehler beim Laden der Captcha-Daten");

    const data = await response.json();
    console.log("DATA:", data);
    captchaChallengeUrl.value = data.captchaChallenge;
    captchaVerifyUrl.value = data.captchaVerify;
    captchaEnabled.value = data.captchaEnabled;
  } catch (error) {
    console.error("Fehler beim Abrufen der Captcha-Details:", error);
  }
};

const verifyCaptcha = async (payload: string) => {
  console.log("VERIFIYING");
  if (!captchaVerifyUrl.value) return;
  console.log("VERIFY");
  try {
    const response = await fetch(captchaVerifyUrl.value, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        siteKey: "zms-dev",
        payload: payload,
      }),
    });

    const data = await response.json();
    if (data.success) {
      emit("validationResult", true);
    } else {
      emit("validationResult", false);
      internalValue.value = "";
    }
  } catch (error) {
    console.error("Fehler bei der Captcha-Validierung:", error);
    emit("validationResult", false);
  }
};

const onStateChange = (ev: CustomEvent | Event) => {
  if ("detail" in ev) {
    const { payload, state } = ev.detail;
    console.log("STATE:", state);
    if (state === "verified" && payload) {
      internalValue.value = payload;
      verifyCaptcha(payload);
      console.log("FINISHED");
    } else {
      internalValue.value = "";
      emit("validationResult", false);
    }
  }
};

onMounted(async () => {
  await fetchCaptchaDetails();

  if (altchaWidget.value) {
    altchaWidget.value.addEventListener("statechange", onStateChange);
  }
});

onUnmounted(() => {
  if (altchaWidget.value) {
    altchaWidget.value.removeEventListener("statechange", onStateChange);
  }
});
</script>

<template>
  <altcha-widget
    v-if="captchaEnabled && captchaChallengeUrl"
    :challengeurl="captchaChallengeUrl"
    ref="altchaWidget"
    debug
  ></altcha-widget>
</template>
