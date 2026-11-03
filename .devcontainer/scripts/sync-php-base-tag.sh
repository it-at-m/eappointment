#!/usr/bin/env bash
# Upserts ZMS_PHP_BASE_TAG from the host arch, then pulls that image if it is missing.
# Compose finds the tag locally. Ubuntu's Compose v1 otherwise pulls through Podman's
# API, drops the tag, and downloads the image again.
# Usage: .devcontainer/scripts/sync-php-base-tag.sh <path-to-.env>
# Invoked from Dev Containers (initializeCommand).
set -euo pipefail
ENV_FILE="${1:-}"
if [ -z "${ENV_FILE}" ]; then
  echo "usage: $0 <path-to-.env>" >&2
  exit 1
fi

case "$(uname -m)" in
  aarch64 | arm64) TAG=8.4-local-arm64 ;;
  *) TAG=8.4-local-amd64 ;;
esac

touch "${ENV_FILE}"
tmp="${ENV_FILE}.tmp.$$"
grep -v '^ZMS_PHP_BASE_TAG=' "${ENV_FILE}" >"${tmp}" || true
mv "${tmp}" "${ENV_FILE}"
echo "ZMS_PHP_BASE_TAG=${TAG}" >>"${ENV_FILE}"

IMAGE="ghcr.io/it-at-m/eappointment/zmsbase:${TAG}"
if command -v podman >/dev/null 2>&1; then
  cli=podman
elif command -v docker >/dev/null 2>&1; then
  cli=docker
else
  echo "podman or docker is required to pull ${IMAGE}" >&2
  exit 1
fi
if ! "${cli}" image inspect "${IMAGE}" >/dev/null 2>&1; then
  "${cli}" pull "${IMAGE}"
fi
