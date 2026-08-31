import fs from "node:fs";
import path from "node:path";
import { pathToFileURL } from "node:url";

const FEATURES_MARKER = "src/test/resources/features/";
const ARTIFACT_SHARDS =
  "zmsadmin|zmscitizenview|zmsstatistic|zmsapi|zmscitizenapi|custom";
const ARTIFACT_BROWSERS = "chrome|firefox|edge";
const ARTIFACT_DIR = new RegExp(
  `^zmsautomation-ataf-reports-\\d+-\\d+-(.+)-(${ARTIFACT_SHARDS})-(${ARTIFACT_BROWSERS})$`,
  "i"
);
const BROWSER_RANK = { chrome: 3, firefox: 2, edge: 1 };

export const toFeatureAnchorId = (rel) =>
  `feature-${rel
    .replace(/\.feature$/i, "")
    .replace(/[^A-Za-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "")
    .toLowerCase()}`;

export const uriToFeatureRel = (uri) => {
  const normalized = String(uri || "")
    .replace(/\\/g, "/")
    .replace(/^file:/, "");
  const index = normalized.indexOf(FEATURES_MARKER);
  if (index === -1) {
    return "";
  }
  return normalized.slice(index + FEATURES_MARKER.length);
};

export const featureStatusFromCucumber = (feature) => {
  const elements = Array.isArray(feature?.elements) ? feature.elements : [];
  let sawPassed = false;
  let sawFailed = false;
  let sawSkipped = false;
  for (const element of elements) {
    if (element?.type === "background") {
      continue;
    }
    const steps = Array.isArray(element.steps) ? element.steps : [];
    let scenarioStatus = "passed";
    for (const step of steps) {
      const status = step?.result?.status;
      if (
        status === "failed" ||
        status === "undefined" ||
        status === "ambiguous"
      ) {
        scenarioStatus = "failed";
        break;
      }
      if (
        (status === "skipped" || status === "pending") &&
        scenarioStatus === "passed"
      ) {
        scenarioStatus = "skipped";
      }
    }
    if (scenarioStatus === "failed") {
      sawFailed = true;
    } else if (scenarioStatus === "skipped") {
      sawSkipped = true;
    } else {
      sawPassed = true;
    }
  }
  if (sawFailed) {
    return "failed";
  }
  if (sawPassed) {
    return "passed";
  }
  if (sawSkipped) {
    return "skipped";
  }
  return "";
};

export const parseArtifactDirName = (dirName) => {
  const match = String(dirName || "").match(ARTIFACT_DIR);
  if (!match) {
    return null;
  }
  return {
    branchSlug: match[1],
    shard: match[2].toLowerCase(),
    browser: match[3].toLowerCase(),
  };
};

const readBranchMetaFile = (dir) => {
  const metaFile = path.join(dir, "branch-meta.json");
  if (!fs.existsSync(metaFile)) {
    return null;
  }
  try {
    const parsed = JSON.parse(fs.readFileSync(metaFile, "utf8"));
    if (!parsed || typeof parsed !== "object") {
      return null;
    }
    const branch = parsed.branch ? String(parsed.branch) : "";
    const branchSlug = parsed.branchSlug ? String(parsed.branchSlug) : "";
    if (!branch && !branchSlug) {
      return null;
    }
    return {
      branch,
      branchSlug,
      shard: parsed.shard ? String(parsed.shard).toLowerCase() : "",
      browser: parsed.browser ? String(parsed.browser).toLowerCase() : "",
    };
  } catch {
    return null;
  }
};

export const findArtifactMetaFromPath = (filePath) => {
  let current = path.dirname(filePath);
  let fromDir = null;
  let fromFile = null;
  while (current && current !== path.dirname(current)) {
    if (!fromDir) {
      fromDir = parseArtifactDirName(path.basename(current));
    }
    if (!fromFile) {
      fromFile = readBranchMetaFile(current);
    }
    if (fromDir && fromFile) {
      break;
    }
    current = path.dirname(current);
  }
  const branchSlug =
    fromDir?.branchSlug || fromFile?.branchSlug || fromFile?.branch || "";
  if (!branchSlug) {
    return null;
  }
  return {
    branch: fromFile?.branch || "",
    branchSlug,
    shard: fromDir?.shard || fromFile?.shard || "",
    browser: fromDir?.browser || fromFile?.browser || "",
  };
};

