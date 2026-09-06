# Running Cronjobs Locally

Run ZMS cronjobs locally with Podman. Local dev uses **zmsbackend** (same as `./cli db full-setup` and root `.htaccess` for `/terminvereinbarung/api/2`).

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
