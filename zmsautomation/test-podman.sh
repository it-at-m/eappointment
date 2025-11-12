#!/usr/bin/env bash
set -euo pipefail

echo "=== Testing Podman Commands ==="
echo ""

echo "1. Testing: podman ps"
podman ps
echo ""

echo "2. Testing: podman ps --format '{{.Names}}'"
podman ps --format "{{.Names}}"
echo ""

echo "3. Testing: podman ps -a"
podman ps -a
echo ""

echo "4. Testing: podman ps -a --format '{{.Names}}'"
podman ps -a --format "{{.Names}}"
echo ""

echo "5. Testing: podman inspect zms-web"
podman inspect zms-web 2>&1 | head -5 || echo "FAILED"
echo ""

echo "6. Testing: podman inspect zms-db"
podman inspect zms-db 2>&1 | head -5 || echo "FAILED"
echo ""

echo "7. Testing: podman exec zms-web echo test"
podman exec zms-web echo "test" 2>&1 || echo "FAILED"
echo ""

echo "8. Testing: podman exec zms-db echo test"
podman exec zms-db echo "test" 2>&1 || echo "FAILED"
echo ""

echo "=== Environment ==="
echo "User: $(whoami)"
echo "UID: $(id -u)"
echo "PODMAN_HOST: ${PODMAN_HOST:-not set}"
echo "CONTAINER_HOST: ${CONTAINER_HOST:-not set}"
echo ""

echo "=== Container Detection ==="
if [[ -f /.dockerenv ]]; then
  echo "INSIDE CONTAINER: Yes (/.dockerenv exists)"
else
  echo "INSIDE CONTAINER: No (/.dockerenv not found)"
fi

if [[ -n "${container:-}" ]]; then
  echo "INSIDE CONTAINER: Yes (container env var set to: ${container})"
else
  echo "INSIDE CONTAINER: No (container env var not set)"
fi

echo "Hostname: $(hostname)"

if grep -qi docker /proc/1/cgroup 2>/dev/null || grep -qi podman /proc/1/cgroup 2>/dev/null; then
  echo "INSIDE CONTAINER: Yes (cgroup indicates container)"
else
  echo "INSIDE CONTAINER: No (cgroup doesn't indicate container)"
fi

