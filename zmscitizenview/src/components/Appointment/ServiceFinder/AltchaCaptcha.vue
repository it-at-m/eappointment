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
      `${getAPIBaseURL(props.baseUrl)}${VUE_APP_ZMS_API_CAPTCHA_DETAILS_ENDPOINT}`
    );
    if (!response.ok) throw new Error("Fehler beim Laden der Captcha-Daten");
    const data = await response.json();
    captchaChallengeUrl.value = `${getAPIBaseURL(props.baseUrl)}${VUE_APP_ZMS_API_CAPTCHA_CHALLENGE_ENDPOINT}`;
    captchaVerifyUrl.value = `${getAPIBaseURL(props.baseUrl)}${VUE_APP_ZMS_API_CAPTCHA_VERIFY_ENDPOINT}`;
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
/* Custom styling for ALTCHA widget to match UI/UX requirements */
:deep(.altcha) {
  background: transparent;
  border: 1px solid #bdd4ea;
  display: flex;
  flex-direction: column;
  max-width: 260px;
  position: relative;
  text-align: left;
  padding: 5px 0;
}

:deep(.altcha-main) {
  align-items: center;
  display: flex;
  gap: 0.7rem;
  padding: 0.7rem 1rem;
}

:deep(.altcha-checkbox) {
  display: flex;
  align-items: center;
  height: 20px;
  width: 20px;
}

:deep(.altcha-checkbox input) {
  appearance: none;
  width: 20px;
  height: 20px;
  border: 2px solid #337bb2;
  cursor: pointer;
  display: inline-block;
  position: relative;
  background-color: #fff;
  transition: all 0.2s ease;
}

:deep(.altcha-checkbox input:checked) {
  background-color: #337bb2;
  border-color: #337bb2;
}

:deep(.altcha-checkbox input:checked::after) {
  content: '';
  position: absolute;
  left: 5px;
  top: 1.5px;
  width: 6px;
  height: 11px;
  border: solid #fff;
  border-width: 0 3px 3px 0;
  transform: rotate(45deg);
}

:deep(.altcha-label) {
  flex-grow: 1;
  display: flex;
}

:deep(.altcha-label label) {
  cursor: pointer;
  margin: 0;
  padding-bottom: 3px;
  color: #3a5368;
}

/* Hide logo and footer */
:deep(.altcha-logo) {
  display: none !important;
}

:deep(.altcha-footer) {
  display: none !important;
}

/* Hide any other ALTCHA branding elements */
:deep([class*="altcha-"]) {
  /* Keep only the main elements we want */
}

:deep(.altcha-error) {
  color: #984447;
  display: flex;
  font-size: 0.85rem;
  gap: 0.3rem;
  padding: 0 0.7rem 0.7rem;
}

/* Rotating spinner while verifying */
:deep(.altcha svg) {
  transform: none;
  transition: transform 0.3s ease;
}

:deep(.altcha[data-state="verifying"] svg) {
  animation: spin 1s linear infinite;
  color: #005a9f;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
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
