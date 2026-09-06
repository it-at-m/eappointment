#!/usr/bin/env bash
# Retry composer on transient GitHub dist failures (HTTP 504/400 on zipball/codeload).
# Successful packages stay in the Composer cache, so later attempts only refetch failures.
set -u

MAX_ATTEMPTS="${COMPOSER_INSTALL_RETRIES:-5}"
SLEEP_BASE="${COMPOSER_INSTALL_RETRY_SLEEP:-8}"
export COMPOSER_MAX_PARALLEL_HTTP="${COMPOSER_MAX_PARALLEL_HTTP:-4}"

COMPOSER_BIN="${COMPOSER:-composer}"

if [ "$#" -eq 0 ]; then
  echo "Usage: $0 <composer-args...>" >&2
  exit 2
fi

attempt=1
while true; do
  if "${COMPOSER_BIN}" "$@"; then
    exit 0
  fi
  if [ "${attempt}" -ge "${MAX_ATTEMPTS}" ]; then
    echo "composer failed after ${MAX_ATTEMPTS} attempts" >&2
    exit 1
  fi
  sleep_s=$((attempt * SLEEP_BASE))
  echo "composer failed (attempt ${attempt}/${MAX_ATTEMPTS}); retrying in ${sleep_s}s..." >&2
  attempt=$((attempt + 1))
  sleep "${sleep_s}"
done
