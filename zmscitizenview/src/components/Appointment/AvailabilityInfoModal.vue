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
</style>
