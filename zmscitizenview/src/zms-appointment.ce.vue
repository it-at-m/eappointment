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
import mucIconsSprite from "@muenchen/muc-patternlab-vue/assets/temporary/muc-icons.svg?raw";
import customIconsSprit from "@muenchen/muc-patternlab-vue/assets/temporary/custom-icons.svg?raw";
import AppointmentView from "@/components/Appointment/AppointmentView.vue";
import { useI18n} from "vue-i18n";

defineProps({
  baseUrl: {
    type: String,
    required: true
  },
  serviceId: {
    type: String,
    required: false
  },
  locationId: {
    type: String,
    required: false
  },
  appointmentHash: {
    type: String,
    required: false
  },
  confirmAppointmentHash: {
    type: String,
    required: false
  }
});

const { t } = useI18n();

</script>

<style>
@import "@muenchen/muc-patternlab-vue/assets/temporary/muenchende-style.css";
@import "@muenchen/muc-patternlab-vue/assets/temporary/custom-style.css";
@import "@muenchen/muc-patternlab-vue/style.css";

:host {
  font-family: Open Sans, sans-serif;
}

main {
  padding-bottom: 32px;
}
</style>
