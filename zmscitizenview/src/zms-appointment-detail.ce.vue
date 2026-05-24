<template>
  <link
    href="https://assets.muenchen.de/mde/1.1.15/css/style.css"
    rel="stylesheet"
    @load="onStylesheetLoaded"
    @error="onStylesheetLoaded"
  />
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
  </main>
</template>

<script lang="ts" setup>
import customIconsSprit from "@muenchen/muc-patternlab-vue/assets/icons/custom-icons.svg?raw";
import mucIconsSprite from "@muenchen/muc-patternlab-vue/assets/icons/muc-icons.svg?raw";
import { onMounted, ref } from "vue";
import { useI18n } from "vue-i18n";

import AppointmentDetailView from "@/components/AppointmentDetail/AppointmentDetailView.vue";
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
const stylesLoaded = ref(false);
const onStylesheetLoaded = () => {
  stylesLoaded.value = true;
};

// Fallback: reveal content after 3s even if stylesheet fails or never loads
onMounted(() => {
  setTimeout(() => {
    stylesLoaded.value = true;
  }, 3000);
});
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

/* Hide content until external stylesheet is loaded to prevent icon flickering */
main.styles-loading {
  visibility: hidden;
}
</style>
