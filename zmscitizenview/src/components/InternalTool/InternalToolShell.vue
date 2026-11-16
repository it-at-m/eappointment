<template>
  <p
    v-if="!available"
    class="tool-unavailable"
  >
    {{ unavailable }}
  </p>
  <div
    v-else
    class="tool-page m-component m-component-form"
  >
    <svg
      class="tool-skyline"
      viewBox="0 0 1440 300"
      preserveAspectRatio="xMidYMax slice"
      aria-hidden="true"
    >
      <path
        fill="currentColor"
        d="M0 300V228H28L64 186L100 228H150V210H190V240H236V120C236 68 300 68 300 120V188H324V120C324 68 388 68 388 120V240H450V208H492V164H516V128L540 64L564 128V164H588V208H598V158H590V142H630V126H686V142H726V158H718V208H728V150C728 100 760 72 774 78V52H790V78C804 72 836 100 836 150V198H940V246H1168V124H1144V92H1168V40H1182V92H1206V124H1182V246H1260V220H1320V200L1364 168L1408 200H1440V300Z"
      />
      <path
        class="tool-skyline__arch"
        d="M618 300V224A40 40 0 0 1 698 224V300Z"
      />
      <g
        fill="none"
        stroke="currentColor"
        stroke-width="5"
        stroke-linecap="round"
      >
        <path d="M1018 246L1070 168L1122 246" />
        <circle
          cx="1070"
          cy="168"
          r="48"
        />
        <path
          d="M1070 124V212M1026 168H1114M1039 137L1101 199M1101 137L1039 199"
        />
        <circle
          cx="1070"
          cy="168"
          r="6"
          fill="currentColor"
          stroke="none"
        />
      </g>
    </svg>
    <div class="container">
      <div class="m-component__grid">
        <div class="m-component__column">
          <h1>{{ title }}</h1>
          <p class="tool-intro">
            {{ intro }}
          </p>
          <slot />
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
defineProps<{
  available: boolean;
  unavailable: string;
  title: string;
  intro: string;
}>();
</script>

<style scoped>
.tool-page {
  position: relative;
  box-sizing: border-box;
  width: 100%;
  min-height: 100vh;
  padding-top: 4.5rem;
  padding-bottom: 14rem;
  overflow: hidden;
  background-color: var(--color-neutrals-blue-xlight, #f2f6fa);
}

.tool-page > .container {
  position: relative;
  z-index: 1;
}

.tool-skyline__arch {
  fill: var(--color-neutrals-blue-xlight, #f2f6fa);
}

.tool-skyline {
  position: absolute;
  right: 0;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 16rem;
  color: color-mix(
    in srgb,
    var(--color-neutrals-blue-xlight, #f2f6fa) 62%,
    #4d6f8c
  );
  pointer-events: none;
}

.tool-unavailable {
  padding: 4.5rem 1.5rem 2rem;
}

.tool-intro {
  margin-top: 0;
  margin-bottom: 1.5rem;
}

.tool-page :deep(.m-component__grid),
.tool-page :deep(.m-component__column),
.tool-page :deep(.m-form) {
  min-width: 0;
  max-width: 100%;
}
</style>
