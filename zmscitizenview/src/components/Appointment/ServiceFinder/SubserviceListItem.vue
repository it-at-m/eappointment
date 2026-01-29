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

import { OfficeImpl } from "@/types/OfficeImpl";
import { SubService } from "@/types/SubService";
import { getServiceBaseURL, MAX_SLOTS } from "@/utils/Constants";

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
  if (subServiceSlots <= 0) return props.subService.maxQuantity;

  // Calculate slots currently used by this subservice
  const thisSlotsUsed = subServiceSlots * count.value;

  // Available slots = minSlotsPerAppointment - (currentSlots - this subservice's contribution)
  const slotsUsedByOthers = props.currentSlots - thisSlotsUsed;
  const availableSlots = props.minSlotsPerAppointment - slotsUsedByOthers;

  // Calculate max count based on available slots
  const maxCountBySlots = Math.floor(availableSlots / subServiceSlots);

  // Return the minimum of maxQuantity and what's allowed by slots
  return Math.max(0, Math.min(props.subService.maxQuantity, maxCountBySlots));
});

const disabled = computed(() => {
  return maxValue.value === 0 && count.value === 0;
});

/**
 * Gets the maximum slots required for a service across all providers.
 * We use MAX (not min) because we need to ensure the booking works at
 * providers with higher slot requirements.
 */
const getMaxSlotOfProvider = (provider: OfficeImpl[]) => {
  let maxSlot = 1; // Default to 1 slot minimum
  provider.forEach((provider) => {
    if (provider.slots && provider.slots > maxSlot) {
      maxSlot = provider.slots;
    }
  });
  return maxSlot;
};
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
