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
