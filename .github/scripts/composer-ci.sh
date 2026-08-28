#!/usr/bin/env bash
# Authenticate Composer against github.com (avoids unauthenticated API 504s /
# rate limits in Actions) and retry flaky dist downloads.
set -euo pipefail

if [[ -z "${COMPOSER_AUTH:-}" ]]; then
  token="${GITHUB_TOKEN:-}"
  if [[ -z "${token}" && -r /run/secrets/github_token ]]; then
    token="$(cat /run/secrets/github_token)"
  fi
  if [[ -n "${token}" ]]; then
    export COMPOSER_AUTH="{\"github-oauth\":{\"github.com\":\"${token}\"}}"
  fi
fi

run() {
  if [[ "${1:-}" == "--make-live" ]]; then
    make live
  else
    composer "$@"
  fi
}

attempts="${COMPOSER_CI_ATTEMPTS:-3}"
delay="${COMPOSER_CI_RETRY_DELAY:-15}"
n=1
until run "$@"; do
  if (( n >= attempts )); then
    exit 1
  fi
  echo "Composer command failed (attempt ${n}/${attempts}); retrying in ${delay}s..." >&2
  sleep "${delay}"
  n=$((n + 1))
  delay=$((delay * 2))
done
