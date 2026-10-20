<template>
  <div class="calendar-list-toggle-container">
    <h2 id="viewToggleLabel">
      {{ t("time") }}
    </h2>
    <muc-toggle
      :model-value="isListView"
      :label-left="t('calendarView')"
      :label-right="t('listView')"
      :aria-label="toggleAriaLabel"
      @update:model-value="emit('update:isListView', $event)"
    />
  </div>
</template>

<script setup lang="ts">
import { MucToggle } from "@muenchen/muc-patternlab-vue";
import { computed } from "vue";

const props = withDefaults(
  defineProps<{
    t: (key: string) => string;
    isListView: boolean;
  }>(),
  { isListView: false }
);

const emit = defineEmits<{
  (e: "update:isListView", value: boolean): void;
}>();

const toggleAriaLabel = computed(() => {
  const current = props.isListView
    ? props.t("listViewActiveLabel")
    : props.t("calendarViewActiveLabel");
  const action = props.isListView
    ? props.t("switchToCalendarViewAriaLabel")
    : props.t("switchToListViewAriaLabel");
  return `${current} ${action}`;
});
</script>

<style lang="scss" scoped>
@use "@/styles/breakpoints.scss" as *;

.calendar-list-toggle-container {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-bottom: 20px;
  padding: 16px 0;
}

/* Responsive layout: on larger screens, display toggle to the right of heading */
@include xs-up {
  .calendar-list-toggle-container {
    flex-direction: row;
    justify-content: space-between;
    align-items: flex-start;
  }
}
</style>
