# Local Database and Cache Operations

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
podman exec -it zms-web bash -lc "cd zmsbackend && bin/configure && bin/migrate --update"
podman exec -it zms-web bash -lc "cd zmsbackend && ./cron/cronjob.hourly --city=munich"
podman exec -it zms-web bash -lc "cd zmsbackend && ./cron/cronjob.minutly"
podman exec -it zms-web bash -lc "./cli modules clear-local-cache"
```

## Create local appointments

Two fillers talk to the local zmsapi as `_system_queuefill` (password `vorschau`). `./cli db full-setup` creates that user in the test-data migration. Inside `zms-web` the base URL is `ZMS_API_URL` when that is set, otherwise `http://127.0.0.1/terminvereinbarung/api/2`. On the host it is `http://127.0.0.1:8090/terminvereinbarung/api/2`. Override with `--base-url`. Client names start with `LOADTEST-local`. Reports are written under `cache/` (not committed).

Create walk-ins or timed appointments for one scope or one cluster. Caps are 700 per scope and 2800 per cluster. `--mode` is `spontaneous` (default), `appointments`, `mixed`, or `fill_capacity`.

```bash
podman exec -it zms-web bash -lc "./cli appointments create --target scope --target-id 241 --count 10"
podman exec -it zms-web bash -lc "./cli appointments create --target cluster --target-id 12 --mode mixed --count 20"
podman exec -it zms-web bash -lc "./cli appointments create --target scope --target-id 241 --count 1 --dry-run"
```

`--wipe-queue before` deletes existing `LOADTEST-` clients for that day, then creates. `--wipe-queue only` deletes and does not create. `--wipe-all` removes every deletable process that day, not only `LOADTEST-` names.

Fill every remaining opening-hour seat (public and intern) at every scope, from today through that scope's bookable end (max 180 days):

```bash
podman exec -it zms-web bash -lc "./cli appointments fill-opening-hours"
```

A later run skips seats that are already booked. Count first with `--dry-run`. Narrow a run with `--scope-ids` and `--days` (`0` is today only). `--max-bookings` stops after that many confirmed appointments. `--service-name` limits the filler to one service.

The full run is thousands of scope-days. Each seat is a free-list calculation plus reserve and confirm, and that gets slower as the local tables grow. On this database, 8 workers with no pause between calls is faster than the default 16:

```bash
podman exec -it zms-web bash -lc "./cli appointments fill-opening-hours --workers 8 --pause 0"
```

Stop the old run first. Seats it already booked are skipped. The filler keeps its own session, so logging in as `superuser` does not replace it. Stay logged out of `_system_queuefill` while it runs.

```bash
podman exec -it zms-web bash -lc "./cli appointments fill-opening-hours --dry-run"
podman exec -it zms-web bash -lc "./cli appointments fill-opening-hours --scope-ids 241 --days 0 --dry-run"
podman exec -it zms-web bash -lc "./cli appointments fill-opening-hours --scope-ids 241 --days 2 --workers 4"
```
