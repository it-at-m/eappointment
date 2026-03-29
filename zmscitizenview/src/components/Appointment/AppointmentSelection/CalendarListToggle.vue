<template>
  <div class="calendar-list-toggle-container">
    <h2 id="viewToggleLabel">
      {{ t("time") }}
    </h2>
    <button
      class="m-toggle-switch"
      :class="{ 'm-toggle-switch--pressed': localIsListView }"
      type="button"
      :aria-label="toggleAriaLabel"
      @click="toggleView"
    >
      <span
        class="m-toggle-switch__label"
        :class="{ disabled: localIsListView }"
        aria-hidden="true"
      >
        {{ t("calendarView") }}
      </span>
      <span
        class="m-toggle-switch__indicator"
        aria-hidden="true"
      >
        <span></span>
      </span>
      <span
        class="m-toggle-switch__label"
        :class="{ disabled: !localIsListView }"
        aria-hidden="true"
      >
        {{ t("listView") }}
      </span>
    </button>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch, withDefaults } from "vue";

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

const localIsListView = ref<boolean>(props.isListView);

watch(
  () => props.isListView,
  (v) => {
    localIsListView.value = v;
  }
);

const toggleAriaLabel = computed(() => {
  const current = localIsListView.value
    ? props.t("listViewActiveLabel")
    : props.t("calendarViewActiveLabel");
  const action = localIsListView.value
    ? props.t("switchToCalendarViewAriaLabel")
    : props.t("switchToListViewAriaLabel");
  return `${current} ${action}`;
});

const toggleView = () => {
  const next = !localIsListView.value;
  localIsListView.value = next;
  emit("update:isListView", next);
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
