<template>
  <li class="m-listing__list-item">
    <div class="list-item">
      <MucCounter
        v-model="count"
        :label="subService.name"
        :max="maxValue"
        :disabled="disabled"
      />
    </div>
  </li>
</template>

<script setup lang="ts">

import { MucCounter } from "@muenchen/muc-patternlab-vue";
import {SubService} from "@/types/SubService";
import {watch, ref, computed} from "vue";
import {OfficeImpl} from "@/types/OfficeImpl";
import {MAX_SLOTS} from "@/utils/Constants";

const props = defineProps<{subService: SubService, currentSlots: number, maxSlotsPerAppointment: number}>();

const emit = defineEmits<{
  (e: "change", id: string, count: number): void;
}>();

const count = ref<number>(0);

watch(count, (newCount) => {

  emit("change", props.subService.id, newCount);

});

const maxValue = computed(
  () => {
    return checkPlusEndabled.value ? props.subService.maxQuantity : count.value ;
  }
)

const disabled = computed(
  () => {
    return !checkPlusEndabled.value && count.value === 0 ;
  }
)

const checkPlusEndabled = computed (() => (props.currentSlots + getMinSlotOfProvider(props.subService.providers)) <= props.maxSlotsPerAppointment)

const getMinSlotOfProvider = (provider: Array<OfficeImpl>) => {
  let minSlot = MAX_SLOTS;
  provider.forEach(provider => {
    if (provider.slots) {
      minSlot = Math.min(minSlot, provider.slots);
    }
  });
  return minSlot;
}

</script>

<style scoped>
.list-item {
  margin-bottom: 1.75rem;
}
</style>
