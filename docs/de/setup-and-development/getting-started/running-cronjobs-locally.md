# Cronjobs lokal ausführen

ZMS-Cronjobs lokal mit Podman ausführen. Die lokale Entwicklung nutzt **zmsbackend** (wie `./cli db full-setup` und root `.htaccess` für `/terminvereinbarung/api/2`).

Stündlicher Cronjob (Standard und stadt-spezifisch):

```bash
podman exec -it zms-web bash -lc "zmsbackend/cron/cronjob.hourly"
podman exec -it zms-web bash -lc "zmsbackend/cron/cronjob.hourly --city=berlin"
podman exec -it zms-web bash -lc "zmsbackend/cron/cronjob.hourly --city=munich"
```

Weitere Cronjobs:

```bash
podman exec -it zms-web bash -lc "zmsbackend/cron/cronjob.minutly"
podman exec -it zms-web bash -lc "zmsbackend/cron/cronjob.daily"
```

Den Minuten-Cron auf denselben 5-Minuten-Marken wiederholen wie in der Umgebung (`:00`, `:05`, `:10`, …). Der erste Durchlauf startet sofort. `Ctrl-C` beendet die Schleife. `--once` macht einen Durchlauf und beendet sich. Die CLI schreibt eine Startzeile und eine Endezeile mit der Dauer; die JSON-Logs des Skripts erscheinen dazwischen.

```bash
podman exec -it zms-web bash -lc "./cli cron minutly"
podman exec -it zms-web bash -lc "./cli cron minutly --once"
```
