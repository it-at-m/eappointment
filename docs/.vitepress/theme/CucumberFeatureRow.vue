<script setup>
import { useData } from "vitepress";
import { computed, onMounted, onUnmounted, ref } from "vue";

import {
  cucumberFeatureMeta,
  cucumberFeatureRunResult,
  cucumberFeatureSourceUrl,
  cucumberFeatureVisible,
  cucumberGhRunCommand,
  cucumberRunTagExpression,
  cucumberSelectedBranch,
  cucumberWorkflowUrl,
  ensureCucumberHashListener,
  ensureCucumberPageState,
  formatBerlinDateTime,
  openCucumberFeatureId,
  syncCucumberFeatureFromHash,
  toggleCucumberFeature,
} from "./cucumberAccordion.js";
import CucumberStatusIcon from "./CucumberStatusIcon.vue";

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
});

const { lang } = useData();

const isDe = computed(() => lang.value === "de");

const meta = computed(() => {
  const entry = cucumberFeatureMeta(props.id);
  if (entry) {
    return entry;
  }
  return {
    fileName: props.id,
    title: props.id,
    tags: [],
    scenarioCount: 0,
    sourceUrl: "",
    rel: "",
    body: "",
  };
});

const sourceHref = computed(
  () =>
    cucumberFeatureSourceUrl(meta.value.rel, cucumberSelectedBranch.value) ||
    meta.value.sourceUrl
);

const workflowHref = computed(() =>
  cucumberWorkflowUrl(cucumberSelectedBranch.value)
);

const isOpen = computed(() => openCucumberFeatureId.value === props.id);

const isVisible = computed(() =>
  cucumberFeatureVisible({ id: props.id, ...meta.value })
);

const panelId = computed(() => `${props.id}-panel`);

const scenarioLabel = computed(() => {
  const count = meta.value.scenarioCount;
  if (isDe.value) {
    return count === 1 ? "1 Szenario" : `${count} Szenarien`;
  }
  return count === 1 ? "1 scenario" : `${count} scenarios`;
});

const sourceLabel = computed(() => (isDe.value ? "Quelle" : "Source"));

const runResult = computed(() => cucumberFeatureRunResult(props.id));

const resultLabel = computed(() => {
  const result = runResult.value;
  if (!result) {
    return "";
  }
  const when = formatBerlinDateTime(result.at, isDe.value ? "de" : "en");
  const statusWord =
    result.status === "failed"
      ? isDe.value
        ? "Fehlgeschlagen"
        : "Failed"
      : result.status === "skipped"
        ? isDe.value
          ? "Übersprungen"
          : "Skipped"
        : isDe.value
          ? "Bestanden"
          : "Passed";
  return when ? `${statusWord} ${when}` : statusWord;
});

const toggleLabel = computed(() => {
  const title = meta.value.title || props.id;
  return resultLabel.value ? `${title}. ${resultLabel.value}` : title;
});

const isTicketTag = (tag) => /^@(?:ZMSKVR|ZMS)-\d+$/i.test(tag);

const copiedKind = ref("");
let copiedTimer = 0;

const tagExpression = computed(() => cucumberRunTagExpression(meta.value));

const tagsLabel = computed(() => {
  if (copiedKind.value === "tags") {
    return isDe.value ? "Tags kopiert" : "Copied tags";
  }
  return isDe.value
    ? "Tags zum Ausführen dieses Tests kopieren"
    : "Copy tags to run this test";
});

const tagsHint = computed(() => {
  const tags = tagExpression.value || (isDe.value ? "Tags" : "tags");
  if (isDe.value) {
    return `Kopiert ${tags} in die Zwischenablage`;
  }
  return `Copies ${tags} to the clipboard`;
});

const runLabel = computed(() => {
  const branch = cucumberSelectedBranch.value;
  if (copiedKind.value === "run") {
    return isDe.value
      ? "Kopiert. GitHub Actions wird geöffnet…"
      : "Copied. Opening GitHub Actions…";
  }
  return isDe.value
    ? `Diesen Test auf ${branch} starten`
    : `Run this test on ${branch}`;
});

