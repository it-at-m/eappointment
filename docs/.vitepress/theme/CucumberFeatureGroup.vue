<script setup>
import { useData } from "vitepress";
import { computed, ref, watch } from "vue";

import {
  cucumberCatalogEntries,
  cucumberFeatureVisible,
  cucumberGroupHasMatch,
  cucumberIsLegacyBuergeransicht,
  cucumberSearchQuery,
  cucumberUsesRemoteCatalog,
  openCucumberFeatureId,
} from "./cucumberAccordion.js";

const props = defineProps({
  testType: {
    type: String,
    required: true,
  },
  module: {
    type: String,
    default: "",
  },
  remote: {
    type: Boolean,
    default: false,
  },
});

const { lang } = useData();

const isDe = computed(() => lang.value === "de");

const isVisible = computed(() => {
  if (props.remote !== cucumberUsesRemoteCatalog()) {
    return false;
  }
  return cucumberGroupHasMatch(props.testType, props.module);
});

const isCollapsible = computed(() =>
  cucumberIsLegacyBuergeransicht(props.testType, props.module)
);

const heading = computed(() =>
  isDe.value ? "buergeransicht (veraltet)" : "buergeransicht (deprecated)"
);

const visibleCount = computed(
  () =>
    cucumberCatalogEntries().filter(
      (entry) =>
        entry.testType === props.testType &&
        entry.module === props.module &&
        cucumberFeatureVisible(entry)
    ).length
);

const countLabel = computed(() => {
  const count = visibleCount.value;
  if (isDe.value) {
    return count === 1 ? "1 Feature" : `${count} Features`;
  }
  return count === 1 ? "1 feature" : `${count} features`;
});

const toggleLabel = computed(() => `${heading.value}. ${countLabel.value}`);

const userOpen = ref(false);

const forceOpen = computed(() => {
  if (cucumberSearchQuery.value.trim()) {
    return true;
  }
  const openId = openCucumberFeatureId.value;
  if (!openId) {
    return false;
  }
  return cucumberCatalogEntries().some(
    (entry) =>
      entry.id === openId &&
      entry.testType === props.testType &&
      entry.module === props.module
  );
});

const isOpen = computed(() => forceOpen.value || userOpen.value);

watch(forceOpen, (open) => {
  if (open) {
    userOpen.value = true;
  }
});

const onToggle = () => {
  userOpen.value = !isOpen.value;
};
</script>

<template>
  <div
    v-if="isVisible"
    class="cucumber-feature-group"
    :class="{ 'cucumber-feature-group--collapse': isCollapsible }"
  >
    <template v-if="isCollapsible">
      <h3 class="cucumber-module-collapse__heading">
        <button
          type="button"
          class="cucumber-module-collapse__toggle"
          :aria-expanded="isOpen"
          :aria-label="toggleLabel"
          @click="onToggle"
        >
          <span
            class="cucumber-module-collapse__chevron"
            :class="{ 'cucumber-module-collapse__chevron--open': isOpen }"
            aria-hidden="true"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="16"
              height="16"
              viewBox="0 0 16 16"
              fill="none"
            >
              <path
                d="M6 3.5 11 8l-5 4.5"
                stroke="currentColor"
                stroke-width="1.75"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
          </span>
          <span class="cucumber-module-collapse__title">{{ heading }}</span>
          <span class="cucumber-module-collapse__count">{{ countLabel }}</span>
        </button>
      </h3>
      <div
        v-show="isOpen"
        class="cucumber-module-collapse__body"
      >
        <slot />
      </div>
    </template>
    <template v-else>
      <slot />
    </template>
  </div>
</template>

<style scoped>
.cucumber-feature-group--collapse {
  margin: 0 0 1.25rem;
  overflow: hidden;
  background: var(--vp-c-bg-soft);
  border: 1px solid var(--vp-c-divider);
  border-radius: 10px;
}

.cucumber-module-collapse__heading {
  margin: 0;
  padding: 0;
  border: 0;
  font-size: 1.1rem;
  font-weight: 600;
  line-height: 1.4;
}

.cucumber-module-collapse__toggle {
  display: flex;
  gap: 0.65rem;
  align-items: center;
  width: 100%;
  padding: 0.75rem 0.9rem;
  color: inherit;
  text-align: left;
  cursor: pointer;
  background: transparent;
  border: 0;
  font: inherit;
}

.cucumber-module-collapse__toggle:hover {
  background: var(--vp-c-bg-alt);
}

.cucumber-module-collapse__toggle:focus-visible {
  outline: 2px solid var(--vp-c-brand-1);
  outline-offset: -2px;
}

.cucumber-module-collapse__chevron {
  display: flex;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  width: 1.25rem;
  height: 1.25rem;
  color: var(--vp-c-text-2);
  transition: transform 0.15s ease;
}

.cucumber-module-collapse__chevron--open {
  transform: rotate(90deg);
}

@media (prefers-reduced-motion: reduce) {
  .cucumber-module-collapse__chevron {
    transition: none;
  }
}

.cucumber-module-collapse__title {
  flex: 1 1 auto;
  min-width: 0;
}

.cucumber-module-collapse__count {
  flex-shrink: 0;
  color: var(--vp-c-text-2);
  font-size: 0.85rem;
  font-weight: 500;
}

.cucumber-module-collapse__body {
  padding: 0 0.85rem 0.85rem;
}

.cucumber-module-collapse__body :deep(h3) {
  display: none;
}

.cucumber-module-collapse__body :deep(.cucumber-feature) {
  background: var(--vp-c-bg);
}
</style>
