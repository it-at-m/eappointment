# Lokale Datenbank- und Cache-Operationen

## Mit DDEV

Vollständige Einrichtung (alle Tabellen verwerfen + Basis-Import + Testdaten via Flyway + PHP-Migrate + Stunden- + Minuten-Cron + lokales Cache-Leeren):

```bash
ddev exec ./cli db full-setup
```

Optionale manuelle Schritte:

```bash
ddev exec ./cli db drop-all-tables
ddev import-db --file=.resources/zms.sql
ddev exec ./cli db migrate-test-data
ddev exec zmsbackend/bin/configure
ddev exec zmsbackend/bin/migrate --update
ddev exec zmsbackend/cron/cronjob.hourly --city=munich
ddev exec zmsbackend/cron/cronjob.minutly
ddev exec ./cli modules clear-local-cache
```

## Mit Podman

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
