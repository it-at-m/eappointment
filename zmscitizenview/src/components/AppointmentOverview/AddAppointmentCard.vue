<template>
  <div class="add-card">
    <a :href="newAppointmentUrl">
      <div class="add-card-content">
        <div class="add-card-header">
          <h3>{{ title }}</h3>
        </div>
        <slot name="content" />
        <div class="add-card-button">
          <muc-button
            class="add-card-muc-button"
            icon="arrow-right"
            variant="primary"
            tabindex="-1"
            >{{ t("arrangeAppointment") }}</muc-button
          >
        </div>
      </div>
    </a>
  </div>
</template>

<script setup lang="ts">
import { MucButton } from "@muenchen/muc-patternlab-vue";

defineProps<{
  title: string;
  newAppointmentUrl: string;
  t: (key: string) => string;
}>();

defineSlots<{
  /**
   * Content beneath the heading shown as text.
   */
  content(): any;
}>();
</script>

<style scoped>
.add-card {
  cursor: pointer;
  border: solid 1px var(--color-neutrals-blue);
  border-bottom: solid 5px var(--color-brand-main-blue);
  transition: background-color ease-in 150ms;
  background-color: var(--color-neutrals-blue-xlight);
}

.add-card a {
  text-decoration: none !important;
  color: var(--color-neutrals-grey) !important;
  display: block;
  height: 100%;
}

.add-card:hover {
  background-color: #e5eef5;
}

.add-card:hover .add-card-muc-button {
  background-color: #004376;
}

.add-card-content {
  text-align: center;
  padding: 32px 24px;
}

.add-card-header {
  margin-bottom: 16px;
}

.add-card-button {
  margin-top: 24px;
}
</style>
