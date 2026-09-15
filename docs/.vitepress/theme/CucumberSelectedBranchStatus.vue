<script setup>
import { useData } from "vitepress";
import { computed } from "vue";

import {
  cucumberBadgeUrl,
  cucumberLatestWorkflowRun,
  cucumberLatestWorkflowRunState,
  cucumberSelectedBranch,
  cucumberWorkflowUrl,
  formatBerlinDateTime,
} from "./cucumberAccordion.js";

const { lang } = useData();

const isDe = computed(() => lang.value === "de");

const branch = computed(() => cucumberSelectedBranch.value);

const sectionLabel = computed(() =>
  isDe.value ? "Status des gewählten Branch" : "Selected branch status"
);

const workflowHref = computed(() => cucumberWorkflowUrl(branch.value));

const badgeSrc = computed(() => cucumberBadgeUrl(branch.value));

const run = computed(() => cucumberLatestWorkflowRun.value);

const formattedTime = computed(() => {
  const iso = run.value?.run_started_at || run.value?.created_at;
  if (!iso) {
    return "";
  }
  return formatBerlinDateTime(iso, isDe.value ? "de" : "en");
});

const runHref = computed(() => run.value?.html_url || workflowHref.value);

const runLabel = computed(() => {
  const name = branch.value;
  const state = cucumberLatestWorkflowRunState.value;
  if (state === "loading" || state === "idle") {
    return isDe.value
      ? `Letzter Lauf auf ${name} wird geladen…`
      : `Loading last run on ${name}…`;
  }
  if (state === "empty") {
    return isDe.value
      ? `Kein Lauf auf ${name} gefunden`
      : `No run on ${name} found`;
  }
  if (state === "error") {
    return isDe.value
      ? `Letzter Lauf auf ${name} nicht verfügbar`
      : `Last run on ${name} unavailable`;
  }
  return isDe.value
    ? `Letzter Lauf auf ${name}: ${formattedTime.value}`
    : `Last run on ${name}: ${formattedTime.value}`;
});
</script>

<template>
  <section
    class="cucumber-branch-status"
    :aria-label="sectionLabel"
  >
    <a
      :href="workflowHref"
      target="_blank"
      rel="noopener noreferrer"
    >
      <img
        :src="badgeSrc"
        :alt="`zmsautomation CI status on ${branch}`"
      />
    </a>
    <p class="cucumber-branch-status__run">
      <a
        v-if="runHref"
        :href="runHref"
        target="_blank"
        rel="noopener noreferrer"
        >{{ runLabel }}</a
      >
      <span v-else>{{ runLabel }}</span>
    </p>
  </section>
</template>

<style scoped>
.cucumber-branch-status {
  display: flex;
  flex-wrap: wrap;
  gap: 0.65rem 1rem;
  align-items: center;
  margin-top: 0.85rem;
  padding: 0.75rem 0 0;
  border-top: 1px solid var(--vp-c-divider);
}

.cucumber-branch-status img {
  display: block;
  height: 20px;
}

.cucumber-branch-status__run {
  margin: 0;
  color: var(--vp-c-text-2);
  font-size: 0.9rem;
}
</style>
