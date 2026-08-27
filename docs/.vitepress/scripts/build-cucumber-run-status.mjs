import fs from "node:fs";
import path from "node:path";
import { pathToFileURL } from "node:url";

const FEATURES_MARKER = "src/test/resources/features/";
const ARTIFACT_DIR =
  /^zmsautomation-ataf-reports-\d+-\d+-next-(.+)-(chrome|firefox|edge)$/i;
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

const parseArtifactMeta = (dirName) => {
  const match = dirName.match(ARTIFACT_DIR);
  if (!match) {
    return null;
  }
  return { shard: match[1], browser: match[2].toLowerCase() };
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

export const buildCucumberRunStatus = (reportsDir, meta = {}) => {
  const features = {};
  for (const file of collectCucumberJsonFiles(reportsDir)) {
    const dirName = path.basename(path.dirname(file));
    const artifact = parseArtifactMeta(dirName) || {
      shard: "",
      browser: "",
    };
    let payload;
    try {
      payload = JSON.parse(fs.readFileSync(file, "utf8"));
    } catch {
      continue;
    }
    if (!Array.isArray(payload)) {
      continue;
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
        shard: artifact.shard,
        browser: artifact.browser,
        rel,
      });
    }
  }
  return {
    runId: meta.runId || "",
    runUrl: meta.runUrl || "",
    runStartedAt: meta.runStartedAt || "",
    publishedAt: meta.publishedAt || new Date().toISOString(),
    features,
  };
};

const isCli =
  Boolean(process.argv[1]) &&
  import.meta.url === pathToFileURL(path.resolve(process.argv[1])).href;

if (isCli) {
  const reportsDir = path.resolve(process.argv[2] || "");
  const outFile = path.resolve(process.argv[3] || "");
  if (!reportsDir || !outFile || !fs.existsSync(reportsDir)) {
    console.error(
      "Usage: node build-cucumber-run-status.mjs <reports-dir> <out-file>"
    );
    process.exit(1);
  }
  const status = buildCucumberRunStatus(reportsDir, {
    runId: process.env.RUN_ID || "",
    runUrl: process.env.RUN_URL || "",
    runStartedAt: process.env.RUN_STARTED_AT || "",
    publishedAt: new Date().toISOString(),
  });
  const count = Object.keys(status.features).length;
  if (!count) {
    console.error("No cucumber.json features found under", reportsDir);
    process.exit(2);
  }
  fs.mkdirSync(path.dirname(outFile), { recursive: true });
  fs.writeFileSync(outFile, `${JSON.stringify(status, null, 2)}\n`);
  console.log(`Wrote ${count} feature results to ${outFile}`);
}
