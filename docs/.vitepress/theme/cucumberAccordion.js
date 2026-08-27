import { ref } from "vue";

import featureMeta from "../data/cucumber-features.json";
import {
  parseFeatureMeta,
  toFeatureAnchorId,
} from "../lib/cucumberFeatureParse.mjs";

/** Exclusive open id for cucumber feature rows on the generated docs page. */
export const openCucumberFeatureId = ref(null);

/** Title/tag filter for the cucumber catalog (not Gherkin body). */
export const cucumberSearchQuery = ref("");

/** Selected run-status keys; empty means all statuses. */
export const cucumberStatusFilter = ref([]);

export const CUCUMBER_DEFAULT_BRANCH = "next";
export const CUCUMBER_REPO = "it-at-m/eappointment";
export const CUCUMBER_FEATURES_PREFIX =
  "zmsautomation/src/test/resources/features/";
export const CUCUMBER_STATUS_REF = "zmsautomation-status";

/** Git branch whose tests, status, and run command are shown. */
export const cucumberSelectedBranch = ref(CUCUMBER_DEFAULT_BRANCH);

/** Remote `.feature` catalog keyed by accordion id; null when unused. */
export const cucumberRemoteMeta = ref(null);

/** ready | loading | error for a non-default branch catalog. */
export const cucumberCatalogLoadState = ref("ready");

export const cucumberBranchOptions = ref([CUCUMBER_DEFAULT_BRANCH]);

export const cucumberBranchesLoadState = ref("idle");

/** Unauthenticated GitHub API quota hit (change IP / network, then reload). */
export const cucumberGithubRateLimited = ref(false);

const featureHashPrefix = "feature-";
const githubHeaders = {
  Accept: "application/vnd.github+json",
  "X-GitHub-Api-Version": "2022-11-28",
};
const BRANCH_STORAGE_KEY = "cucumberSelectedBranch";
const PINNED_BRANCHES = [CUCUMBER_DEFAULT_BRANCH];

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

export function cucumberBranchSlug(branch) {
  return String(branch || CUCUMBER_DEFAULT_BRANCH).replace(/[/:]/g, "-");
}

export function cucumberUsesRemoteCatalog() {
  return cucumberSelectedBranch.value !== CUCUMBER_DEFAULT_BRANCH;
}

export function cucumberFeatureSourceUrl(
  rel,
  branch = cucumberSelectedBranch.value
) {
  if (!rel) {
    return "";
  }
  return `https://github.com/${CUCUMBER_REPO}/blob/${branch}/${CUCUMBER_FEATURES_PREFIX}${rel}`;
}

export function cucumberWorkflowUrl(branch = cucumberSelectedBranch.value) {
  return `https://github.com/${CUCUMBER_REPO}/actions/workflows/zmsautomation-workflow.yaml?query=branch%3A${encodeURIComponent(branch)}`;
}

export function cucumberBadgeUrl(branch = cucumberSelectedBranch.value) {
  return `https://github.com/${CUCUMBER_REPO}/actions/workflows/zmsautomation-workflow.yaml/badge.svg?branch=${encodeURIComponent(branch)}`;
}

export function cucumberRunStatusUrl(branch = cucumberSelectedBranch.value) {
  const slug = cucumberBranchSlug(branch);
  return `https://raw.githubusercontent.com/${CUCUMBER_REPO}/${CUCUMBER_STATUS_REF}/${encodeURIComponent(slug)}.json`;
}

export function cucumberWorkflowRunsUrl(branch = cucumberSelectedBranch.value) {
  return `https://api.github.com/repos/${CUCUMBER_REPO}/actions/workflows/zmsautomation-workflow.yaml/runs?branch=${encodeURIComponent(branch)}&per_page=1`;
}

export const ZMSAUTOMATION_WORKFLOW_URL = cucumberWorkflowUrl(
  CUCUMBER_DEFAULT_BRANCH
);
export const ZMSAUTOMATION_BADGE_URL = cucumberBadgeUrl(
  CUCUMBER_DEFAULT_BRANCH
);
export function cucumberBranchIndexUrl() {
  return `https://raw.githubusercontent.com/${CUCUMBER_REPO}/${CUCUMBER_STATUS_REF}/index.json`;
}