const mergeFeature = (existing, incoming) => {
  if (!existing) {
    return incoming;
  }
  const failed = existing.status === "failed" || incoming.status === "failed";
  let status = incoming.status || existing.status;
  if (failed) {
    status = "failed";
  } else if (existing.status === "passed" || incoming.status === "passed") {
    status = "passed";
  }
  const existingRank = BROWSER_RANK[existing.browser] || 0;
  const incomingRank = BROWSER_RANK[incoming.browser] || 0;
  const preferIncoming = incomingRank >= existingRank;
  return {
    status,
    shard: preferIncoming ? incoming.shard : existing.shard,
    browser: preferIncoming ? incoming.browser : existing.browser,
    rel: incoming.rel || existing.rel,
    runId: incoming.runId || existing.runId || "",
    runUrl: incoming.runUrl || existing.runUrl || "",
    runStartedAt: incoming.runStartedAt || existing.runStartedAt || "",
  };
};

export const collectCucumberJsonFiles = (rootDir) => {
  const files = [];
  const walk = (dir) => {
    let entries;
    try {
      entries = fs.readdirSync(dir, { withFileTypes: true });
    } catch {
      return;
    }
    for (const entry of entries) {
      const full = path.join(dir, entry.name);
      if (entry.isDirectory()) {
        walk(full);
      } else if (entry.isFile() && entry.name === "cucumber.json") {
        files.push(full);
      }
    }
  };
  walk(rootDir);
  return files;
};

const emptyStatus = (meta = {}, branchSlug = "") => ({
  branch: meta.branch || branchSlug,
  branchSlug,
  runId: meta.runId || "",
  runUrl: meta.runUrl || "",
  runStartedAt: meta.runStartedAt || "",
  publishedAt: meta.publishedAt || new Date().toISOString(),
  features: {},
});

export const findBranchNameFromPath = (filePath) =>
  findArtifactMetaFromPath(filePath)?.branch || "";

const ingestCucumberFile = (features, file, artifact, meta) => {
  let payload;
  try {
    payload = JSON.parse(fs.readFileSync(file, "utf8"));
  } catch {
    return;
  }
  if (!Array.isArray(payload)) {
    return;
  }
  for (const feature of payload) {
    const rel = uriToFeatureRel(feature.uri);
    if (!rel) {
      continue;
    }
    const id = toFeatureAnchorId(rel);
    const status = featureStatusFromCucumber(feature);
    if (!status) {
      continue;
    }
    features[id] = mergeFeature(features[id], {
      status,
      shard: artifact.shard || "",
      browser: artifact.browser || "",
      rel,
      runId: meta.runId || "",
      runUrl: meta.runUrl || "",
      runStartedAt: meta.runStartedAt || "",
    });
  }
};

export const buildCucumberRunStatus = (reportsDir, meta = {}) => {
  const features = {};
  for (const file of collectCucumberJsonFiles(reportsDir)) {
    const artifact = findArtifactMetaFromPath(file) || {
      shard: "",
      browser: "",
    };
    ingestCucumberFile(features, file, artifact, meta);
  }
  return {
    ...emptyStatus(meta, meta.branchSlug || ""),
    features,
  };
};

export const buildCucumberRunStatusByBranch = (reportsDir, meta = {}) => {
  const bySlug = new Map();
  for (const file of collectCucumberJsonFiles(reportsDir)) {
    const artifact = findArtifactMetaFromPath(file);
    if (!artifact?.branchSlug) {
      continue;
    }
    const slug = artifact.branchSlug;
    if (!bySlug.has(slug)) {
      bySlug.set(slug, emptyStatus(meta, slug));
    }
    const status = bySlug.get(slug);
    if (artifact.branch) {
      status.branch = artifact.branch;
    }
    ingestCucumberFile(status.features, file, artifact, meta);
  }
  return bySlug;
};

const sortIndexBranches = (branches) =>
  [...branches].sort((a, b) => {
    if (a.name === "next") {
      return -1;
    }
    if (b.name === "next") {
      return 1;
    }
    if (a.name === "main") {
      return -1;
    }
    if (b.name === "main") {
      return 1;
    }
    return a.name.localeCompare(b.name);
  });

export const mergeBranchIndex = (previous, updates, meta = {}) => {
  const bySlug = new Map();
  for (const entry of previous?.branches || []) {
    const slug = entry.slug || entry.name;
    if (!slug) {
      continue;
    }
    bySlug.set(slug, {
      name: entry.name || slug,
      slug,
      runId: entry.runId || "",
      runStartedAt: entry.runStartedAt || "",
    });
  }
  for (const status of updates) {
    const slug = status.branchSlug || "";
    if (!slug) {
      continue;
    }
    bySlug.set(slug, {
      name: status.branch || slug,
      slug,
      runId: status.runId || meta.runId || "",
      runStartedAt: status.runStartedAt || meta.runStartedAt || "",
    });
  }
  return {
    publishedAt: meta.publishedAt || new Date().toISOString(),
    branches: sortIndexBranches([...bySlug.values()]),
  };
};

