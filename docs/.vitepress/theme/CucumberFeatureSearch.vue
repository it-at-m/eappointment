<script setup>
import { useData } from "vitepress";
import { computed, onMounted, ref, watch } from "vue";

import {
  closeCucumberFeatureIfHidden,
  cucumberBranchOptions,
  cucumberCatalogEntries,
  cucumberCatalogLoadState,
  cucumberFeatureVisible,
  cucumberGithubRateLimited,
  cucumberSearchQuery,
  cucumberSelectedBranch,
  cucumberStatusFilter,
  cucumberUsesRemoteCatalog,
  ensureCucumberPageState,
  setCucumberSelectedBranch,
  toggleCucumberStatusFilter,
} from "./cucumberAccordion.js";
import CucumberSelectedBranchStatus from "./CucumberSelectedBranchStatus.vue";
import CucumberStatusIcon from "./CucumberStatusIcon.vue";

const { lang } = useData();

const isDe = computed(() => lang.value === "de");

const placeholder = computed(() =>
  isDe.value
    ? "Titel oder Tags suchen, z. B. @ZMSKVR-1046"
    : "Search titles or tags, e.g. @ZMSKVR-1046"
);

const branchLabel = computed(() => (isDe.value ? "Branch" : "Branch"));

const branchHint = computed(() =>
  isDe.value
    ? "Branch mit zmsautomation-Lauf suchen oder auswählen."
    : "Search or select a branch that has a zmsautomation run."
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

const branchDraft = ref(cucumberSelectedBranch.value);
const branchListOpen = ref(false);
const branchHighlight = ref(0);

const filteredBranches = computed(() => {
  const q = branchDraft.value.trim().toLowerCase();
  const names = cucumberBranchOptions.value;
  if (!q) {
    return names;
  }
  return names.filter((name) => name.toLowerCase().includes(q));
});

const rateLimitLabel = computed(() =>
  isDe.value
    ? "GitHub-API-Rate-Limit überschritten. Wechsle die IP-Adresse bzw. das Netzwerk und lade die Seite neu."
    : "GitHub API rate limit exceeded. Change IP address or network, then reload this page."
);

const catalogLoading = computed(
  () =>
    cucumberUsesRemoteCatalog() && cucumberCatalogLoadState.value === "loading"
);

const total = computed(() => cucumberCatalogEntries().length);

const matchCount = computed(
  () =>
    cucumberCatalogEntries().filter((entry) => cucumberFeatureVisible(entry))
      .length
);

const countLabel = computed(() => {
  if (catalogLoading.value) {
    return isDe.value
      ? `Tests von ${cucumberSelectedBranch.value} werden geladen…`
      : `Loading tests from ${cucumberSelectedBranch.value}…`;
  }
  if (
    cucumberUsesRemoteCatalog() &&
    cucumberCatalogLoadState.value === "error"
  ) {
    return isDe.value
      ? `Tests von ${cucumberSelectedBranch.value} konnten nicht geladen werden`
      : `Could not load tests from ${cucumberSelectedBranch.value}`;
  }
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

watch(cucumberSelectedBranch, (branch) => {
  branchDraft.value = branch;
});

watch(filteredBranches, () => {
  branchHighlight.value = 0;
});

watch(
  [cucumberSearchQuery, cucumberStatusFilter, cucumberSelectedBranch],
  () => {
    closeCucumberFeatureIfHidden();
  }
);

const applyBranch = () => {
  setCucumberSelectedBranch(branchDraft.value);
  branchListOpen.value = false;
};

const openBranchList = () => {
  branchListOpen.value = true;
};

const selectBranch = (name) => {
  branchDraft.value = name;
  applyBranch();
};

const onBranchBlur = () => {
  window.setTimeout(() => {
    if (!branchListOpen.value) {
      return;
    }
    applyBranch();
  }, 120);
};

const onBranchKeydown = (event) => {
  const names = filteredBranches.value;
  if (event.key === "ArrowDown") {
    event.preventDefault();
    if (!branchListOpen.value) {
      branchListOpen.value = true;
      return;
    }
    if (!names.length) {
      return;
    }
    branchHighlight.value = (branchHighlight.value + 1) % names.length;
    return;
  }
  if (event.key === "ArrowUp") {
    event.preventDefault();
    if (!branchListOpen.value || !names.length) {
      return;
    }
    branchHighlight.value =
      (branchHighlight.value - 1 + names.length) % names.length;
    return;
  }
  if (event.key === "Enter") {
    event.preventDefault();
    const highlighted = names[branchHighlight.value];
    if (branchListOpen.value && highlighted) {
      selectBranch(highlighted);
      return;
    }
    applyBranch();
    return;
  }
  if (event.key === "Escape") {
    branchListOpen.value = false;
    branchDraft.value = cucumberSelectedBranch.value;
  }
};

const clearSearch = () => {
  cucumberSearchQuery.value = "";
  cucumberStatusFilter.value = [];
};

onMounted(() => {
  ensureCucumberPageState();
  branchDraft.value = cucumberSelectedBranch.value;
});
</script>

<template>
  <div class="cucumber-search">
    <p
      v-if="cucumberGithubRateLimited"
      class="cucumber-search__rate-limit"
      role="status"
    >
      {{ rateLimitLabel }}
    </p>
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
    <div class="cucumber-search__branch">
      <span class="cucumber-search__branch-label">{{ branchLabel }}</span>
      <div class="cucumber-search__combobox">
        <input
          v-model="branchDraft"
          class="cucumber-search__input cucumber-search__input--branch"
          role="combobox"
          aria-autocomplete="list"
          aria-controls="cucumber-branch-options"
          :aria-expanded="branchListOpen"
          :aria-activedescendant="
            branchListOpen && filteredBranches[branchHighlight]
              ? `cucumber-branch-option-${branchHighlight}`
              : undefined
          "
          :aria-label="branchHint"
          :title="branchHint"
          autocomplete="off"
          spellcheck="false"
          @focus="openBranchList"
          @input="openBranchList"
          @blur="onBranchBlur"
          @keydown="onBranchKeydown"
        />
        <ul
          v-show="branchListOpen"
          id="cucumber-branch-options"
          class="cucumber-search__suggestions"
          role="listbox"
        >
          <li
            v-if="!filteredBranches.length"
            class="cucumber-search__suggestion cucumber-search__suggestion--empty"
          >
            {{ noBranchMatchLabel }}
          </li>
          <li
            v-for="(name, index) in filteredBranches"
            :id="`cucumber-branch-option-${index}`"
            :key="name"
            class="cucumber-search__suggestion"
            :class="{
              'cucumber-search__suggestion--active': index === branchHighlight,
            }"
            role="option"
            :aria-selected="index === branchHighlight"
            @mousedown.prevent="selectBranch(name)"
          >
            {{ name }}
          </li>
        </ul>
      </div>
    </div>
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
    <CucumberSelectedBranchStatus />
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
  overflow: visible;
  border-top: 1px solid var(--vp-c-divider);
}

.cucumber-search__rate-limit {
  margin: 0 0 0.85rem;
  padding: 0.7rem 0.85rem;
  color: var(--vp-c-text-1);
  background: var(--vp-c-caution-soft, var(--vp-c-bg-soft));
  border: 1px solid var(--vp-c-caution-1, var(--vp-c-divider));
  border-radius: 8px;
  font-size: 0.9rem;
  line-height: 1.45;
}

.cucumber-search__label {
  display: block;
}

.cucumber-search__branch {
  display: flex;
  gap: 0.65rem;
  align-items: center;
  margin-top: 0.65rem;
}

.cucumber-search__branch-label {
  flex-shrink: 0;
  color: var(--vp-c-text-2);
  font-size: 0.85rem;
}

.cucumber-search__combobox {
  position: relative;
  flex: 1 1 auto;
  min-width: 0;
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

.cucumber-search__input--branch {
  font-family: var(--vp-font-family-mono);
  font-size: 0.9rem;
}

.cucumber-search__input:focus-visible {
  outline: 2px solid var(--vp-c-brand-1);
  outline-offset: 1px;
}

.cucumber-search__combobox .cucumber-search__suggestions {
  position: absolute;
  top: calc(100% + 0.3rem);
  right: 0;
  left: 0;
  z-index: 30;
  max-height: 16rem;
  margin: 0;
  padding: 0.25rem 0;
  overflow-x: hidden;
  overflow-y: auto;
  list-style: none;
  background: var(--vp-c-bg);
  border: 1px solid var(--vp-c-divider);
  border-radius: 8px;
  box-shadow: var(--vp-shadow-2, 0 8px 24px rgb(0 0 0 / 12%));
}

.cucumber-search__suggestion {
  padding: 0.4rem 0.75rem;
  color: var(--vp-c-text-1);
  cursor: pointer;
  font-family: var(--vp-font-family-mono);
  font-size: 0.85rem;
  line-height: 1.35;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.cucumber-search__suggestion--empty {
  color: var(--vp-c-text-2);
  cursor: default;
}

.cucumber-search__suggestion--active,
.cucumber-search__suggestion:hover {
  background: var(--vp-c-bg-alt);
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
