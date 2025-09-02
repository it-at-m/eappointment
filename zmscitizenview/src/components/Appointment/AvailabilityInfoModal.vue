<template>
  <div
    v-if="show"
    class="modal fade show"
    style="display: block"
    role="dialog"
    aria-modal="true"
    :aria-label="dialogAriaLabel"
    @click.self="$emit('close')"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h2 class="standard-headline">
            {{
              t ? t("newAppointmentsInfoLink") : "Wann gibt es neue Termine?"
            }}
          </h2>
          <button
            type="button"
            class="modal-button-close"
            @click="$emit('close')"
            :aria-label="closeAriaLabel || 'Dialog schliessen'"
          >
            <svg
              aria-hidden="true"
              class="icon"
            >
              <use xlink:href="#icon-close"></use>
            </svg>
          </button>
        </div>
        <div class="modal-body">
          <div v-html="sanitizedHtml"></div>
        </div>
      </div>
    </div>
  </div>
  <div
    v-if="show"
    class="modal-backdrop fade show"
    @click="$emit('close')"
  ></div>
</template>

<script setup lang="ts">
import { computed } from "vue";

import { sanitizeHtml } from "@/utils/sanitizeHtml";

const props = defineProps<{
  show: boolean;
  html: string;
  closeAriaLabel?: string;
  dialogAriaLabel?: string;
  t?: (key: string) => string;
}>();

const sanitizedHtml = computed(() => sanitizeHtml(props.html ?? ""));

defineEmits<{
  (e: "close"): void;
}>();
</script>

<style>
.modal-header {
  padding: 1rem 1.5rem 0 1.5rem !important;
  position: relative;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
}
.modal-header h2 {
  font-size: 1.75rem !important;
}

.modal-body {
  padding: 0 1.5rem 1.5rem 1.5rem !important;
}

.modal-body h3 {
  margin-top: 1.5rem !important;
  margin-bottom: 0.5rem !important;
  color: var(--color-neutrals-blue-dark);
  font-weight: 600;
}

.modal-body div {
  margin-bottom: 1rem;
}

.modal-body div:last-child {
  margin-bottom: 0;
}

.modal-header .standard-headline {
  margin: 0 0 1.5rem 0 !important;
  color: var(--color-neutrals-blue-dark);
  font-size: 1.5rem;
  font-weight: 600;
  line-height: 1.2;
  flex: 1;
}

.modal-button-close {
  font-size: 1.75rem !important;
  margin: 0 0 0 0 !important;
  z-index: 10;
  background: none;
  border: none;
  cursor: pointer;
  flex-shrink: 0;
  margin-left: 1rem;
}
</style>
