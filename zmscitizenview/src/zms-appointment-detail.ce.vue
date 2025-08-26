<template>
  <link
    href="https://assets.muenchen.de/mde/1.0.10/css/style.css"
    rel="stylesheet"
  />
  <main>
    <div>
      <div v-html="mucIconsSprite"></div>
      <div v-html="customIconsSprit"></div>
      <appointment-detail-view
        :base-url="baseUrl"
        :appointment-overview-url="appointmentOverviewUrl"
        :reschedule-appointment-url="rescheduleAppointmentUrl"
        :t="t"
      />
    </div>
  </main>
</template>

<script lang="ts" setup>
import customIconsSprit from "@muenchen/muc-patternlab-vue/assets/icons/custom-icons.svg?raw";
import mucIconsSprite from "@muenchen/muc-patternlab-vue/assets/icons/muc-icons.svg?raw";
import { useI18n } from "vue-i18n";

import AppointmentDetailView from "@/components/AppointmentDetail/AppointmentDetailView.vue";
import { registerAuthenticationHook } from "./utils/auth";
import { ref } from "vue";

defineProps({
  baseUrl: {
    type: String,
    required: false,
    default: undefined,
  },
  appointmentOverviewUrl: {
    type: String,
    required: true,
  },
  rescheduleAppointmentUrl: {
    type: String,
    required: true,
  },
});

const { t } = useI18n();
const accessToken = ref<string | null>(null);
registerAuthenticationHook(
  (newAccessToken) => {
    accessToken.value = newAccessToken;
  },
  () => {
    accessToken.value = null;
  }
);
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
