<template>
  <main>
    <div>
      <div v-html="mucIconsSprite"></div>
      <div v-html="customIconsSprit"></div>
      <appointment-view
        :base-url="baseUrl"
        :service-id="serviceId"
        :location-id="locationId"
        :appointment-hash="appointmentHash"
        :confirm-appointment-hash="confirmAppointmentHash"
        :t="t"
      />
    </div>
  </main>
</template>

<script lang="ts" setup>
import customIconsSprit from "@muenchen/muc-patternlab-vue/assets/temporary/custom-icons.svg?raw";
import mucIconsSprite from "@muenchen/muc-patternlab-vue/assets/temporary/muc-icons.svg?raw";
import { useI18n } from "vue-i18n";

import AppointmentView from "@/components/Appointment/AppointmentView.vue";

defineProps({
  baseUrl: {
    type: String,
    required: false,
    default: undefined,
  },
  serviceId: {
    type: String,
    required: false,
    default: undefined,
  },
  locationId: {
    type: String,
    required: false,
    default: undefined,
  },
  appointmentHash: {
    type: String,
    required: false,
    default: undefined,
  },
  confirmAppointmentHash: {
    type: String,
    required: false,
    default: undefined,
  },
});

const { t } = useI18n();
</script>

<style>
@import "@muenchen/muc-patternlab-vue/assets/temporary/muenchende-style.css";
@import "@muenchen/muc-patternlab-vue/assets/temporary/custom-style.css";
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
