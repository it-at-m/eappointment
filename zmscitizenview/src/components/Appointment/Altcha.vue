<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from "vue";

// Importing altcha package will introduce a new element <altcha-widget>
import "altcha";

const altchaWidget = ref<HTMLElement | null>(null);
const props = defineProps({
  payload: {
    type: String,
    required: false,
  },
});
const emit = defineEmits<{
  (e: "update:payload", value: string): void;
}>();
const internalValue = ref(props.payload);

watch(internalValue, (v) => {
  emit("update:payload", v || "");
});

const onStateChange = (ev: CustomEvent | Event) => {
  if ("detail" in ev) {
    const { payload, state } = ev.detail;
    if (state === "verified" && payload) {
      internalValue.value = payload;
    } else {
      internalValue.value = "";
    }
  }
};

onMounted(() => {
  if (altchaWidget.value) {
    altchaWidget.value.addEventListener("statechange", onStateChange);
  }
});

onUnmounted(() => {
  if (altchaWidget.value) {
    altchaWidget.value.removeEventListener("statechange", onStateChange);
  }
});
</script>

<template>
  <!-- Configure your `challengeurl` and remove the `test` attribute, see docs: https://altcha.org/docs/website-integration/#using-altcha-widget -->
  <altcha-widget
    challengeurl="https://captcha-k.muenchen.de/api/v1/captcha/challenge"
    ref="altchaWidget"
    debug
  ></altcha-widget>
</template>
