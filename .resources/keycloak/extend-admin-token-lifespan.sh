#!/bin/sh
# Keycloak 26.x master realm defaults accessTokenLifespan to 60s.
# keycloakmigration authenticates once; changelogs longer than 60s then 401.
# Run this once Keycloak is up, before init-keycloak.
set -eu

BASEURL="${BASEURL:-http://keycloak:8080/auth}"
ADMIN_USER="${ADMIN_USER:-admin}"
ADMIN_PASSWORD="${ADMIN_PASSWORD:-admin}"
TARGET_LIFESPAN="${KEYCLOAK_ADMIN_TOKEN_LIFESPAN:-1800}"
TOKEN_URL="${BASEURL}/realms/master/protocol/openid-connect/token"
REALM_URL="${BASEURL}/admin/realms/master"

echo "Waiting for Keycloak at ${BASEURL} ..."
i=0
while [ "$i" -lt 90 ]; do
  if curl -sf "${BASEURL}/realms/master" >/dev/null; then
    break
  fi
  i=$((i + 1))
  sleep 2
done
if ! curl -sf "${BASEURL}/realms/master" >/dev/null; then
  echo "ERROR: Keycloak did not become ready" >&2
  exit 1
fi

echo "Extending master accessTokenLifespan to ${TARGET_LIFESPAN}s ..."
TOKEN="$(
  curl -sf -X POST "${TOKEN_URL}" \
    -d "client_id=admin-cli" \
    -d "username=${ADMIN_USER}" \
    -d "password=${ADMIN_PASSWORD}" \
    -d "grant_type=password" \
    | sed -n 's/.*"access_token"[[:space:]]*:[[:space:]]*"\([^"]*\)".*/\1/p'
)"
if [ -z "${TOKEN}" ]; then
  echo "ERROR: could not obtain admin token" >&2
  exit 1
fi

REALM_JSON="$(curl -sf -H "Authorization: Bearer ${TOKEN}" "${REALM_URL}")"
UPDATED_JSON="$(
  printf '%s' "${REALM_JSON}" \
    | sed "s/\"accessTokenLifespan\"[[:space:]]*:[[:space:]]*[0-9]*/\"accessTokenLifespan\": ${TARGET_LIFESPAN}/"
)"

curl -sf -X PUT "${REALM_URL}" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" \
  -d "${UPDATED_JSON}" >/dev/null

echo "Master accessTokenLifespan updated."
