# Cronjobs lokal ausführen

ZMS-Cronjobs lassen sich lokal mit DDEV oder Podman ausführen.

## DDEV

Stündlicher Cronjob (Standard und stadt-spezifisch):

```bash
ddev exec zmsapi/cron/cronjob.hourly
ddev exec zmsapi/cron/cronjob.hourly --city=berlin
ddev exec zmsapi/cron/cronjob.hourly --city=munich
```

Weitere Cronjobs:

```bash
ddev exec zmsapi/cron/cronjob.minutly
ddev exec zmsapi/cron/cronjob.daily
```

## Podman

Stündlicher Cronjob (Standard und stadt-spezifisch):

```bash
podman exec -it zms-web bash -lc "zmsapi/cron/cronjob.hourly"
podman exec -it zms-web bash -lc "zmsapi/cron/cronjob.hourly --city=berlin"
podman exec -it zms-web bash -lc "zmsapi/cron/cronjob.hourly --city=munich"
```

Weitere Cronjobs:

```bash
podman exec -it zms-web bash -lc "zmsapi/cron/cronjob.minutly"
podman exec -it zms-web bash -lc "zmsapi/cron/cronjob.daily"
```
