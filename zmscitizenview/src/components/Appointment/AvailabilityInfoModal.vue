<template>
  <div
    v-if="show"
    class="modal fade show"
    style="display: block"
    role="dialog"
    aria-modal="true"
    :aria-labelledby="undefined"
    @click.self="$emit('close')"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <button
            type="button"
            class="modal-button-close"
            @click="$emit('close')"
            aria-label="Dialog schliessen"
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
          <div v-html="html"></div>
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
defineProps<{
  show: boolean;
  html: string;
}>();
defineEmits<{
  (e: "close"): void;
}>();
</script>

<style scoped>
.modal-body {
  padding-bottom: 24px;
}

.modal-header {
  position: relative;
  padding: 0;
  border: none;
  background: none;
}

.modal-button-close {
  position: absolute;
  top: 0;
  right: 0;
  z-index: 10;
  background: none;
  border: none;
  padding: 0;
  cursor: pointer;
}
</style>

<style>
.modal-body h3 {
  margin-top: 1.5rem !important;
  margin-bottom: 0.5rem !important;
  color: var(--color-neutrals-blue-dark);
  font-size: 1.125rem;
  font-weight: 600;
}

.modal-body h3.first-provider {
  margin-top: 0 !important;
}

.modal-body div {
  margin-bottom: 1rem;
}

.modal-body div:last-child {
  margin-bottom: 0;
}
</style>