const runHint = computed(() => {
  const branch = cucumberSelectedBranch.value;
  if (isDe.value) {
    return `Kopiert einen Befehl, um diesen Test auf ${branch} zu starten`;
  }
  return `Copies a command to run this test on ${branch}`;
});

const copyText = async (text) => {
  try {
    await navigator.clipboard.writeText(text);
  } catch {
    const field = document.createElement("textarea");
    field.value = text;
    field.setAttribute("readonly", "");
    field.style.position = "fixed";
    field.style.left = "-9999px";
    document.body.appendChild(field);
    field.select();
    document.execCommand("copy");
    document.body.removeChild(field);
  }
};

const markCopied = (kind) => {
  copiedKind.value = kind;
  window.clearTimeout(copiedTimer);
  copiedTimer = window.setTimeout(() => {
    copiedKind.value = "";
  }, 2000);
};

const copyTags = async () => {
  await copyText(tagExpression.value);
  markCopied("tags");
};

const copyRunCommand = async () => {
  await copyText(cucumberGhRunCommand(meta.value));
  markCopied("run");
};

const onToggle = () => {
  toggleCucumberFeature(props.id);
};

onMounted(() => {
  ensureCucumberHashListener();
  syncCucumberFeatureFromHash();
  ensureCucumberPageState();
});

onUnmounted(() => {
  window.clearTimeout(copiedTimer);
});
</script>

<template>
  <article
    v-show="isVisible"
    :id="id"
    class="cucumber-feature"
    :class="{ 'cucumber-feature--open': isOpen }"
  >
    <h4 class="cucumber-feature__heading">
      <button
        type="button"
        class="cucumber-feature__toggle"
        :aria-expanded="isOpen"
        :aria-controls="panelId"
        :aria-label="toggleLabel"
        @click="onToggle"
      >
        <span
          class="cucumber-feature__chevron"
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
        <span class="cucumber-feature__summary">
          <span class="cucumber-feature__title-row">
            <span
              v-if="runResult"
              class="cucumber-feature__result"
              :class="`cucumber-feature__result--${runResult.status}`"
              :data-tooltip="resultLabel"
              aria-hidden="true"
            >
              <CucumberStatusIcon :status="runResult.status" />
            </span>
            <span class="cucumber-feature__title">{{ meta.title }}</span>
          </span>
          <span class="cucumber-feature__meta">
            <code class="cucumber-feature__file">{{ meta.fileName }}</code>
            <span class="cucumber-feature__count">{{ scenarioLabel }}</span>
          </span>
          <span
            v-if="meta.tags.length"
            class="cucumber-feature__tags"
          >
            <span
              v-for="tag in meta.tags"
              :key="tag"
              class="cucumber-feature__tag"
              :class="{ 'cucumber-feature__tag--ticket': isTicketTag(tag) }"
              >{{ tag }}</span
            >
          </span>
        </span>
      </button>
      <div class="cucumber-feature__actions">
        <button
          type="button"
          class="cucumber-feature__action"
          :class="{ 'cucumber-feature__action--copied': copiedKind === 'tags' }"
          :aria-label="tagsHint"
          :data-tooltip="tagsLabel"
          @click.stop="copyTags"
        >
          <span
            class="cucumber-feature__action-icon"
            aria-hidden="true"
          >
            <svg
              v-if="copiedKind === 'tags'"
              xmlns="http://www.w3.org/2000/svg"
              width="16"
              height="16"
              viewBox="0 0 16 16"
              fill="none"
            >
              <path
                d="M3.5 8.2 6.4 11l6.1-7"
                stroke="currentColor"
                stroke-width="1.75"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
            <svg
              v-else
              xmlns="http://www.w3.org/2000/svg"
              width="16"
              height="16"
              viewBox="0 0 16 16"
              fill="none"
            >
              <rect
                x="5.25"
                y="5.25"
                width="7.25"
                height="8"
                rx="1.25"
                stroke="currentColor"
                stroke-width="1.5"
              />
              <path
                d="M10.75 5.25V4.2A1.7 1.7 0 0 0 9.05 2.5H4.2A1.7 1.7 0 0 0 2.5 4.2v6.85A1.7 1.7 0 0 0 4.2 12.75h1.05"
                stroke="currentColor"
                stroke-width="1.5"
                stroke-linecap="round"
              />
            </svg>
          </span>
        </button>
        <a
          class="cucumber-feature__action"
          :class="{ 'cucumber-feature__action--copied': copiedKind === 'run' }"
          :href="workflowHref"
          target="_blank"
          rel="noopener noreferrer"
          :aria-label="runHint"
          :data-tooltip="runLabel"
          @click.stop="copyRunCommand"
        >
          <span
            class="cucumber-feature__action-icon"
            aria-hidden="true"
          >
            <svg
              v-if="copiedKind === 'run'"
              xmlns="http://www.w3.org/2000/svg"
              width="16"
              height="16"
              viewBox="0 0 16 16"
              fill="none"
            >
              <path
                d="M3.5 8.2 6.4 11l6.1-7"
                stroke="currentColor"
                stroke-width="1.75"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
            <svg
              v-else
              xmlns="http://www.w3.org/2000/svg"
              width="16"
              height="16"
              viewBox="0 0 16 16"
              fill="none"
            >
              <path
                d="M5 3.2v9.6L13.2 8 5 3.2Z"
                fill="currentColor"
              />
            </svg>
          </span>
        </a>
      </div>
    </h4>
    <div
      v-if="isOpen"
      :id="panelId"
      class="cucumber-feature__panel"
      role="region"
    >
      <p
        v-if="sourceHref"
        class="cucumber-feature__source"
      >
        {{ sourceLabel }}:
        <a
          :href="sourceHref"
          target="_blank"
          rel="noopener noreferrer"
          >{{ meta.fileName }}</a
        >
      </p>
      <slot />
      <pre
        v-if="!$slots.default && meta.body"
        class="cucumber-feature__gherkin"
        >{{ meta.body }}</pre>
    </div>
  </article>
