<template>
  <muc-modal
    :open="open"
    :close-aria-label="closeAriaLabel"
    @close="handleClose"
    @cancel="handleClose"
  >
    <template #title>
      {{ t("newAppointmentsInfoLink") }}
    </template>

    <template #body>
      <div v-html="sanitizedHtml"></div>
    </template>
  </muc-modal>
</template>

<script setup lang="ts">
import { MucModal } from "@muenchen/muc-patternlab-vue";
import { computed } from "vue";

import { sanitizeHtml } from "@/utils/sanitizeHtml";

const props = defineProps<{
  open: boolean;
  html: string;
  closeAriaLabel: string;
  t: (key: string) => string;
}>();

const emit = defineEmits<(e: "update:open", value: boolean) => void>();

const sanitizedHtml = computed(() => sanitizeHtml(props.html ?? ""));

const handleClose = () => {
  emit("update:open", false);
};
</script>
