#!/usr/bin/env bash
# Copy Monaco editor + RequireJS into zmsadmin/public/_libs for local mail template editing.
# Used by DDEV post-start, Dev Container postStart, and ./cli dev setup-local.
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
ZMSADMIN="${REPO_ROOT}/zmsadmin"

if [ ! -d "${ZMSADMIN}/node_modules/monaco-editor" ]; then
  echo "copy-zmsadmin-editor-libs: skipping (monaco-editor not installed; run npm install in zmsadmin)" >&2
  exit 0
fi

make -C "${ZMSADMIN}" libs
