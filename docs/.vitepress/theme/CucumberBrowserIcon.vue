<script setup>
defineProps({
  browser: {
    type: String,
    required: true,
  },
});

const iconClass = {
  chrome: "fa-brands fa-chrome",
  firefox: "fa-brands fa-firefox-browser",
  edge: "fa-brands fa-edge",
};
</script>

<template>
  <i
    class="cucumber-browser-icon"
    :class="iconClass[browser] || 'fa-brands fa-chrome'"
    aria-hidden="true"
  />
</template>

<style scoped>
.cucumber-browser-icon {
  display: inline-flex;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  width: 1.15em;
  font-size: 1rem;
  line-height: 1;
}
</style>
