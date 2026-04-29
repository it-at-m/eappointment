# Local Database and Cache Operations

## Using DDEV

Full setup (drop all tables + base import + test data Flyway + PHP migrate + hourly + minutly + local cache clear):

```bash
ddev exec ./cli db full-setup
```

Optional manual steps:

```bash
ddev exec ./cli db drop-all-tables
ddev import-db --file=.resources/zms.sql
ddev exec ./cli db migrate-test-data
ddev exec zmsapi/vendor/bin/migrate --update
ddev exec zmsapi/cron/cronjob.hourly --city=munich
ddev exec zmsapi/cron/cronjob.minutly
ddev exec ./cli modules clear-local-cache
```

## Using Podman

One-shot local dev setup (`db full-setup` first, then composer + npm install + npm build):

```bash
podman exec -it zms-web bash -lc "./cli dev setup-local"
```

You can also run `./cli dev setup-local` directly wherever the repo is mounted (for example already inside `zms-web`).

Full setup (drop all tables + base import + test data Flyway + PHP migrate + hourly + minutly + local cache clear):

```bash
podman exec -it zms-web bash -lc "./cli db full-setup"
```

Optional manual steps:

```bash
podman exec -it zms-web bash -lc "./cli db drop-all-tables"
podman exec -i zms-db mysql -u root -proot db < .resources/zms.sql
podman exec -it zms-web bash -lc "./cli db migrate-test-data"
podman exec -it zms-web bash -lc "cd zmsapi && vendor/bin/migrate --update"
podman exec -it zms-web bash -lc "cd zmsapi && ./cron/cronjob.hourly --city=munich"
podman exec -it zms-web bash -lc "cd zmsapi && ./cron/cronjob.minutly"
podman exec -it zms-web bash -lc "./cli modules clear-local-cache"
```
