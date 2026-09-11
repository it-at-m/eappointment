<template>
  <muc-intro
    :title="appointmentId ? appointmentId : ''"
    :tagline="t('appointment')"
    divider
    variant="detail"
  >
    <p style="margin-top: 32px; padding-bottom: 8px">
      <strong>{{ t("noLoginInfo") }}</strong>
    </p>
    <p style="padding-bottom: 32px">
      {{ t("noLoginText") }}
    </p>
    <muc-button
      icon="sign-in"
      @click="requestLogin"
    >
      {{ t("login") }}
    </muc-button>
  </muc-intro>
</template>

<script lang="ts" setup>
import { MucButton, MucIntro } from "@muenchen/muc-patternlab-vue";

import { ETRACKER_COMPONENT, trackCitizenEvent } from "@/utils/etracker";

defineProps<{
  appointmentId: string | null | undefined;
  t: (key: string) => string;
}>();

const requestLogin = () => {
  trackCitizenEvent({
    object: "Login",
    action: "click",
    type: ETRACKER_COMPONENT.detail,
  });
  document.dispatchEvent(
    new CustomEvent("authorization-request", {
      detail: {
        loginProvider: undefined,
        authLevel: undefined,
      },
    })
  );
};
</script>

<style scoped>
:deep(.m-intro-vertical__title) {
  margin-bottom: 0 !important;
}
</style>
