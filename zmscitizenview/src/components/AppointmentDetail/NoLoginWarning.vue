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
      icon="arrow-right"
      @click="openRequestLoginModal"
    >
      {{ t("noLoginButton") }}
    </muc-button>
  </muc-intro>
  <muc-modal
    :open="requestLoginModalOpen"
    @close="requestLoginModalOpen = false"
    @cancel="requestLoginModalOpen = false"
  >
    <template #title>{{ t("requestLoginModalHeading") }}</template>
    <template #body>
      {{ t("requestLoginModalText") }}
    </template>
    <template #buttons>
      <muc-button
        icon="ext-link"
        @click="requestLogin"
      >
        {{ t("login") }}
      </muc-button>
    </template>
  </muc-modal>
</template>

<script lang="ts" setup>
import { MucButton, MucIntro, MucModal } from "@muenchen/muc-patternlab-vue";
import { ref } from "vue";

defineProps<{
  appointmentId: string | undefined;
  t: (key: string) => string;
}>();

const requestLoginModalOpen = ref(false);

const openRequestLoginModal = () => (requestLoginModalOpen.value = true);

const requestLogin = () => {
  requestLoginModalOpen.value = false;
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