</template>

<style scoped>
.cucumber-feature {
  margin: 0 0 0.5rem;
  overflow: hidden;
  background: var(--vp-c-bg-soft);
  border: 1px solid var(--vp-c-divider);
  border-radius: 8px;
  scroll-margin-top: calc(var(--vp-nav-height, 64px) + 16px);
}

.cucumber-feature--open {
  border-color: var(--vp-c-brand-1);
}

.cucumber-feature__heading {
  display: flex;
  align-items: stretch;
  margin: 0;
  padding: 0;
  border: 0;
  font-size: 1rem;
  font-weight: 600;
  line-height: 1.4;
}

.cucumber-feature__toggle {
  display: flex;
  flex: 1 1 auto;
  gap: 0.65rem;
  align-items: flex-start;
  min-width: 0;
  padding: 0.7rem 0.85rem;
  color: inherit;
  text-align: left;
  cursor: pointer;
  background: transparent;
  border: 0;
  font: inherit;
}

.cucumber-feature__toggle:hover {
  background: var(--vp-c-bg-alt);
}

.cucumber-feature__toggle:focus-visible {
  outline: 2px solid var(--vp-c-brand-1);
  outline-offset: -2px;
}

.cucumber-feature__actions {
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
  border-left: 1px solid var(--vp-c-divider);
}

.cucumber-feature__action {
  position: relative;
  display: flex;
  flex: 1 1 auto;
  align-items: center;
  justify-content: center;
  width: 2.65rem;
  min-height: 1.85rem;
  padding: 0;
  color: var(--vp-c-text-2);
  cursor: pointer;
  text-decoration: none;
  background: transparent;
  border: 0;
}

.cucumber-feature__action + .cucumber-feature__action {
  border-top: 1px solid var(--vp-c-divider);
}

.cucumber-feature__action:hover,
.cucumber-feature__action:focus-visible {
  color: var(--vp-c-brand-1);
  background: var(--vp-c-bg-alt);
}

.cucumber-feature__action:focus-visible {
  outline: 2px solid var(--vp-c-brand-1);
  outline-offset: -2px;
}

.cucumber-feature__action--copied {
  color: var(--vp-c-brand-1);
}

.cucumber-feature__action-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 1.25rem;
  height: 1.25rem;
}

