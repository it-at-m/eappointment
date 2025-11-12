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

