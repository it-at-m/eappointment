#!/usr/bin/env bash
# Select @muenchen/muc-patternlab-vue channel for zmscitizenview CI installs.
#
# - main branch and git tags  → keep the release pin from package.json / lockfile
# - every other ref (next, feature branches, …) → npm dist-tag "beta"
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
