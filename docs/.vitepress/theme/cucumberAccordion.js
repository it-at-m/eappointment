import { ref } from "vue";

import featureMeta from "../data/cucumber-features.json";

/** Exclusive open id for cucumber feature rows on the generated docs page. */
export const openCucumberFeatureId = ref(null);

/** Title/tag filter for the cucumber catalog (not Gherkin body). */
export const cucumberSearchQuery = ref("");

/** Selected run-status keys; empty means all statuses. */
export const cucumberStatusFilter = ref([]);

const featureHashPrefix = "feature-";

const currentHash = () => {
  if (typeof window === "undefined") {
    return "";
  }
  return window.location.hash.replace(/^#/, "");
};

const setFeatureHash = (id) => {
  if (typeof window === "undefined") {
    return;
  }
  const url = new URL(window.location.href);
  if (id) {
    url.hash = id;
  } else {
    url.hash = "";
  }
  history.replaceState(null, "", url);
};

export function toggleCucumberFeature(id) {
  if (openCucumberFeatureId.value === id) {
    openCucumberFeatureId.value = null;
    if (currentHash() === id) {
      setFeatureHash("");
    }
    return;
  }
  openCucumberFeatureId.value = id;
  setFeatureHash(id);
}

export function syncCucumberFeatureFromHash() {
  const hash = currentHash();
  if (hash.startsWith(featureHashPrefix)) {
    openCucumberFeatureId.value = hash;
  }
}

let hashListenerBound = false;

export function ensureCucumberHashListener() {
  if (typeof window === "undefined" || hashListenerBound) {
    return;
  }
  hashListenerBound = true;
  window.addEventListener("hashchange", syncCucumberFeatureFromHash);
}

export function cucumberFeatureMatches(entry, query) {
  const q = (query || "").trim().toLowerCase();
  if (!q) {
    return true;
  }
  if (!entry) {
    return false;
  }
  const tags = Array.isArray(entry.tags) ? entry.tags.join(" ") : "";
  const haystack = [
    entry.title,
    entry.fileName,
    entry.rel,
    tags,
    tags.replaceAll("@", ""),
  ]
    .filter(Boolean)
    .join(" ")
    .toLowerCase();
  return q.split(/\s+/).every((token) => {
    const plain = token.replace(/^@+/, "");
    return (
      haystack.includes(token) || (plain !== "" && haystack.includes(plain))
    );
  });
}

export function cucumberFeatureVisible(entry) {
  if (!cucumberFeatureMatches(entry, cucumberSearchQuery.value)) {
    return false;
  }
  const selected = cucumberStatusFilter.value;
  if (!selected.length) {
    return true;
  }
  const status = cucumberFeatureRunResult(entry?.id)?.status || "none";
  return selected.includes(status);
}

export function cucumberCatalogEntries() {
  return Object.entries(featureMeta).map(([id, entry]) => ({
    id,
    ...entry,
  }));
}

export function cucumberGroupHasMatch(testType, module = "") {
  return cucumberCatalogEntries().some((entry) => {
    if (entry.testType !== testType) {
      return false;
    }
    if (module && entry.module !== module) {
      return false;
    }
    return cucumberFeatureVisible(entry);
  });
}

export function closeCucumberFeatureIfHidden() {
  const id = openCucumberFeatureId.value;
  if (!id) {
    return;
  }
  if (!cucumberFeatureVisible({ id, ...featureMeta[id] })) {
    openCucumberFeatureId.value = null;
  }
}

export function toggleCucumberStatusFilter(status) {
  const selected = cucumberStatusFilter.value;
  cucumberStatusFilter.value = selected.includes(status)
    ? selected.filter((value) => value !== status)
    : [...selected, status];
}

export const ZMSAUTOMATION_WORKFLOW_URL =
  "https://github.com/it-at-m/eappointment/actions/workflows/zmsautomation-workflow.yaml?query=branch%3Anext";

export const ZMSAUTOMATION_BADGE_URL =
  "https://github.com/it-at-m/eappointment/actions/workflows/zmsautomation-workflow.yaml/badge.svg?branch=next";

export const ZMSAUTOMATION_SCHEDULED_RUNS_API =
  "https://api.github.com/repos/it-at-m/eappointment/actions/workflows/zmsautomation-workflow.yaml/runs?branch=next&event=schedule&per_page=1";

export const CUCUMBER_RUN_STATUS_URL =
  "https://raw.githubusercontent.com/it-at-m/eappointment/zmsautomation-status/cucumber-run-status.json";

/** Latest scheduled-run feature results (public JSON, no token). */
export const cucumberRunStatus = ref(null);

let cucumberRunStatusStarted = false;

export function ensureCucumberRunStatus() {
  if (typeof window === "undefined" || cucumberRunStatusStarted) {
    return;
  }
  cucumberRunStatusStarted = true;
  fetch(CUCUMBER_RUN_STATUS_URL, { cache: "no-store" })
    .then((response) => {
      if (!response.ok) {
        throw new Error(String(response.status));
      }
      return response.json();
    })
    .then((data) => {
      cucumberRunStatus.value = data && typeof data === "object" ? data : null;
    })
    .catch(() => {
      cucumberRunStatus.value = null;
    });
}

export function formatBerlinDateTime(iso, locale) {
  if (!iso) {
    return "";
  }
  try {
    return new Intl.DateTimeFormat(locale === "de" ? "de-DE" : "en-GB", {
      day: "numeric",
      month: "short",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
      timeZone: "Europe/Berlin",
      timeZoneName: "short",
    }).format(new Date(iso));
  } catch {
    return String(iso);
  }
}

export function cucumberFeatureRunResult(id) {
  const entry = cucumberRunStatus.value?.features?.[id];
  if (!entry?.status) {
    return null;
  }
  return {
    status: entry.status,
    shard: entry.shard || "",
    browser: entry.browser || "",
    runUrl: cucumberRunStatus.value?.runUrl || "",
    at:
      cucumberRunStatus.value?.runStartedAt ||
      cucumberRunStatus.value?.publishedAt ||
      "",
  };
}

const TICKET_TAG = /^@(?:ZMSKVR|ZMS)-\d+$/i;

export function cucumberPrimaryTicketTag(entry) {
  const tags = Array.isArray(entry?.tags) ? entry.tags : [];
  const tickets = tags.filter((tag) => TICKET_TAG.test(tag));
  if (!tickets.length) {
    return "";
  }
  const file = `${entry.fileName || ""} ${entry.rel || ""}`.toLowerCase();
  const fromFile = tickets.find((tag) =>
    file.includes(tag.replace(/^@/, "").toLowerCase())
  );
  if (fromFile) {
    return fromFile;
  }
  return tickets.find((tag) => /^@ZMSKVR-/i.test(tag)) || tickets[0];
}

export function cucumberRunTagExpression(entry) {
  if (!entry) {
    return "";
  }
  const moduleTag = entry.module ? `@${entry.module}` : "";
  const ticket = cucumberPrimaryTicketTag(entry);
  if (ticket && moduleTag) {
    return `(${ticket}) and ${moduleTag}`;
  }
  if (ticket) {
    return ticket;
  }
  const tags = (Array.isArray(entry.tags) ? entry.tags : []).filter(
    (tag) => !/^@ignore$/i.test(tag)
  );
  if (!moduleTag) {
    return tags.join(" and ");
  }
  const extra = tags.filter(
    (tag) =>
      tag.toLowerCase() !== moduleTag.toLowerCase() &&
      !/^@(?:rest|web)$/i.test(tag)
  );
  if (extra.length) {
    return [moduleTag, ...extra].join(" and ");
  }
  return moduleTag;
}

const shellSingleQuote = (value) => `'${String(value).replace(/'/g, `'\\''`)}'`;

export function cucumberGhRunCommand(entry) {
  const tags = cucumberRunTagExpression(entry);
  const testLayer = entry?.testType === "rest" ? "api" : "ui";
  const fields = [
    ["run_main_next_matrix", "false"],
    ["run_all_in_one_job", "false"],
    ["module_admin", "false"],
    ["module_citizenview", "false"],
    ["module_statistic", "false"],
    ["module_zmsapi", "false"],
    ["module_zmscitizenapi", "false"],
    ["use_custom_tags", "true"],
    ["test_layer", testLayer],
    ["cucumber_tag_expressions", tags],
    ["browser", "chrome"],
    ["per_step_screenshots", "true"],
  ];
  const lines = [
    "gh workflow run zmsautomation-workflow.yaml \\",
    "  --repo it-at-m/eappointment \\",
    "  --ref next \\",
  ];
  fields.forEach(([key, value], index) => {
    const suffix = index === fields.length - 1 ? "" : " \\";
    lines.push(`  -f ${key}=${shellSingleQuote(value)}${suffix}`);
  });
  return lines.join("\n");
}
