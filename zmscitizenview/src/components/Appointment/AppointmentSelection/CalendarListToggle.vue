<template>
  <div class="calendar-list-toggle-container">
    <h2 tabindex="0">{{ t("time") }}</h2>
    <div
      class="m-toggle-switch"
      role="switch"
      :aria-checked="isListView"
      tabindex="0"
      @click="toggleView"
      @keydown.enter.prevent="toggleView"
      @keydown.space.prevent="toggleView"
    >
      <span
        class="m-toggle-switch__label"
        :class="{ disabled: isListView }"
        >{{ t("calendarView") }}</span
      >
      <span class="m-toggle-switch__indicator"><span></span></span>
      <span
        class="m-toggle-switch__label"
        :class="{ disabled: !isListView }"
        >{{ t("listView") }}</span
      >
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from "vue";

const props = defineProps<{
  t: (key: string) => string;
}>();

const emit = defineEmits<{
  (e: "update:isListView", value: boolean): void;
}>();

const isListView = ref(false);

const toggleView = () => {
  isListView.value = !isListView.value;
  emit("update:isListView", isListView.value);
};
</script>

<style lang="scss" scoped>
@use "@/styles/breakpoints.scss" as *;

.calendar-list-toggle-container {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-bottom: 20px;
  padding: 16px 0;
  border-bottom: 1px solid var(--color-neutrals-blue);
}

/* Responsive layout: on larger screens, display toggle to the right of heading */
@include xs-up {
  .calendar-list-toggle-container {
    flex-direction: row;
    justify-content: space-between;
    align-items: flex-start;
  }
}

.m-toggle-switch {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  user-select: none;
}

.m-toggle-switch__label {
  color: #005a9f; /* BDE Blue */
  transition: color 0.2s ease;
}

.m-toggle-switch__label.disabled {
  opacity: 1;
  color: #617586; /* Grey Light */
  transition: color 0.2s ease;
}

.m-toggle-switch:focus {
  outline: 2px solid var(--color-primary);
  outline-offset: 2px;
}
</style>
