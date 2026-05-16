<template>
  <altcha-widget
    v-if="captchaEnabled && challengeUrl"
    ref="altchaWidget"
    class="altcha-captcha"
    :challenge="challengeUrl"
    :configuration="configuration"
    :language="altchaLanguage"
    :aria-label="t('altcha.ariaLabel')"
    aria-describedby="captcha-description"
  />
  <p v-else>{{ t("altcha.loadError") }}</p>
  <div
    id="captcha-description"
    class="sr-only"
  >
    {{ t("altcha.screenReaderDescription") }}
  </div>
</template>

<script setup lang="ts">
import "altcha";
import "altcha/i18n/de";
import "altcha/i18n/en";

import type { CaptchaVerifyResponse } from "@/utils/altchaVerifyFetch";
import type { WidgetAttributes, WidgetMethods } from "altcha/types";
import type { I18n } from "vue-i18n";

import { State } from "altcha/types";
import {
  computed,
  inject,
  nextTick,
  onMounted,
  onUnmounted,
  ref,
  watch,
} from "vue";
import { I18nInjectionKey } from "vue-i18n";

import { applyAltchaStrings, resolveAltchaLanguage } from "@/utils/altchaI18n";
import {
  captchaVerifyFetch,
  isCaptchaVerifySuccess,
} from "@/utils/altchaVerifyFetch";
import {
  getAPIBaseURL,
  VUE_APP_ZMS_API_CAPTCHA_CHALLENGE_ENDPOINT,
  VUE_APP_ZMS_API_CAPTCHA_DETAILS_ENDPOINT,
  VUE_APP_ZMS_API_CAPTCHA_VERIFY_ENDPOINT,
} from "@/utils/Constants";

const props = defineProps<{
  t: (key: string) => string;
  baseUrl: string | undefined;
}>();

const i18n = inject<I18n | null>(I18nInjectionKey, null);

const vueLocale = computed(() => {
  if (!i18n) return "de-DE";
  const locale = i18n.global.locale;
  return typeof locale === "string" ? locale : locale.value;
});

const altchaLanguage = computed(() => resolveAltchaLanguage(vueLocale.value));

const emit = defineEmits<{
  (e: "validationResult", value: boolean): void;
  (e: "tokenChanged", token: string | null): void;
}>();

const altchaWidget = ref<
  (HTMLElement & WidgetAttributes & WidgetMethods) | null
>(null);
const captchaEnabled = ref(false);
const challengeUrl = ref<string | null>(null);
const verifyUrl = ref<string | null>(null);

const configuration = computed(() =>
  verifyUrl.value
    ? JSON.stringify({
        hideFooter: true,
        hideLogo: true,
        verifyUrl: verifyUrl.value,
      })
    : undefined
);

/* PoW finished locally; server check + JWT follow via serververification. */
const onStateChange = (ev: CustomEvent | Event) => {
  if (!("detail" in ev)) return;
  const { state } = ev.detail as { state: State };
  if (
    state === State.VERIFYING ||
    state === State.ERROR ||
    state === State.EXPIRED
  ) {
    emit("validationResult", false);
    emit("tokenChanged", null);
  } else if (state === State.VERIFIED) {
    emit("validationResult", true);
  }
};

/* Authoritative server verification result and JWT for the booking flow. */
const onServerVerification = (ev: CustomEvent | Event) => {
  if (!("detail" in ev)) return;
  const detail = ev.detail as CaptchaVerifyResponse;
  const valid = isCaptchaVerifySuccess(detail.meta, detail.data);
  emit("validationResult", valid);
  emit("tokenChanged", valid ? (detail.token ?? null) : null);
};

const fetchCaptchaDetails = async () => {
  try {
    const apiBase = getAPIBaseURL(props.baseUrl, false);
    const response = await fetch(
      `${apiBase}${VUE_APP_ZMS_API_CAPTCHA_DETAILS_ENDPOINT}`
    );
    if (!response.ok) throw new Error("Failed to load captcha details");
    const data = await response.json();
    captchaEnabled.value = data.captchaEnabled;
    if (!data.captchaEnabled) return;
    challengeUrl.value = `${apiBase}${VUE_APP_ZMS_API_CAPTCHA_CHALLENGE_ENDPOINT}`;
    verifyUrl.value = `${apiBase}${VUE_APP_ZMS_API_CAPTCHA_VERIFY_ENDPOINT}`;
  } catch (error) {
    console.error("Failed to fetch captcha details:", error);
    captchaEnabled.value = false;
  }
};

watch(vueLocale, (locale, previous) => {
  if (locale === previous) return;
  applyAltchaStrings(props.t, resolveAltchaLanguage(locale));
});

onMounted(async () => {
  applyAltchaStrings(props.t, altchaLanguage.value);
  await fetchCaptchaDetails();
  await nextTick();
  const widget = altchaWidget.value;
  if (widget && typeof widget.configure === "function") {
    await widget.configure({ fetch: captchaVerifyFetch });
  }
  altchaWidget.value?.addEventListener("statechange", onStateChange);
  altchaWidget.value?.addEventListener(
    "serververification",
    onServerVerification
  );
});

onUnmounted(() => {
  altchaWidget.value?.removeEventListener("statechange", onStateChange);
  altchaWidget.value?.removeEventListener(
    "serververification",
    onServerVerification
  );
});

defineExpose({ onStateChange, onServerVerification });
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
