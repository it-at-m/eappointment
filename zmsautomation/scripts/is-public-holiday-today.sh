#!/usr/bin/env bash
# Exit 0 when today (Europe/Berlin) is listed in V10 feiertage test data; exit 1 otherwise.
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
MIGRATION="${1:-$SCRIPT_DIR/../src/main/resources/db/migration/V10__add_day_off_holidays_test_data.sql}"
TODAY="$(TZ=Europe/Berlin date +%Y-%m-%d)"

if grep -q "'${TODAY}'" "$MIGRATION"; then
  echo "Public holiday detected for ${TODAY} (see V10 feiertage test data)."
  exit 0
fi

exit 1
