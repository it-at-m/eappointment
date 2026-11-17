# Lokale Datenbank- und Cache-Operationen

Einmalige lokale Dev-Einrichtung (zuerst `db full-setup`, dann composer + npm install + npm build):

```bash
podman exec -it zms-web bash -lc "./cli dev setup-local"
```

Du kannst `./cli dev setup-local` auch direkt dort ausführen, wo das Repo eingebunden ist (zum Beispiel bereits innerhalb von `zms-web`).

Vollständige Einrichtung (alle Tabellen verwerfen + Basis-Import + Testdaten via Flyway + PHP-Migrate + Stunden- + Minuten-Cron + lokales Cache-Leeren):

```bash
podman exec -it zms-web bash -lc "./cli db full-setup"
```

Optionale manuelle Schritte:

```bash
podman exec -it zms-web bash -lc "./cli db drop-all-tables"
podman exec -i zms-db mysql -u root -proot db < .resources/zms.sql
podman exec -it zms-web bash -lc "./cli db migrate-test-data"
podman exec -it zms-web bash -lc "cd zmsbackend && bin/configure && bin/migrate --update"
podman exec -it zms-web bash -lc "cd zmsbackend && ./cron/cronjob.hourly --city=munich"
podman exec -it zms-web bash -lc "cd zmsbackend && ./cron/cronjob.minutly"
podman exec -it zms-web bash -lc "./cli modules clear-local-cache"
```

## Termine lokal anlegen

Zwei Befehle sprechen die lokale zmsapi als `_system_queuefill` an (Passwort `vorschau`). `./cli db full-setup` legt diesen Nutzer in der Testdaten-Migration an. In `zms-web` ist die Basis-URL `ZMS_API_URL`, falls gesetzt, sonst `http://127.0.0.1/terminvereinbarung/api/2`. Auf dem Host ist es `http://127.0.0.1:8090/terminvereinbarung/api/2`. Mit `--base-url` überschreiben. Kundennamen beginnen mit `LOADTEST-local`. Berichte liegen unter `cache/` und gehören nicht ins Repository.

Walk-ins oder Terminbuchungen für einen Standort oder ein Cluster. Obergrenzen: 700 pro Standort, 2800 pro Cluster. `--mode` ist `spontaneous` (Standard), `appointments`, `mixed` oder `fill_capacity`.

```bash
podman exec -it zms-web bash -lc "./cli appointments create --target scope --target-id 241 --count 10"
podman exec -it zms-web bash -lc "./cli appointments create --target cluster --target-id 12 --mode mixed --count 20"
podman exec -it zms-web bash -lc "./cli appointments create --target scope --target-id 241 --count 1 --dry-run"
```

`--wipe-queue before` löscht vorhandene `LOADTEST-`-Kunden dieses Tages und legt danach an. `--wipe-queue only` löscht nur. `--wipe-all` entfernt jeden löschbaren Vorgang dieses Tages, nicht nur Namen mit `LOADTEST-`.

Alle noch freien Plätze in den Öffnungszeiten buchen (öffentlich und intern), an jedem Standort, von heute bis zum buchbaren Ende dieses Standorts (höchstens 180 Tage):

```bash
podman exec -it zms-web bash -lc "./cli appointments fill-opening-hours"
```

Ein erneuter Lauf überspringt bereits gebuchte Plätze. Vorher mit `--dry-run` zählen. Mit `--scope-ids` und `--days` eingrenzen (`0` ist nur heute). `--max-bookings` stoppt nach so vielen bestätigten Terminen. `--service-name` beschränkt den Lauf auf eine Dienstleistung.

Der volle Lauf sind tausende Standort-Tage. Jeder Platz ist eine Frei-Liste plus Reservieren und Bestätigen, und das wird langsamer, je voller die lokale Datenbank ist. Auf dieser Datenbank sind 8 Worker ohne Pause zwischen den Aufrufen schneller als die voreingestellten 16:

```bash
podman exec -it zms-web bash -lc "./cli appointments fill-opening-hours --workers 8 --pause 0"
```

Den alten Lauf zuerst beenden. Bereits gebuchte Plätze werden übersprungen. Der Lauf hat eine eigene Sitzung, eine Anmeldung als `superuser` ersetzt sie nicht. Währenddessen nicht als `_system_queuefill` angemeldet bleiben.

```bash
podman exec -it zms-web bash -lc "./cli appointments fill-opening-hours --dry-run"
podman exec -it zms-web bash -lc "./cli appointments fill-opening-hours --scope-ids 241 --days 0 --dry-run"
podman exec -it zms-web bash -lc "./cli appointments fill-opening-hours --scope-ids 241 --days 2 --workers 4"
```
