# Operations

## Cronjobs

Run cronjobs locally with DDEV:

```bash
ddev exec zmsapi/cron/cronjob.hourly
ddev exec zmsapi/cron/cronjob.hourly --city=berlin
ddev exec zmsapi/cron/cronjob.hourly --city=munich
ddev exec zmsapi/cron/cronjob.minutly
ddev exec zmsapi/cron/cronjob.daily
```

Podman equivalents:

```bash
podman exec -it zms-web bash -lc "zmsapi/cron/cronjob.hourly"
podman exec -it zms-web bash -lc "zmsapi/cron/cronjob.hourly --city=munich"
podman exec -it zms-web bash -lc "zmsapi/cron/cronjob.minutly"
podman exec -it zms-web bash -lc "zmsapi/cron/cronjob.daily"
```

## Dependency Graph

Key relationship summary:

- `zmscitizenview` and `refarch-gateway` build on top of `zmscitizenapi`
- `zmscitizenapi` interacts with `zmsapi`
- core PHP modules depend on shared components such as `zmsentities`, `zmsslim`, and `mellon`
