# Running Cronjobs Locally

Run ZMS cronjobs locally with either DDEV or Podman.

## DDEV

Hourly cronjob (default and city-specific):

```bash
ddev exec zmsapi/cron/cronjob.hourly
ddev exec zmsapi/cron/cronjob.hourly --city=berlin
ddev exec zmsapi/cron/cronjob.hourly --city=munich
```

Other cronjobs:

```bash
ddev exec zmsapi/cron/cronjob.minutly
ddev exec zmsapi/cron/cronjob.daily
```

## Podman

Hourly cronjob (default and city-specific):

```bash
podman exec -it zms-web bash -lc "zmsapi/cron/cronjob.hourly"
podman exec -it zms-web bash -lc "zmsapi/cron/cronjob.hourly --city=berlin"
podman exec -it zms-web bash -lc "zmsapi/cron/cronjob.hourly --city=munich"
```

Other cronjobs:

```bash
podman exec -it zms-web bash -lc "zmsapi/cron/cronjob.minutly"
podman exec -it zms-web bash -lc "zmsapi/cron/cronjob.daily"
```
