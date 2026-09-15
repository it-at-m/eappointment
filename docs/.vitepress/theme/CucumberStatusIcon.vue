<script setup>
defineProps({
  status: {
    type: String,
    required: true,
  },
});
</script>

<template>
  <svg
    class="cucumber-status-icon"
    :class="`cucumber-status-icon--${status}`"
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 16 16"
    aria-hidden="true"
  >
    <template v-if="status === 'passed'">
      <circle
        cx="8"
        cy="8"
        r="8"
        fill="currentColor"
      />
      <path
        d="M4.4 8.3 6.8 10.7 11.6 5.4"
        fill="none"
        stroke="#fff"
        stroke-width="1.75"
        stroke-linecap="round"
        stroke-linejoin="round"
      />
    </template>
    <template v-else-if="status === 'failed'">
      <circle
        cx="8"
        cy="8"
        r="8"
        fill="currentColor"
      />
      <path
        d="M5.2 5.2 10.8 10.8M10.8 5.2 5.2 10.8"
        fill="none"
        stroke="#fff"
        stroke-width="1.75"
        stroke-linecap="round"
      />
    </template>
    <template v-else-if="status === 'skipped'">
      <circle
        cx="8"
        cy="8"
        r="8"
        fill="currentColor"
      />
      <path
        d="M4.6 8h6.8"
        fill="none"
        stroke="#fff"
        stroke-width="1.75"
        stroke-linecap="round"
      />
    </template>
    <circle
      v-else
      cx="8"
      cy="8"
      r="7"
      fill="none"
      stroke="currentColor"
      stroke-width="1.5"
    />
  </svg>
</template>

<style scoped>
.cucumber-status-icon {
  display: block;
  width: 1rem;
  height: 1rem;
}

.cucumber-status-icon--passed {
  color: #1a7f37;
}

.cucumber-status-icon--failed {
  color: #cf222e;
}

.cucumber-status-icon--skipped,
.cucumber-status-icon--none {
  color: var(--vp-c-text-3);
}
</style>
