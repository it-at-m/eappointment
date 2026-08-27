<script setup>
import { useData } from "vitepress";
import { computed, onMounted, watch } from "vue";

import {
  closeCucumberFeatureIfHidden,
  cucumberCatalogEntries,
  cucumberFeatureVisible,
  cucumberSearchQuery,
  cucumberStatusFilter,
  ensureCucumberRunStatus,
  toggleCucumberStatusFilter,
} from "./cucumberAccordion.js";
import CucumberStatusIcon from "./CucumberStatusIcon.vue";

const { lang } = useData();

const isDe = computed(() => lang.value === "de");

const placeholder = computed(() =>
  isDe.value
    ? "Titel oder Tags suchen, z. B. @ZMSKVR-1046"
    : "Search titles or tags, e.g. @ZMSKVR-1046"
);

const statusFilterLabel = computed(() =>
  isDe.value ? "Nach Status filtern" : "Filter by status"
);

const statusOptions = computed(() => [
  {
    status: "passed",
    label: isDe.value ? "Bestanden" : "Passed",
  },
  {
    status: "failed",
    label: isDe.value ? "Fehlgeschlagen" : "Failed",
  },
  {
    status: "skipped",
    label: isDe.value ? "Übersprungen" : "Skipped",
  },
  {
    status: "none",
    label: isDe.value ? "Kein Ergebnis" : "No result",
  },
]);

const total = computed(() => cucumberCatalogEntries().length);

const matchCount = computed(
  () =>
    cucumberCatalogEntries().filter((entry) => cucumberFeatureVisible(entry))
      .length
);

const countLabel = computed(() => {
  const n = matchCount.value;
  const m = total.value;
  if (isDe.value) {
    return `${n} von ${m} Tests`;
  }
  return `${n} of ${m} tests`;
});

const hasActiveFilters = computed(
  () =>
    Boolean(cucumberSearchQuery.value) || cucumberStatusFilter.value.length > 0
);

const clearLabel = computed(() => (isDe.value ? "Zurücksetzen" : "Clear"));

const isStatusActive = (status) => cucumberStatusFilter.value.includes(status);

watch([cucumberSearchQuery, cucumberStatusFilter], () => {
  closeCucumberFeatureIfHidden();
});

const clearSearch = () => {
  cucumberSearchQuery.value = "";
  cucumberStatusFilter.value = [];
};

onMounted(() => {
  ensureCucumberRunStatus();
});
</script>

<template>
  <div class="cucumber-search">
    <label class="cucumber-search__label">
      <span class="visually-hidden">{{ placeholder }}</span>
      <input
        v-model="cucumberSearchQuery"
        type="search"
        class="cucumber-search__input"
        :placeholder="placeholder"
        :aria-label="placeholder"
      />
    </label>
    <div
      class="cucumber-search__filters"
      role="group"
      :aria-label="statusFilterLabel"
    >
      <button
        v-for="option in statusOptions"
        :key="option.status"
        type="button"
        class="cucumber-search__filter"
        :class="{
          'cucumber-search__filter--active': isStatusActive(option.status),
        }"
        :aria-pressed="isStatusActive(option.status)"
        :aria-label="option.label"
        :data-tooltip="option.label"
        @click="toggleCucumberStatusFilter(option.status)"
      >
        <CucumberStatusIcon :status="option.status" />
      </button>
    </div>
    <div class="cucumber-search__meta">
      <span>{{ countLabel }}</span>
      <button
        v-if="hasActiveFilters"
        type="button"
        class="cucumber-search__clear"
        @click="clearSearch"
      >
        {{ clearLabel }}
      </button>
    </div>
  </div>
</template>

<style scoped>
.cucumber-search {
  margin: 2.25rem 0 1.75rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--vp-c-divider);
}

.cucumber-search__label {
  display: block;
}

.cucumber-search__input {
  width: 100%;
  padding: 0.55rem 0.75rem;
  color: var(--vp-c-text-1);
  background: var(--vp-c-bg-soft);
  border: 1px solid var(--vp-c-divider);
  border-radius: 8px;
  font: inherit;
}

.cucumber-search__input:focus-visible {
  outline: 2px solid var(--vp-c-brand-1);
  outline-offset: 1px;
}

.cucumber-search__filters {
  display: flex;
  flex-wrap: wrap;
  gap: 0.4rem;
  margin-top: 0.65rem;
}

.cucumber-search__filter {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2rem;
  height: 2rem;
  padding: 0;
  cursor: pointer;
  background: var(--vp-c-bg-soft);
  border: 1px solid var(--vp-c-divider);
  border-radius: 8px;
}

.cucumber-search__filter:hover,
.cucumber-search__filter:focus-visible {
  background: var(--vp-c-bg-alt);
}

.cucumber-search__filter:focus-visible {
  outline: 2px solid var(--vp-c-brand-1);
  outline-offset: 1px;
}

.cucumber-search__filter--active {
  background: var(--vp-c-brand-soft);
  border-color: var(--vp-c-brand-1);
}

.cucumber-search__filter::after {
  position: absolute;
  top: calc(100% + 0.35rem);
  left: 50%;
  z-index: 4;
  width: max-content;
  padding: 0.28rem 0.5rem;
  color: var(--vp-c-bg);
  font-size: 0.75rem;
  font-weight: 500;
  line-height: 1.3;
  white-space: nowrap;
  pointer-events: none;
  content: attr(data-tooltip);
  background: var(--vp-c-text-1);
  border-radius: 6px;
  opacity: 0;
  transform: translateX(-50%);
}

.cucumber-search__filter:hover::after,
.cucumber-search__filter:focus-visible::after {
  opacity: 1;
}

.cucumber-search__meta {
  display: flex;
  gap: 0.75rem;
  align-items: center;
  margin-top: 0.45rem;
  color: var(--vp-c-text-2);
  font-size: 0.85rem;
}

.cucumber-search__clear {
  padding: 0;
  color: var(--vp-c-brand-1);
  cursor: pointer;
  background: none;
  border: 0;
  font: inherit;
}

.cucumber-search__clear:hover {
  text-decoration: underline;
}

.visually-hidden {
  position: absolute;
  width: 1px;
  height: 1px;
  overflow: hidden;
  clip: rect(0 0 0 0);
  white-space: nowrap;
}
</style>
