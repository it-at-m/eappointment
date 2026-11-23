<template>
  <main
    :class="{
      'details-background': displayOptionDetailScreen,
      'styles-loading': !stylesLoaded,
    }"
  >
    <div>
      <div v-html="mucIconsSprite"></div>
      <div v-html="customIconsSprit"></div>
      <appointment-slider-view
        v-if="globalState.isLoggedIn"
        :global-state="globalState"
        :appointment-detail-url="appointmentDetailUrl"
        :appointment-overview-url="appointmentOverviewUrl"
        :new-appointment-url="newAppointmentUrl"
        :displayed-on-detail-screen="displayOptionDetailScreen"
        :t="t"
      />
    </div>
    <build-info-badge />
  </main>
</template>

<script lang="ts" setup>
import customIconsSprit from "@muenchen/muc-patternlab-vue/assets/icons/custom-icons.svg?raw";
import mucIconsSprite from "@muenchen/muc-patternlab-vue/assets/icons/muc-icons.svg?raw";
import { onMounted, ref } from "vue";
import { useI18n } from "vue-i18n";

import "@/utils/installMdePreviewFonts";

import AppointmentSliderView from "@/components/AppointmentOverview/AppointmentSliderView.vue";
import BuildInfoBadge from "@/components/Common/BuildInfoBadge.vue";
import { trackWidgetView } from "@/utils/trackAppointmentEvent";
import { useGlobalState } from "./utils/useGlobalState";

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
const globalState = useGlobalState(props);

// Prevent flickering: hide content until external stylesheet is loaded
const stylesLoaded = ref(true);

// Fallback: reveal content after 3s even if stylesheet fails or never loads
onMounted(() => {
  trackWidgetView("appointment_slider");
});
</script>

<style>
@import "@/styles/mde-preview.css";
@import "@muenchen/muc-patternlab-vue/style.css";

:host {
  display: block;
  position: relative;
  font-family: var(--mde-typography-font-family-base);
}

/* Background color on details page */
.details-background {
  background-color: var(--color-neutrals-blue-xlight);
}

/* Hide content until external stylesheet is loaded to prevent icon flickering */
main.styles-loading {
  visibility: hidden;
}
</style>
