<template>
  <div v-if="captchaEnabled && captchaChallengeUrl && captchaVerifyUrl">
    <altcha-widget
      :challengeurl="captchaChallengeUrl"
      :verifyurl="captchaVerifyUrl"
      ref="altchaWidget"
      :aria-label="props.t('altcha.ariaLabel')"
      aria-describedby="captcha-description"
    />
    <div
      id="captcha-description"
      class="sr-only"
    >
      {{ props.t("altcha.screenReaderDescription") }}
    </div>
  </div>
  <div v-else>
    <p>{{ props.t("altcha.loadError") }}</p>
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

const props = defineProps<{
  t: (key: string) => string;
  baseUrl: string | undefined;
}>();

const altchaWidget = ref<Partial<AltchaWidget> | null>(null);
const getWidget = () => altchaWidget.value as AltchaWidget;

const captchaChallengeUrl = ref<string | null>(null);
const captchaVerifyUrl = ref<string | null>(null);
const captchaEnabled = ref<boolean>(true);

const emit = defineEmits<{
  (e: "validationResult", value: boolean): void;
  (e: "tokenChanged", token: string | null): void;
}>();

const fetchCaptchaDetails = async () => {
  try {
    const response = await fetch(
      `${getAPIBaseURL(props.baseUrl, false)}${VUE_APP_ZMS_API_CAPTCHA_DETAILS_ENDPOINT}`
    );
    if (!response.ok) throw new Error("Fehler beim Laden der Captcha-Daten");
    const data = await response.json();
    captchaChallengeUrl.value = `${getAPIBaseURL(props.baseUrl, false)}${VUE_APP_ZMS_API_CAPTCHA_CHALLENGE_ENDPOINT}`;
    captchaVerifyUrl.value = `${getAPIBaseURL(props.baseUrl, false)}${VUE_APP_ZMS_API_CAPTCHA_VERIFY_ENDPOINT}`;
    captchaEnabled.value = data.captchaEnabled;
  } catch (error) {
    console.error("Fehler beim Abrufen der Captcha-Details:", error);
    captchaEnabled.value = false;
  }
};

const handleStateChange = (ev: CustomEvent | Event) => {
  if ("detail" in ev) {
    const state = ev.detail.state;
    if (state === "verifying") {
      emit("validationResult", false);
    }
  }
};

const handleServerVerification = (ev: CustomEvent | Event) => {
  if ("detail" in ev) {
    const payload = ev.detail;
    if (payload?.meta?.success === true && payload?.data?.valid === true) {
      emit("validationResult", true);
    } else {
      emit("validationResult", false);
      getWidget()?.configure({
        strings: {
          verified: props.t("altcha.error"),
        },
      });
    }
    emit("tokenChanged", ev.detail.token);
  }
};

const configureWidget = () => {
  const widget = getWidget();
  if (widget) {
    try {
      widget.configure({
        strings: {
          error: props.t("altcha.error"),
          expired: props.t("altcha.expired"),
          footer: props.t("altcha.footer"),
          label: props.t("altcha.label"),
          verified: props.t("altcha.verified"),
          verifying: props.t("altcha.verifying"),
          waitAlert: props.t("altcha.waitAlert"),
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
  getWidget()?.addEventListener("serververification", handleServerVerification);
  nextTick(() => {
    configureWidget();
  });
});

onUnmounted(() => {
  getWidget()?.removeEventListener("statechange", handleStateChange);
  getWidget()?.removeEventListener(
    "serververification",
    handleServerVerification
  );
});
</script>

<style scoped>
.altcha-captcha {
  --altcha-border-color: var(--mde-color-neutral-beau-blue, #bdd4ea);
  --altcha-border-radius: 0;
  --altcha-checkbox-border-color: var(--mde-color-brand-mde-blue, #005a9f);
  --altcha-checkbox-border-radius: 0;
  --altcha-checkbox-border-width: 2px;
  --altcha-color-base-content: var(--mde-color-neutral-grey, #3a5368);
  --altcha-color-error: var(--mde-color-status-error, #984447);
  --altcha-padding: 1.25rem;
}

/* Screen reader only class */
.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}
</style>
