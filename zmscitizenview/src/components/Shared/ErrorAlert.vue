<script lang="ts" setup>
import {MucBanner} from "@muenchen/muc-patternlab-vue";

defineProps<{message: string}>()
</script>

<template>
  <muc-banner type="emergency">
    <p v-html="message"></p>
  </muc-banner>
</template>

