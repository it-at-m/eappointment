<template>
  <link
    href="https://assets.muenchen.de/mde/1.1.15/css/style.css"
    rel="stylesheet"
    @load="onStylesheetLoaded"
  />
  <main :class="{ 'styles-loading': !stylesLoaded }">
    <div>
      <div v-html="mucIconsSprite"></div>
      <div v-html="customIconsSprit"></div>
      <appointment-overview-view
        v-if="globalState.isLoggedIn"
        :global-state="globalState"
        :appointment-detail-url="appointmentDetailUrl"
        :new-appointment-url="newAppointmentUrl"
        :overview-url="overviewUrl"
        :t="t"
      />
    </div>
  </main>
</template>

<script lang="ts" setup>
import customIconsSprit from "@muenchen/muc-patternlab-vue/assets/icons/custom-icons.svg?raw";
import mucIconsSprite from "@muenchen/muc-patternlab-vue/assets/icons/muc-icons.svg?raw";
import { ref } from "vue";
import { useI18n } from "vue-i18n";

import AppointmentOverviewView from "@/components/AppointmentOverview/AppointmentOverviewView.vue";
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
  newAppointmentUrl: {
    type: String,
    required: true,
  },
  overviewUrl: {
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
  margin: 32px 0;
}

@media (min-width: 768px) {
  main {
    margin: 48px 0;
  }
}

/* Hide content until external stylesheet is loaded to prevent icon flickering */
main.styles-loading {
  visibility: hidden;
}
</style>