export const CUCUMBER_RUN_STATUS_URL = `https://raw.githubusercontent.com/${CUCUMBER_REPO}/${CUCUMBER_STATUS_REF}/cucumber-run-status.json`;

export const ZMSAUTOMATION_SCHEDULED_RUNS_API = `https://api.github.com/repos/${CUCUMBER_REPO}/actions/workflows/zmsautomation-workflow.yaml/runs?branch=${encodeURIComponent(CUCUMBER_DEFAULT_BRANCH)}&event=schedule&per_page=1`;

export function cucumberFeatureMatches(entry, query) {
  const q = (query || "").trim().toLowerCase();
  if (!q) {
    return true;
  }
  if (!entry) {
    return false;
  }
  const tags = Array.isArray(entry.tags) ? entry.tags : [];
  const haystack = [
    entry.title,
    entry.fileName,
    entry.rel,
    tags.join(" "),
    tags.join(" ").replaceAll("@", ""),
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
  const source = cucumberUsesRemoteCatalog()
    ? cucumberRemoteMeta.value || {}
    : featureMeta;
  return Object.entries(source).map(([id, entry]) => ({
    id,
    ...entry,
  }));
}

export function cucumberFeatureMeta(id) {
  if (cucumberUsesRemoteCatalog()) {
    return cucumberRemoteMeta.value?.[id] || null;
  }
  return featureMeta[id] || null;
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

const sortModuleNames = (names) =>
  [...names].sort((a, b) => {
    if (a === "buergeransicht") {
      return 1;
    }
    if (b === "buergeransicht") {
      return -1;
    }
    return a.localeCompare(b);
  });

export function cucumberGroupedCatalog(entries = cucumberCatalogEntries()) {
  const byType = new Map();
  for (const entry of entries) {
    const testType = entry.testType || "other";
    const module = entry.module || "misc";
    if (!byType.has(testType)) {
      byType.set(testType, new Map());
    }
    const modules = byType.get(testType);
    if (!modules.has(module)) {
      modules.set(module, []);
    }
    modules.get(module).push(entry);
  }
  const typeOrder = ["rest", "ui"];
  const types = [...byType.keys()].sort((a, b) => {
    const ai = typeOrder.indexOf(a);
    const bi = typeOrder.indexOf(b);
    if (ai !== -1 || bi !== -1) {
      return (ai === -1 ? 99 : ai) - (bi === -1 ? 99 : bi);
    }
    return a.localeCompare(b);
  });
  return types.map((testType) => ({
    testType,
    modules: sortModuleNames([...byType.get(testType).keys()]).map(
      (module) => ({
        module,
        entries: byType.get(testType).get(module),
      })
    ),
  }));
}

export function closeCucumberFeatureIfHidden() {
  const id = openCucumberFeatureId.value;
  if (!id) {
    return;
  }
  const entry = cucumberCatalogEntries().find((item) => item.id === id);
  if (!entry || !cucumberFeatureVisible(entry)) {
    openCucumberFeatureId.value = null;
  }
}

export function toggleCucumberStatusFilter(status) {
  const selected = cucumberStatusFilter.value;
  cucumberStatusFilter.value = selected.includes(status)
    ? selected.filter((value) => value !== status)
    : [...selected, status];
}

/** Latest published feature results for the selected branch. */
export const cucumberRunStatus = ref(null);

/** idle | loading | ready | empty */
export const cucumberRunStatusLoadState = ref("idle");

let statusRequestId = 0;
let catalogRequestId = 0;
const remoteCatalogCache = new Map();

const persistSelectedBranch = (branch) => {
  if (typeof window === "undefined") {
    return;
  }
  try {
    sessionStorage.setItem(BRANCH_STORAGE_KEY, branch);
  } catch {
    /* ignore private-mode quota */
  }
  const url = new URL(window.location.href);
  if (branch === CUCUMBER_DEFAULT_BRANCH) {
    url.searchParams.delete("branch");
  } else {
    url.searchParams.set("branch", branch);
  }
  history.replaceState(null, "", url);
};

let branchRestored = false;

const restoreSelectedBranch = () => {
  if (branchRestored || typeof window === "undefined") {
    return;
  }
  branchRestored = true;
  const query = new URLSearchParams(window.location.search).get("branch");
  let stored = "";
  try {
    stored = sessionStorage.getItem(BRANCH_STORAGE_KEY) || "";
  } catch {
    stored = "";
  }
  const branch = (query || stored || CUCUMBER_DEFAULT_BRANCH).trim();
  cucumberSelectedBranch.value = branch || CUCUMBER_DEFAULT_BRANCH;
};

export async function loadCucumberRunStatus() {
  if (typeof window === "undefined") {
    return;
  }
  const requestId = ++statusRequestId;
  const branch = cucumberSelectedBranch.value;
  cucumberRunStatus.value = null;
  cucumberRunStatusLoadState.value = "loading";
  const urls = [cucumberRunStatusUrl(branch)];
  if (cucumberBranchSlug(branch) === CUCUMBER_DEFAULT_BRANCH) {
    urls.push(CUCUMBER_RUN_STATUS_URL);
  }
  for (const url of urls) {
    try {
      const response = await fetch(url, { cache: "no-store" });
      if (!response.ok) {
        continue;
      }
      const data = await response.json();
      if (requestId !== statusRequestId) {
        return;
      }
      cucumberRunStatus.value = data && typeof data === "object" ? data : null;
      cucumberRunStatusLoadState.value = cucumberRunStatus.value
        ? "ready"
        : "empty";
      return;
    } catch {
      /* try fallback */
    }
  }
  if (requestId === statusRequestId) {
    cucumberRunStatus.value = null;
    cucumberRunStatusLoadState.value = "empty";
  }
}

export async function noteCucumberGithubRateLimit(response) {
  if (!response || cucumberGithubRateLimited.value) {
    return;
  }
  if (response.status === 429) {
    cucumberGithubRateLimited.value = true;
    return;
  }
  const remaining = response.headers.get("x-ratelimit-remaining");
  if (remaining === "0") {
    cucumberGithubRateLimited.value = true;
    return;
  }
  if (response.status !== 403) {
    return;
  }
  try {
    const payload = await response.clone().json();
    if (
      String(payload?.message || "")
        .toLowerCase()
        .includes("rate limit")
    ) {
      cucumberGithubRateLimited.value = true;
    }
  } catch {
    /* ignore non-JSON bodies */
  }
}

const githubJson = async (url) => {
  const response = await fetch(url, { headers: githubHeaders });
  await noteCucumberGithubRateLimit(response);
  if (!response.ok) {
    throw new Error(String(response.status));
  }
  return response.json();
};

/** Latest zmsautomation workflow run for the selected branch (any event). */
export const cucumberLatestWorkflowRun = ref(null);

/** idle | loading | ready | empty | error */
export const cucumberLatestWorkflowRunState = ref("idle");

const latestRunCache = new Map();
let latestRunRequestId = 0;

export async function loadCucumberLatestWorkflowRun() {
  if (typeof window === "undefined") {
    return;
  }
  const branch = cucumberSelectedBranch.value;
  const requestId = ++latestRunRequestId;
  if (latestRunCache.has(branch)) {
    cucumberLatestWorkflowRun.value = latestRunCache.get(branch);
    cucumberLatestWorkflowRunState.value = cucumberLatestWorkflowRun.value
      ? "ready"
      : "empty";
    return;
  }
  cucumberLatestWorkflowRun.value = null;
  cucumberLatestWorkflowRunState.value = "loading";
  try {
    const data = await githubJson(cucumberWorkflowRunsUrl(branch));
    const latest = Array.isArray(data.workflow_runs)
      ? data.workflow_runs[0]
      : null;
    if (requestId !== latestRunRequestId) {
      return;
    }
    latestRunCache.set(branch, latest || null);
    cucumberLatestWorkflowRun.value = latest || null;
    cucumberLatestWorkflowRunState.value = latest ? "ready" : "empty";
  } catch {
    if (requestId !== latestRunRequestId) {
      return;
    }
    cucumberLatestWorkflowRun.value = null;
    cucumberLatestWorkflowRunState.value = "error";
  }
}

const uniqueIds = () => {
  const used = new Set();
  return (rel) => {
    let id = toFeatureAnchorId(rel);
    if (used.has(id)) {
      let suffix = 2;
      while (used.has(`${id}-${suffix}`)) {
        suffix += 1;
      }
      id = `${id}-${suffix}`;
    }
    used.add(id);
    return id;
  };
};

const buildRemoteCatalog = async (branch, sha, paths) => {
  const nextId = uniqueIds();
  const meta = {};
  const results = await Promise.all(
    paths.map(async (repoPath) => {
      const rel = repoPath.slice(CUCUMBER_FEATURES_PREFIX.length);
      const rawUrl = `https://raw.githubusercontent.com/${CUCUMBER_REPO}/${sha}/${repoPath}`;
      const response = await fetch(rawUrl, { cache: "no-store" });
      if (!response.ok) {
        throw new Error(String(response.status));
      }
      const body = await response.text();
      return { rel, body };
    })
  );
  for (const { rel, body } of results) {
    const parts = rel.split("/");
    const testType = parts[0] || "other";
    const module = parts[1] || "misc";
    const parsed = parseFeatureMeta(body);
    const id = nextId(rel);
    const fileName = parts[parts.length - 1] || rel;
    meta[id] = {
      rel,
      fileName,
      title: parsed.title || fileName,
      tags: parsed.tags,
      scenarioCount: parsed.scenarioCount,
      sourceUrl: cucumberFeatureSourceUrl(rel, branch),
      testType,
      module,
      body,
    };
  }
  return meta;
};

export async function loadCucumberRemoteCatalog() {
  if (typeof window === "undefined") {
    return;
  }
  if (!cucumberUsesRemoteCatalog()) {
    catalogRequestId += 1;
    cucumberRemoteMeta.value = null;
    cucumberCatalogLoadState.value = "ready";
    return;
  }
  const branch = cucumberSelectedBranch.value;
  const cached = remoteCatalogCache.get(branch);
  if (cached) {
    cucumberRemoteMeta.value = cached;
    cucumberCatalogLoadState.value = "ready";
    return;
  }
  const requestId = ++catalogRequestId;
  cucumberCatalogLoadState.value = "loading";
  cucumberRemoteMeta.value = null;
  try {
    const commit = await githubJson(
      `https://api.github.com/repos/${CUCUMBER_REPO}/commits/${encodeURIComponent(branch)}`
    );
    const sha = commit.sha;
    const featuresRoot = CUCUMBER_FEATURES_PREFIX.replace(/\/$/, "");
    const tree = await githubJson(
      `https://api.github.com/repos/${CUCUMBER_REPO}/git/trees/${encodeURIComponent(`${sha}:${featuresRoot}`)}?recursive=1`
    );
    const paths = (Array.isArray(tree.tree) ? tree.tree : [])
      .filter(
        (entry) =>
          entry?.type === "blob" &&
          typeof entry.path === "string" &&
          entry.path.endsWith(".feature")
      )
      .map((entry) => `${CUCUMBER_FEATURES_PREFIX}${entry.path}`)
      .sort((a, b) => a.localeCompare(b));
    const meta = await buildRemoteCatalog(branch, sha, paths);
    if (requestId !== catalogRequestId) {
      return;
    }
    remoteCatalogCache.set(branch, meta);
    cucumberRemoteMeta.value = meta;
    cucumberCatalogLoadState.value = "ready";
  } catch {
    if (requestId !== catalogRequestId) {
      return;
    }
    cucumberRemoteMeta.value = null;
    cucumberCatalogLoadState.value = "error";
  }
}

const refreshCucumberBranchData = () => {
  loadCucumberRunStatus();
  loadCucumberRemoteCatalog();
  loadCucumberLatestWorkflowRun();
};

export function setCucumberSelectedBranch(branch) {
  const next = (branch || "").trim() || CUCUMBER_DEFAULT_BRANCH;
  if (cucumberSelectedBranch.value === next) {
    persistSelectedBranch(next);
    if (
      cucumberCatalogLoadState.value === "error" ||
      cucumberRunStatusLoadState.value === "empty" ||
      cucumberLatestWorkflowRunState.value === "error"
    ) {
      refreshCucumberBranchData();
    }
    return;
  }
  cucumberSelectedBranch.value = next;
  cucumberBranchOptions.value = mergeBranchOptions([
    ...cucumberBranchOptions.value,
    next,
  ]);
  openCucumberFeatureId.value = null;
  persistSelectedBranch(next);
  refreshCucumberBranchData();
}

const mergeBranchOptions = (names) => {
  const seen = new Set();
  const out = [];
  for (const name of [...PINNED_BRANCHES, ...names]) {
    if (!name || seen.has(name)) {
      continue;
    }
    seen.add(name);
    out.push(name);
  }
  return out;
};

const namesFromIndex = (data) => {
  if (!data || typeof data !== "object") {
    return [];
  }
  if (Array.isArray(data.branches)) {
    return data.branches
      .map((entry) => entry?.name || entry?.slug || "")
      .filter(Boolean);
  }
  return [];
};

const namesFromWorkflowRuns = (data) => {
  const runs = Array.isArray(data?.workflow_runs) ? data.workflow_runs : [];
  const names = [];
  const seen = new Set();
  for (const run of runs) {
    const name = run?.head_branch;
    if (!name || seen.has(name)) {
      continue;
    }
    seen.add(name);
    names.push(name);
  }
  return names;
};

export async function loadCucumberBranches() {
  if (typeof window === "undefined") {
    return;
  }
  cucumberBranchesLoadState.value = "loading";
  const names = [];
  try {
    const data = await githubJson(
      `https://api.github.com/repos/${CUCUMBER_REPO}/actions/workflows/zmsautomation-workflow.yaml/runs?per_page=100`
    );
    names.push(...namesFromWorkflowRuns(data));
  } catch {
    /* next remains pinned */
  }
  try {
    const response = await fetch(cucumberBranchIndexUrl(), {
      cache: "no-store",
    });
    if (response.ok) {
      names.push(...namesFromIndex(await response.json()));
    }
  } catch {
    /* index is optional until status is published */
  }
  if (
    cucumberSelectedBranch.value &&
    cucumberSelectedBranch.value !== CUCUMBER_DEFAULT_BRANCH
  ) {
    names.push(cucumberSelectedBranch.value);
  }
  cucumberBranchOptions.value = mergeBranchOptions(names);
  cucumberBranchesLoadState.value = "ready";
}

let pageStateStarted = false;

export function ensureCucumberPageState() {
  if (typeof window === "undefined") {
    return;
  }
  restoreSelectedBranch();
  ensureCucumberHashListener();
  if (pageStateStarted) {
    return;
  }
  pageStateStarted = true;
  loadCucumberBranches();
  refreshCucumberBranchData();
}

export function ensureCucumberRunStatus() {
  ensureCucumberPageState();
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
    runUrl: entry.runUrl || cucumberRunStatus.value?.runUrl || "",
    at:
      entry.runStartedAt ||
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
  const branch = cucumberSelectedBranch.value || CUCUMBER_DEFAULT_BRANCH;
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
    `  --ref ${shellSingleQuote(branch)} \\`,
  ];
  fields.forEach(([key, value], index) => {
    const suffix = index === fields.length - 1 ? "" : " \\";
    lines.push(`  -f ${key}=${shellSingleQuote(value)}${suffix}`);
  });
  return lines.join("\n");
}
