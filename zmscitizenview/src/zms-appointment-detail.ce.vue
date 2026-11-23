<template>
  <main :class="{ 'styles-loading': !stylesLoaded }">
    <div>
      <div v-html="mucIconsSprite"></div>
      <div v-html="customIconsSprit"></div>
      <appointment-detail-view
        :global-state="globalState"
        :appointment-overview-url="appointmentOverviewUrl"
        :reschedule-appointment-url="rescheduleAppointmentUrl"
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

import AppointmentDetailView from "@/components/AppointmentDetail/AppointmentDetailView.vue";
import BuildInfoBadge from "@/components/Common/BuildInfoBadge.vue";
import { trackWidgetView } from "@/utils/trackAppointmentEvent";
import { useGlobalState } from "./utils/useGlobalState";

const props = defineProps({
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
const globalState = useGlobalState(props);

// Prevent flickering: hide content until external stylesheet is loaded
const stylesLoaded = ref(true);

// Fallback: reveal content after 3s even if stylesheet fails or never loads
onMounted(() => {
  trackWidgetView("appointment_detail");
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

main {
  padding-bottom: 32px;
}

/* Hide content until external stylesheet is loaded to prevent icon flickering */
main.styles-loading {
  visibility: hidden;
}
</style>
