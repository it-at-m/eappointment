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
      {{ officeName(officeId) }}
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
            <template #default>{{ formatTime(time) }}</template>
          </muc-button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { MucButton } from "@muenchen/muc-patternlab-vue";

const props = defineProps<{
  officeId: number | string;
  times: number[];
  timeLabel: string;
  showLocationTitle: boolean;
  officeName: (id: number | string) => string | null;
  isSlotSelected: (officeId: number | string, time: number) => boolean;
  formatTime: (time: number) => string;
}>();

defineEmits<{
  (
    e: "selectTimeSlot",
    payload: { officeId: number | string; time: number }
  ): void;
}>();
</script>

<style lang="scss" scoped>
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
}

.grid-item {
  margin: 8px 8px;
}

.location-title {
  margin-top: 10px;
}

.timeslot {
  height: 2rem;
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
@media (max-width: 576px) {
  .timeslot.m-button,
  .timeslot .m-button {
    padding: 1px 8px !important;
    min-height: 2.25rem;
  }

  .grid-item {
    margin: 6px 6px;
  }
  .grid {
    margin-right: 0px;
  }
}
</style>
