<script setup>
import { useData } from "vitepress";
import { computed, watch } from "vue";

import {
  closeCucumberFeatureIfHidden,
  cucumberCatalogEntries,
  cucumberFeatureMatches,
  cucumberSearchQuery,
} from "./cucumberAccordion.js";

const { lang } = useData();

const isDe = computed(() => lang.value === "de");

const placeholder = computed(() =>
  isDe.value
    ? "Titel oder Tags suchen, z. B. @ZMSKVR-1046"
    : "Search titles or tags, e.g. @ZMSKVR-1046"
);

const total = computed(() => cucumberCatalogEntries().length);

const matchCount = computed(
  () =>
    cucumberCatalogEntries().filter((entry) =>
      cucumberFeatureMatches(entry, cucumberSearchQuery.value)
    ).length
);

const countLabel = computed(() => {
  const n = matchCount.value;
  const m = total.value;
  if (isDe.value) {
    return `${n} von ${m} Tests`;
  }
  return `${n} of ${m} tests`;
});

const clearLabel = computed(() => (isDe.value ? "Zurücksetzen" : "Clear"));

watch(cucumberSearchQuery, () => {
  closeCucumberFeatureIfHidden();
});

const clearSearch = () => {
  cucumberSearchQuery.value = "";
};
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
    <div class="cucumber-search__meta">
      <span>{{ countLabel }}</span>
      <button
        v-if="cucumberSearchQuery"
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
  margin: 1.25rem 0 1.75rem;
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
