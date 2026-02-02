<template>
  <li class="m-listing__list-item">
    <div class="list-item">
      <muc-counter
        v-model="count"
        :label="subService.name"
        :link="getServiceBaseURL() + subService.id"
        :max="maxValue"
        :disabled="disabled"
      />
    </div>
  </li>
</template>

<script setup lang="ts">
import { MucCounter } from "@muenchen/muc-patternlab-vue";
import { computed, ref, watch } from "vue";

import { SubService } from "@/types/SubService";
import { getServiceBaseURL } from "@/utils/Constants";
import {
  calculateMaxCountBySlots,
  getMaxSlotOfProvider,
} from "@/utils/slotCalculations";

const props = defineProps<{
  subService: SubService;
  currentSlots: number;
  minSlotsPerAppointment: number;
}>();

const emit = defineEmits<(e: "change", id: string, count: number) => void>();

const count = ref<number>(props.subService.count);

watch(count, (newCount) => {
  emit("change", props.subService.id, newCount);
});

/**
 * Calculates the maximum count for this subservice, considering both:
 * - maxQuantity: the subservice's own limit
 * - minSlotsPerAppointment: remaining slots after accounting for main service and other subservices
 */
const maxValue = computed(() => {
  const subServiceSlots = getMaxSlotOfProvider(props.subService.providers);

  // Calculate slots currently used by this subservice
  const thisSlotsUsed = subServiceSlots * count.value;

  // Slots used by others = currentSlots - this subservice's contribution
  const slotsUsedByOthers = props.currentSlots - thisSlotsUsed;

  return calculateMaxCountBySlots(
    subServiceSlots,
    props.subService.maxQuantity,
    props.minSlotsPerAppointment,
    slotsUsedByOthers
  );
});

const disabled = computed(() => {
  return maxValue.value === 0 && count.value === 0;
});
</script>

<style lang="scss" scoped>
@use "@/styles/breakpoints.scss" as *;
.list-item {
  margin-bottom: 1.75rem;
}
.grid {
  grid-template-columns: minmax(150px, auto) 1fr !important;
}

@include sm-down {
  .grid {
    grid-template-columns: 1fr !important;
  }
}
</style>
