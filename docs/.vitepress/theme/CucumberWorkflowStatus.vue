<script setup>
import { useData } from "vitepress";
import { computed, onMounted, ref } from "vue";

import {
  ZMSAUTOMATION_BADGE_URL,
  ZMSAUTOMATION_SCHEDULED_RUNS_API,
  ZMSAUTOMATION_WORKFLOW_URL,
} from "./cucumberAccordion.js";

const { lang } = useData();

const isDe = computed(() => lang.value === "de");

const loadState = ref("loading");
const run = ref(null);

onMounted(async () => {
  try {
    const response = await fetch(ZMSAUTOMATION_SCHEDULED_RUNS_API, {
      headers: {
        Accept: "application/vnd.github+json",
        "X-GitHub-Api-Version": "2022-11-28",
      },
    });
    if (!response.ok) {
      throw new Error(String(response.status));
    }
    const data = await response.json();
    const latest = Array.isArray(data.workflow_runs)
      ? data.workflow_runs[0]
      : null;
    if (!latest) {
      loadState.value = "empty";
      return;
    }
    run.value = latest;
    loadState.value = "ready";
  } catch {
    loadState.value = "error";
  }
});

const formattedTime = computed(() => {
  const iso = run.value?.run_started_at || run.value?.created_at;
  if (!iso) {
    return "";
  }
  return new Intl.DateTimeFormat(isDe.value ? "de-DE" : "en-GB", {
    dateStyle: "medium",
    timeStyle: "short",
    timeZone: "Europe/Berlin",
    timeZoneName: "short",
  }).format(new Date(iso));
});

const scheduleLabel = computed(() => {
  if (loadState.value === "loading") {
    return isDe.value
      ? "Letzter geplanter Lauf wird geladen…"
      : "Loading last scheduled run…";
  }
  if (loadState.value === "empty") {
    return isDe.value
      ? "Kein geplanter Lauf auf next gefunden"
      : "No scheduled run on next found";
  }
  if (loadState.value === "error") {
    return isDe.value
      ? "Letzter geplanter Lauf nicht verfügbar"
      : "Last scheduled run unavailable";
  }
  return isDe.value
    ? `Letzter geplanter Lauf auf next: ${formattedTime.value}`
    : `Last scheduled run on next: ${formattedTime.value}`;
});
</script>

<template>
  <div class="cucumber-workflow-status">
    <a
      :href="ZMSAUTOMATION_WORKFLOW_URL"
      target="_blank"
      rel="noopener noreferrer"
    >
      <img
        :src="ZMSAUTOMATION_BADGE_URL"
        alt="zmsautomation CI status"
      />
    </a>
    <p class="cucumber-workflow-status__run">
      <a
        v-if="run?.html_url"
        :href="run.html_url"
        target="_blank"
        rel="noopener noreferrer"
        >{{ scheduleLabel }}</a
      >
      <span v-else>{{ scheduleLabel }}</span>
    </p>
  </div>
</template>

<style scoped>
.cucumber-workflow-status {
  display: flex;
  flex-wrap: wrap;
  gap: 0.65rem 1rem;
  align-items: center;
  margin: 0.35rem 0 1.35rem;
}

.cucumber-workflow-status img {
  display: block;
  height: 20px;
}

.cucumber-workflow-status__run {
  margin: 0;
  color: var(--vp-c-text-2);
  font-size: 0.9rem;
}
</style>
