#!/bin/bash
# Write module VERSION file. Prefer ZMS_VERSION (CI/image build), then git describe,
# then keep an existing non-empty VERSION (e.g. baked in before `make live`).
set -euo pipefail

ROOT="${1:?module root required}"

VERSION="${ZMS_VERSION:-}"
if [ -z "$VERSION" ]; then
  VERSION="$(git -C "$ROOT" describe --tags --always 2>/dev/null || true)"
fi
if [ -z "$VERSION" ] && [ -s "$ROOT/VERSION" ]; then
  VERSION="$(tr -d '\n' < "$ROOT/VERSION")"
fi
if [ -z "$VERSION" ]; then
  echo "Warning: could not determine version for $ROOT" >&2
  exit 0
fi

echo "$VERSION" > "$ROOT/VERSION"
echo " $VERSION"
