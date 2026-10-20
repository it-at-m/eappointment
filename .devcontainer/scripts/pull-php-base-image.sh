#!/usr/bin/env bash
# Pull the zms-web base image with the container CLI before `docker-compose up`.
#
# Ubuntu's /usr/bin/docker-compose is Compose v1. It pulls through Podman's
# Docker API, which can report every layer "Download complete" and still leave
# the tag unknown. Compose then pulls the same image again while creating
# zms-web and fails with "image not known". A CLI pull registers the tag, so
# Compose finds the image and does not pull it.
#
# Usage: .devcontainer/scripts/pull-php-base-image.sh <path-to-.env>
# Invoked from Dev Containers (initializeCommand), after sync-php-base-tag.sh.
set -euo pipefail

ENV_FILE="${1:-}"
if [ -z "${ENV_FILE}" ]; then
  echo "usage: $0 <path-to-.env>" >&2
  exit 1
fi

TAG="8.4-local-amd64"
if [ -f "${ENV_FILE}" ]; then
  configured="$(grep -E '^ZMS_PHP_BASE_TAG=' "${ENV_FILE}" | tail -n 1 | cut -d= -f2- | tr -d '"' || true)"
  if [ -n "${configured}" ]; then
    TAG="${configured}"
  fi
fi

IMAGE="ghcr.io/it-at-m/eappointment/zmsbase:${TAG}"

# Compose talks to DOCKER_HOST, or to /var/run/docker.sock when that is unset.
socket="${DOCKER_HOST:-}"
if [ -z "${socket}" ] && [ -S /var/run/docker.sock ]; then
  socket="unix:///var/run/docker.sock"
fi

use_podman=false
if command -v podman >/dev/null 2>&1; then
  if [ -n "${socket}" ] && podman --url "${socket}" version >/dev/null 2>&1; then
    use_podman=true
  elif [ -z "${socket}" ]; then
    use_podman=true
  fi
fi

if [ "${use_podman}" = true ]; then
  if podman image exists "${IMAGE}"; then
    exit 0
  fi
  podman pull "${IMAGE}"
  exit 0
fi

if command -v docker >/dev/null 2>&1; then
  if docker image inspect "${IMAGE}" >/dev/null 2>&1; then
    exit 0
  fi
  docker pull "${IMAGE}"
  exit 0
fi

echo "podman or docker is required to pull ${IMAGE}" >&2
exit 1
