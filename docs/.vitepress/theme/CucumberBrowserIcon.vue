<script setup>
defineProps({
  browser: {
    type: String,
    required: true,
  },
});
</script>

<template>
  <svg
    class="cucumber-browser-icon"
    :class="`cucumber-browser-icon--${browser}`"
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 16 16"
    aria-hidden="true"
  >
    <template v-if="browser === 'firefox'">
      <circle
        cx="8"
        cy="8"
        r="8"
        fill="#ff7139"
      />
      <path
        d="M3.4 9.2c.7-2.6 2.5-4.4 4.8-4.8 1.4-.2 2.5.3 3.3 1.2-.8-.1-1.6 0-2.3.4 1.5.1 2.6 1.1 3.1 2.4-.7-.5-1.5-.7-2.4-.6 1.2.6 1.9 1.7 2 3.1-1.2-.9-2.5-1.3-4-1.1-1.6.3-2.8 1.3-3.4 2.7-.7-1-.9-2.1-1.1-3.3Z"
        fill="#fff"
      />
    </template>
    <template v-else-if="browser === 'edge'">
      <circle
        cx="8"
        cy="8"
        r="8"
        fill="#0078d4"
      />
      <path
        d="M4 9.4c.6-2.3 2.4-3.8 4.6-3.8 1.7 0 3 .8 3.6 2.1-1.3-.9-2.8-1-4.2-.3 1.8.1 3.2 1.3 3.5 3.1H5.4c.2 1.1 1.2 1.9 2.6 1.9 1.1 0 2-.4 2.6-1.1v1.3C9.8 13.4 8.6 14 7.2 14 5.2 14 3.7 12.6 3.4 10.6c0-.4.1-.8.2-1.2H4Z"
        fill="#fff"
      />
    </template>
    <template v-else>
      <circle
        cx="8"
        cy="8"
        r="8"
        fill="#de3a3a"
      />
      <circle
        cx="8"
        cy="8"
        r="3.15"
        fill="#fff"
      />
      <circle
        cx="8"
        cy="8"
        r="2.15"
        fill="#4c8bf5"
      />
      <path
        d="M8 5.1h6.2"
        fill="none"
        stroke="#ffce3a"
        stroke-width="2.1"
        stroke-linecap="round"
      />
      <path
        d="M5.5 12.4 9.7 5.2"
        fill="none"
        stroke="#1da462"
        stroke-width="2.1"
        stroke-linecap="round"
      />
    </template>
  </svg>
</template>

<style scoped>
.cucumber-browser-icon {
  display: block;
  flex-shrink: 0;
  width: 1rem;
  height: 1rem;
}
</style>
