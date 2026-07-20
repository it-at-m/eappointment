# Running Cronjobs Locally

Run ZMS cronjobs locally with either DDEV or Podman. Local dev uses **zmsbackend** (same as `./cli db full-setup` and root `.htaccess` for `/terminvereinbarung/api/2`).

## DDEV

Hourly cronjob (default and city-specific):

```bash
ddev exec zmsbackend/cron/cronjob.hourly
ddev exec zmsbackend/cron/cronjob.hourly --city=berlin
ddev exec zmsbackend/cron/cronjob.hourly --city=munich
```

Other cronjobs:

```bash
ddev exec zmsbackend/cron/cronjob.minutly
ddev exec zmsbackend/cron/cronjob.daily
```

## Podman

Hourly cronjob (default and city-specific):

```bash
podman exec -it zms-web bash -lc "zmsbackend/cron/cronjob.hourly"
podman exec -it zms-web bash -lc "zmsbackend/cron/cronjob.hourly --city=berlin"
podman exec -it zms-web bash -lc "zmsbackend/cron/cronjob.hourly --city=munich"
```

Other cronjobs:

```bash
podman exec -it zms-web bash -lc "zmsbackend/cron/cronjob.minutly"
podman exec -it zms-web bash -lc "zmsbackend/cron/cronjob.daily"
```
