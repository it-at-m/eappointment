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

Repeat the minutely cron on the same 5-minute marks used in deployment (`:00`, `:05`, `:10`, …). The first pass starts immediately. `Ctrl-C` stops the loop. `--once` runs a single pass and exits. The CLI prints a start line and a finish line with the duration; the script's JSON logs appear between them.

```bash
podman exec -it zms-web bash -lc "./cli cron minutly"
podman exec -it zms-web bash -lc "./cli cron minutly --once"
```
