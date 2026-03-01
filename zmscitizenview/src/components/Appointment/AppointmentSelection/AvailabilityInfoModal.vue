<template>
  <muc-modal
    :open="open"
    @close="emit('update:open', false)"
    aria-labelledby="modalTitle"
    :closeAriaLabel="closeAriaLabel || 'Dialog schließen'"
  >
    <template #title>
      <h2
        id="modalTitle"
        class="modal-title"
      >
        {{ t ? t("newAppointmentsInfoLink") : "Wann gibt es neue Termine?" }}
      </h2>
    </template>

    <template #body>
      <div v-html="sanitizedHtml"></div>
    </template>

    <template #footer>
      <muc-button
        variant="secondary"
        @click="emit('update:open', false)"
      >
        {{ t ? t("close") : "Schließen" }}
      </muc-button>
    </template>
  </muc-modal>
</template>

<script setup lang="ts">
import { MucButton, MucModal } from "@muenchen/muc-patternlab-vue";
import { computed } from "vue";

import { sanitizeHtml } from "@/utils/sanitizeHtml";

const props = defineProps<{
  open: boolean;
  html: string;
  closeAriaLabel?: string;
  dialogAriaLabel?: string;
  t?: (key: string) => string;
}>();

const emit = defineEmits<{
  (e: "update:open", value: boolean): void;
}>();

const sanitizedHtml = computed(() => sanitizeHtml(props.html ?? ""));
</script>
