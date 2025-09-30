<template>
  <link
    href="https://assets.muenchen.de/mde/1.0.10/css/style.css"
    rel="stylesheet"
  />
  <main :class="{ 'details-background': displayOptionDetailScreen }">
    <div>
      <div v-html="mucIconsSprite"></div>
      <div v-html="customIconsSprit"></div>
      <appointment-slider-view
        :base-url="baseUrl"
        :appointment-detail-url="appointmentDetailUrl"
        :appointment-overview-url="appointmentOverviewUrl"
        :new-appointment-url="newAppointmentUrl"
        :displayed-on-detail-screen="displayOptionDetailScreen"
        :t="t"
      />
    </div>
  </main>
</template>

<script lang="ts" setup>
import customIconsSprit from "@muenchen/muc-patternlab-vue/assets/icons/custom-icons.svg?raw";
import mucIconsSprite from "@muenchen/muc-patternlab-vue/assets/icons/muc-icons.svg?raw";
import { useI18n } from "vue-i18n";

import AppointmentSliderView from "@/components/AppointmentOverview/AppointmentSliderView.vue";

const props = defineProps({
  baseUrl: {
    type: String,
    required: false,
    default: undefined,
  },
  appointmentDetailUrl: {
    type: String,
    required: true,
  },
  appointmentOverviewUrl: {
    type: String,
    required: true,
  },
  newAppointmentUrl: {
    type: String,
    required: true,
  },
  displayedOnDetailScreen: {
    type: String,
    required: false,
    default: "false",
  },
});

const displayOptionDetailScreen =
  props.displayedOnDetailScreen.toLowerCase() === "true";

const { t } = useI18n();
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

/* Background color on details page */
.details-background {
  background-color: var(--color-neutrals-blue-xlight);
}
</style>
