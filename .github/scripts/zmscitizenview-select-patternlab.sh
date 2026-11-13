#!/usr/bin/env bash
# Select @muenchen/muc-patternlab-vue channel for zmscitizenview CI installs.
#
# - main branch and git tags  → keep the release pin from package.json / lockfile
# - every other ref (next, feature branches, …) → the newer semver of npm
#   dist-tags "beta" and "latest" (a stale beta must not downgrade a release)
# - pull requests targeting main → keep the release pin (matches what will merge)
#
# Usage: .github/scripts/zmscitizenview-select-patternlab.sh [app-path]
set -euo pipefail

APP_PATH="${1:-zmscitizenview}"
REF="${GITHUB_REF:-}"
REF_TYPE="${GITHUB_REF_TYPE:-}"
EVENT_NAME="${GITHUB_EVENT_NAME:-}"
BASE_REF="${GITHUB_BASE_REF:-}"

use_release=false
if [[ "${REF}" == "refs/heads/main" || "${REF_TYPE}" == "tag" ]]; then
  use_release=true
fi
if [[ "${EVENT_NAME}" == "pull_request" && "${BASE_REF}" == "main" ]]; then
  use_release=true
fi

cd "${APP_PATH}"

if [[ "${use_release}" == "true" ]]; then
  echo "muc-patternlab-vue: keeping pinned release from package-lock (ref=${REF})"
  exit 0
fi

beta_version="$(npm view @muenchen/muc-patternlab-vue@beta version)"
latest_version="$(npm view @muenchen/muc-patternlab-vue@latest version)"
chosen_version="$(
  BETA_VERSION="${beta_version}" LATEST_VERSION="${latest_version}" node -e '
function parse(version) {
  const match = /^v?(\d+)\.(\d+)\.(\d+)(?:-([0-9A-Za-z.-]+))?(?:\+.*)?$/.exec(String(version).trim());
  if (!match) {
    throw new Error("muc-patternlab-vue: not a semver version: " + version);
  }
  return {
    major: Number(match[1]),
    minor: Number(match[2]),
    patch: Number(match[3]),
    pre: match[4] ? match[4].split(".") : null,
  };
}
function cmpIdent(left, right) {
  const leftNum = /^[0-9]+$/.test(left);
  const rightNum = /^[0-9]+$/.test(right);
  if (leftNum && rightNum) {
    const delta = Number(left) - Number(right);
    return delta < 0 ? -1 : delta > 0 ? 1 : 0;
  }
  if (leftNum) return -1;
  if (rightNum) return 1;
  if (left === right) return 0;
  return left < right ? -1 : 1;
}
function cmpPre(left, right) {
  if (left === null && right === null) return 0;
  if (left === null) return 1;
  if (right === null) return -1;
  const length = Math.max(left.length, right.length);
  for (let i = 0; i < length; i += 1) {
    if (i >= left.length) return -1;
    if (i >= right.length) return 1;
    const delta = cmpIdent(left[i], right[i]);
    if (delta !== 0) return delta;
  }
  return 0;
}
function cmp(left, right) {
  const a = parse(left);
  const b = parse(right);
  if (a.major !== b.major) return a.major < b.major ? -1 : 1;
  if (a.minor !== b.minor) return a.minor < b.minor ? -1 : 1;
  if (a.patch !== b.patch) return a.patch < b.patch ? -1 : 1;
  return cmpPre(a.pre, b.pre);
}
const beta = process.env.BETA_VERSION;
const latest = process.env.LATEST_VERSION;
process.stdout.write(cmp(beta, latest) > 0 ? beta.trim() : latest.trim());
'
)"

echo "muc-patternlab-vue: beta=${beta_version} latest=${latest_version} chosen=${chosen_version} (ref=${REF})"
npm install "@muenchen/muc-patternlab-vue@${chosen_version}" \
  --package-lock-only \
  --ignore-scripts \
  --no-fund \
  --no-audit

# Show what the lockfile will install next (npm ci / action-npm-build).
node -e '
const lock = require("./package-lock.json");
const pkg = lock.packages?.["node_modules/@muenchen/muc-patternlab-vue"];
console.log("muc-patternlab-vue lock version:", pkg?.version ?? "(missing)");
'
