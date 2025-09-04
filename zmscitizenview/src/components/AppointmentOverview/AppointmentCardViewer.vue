<template>
  <muc-card-container v-if="allAppointments.length == 0">
    <add-appointment-card
      :title="t('newAppointmentTitle')"
      :new-appointment-url="newAppointmentUrl"
      :t="t"
    >
      <template #content>
        <add-appointment-svg />
      </template>
    </add-appointment-card>
  </muc-card-container>
  <div v-else-if="isMobile">
    <muc-slider
      v-if="allAppointments.length < 3"
      class="slider-content"
    >
      <muc-slider-item
        v-for="(appointment, index) in allAppointments"
        :key="index"
      >
        <appointment-card
          :appointment="appointment"
          :appointment-detail-url="appointmentDetailUrl"
          :offices="offices"
          :class="{ 'card-color': displayedOnDetailScreen }"
          class="mobile-card-height"
          :t="t"
          tabindex="0"
        />
      </muc-slider-item>
      <muc-slider-item v-if="!displayedOnDetailScreen">
        <add-appointment-card
          class="mobile-card-height"
          :title="t('newAppointmentTitle')"
          :new-appointment-url="newAppointmentUrl"
          :t="t"
        >
          <template #content>
            <add-appointment-svg />
          </template>
        </add-appointment-card>
      </muc-slider-item>
    </muc-slider>
    <muc-slider
      v-else
      class="slider-content"
    >
      <muc-slider-item
        v-for="(appointment, index) in allAppointments.slice(0, 3)"
        :key="index"
      >
        <appointment-card
          :appointment="appointment"
          :appointment-detail-url="appointmentDetailUrl"
          :offices="offices"
          :class="{ 'card-color': displayedOnDetailScreen }"
          class="mobile-card-height"
          :t="t"
          tabindex="0"
        />
      </muc-slider-item>
    </muc-slider>
  </div>
  <div v-else>
    <muc-card-container v-if="allAppointments.length < 3">
      <appointment-card
        v-for="(appointment, index) in allAppointments"
        :key="index"
        :appointment="appointment"
        :appointment-detail-url="appointmentDetailUrl"
        :offices="offices"
        :class="{ 'card-color': displayedOnDetailScreen }"
        :t="t"
        tabindex="0"
      />
      <add-appointment-card
        v-if="!displayedOnDetailScreen"
        :title="t('newAppointmentTitle')"
        :new-appointment-url="newAppointmentUrl"
        :t="t"
      >
        <template #content>
          <add-appointment-svg />
        </template>
      </add-appointment-card>
    </muc-card-container>
    <muc-card-container v-else>
      <appointment-card
        v-for="(appointment, index) in allAppointments.slice(0, 3)"
        :key="index"
        :appointment="appointment"
        :appointment-detail-url="appointmentDetailUrl"
        :offices="offices"
        :class="{ 'card-color': displayedOnDetailScreen }"
        :t="t"
        tabindex="0"
      />
    </muc-card-container>
  </div>
</template>

<script setup lang="ts">
import {
  MucCardContainer,
  MucSlider,
  MucSliderItem,
} from "@muenchen/muc-patternlab-vue";

import { AppointmentDTO } from "@/api/models/AppointmentDTO";
import { Office } from "@/api/models/Office";
import AddAppointmentCard from "@/components/AppointmentOverview/AddAppointmentCard.vue";
import AddAppointmentSvg from "@/components/AppointmentOverview/AddAppointmentSvg.vue";
import AppointmentCard from "@/components/AppointmentOverview/AppointmentCard.vue";

defineProps<{
  allAppointments: AppointmentDTO[];
  isMobile: boolean;
  newAppointmentUrl: string;
  appointmentDetailUrl: string;
  displayedOnDetailScreen: boolean;
  offices: Office[];
  t: (key: string) => string;
}>();
</script>

<style scoped>
/* No extra padding in MucSlider */
.m-component {
  padding: 0 !important;
}

/* Content of the slider extends to the edge of the screen */
.slider-content {
  margin-left: -1.5rem;
  margin-right: -1.5rem;
}

/* Background color of the cards */
.card-color {
  background-color: white !important;
}

/* Height of the cards in the mobile view, so that all cards have the same height */
.mobile-card-height {
  height: 100%;
}

.card:hover {
  background-color: var(--color-neutrals-blue-xlight) !important;
}
</style>
