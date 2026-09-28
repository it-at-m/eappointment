#!/usr/bin/env bash
# Select @muenchen/muc-patternlab-vue channel for zmscitizenview CI installs.
#
# - main branch and git tags  → keep the release pin from package.json / lockfile
# - every other ref (next, feature branches, …) → npm dist-tag "beta"
# - pull requests targeting main → keep the release pin (matches what will merge)
#
# Usage: .github/scripts/zmscitizenview-select-patternlab.sh [app-path]
#
# A manual rebuild can target a branch other than the workflow run. When
# ZMS_BUILD_REF is set, that ref is used instead of GITHUB_REF (same for
# ZMS_BUILD_REF_TYPE, ZMS_BUILD_EVENT_NAME, and ZMS_BUILD_BASE_REF).
set -euo pipefail

APP_PATH="${1:-zmscitizenview}"
if [[ -n "${ZMS_BUILD_REF+x}" ]]; then
  REF="$ZMS_BUILD_REF"
else
  REF="${GITHUB_REF:-}"
fi
if [[ -n "${ZMS_BUILD_REF_TYPE+x}" ]]; then
  REF_TYPE="$ZMS_BUILD_REF_TYPE"
else
  REF_TYPE="${GITHUB_REF_TYPE:-}"
fi
if [[ -n "${ZMS_BUILD_EVENT_NAME+x}" ]]; then
  EVENT_NAME="$ZMS_BUILD_EVENT_NAME"
else
  EVENT_NAME="${GITHUB_EVENT_NAME:-}"
fi
if [[ -n "${ZMS_BUILD_BASE_REF+x}" ]]; then
  BASE_REF="$ZMS_BUILD_BASE_REF"
else
  BASE_REF="${GITHUB_BASE_REF:-}"
fi

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

echo "muc-patternlab-vue: switching to npm dist-tag beta (ref=${REF})"
npm install @muenchen/muc-patternlab-vue@beta \
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
