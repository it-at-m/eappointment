<template>
  <div>
    <!-- Location title for multiple providers -->
    <div
      v-if="showLocationTitle"
      class="ml-4 location-title"
    >
      <svg
        aria-hidden="true"
        class="icon icon--before"
      >
        <use xlink:href="#icon-map-pin"></use>
      </svg>
      {{ officeNameById(officeId) }}
    </div>

    <!-- Time slot display -->
    <div class="wrapper">
      <p class="left-text nowrap">
        {{ timeLabel }}
      </p>
      <div class="grid">
        <div
          v-for="time in times"
          :key="time"
          class="grid-item"
        >
          <muc-button
            class="timeslot"
            :variant="isSlotSelected(officeId, time) ? 'primary' : 'secondary'"
            @click="$emit('selectTimeSlot', { officeId, time })"
          >
            <template #default>{{ formatTimeFromUnix(time) }}</template>
          </muc-button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { MucButton } from "@muenchen/muc-patternlab-vue";

import { formatTimeFromUnix } from "@/utils/formatAppointmentDateTime";

const props = defineProps<{
  officeId: number | string;
  times: number[];
  timeLabel: string;
  showLocationTitle: boolean;
  officeNameById: (id: number | string) => string | null;
  isSlotSelected: (officeId: number | string, time: number) => boolean;
}>();

defineEmits<{
  (
    e: "selectTimeSlot",
    payload: { officeId: number | string; time: number }
  ): void;
}>();
</script>

<style lang="scss" scoped>
@use "@/styles/breakpoints.scss" as *;
.wrapper {
  display: grid;
  grid-template-columns: 6rem 1fr;
  column-gap: 8px;
  padding: 16px 0;
  border-bottom: 1px solid var(--color-neutrals-blue);
  align-items: center;
}

.wrapper > * {
  margin: 0 8px;
}

.nowrap {
  white-space: nowrap;
}

.grid {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
}

.grid-item {
  margin: 0;
}

.location-title {
  margin-top: 10px;
}

.timeslot {
  min-height: 2rem;
  height: auto;
}

.timeslot.m-button,
:deep(.timeslot .m-button) {
  padding: 4px 12px !important;
}

.left-text {
  display: flex;
  justify-content: left;
  align-items: center;
  height: 100%;
  width: 100px;
  padding-left: 0;
  margin-left: 0;
}

/* Target any div containing .left-text (more specific) */
div:has(.left-text) {
  padding-left: 0 !important;
  margin-left: 0 !important;
}

/* Mobile adjustments */
@include xs-down {
  .wrapper {
    grid-template-columns: 5.75rem 1fr; /* 5.75rem width of the hour column, 1fr times grid takes all remaining width */
    padding: 13px 0 11px; /* 13px top padding, 0 left/right, 11px bottom padding */
  }

  .grid {
    margin-right: 0;
    gap: 13px 11px; /* space between buttons */
  }

  /* Timeslot buttons - smaller padding for mobile */
  .timeslot.m-button,
  .timeslot .m-button {
    padding: 1px 8px !important; /* Even smaller padding for very small screens */
    min-height: 2.25rem;
  }
}
</style>
