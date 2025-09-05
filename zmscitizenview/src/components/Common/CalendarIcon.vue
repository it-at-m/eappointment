<template>
  <div
    style="padding-right: 16px; font-size: 32px"
    class="m-teaser-vertical m-teaser-vertical-event"
  >
    <div class="m-teaser-vertical__date-range">
      <time class="m-teaser-vertical__date-range__item">
        <span class="m-teaser-vertical__date-range__item__day">{{
          formatDay()
        }}</span>
        <span class="m-teaser-vertical__date-range__item__month">{{
          formatMonth()
        }}</span>
      </time>
    </div>
  </div>
</template>

<script setup lang="ts">
const props = defineProps<{
  timestamp: number;
}>();

const formatDay = () => {
  const date = new Date(props.timestamp * 1000);
  return new Intl.DateTimeFormat("de-DE", { day: "numeric" }).format(date);
};

const formatMonth = () => {
  const date = new Date(props.timestamp * 1000);
  return new Intl.DateTimeFormat("de-DE", { month: "short" }).format(date);
};
</script>

<style scoped>
.m-teaser-vertical {
  border-bottom: 0 !important;
}

.m-teaser-vertical__date-range {
  position: unset !important;
  border-top: 4px solid #005a9f !important;
}
</style>
