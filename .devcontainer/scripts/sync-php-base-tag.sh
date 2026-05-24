#!/usr/bin/env bash
# Upserts ZMS_PHP_BASE_TAG from the *host* machine arch (8.3-local-amd64 vs 8.3-local-arm64).
# Usage: .devcontainer/scripts/sync-php-base-tag.sh <path-to-.env>
# Invoked from Dev Containers (initializeCommand) and DDEV (pre-start exec-host).
set -euo pipefail
ENV_FILE="${1:-}"
if [ -z "${ENV_FILE}" ]; then
  echo "usage: $0 <path-to-.env>" >&2
  exit 1
fi

case "$(uname -m)" in
  aarch64 | arm64) TAG=8.3-local-arm64 ;;
  *) TAG=8.3-local-amd64 ;;
esac

touch "${ENV_FILE}"
tmp="${ENV_FILE}.tmp.$$"
grep -v '^ZMS_PHP_BASE_TAG=' "${ENV_FILE}" >"${tmp}" || true
mv "${tmp}" "${ENV_FILE}"
echo "ZMS_PHP_BASE_TAG=${TAG}" >>"${ENV_FILE}"