.cucumber-feature__action::after {
  position: absolute;
  top: 50%;
  right: calc(100% + 0.45rem);
  z-index: 4;
  width: max-content;
  max-width: min(16rem, calc(100vw - 4rem));
  padding: 0.28rem 0.5rem;
  color: var(--vp-c-bg);
  font-size: 0.75rem;
  font-weight: 500;
  line-height: 1.3;
  white-space: normal;
  pointer-events: none;
  content: attr(data-tooltip);
  background: var(--vp-c-text-1);
  border-radius: 6px;
  opacity: 0;
  transform: translateY(-50%);
  transition: opacity 0.12s ease;
}

.cucumber-feature__action:hover::after,
.cucumber-feature__action:focus-visible::after {
  opacity: 1;
}

@media (prefers-reduced-motion: reduce) {
  .cucumber-feature__action::after {
    transition: none;
  }
}

.cucumber-feature__chevron {
  display: flex;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  width: 1.25rem;
  height: 1.4rem;
  color: var(--vp-c-text-2);
  transition: transform 0.15s ease;
}

.cucumber-feature--open .cucumber-feature__chevron {
  transform: rotate(90deg);
}

@media (prefers-reduced-motion: reduce) {
  .cucumber-feature__chevron {
    transition: none;
  }
}

.cucumber-feature__summary {
  display: flex;
  flex: 1 1 auto;
  flex-direction: column;
  gap: 0.28rem;
  min-width: 0;
}

.cucumber-feature__title-row {
  display: flex;
  gap: 0.45rem;
  align-items: flex-start;
}

.cucumber-feature__result {
  position: relative;
  display: flex;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  width: 1rem;
  height: 1rem;
  margin-top: 0.2rem;
}

.cucumber-feature__result svg {
  display: block;
}

.cucumber-feature__result::after {
  position: absolute;
  top: 50%;
  left: calc(100% + 0.4rem);
  z-index: 5;
  width: max-content;
  max-width: min(16rem, calc(100vw - 4rem));
  padding: 0.28rem 0.5rem;
  color: var(--vp-c-bg);
  font-size: 0.75rem;
  font-weight: 500;
  line-height: 1.3;
  white-space: normal;
  pointer-events: none;
  content: attr(data-tooltip);
  background: var(--vp-c-text-1);
  border-radius: 6px;
  opacity: 0;
  transform: translateY(-50%);
}

.cucumber-feature__result:hover::after,
.cucumber-feature__result:focus-visible::after {
  opacity: 1;
}

.cucumber-feature__title {
  color: var(--vp-c-text-1);
}

.cucumber-feature__meta {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem 0.85rem;
  align-items: baseline;
  color: var(--vp-c-text-2);
  font-size: 0.82rem;
  font-weight: 400;
}

.cucumber-feature__file {
  font-size: 0.8rem;
}

.cucumber-feature__tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.3rem;
}

.cucumber-feature__tag {
  display: inline-block;
  padding: 0.05rem 0.4rem;
  color: var(--vp-c-text-2);
  background: var(--vp-c-bg);
  border: 1px solid var(--vp-c-divider);
  border-radius: 999px;
  font-size: 0.72rem;
  font-weight: 500;
  font-family: var(--vp-font-family-mono);
  line-height: 1.4;
}

.cucumber-feature__tag--ticket {
  color: var(--vp-c-brand-1);
  background: var(--vp-c-brand-soft);
  border-color: color-mix(
    in srgb,
    var(--vp-c-brand-1) 35%,
    var(--vp-c-divider)
  );
}

.cucumber-feature__panel {
  padding: 0 0.85rem 0.85rem;
}

.cucumber-feature__source {
  margin: 0 0 0.75rem;
  font-size: 0.9rem;
}

.cucumber-feature__gherkin {
  margin: 0;
  padding: 1rem 1.15rem;
  overflow: auto;
  color: var(--vp-c-text-1);
  background: var(--vp-code-bg, var(--vp-c-bg-alt));
  border-radius: 8px;
  font-size: 0.85rem;
  font-family: var(--vp-font-family-mono);
  line-height: 1.55;
  white-space: pre;
}

.cucumber-feature__panel :deep(div[class*="language-"]) {
  margin: 0;
}

.cucumber-feature__panel :deep(.copy) {
  margin-top: 0;
}
</style>
