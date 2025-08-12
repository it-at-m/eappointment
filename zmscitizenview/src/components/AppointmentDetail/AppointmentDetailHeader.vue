<template>
  <muc-intro
    :tagline="t('appointmentOverview.onSiteAppointment')"
    :title="formatTitle(appointment)"
  >
    <p style="padding-bottom: 18px">
      <b> {{ t('appointmentOverview.appointmentNumber') }} </b
      >{{ appointment.processId }}
    </p>
    <p style="padding-bottom: 18px">
      <b> {{ t('appointmentOverview.appointmentNumber') }} </b
      >{{ appointment.processId }}
    </p>
  </muc-intro>
</template>

<script setup lang="ts">

import { MucIntro } from "@muenchen/muc-patternlab-vue";
import { formatTime } from "@/utils/formatTime";
import { formatTitle } from "@/utils/formatTitle";
import {AppointmentDTO} from "@/api/models/AppointmentDTO";

const props = defineProps<{
  appointment: AppointmentDTO;
  t: (key: string) => string;
}>();

</script>
