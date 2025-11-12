#!/usr/bin/env bash
set -euo pipefail

# ---------------------------
# Ensure correct Podman binary
# ---------------------------
export PATH="/usr/bin:$PATH"

echo "Using podman from: $(which podman)"
echo "Podman version: $(podman --version)"

# ---------------------------
# Environment variables
# ---------------------------

echo "XDG_RUNTIME_DIR: $XDG_RUNTIME_DIR"
echo "DBUS_SESSION_BUS_ADDRESS: ${DBUS_SESSION_BUS_ADDRESS:-not set}"
#echo "CONTAINER_HOST: $CONTAINER_HOST"
ls -l "$XDG_RUNTIME_DIR/podman/podman.sock" || echo "Podman socket not visible"

# ---------------------------
# Podman container testing
# ---------------------------
echo ""
echo "=== Testing Podman Commands ==="
echo ""

echo "1. podman ps"
podman ps || echo "Failed to list running containers"
echo ""

echo "2. podman ps --format '{{.Names}}'"
podman ps --format "{{.Names}}" || echo "Failed to list container names"
echo ""

echo "3. podman ps -a"
podman ps -a || echo "Failed to list all containers"
echo ""

echo "4. podman ps -a --format '{{.Names}}'"
podman ps -a --format "{{.Names}}" || echo "Failed to list all container names"
echo ""

echo "5. podman inspect zms-web"
podman inspect zms-web 2>&1 | head -5 || echo "FAILED"
echo ""

echo "6. podman inspect zms-db"
podman inspect zms-db 2>&1 | head -5 || echo "FAILED"
echo ""

echo "7. podman exec zms-web echo test"
podman exec zms-web echo "test" 2>&1 || echo "FAILED"
echo ""

echo "8. podman exec zms-db echo test"
podman exec zms-db echo "test" 2>&1 || echo "FAILED"
echo ""

# ---------------------------
# Environment diagnostics
# ---------------------------
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