export const mergeCucumberRunStatus = (previous, next) => {
  const features = {
    ...(previous && typeof previous.features === "object"
      ? previous.features
      : {}),
  };
  for (const [id, feature] of Object.entries(next.features || {})) {
    features[id] = feature;
  }
  return {
    branch: next.branch || previous?.branch || next.branchSlug || "",
    branchSlug: next.branchSlug || previous?.branchSlug || "",
    runId: next.runId || previous?.runId || "",
    runUrl: next.runUrl || previous?.runUrl || "",
    runStartedAt: next.runStartedAt || previous?.runStartedAt || "",
    publishedAt: next.publishedAt || new Date().toISOString(),
    features,
  };
};

const readPreviousStatus = (file) => {
  if (!file || !fs.existsSync(file)) {
    return null;
  }
  try {
    const parsed = JSON.parse(fs.readFileSync(file, "utf8"));
    return parsed && typeof parsed === "object" ? parsed : null;
  } catch {
    return null;
  }
};

const INDEX_SKIP = new Set(["index.json", "cucumber-run-status.json"]);

const readPreviousIndex = (previousDir) => {
  if (!previousDir || !fs.existsSync(previousDir)) {
    return { branches: [] };
  }
  const fromFile = readPreviousStatus(path.join(previousDir, "index.json"));
  if (Array.isArray(fromFile?.branches) && fromFile.branches.length) {
    return fromFile;
  }
  let names = [];
  try {
    names = fs.readdirSync(previousDir);
  } catch {
    return { branches: [] };
  }
  const branches = [];
  for (const name of names) {
    if (!name.endsWith(".json") || INDEX_SKIP.has(name)) {
      continue;
    }
    const slug = name.replace(/\.json$/i, "");
    const parsed = readPreviousStatus(path.join(previousDir, name));
    branches.push({
      name: parsed?.branch || slug,
      slug: parsed?.branchSlug || slug,
      runId: parsed?.runId || "",
      runStartedAt: parsed?.runStartedAt || "",
    });
  }
  return { branches };
};

const previousPathForSlug = (previousDir, slug) => {
  const slugFile = path.join(previousDir, `${slug}.json`);
  if (fs.existsSync(slugFile)) {
    return slugFile;
  }
  if (slug === "next") {
    const legacy = path.join(previousDir, "cucumber-run-status.json");
    if (fs.existsSync(legacy)) {
      return legacy;
    }
  }
  return "";
};

const isCli =
  Boolean(process.argv[1]) &&
  import.meta.url === pathToFileURL(path.resolve(process.argv[1])).href;

if (isCli) {
  const reportsDir = path.resolve(process.argv[2] || "");
  const outDir = path.resolve(process.argv[3] || "");
  const previousDir = process.env.PREVIOUS_STATUS_DIR
    ? path.resolve(process.env.PREVIOUS_STATUS_DIR)
    : process.argv[4]
      ? path.resolve(process.argv[4])
      : "";
  if (!reportsDir || !outDir || !fs.existsSync(reportsDir)) {
    console.error(
      "Usage: node build-cucumber-run-status.mjs <reports-dir> <out-dir> [previous-dir]"
    );
    process.exit(1);
  }
  const meta = {
    runId: process.env.RUN_ID || "",
    runUrl: process.env.RUN_URL || "",
    runStartedAt: process.env.RUN_STARTED_AT || "",
    publishedAt: new Date().toISOString(),
  };
  const bySlug = buildCucumberRunStatusByBranch(reportsDir, meta);
  const withFeatures = [...bySlug.entries()].filter(
    ([, status]) => Object.keys(status.features).length > 0
  );
  if (!withFeatures.length) {
    console.error("No cucumber.json features found under", reportsDir);
    process.exit(2);
  }
  fs.mkdirSync(outDir, { recursive: true });
  const written = [];
  for (const [slug, next] of withFeatures) {
    const previous = previousDir
      ? readPreviousStatus(previousPathForSlug(previousDir, slug))
      : null;
    const status = mergeCucumberRunStatus(previous, next);
    const outFile = path.join(outDir, `${slug}.json`);
    fs.writeFileSync(outFile, `${JSON.stringify(status, null, 2)}\n`);
    if (slug === "next") {
      fs.writeFileSync(
        path.join(outDir, "cucumber-run-status.json"),
        `${JSON.stringify(status, null, 2)}\n`
      );
    }
    written.push(status);
    console.log(
      `Wrote ${Object.keys(next.features).length} updated / ${Object.keys(status.features).length} total feature results to ${outFile}`
    );
  }
  const index = mergeBranchIndex(readPreviousIndex(previousDir), written, meta);
  const indexFile = path.join(outDir, "index.json");
  fs.writeFileSync(indexFile, `${JSON.stringify(index, null, 2)}\n`);
  console.log(
    `Wrote ${index.branches.length} branch index entries to ${indexFile}`
  );
}
