#!/usr/bin/env bash
# Run on the host. Confirms zms-web has the php-base ICU locale fixes and Intl works over Apache.
set -euo pipefail
CTR="${1:-zms-web}"
echo "=== /etc/eappointment-php-base-image-marker (missing = old image) ==="
podman exec "$CTR" cat /etc/eappointment-php-base-image-marker 2>/dev/null || echo "MISSING — pull ghcr.io/it-at-m/eappointment-php-base:8.3-local again"
echo "=== Apache mod_env locale conf ==="
podman exec "$CTR" test -f /etc/apache2/conf-enabled/eappointment-icu-locale.conf && echo OK || echo "MISSING"
echo "=== tail /etc/apache2/envvars ==="
podman exec "$CTR" tail -n 12 /etc/apache2/envvars
echo "=== apache master LANG/LC_ALL ==="
pid=$(podman exec "$CTR" sh -c 'pgrep -o apache2 || pgrep -o httpd || true')
if [ -n "$pid" ]; then
  podman exec "$CTR" sh -c "tr '\0' '\n' < /proc/$pid/environ | grep -E '^LANG=|^LC_ALL=' || true"
else
  echo "(no apache2/httpd pid found)"
fi
echo "=== IntlDateFormatter over HTTP (must print ok) ==="
podman exec "$CTR" sh -c 'printf %s "<?php new IntlDateFormatter(\"de_DE\", IntlDateFormatter::MEDIUM, IntlDateFormatter::MEDIUM, \"Europe/Berlin\", IntlDateFormatter::GREGORIAN, \"yyyy-MM-dd\"); echo \"ok\";" > /var/www/html/_intl_verify.php'
podman exec "$CTR" python3 -c 'import urllib.request as u; print(u.urlopen("http://127.0.0.1/_intl_verify.php", timeout=15).read().decode())'
podman exec "$CTR" rm -f /var/www/html/_intl_verify.php
