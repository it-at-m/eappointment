<script setup>
import { useData } from "vitepress";
import { computed } from "vue";

import {
  cucumberCatalogLoadState,
  cucumberGroupedCatalog,
  cucumberIsLegacyBuergeransicht,
  cucumberSelectedBranch,
  cucumberUsesRemoteCatalog,
} from "./cucumberAccordion.js";
import CucumberFeatureGroup from "./CucumberFeatureGroup.vue";
import CucumberFeatureRow from "./CucumberFeatureRow.vue";

const { lang } = useData();

const isDe = computed(() => lang.value === "de");

const isRemote = computed(() => cucumberUsesRemoteCatalog());

const groups = computed(() => cucumberGroupedCatalog());

const loadingLabel = computed(() =>
  isDe.value
    ? `Feature-Dateien von ${cucumberSelectedBranch.value} werden geladen…`
    : `Loading feature files from ${cucumberSelectedBranch.value}…`
);

const errorLabel = computed(() =>
  isDe.value
    ? `Feature-Dateien von ${cucumberSelectedBranch.value} konnten nicht geladen werden. Branch prüfen oder später erneut versuchen.`
    : `Could not load feature files from ${cucumberSelectedBranch.value}. Check the branch name or try again later.`
);

const emptyLabel = computed(() =>
  isDe.value
    ? `Keine .feature-Dateien auf ${cucumberSelectedBranch.value} gefunden.`
    : `No .feature files found on ${cucumberSelectedBranch.value}.`
);

const deprecatedNote = computed(() =>
  isDe.value
    ? "Veraltet: Diese Szenarien adressieren das alte buergeransicht-Frontend aus `it-at-m/eappointment-buergeransicht` und werden für `zmscitizenview` nicht mehr verwendet."
    : "Deprecated: These scenarios target the legacy buergeransicht frontend from `it-at-m/eappointment-buergeransicht` and are not used for `zmscitizenview`."
);

const moduleTitle = (testType, module) => {
  if (cucumberIsLegacyBuergeransicht(testType, module)) {
    return isDe.value ? `${module} (veraltet)` : `${module} (deprecated)`;
  }
  return module;
};

const isLegacyModule = cucumberIsLegacyBuergeransicht;
</script>

<template>
  <div
    v-if="isRemote"
    class="cucumber-remote-catalog"
  >
    <p
      v-if="cucumberCatalogLoadState === 'loading'"
      class="cucumber-remote-catalog__status"
    >
      {{ loadingLabel }}
    </p>
    <p
      v-else-if="cucumberCatalogLoadState === 'error'"
      class="cucumber-remote-catalog__status"
    >
      {{ errorLabel }}
    </p>
    <p
      v-else-if="!groups.length"
      class="cucumber-remote-catalog__status"
    >
      {{ emptyLabel }}
    </p>
    <template v-else>
      <CucumberFeatureGroup
        v-for="group in groups"
        :key="group.testType"
        remote
        :test-type="group.testType"
      >
        <h2>{{ group.testType.toUpperCase() }}</h2>
        <CucumberFeatureGroup
          v-for="mod in group.modules"
          :key="`${group.testType}-${mod.module}`"
          remote
          :test-type="group.testType"
          :module="mod.module"
        >
          <h3 v-if="!isLegacyModule(group.testType, mod.module)">
            {{ moduleTitle(group.testType, mod.module) }}
          </h3>
          <blockquote v-if="isLegacyModule(group.testType, mod.module)">
            {{ deprecatedNote }}
          </blockquote>
          <CucumberFeatureRow
            v-for="entry in mod.entries"
            :id="entry.id"
            :key="entry.id"
          />
        </CucumberFeatureGroup>
      </CucumberFeatureGroup>
    </template>
  </div>
</template>

<style scoped>
.cucumber-remote-catalog__status {
  color: var(--vp-c-text-2);
}
</style>
