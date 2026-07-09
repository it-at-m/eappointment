# Database Migrations

SQL migrations live in `zmsdb/migrations/`. The migration runner is `bin/migrate` (in the `zmsdb` module). From `zmsapi`, use the Composer wrapper:

```bash
zmsapi/vendor/bin/migrate
```

For a full local database reset (base schema, test data, migrations, cron jobs), see [Local Database and Cache Operations](./local-database-and-cache-operations.md).

## Running migrations locally

### DDEV

Check which migrations are pending (dry run):

```bash
ddev exec zmsapi/vendor/bin/migrate
```

Apply all pending migrations:

```bash
ddev exec zmsapi/vendor/bin/migrate --update
```

Apply only expand-phase migrations (see below):

```bash
ddev exec zmsapi/vendor/bin/migrate --update --phase=expand
```

Apply only contract-phase migrations:

```bash
ddev exec zmsapi/vendor/bin/migrate --update --phase=contract
```

### Podman

Check which migrations are pending (dry run):

```bash
podman exec -it zms-web bash -lc "cd zmsapi && vendor/bin/migrate"
```

Apply all pending migrations:

```bash
podman exec -it zms-web bash -lc "cd zmsapi && vendor/bin/migrate --update"
```

Apply only expand-phase migrations:

```bash
podman exec -it zms-web bash -lc "cd zmsapi && vendor/bin/migrate --update --phase=expand"
```

Apply only contract-phase migrations:

```bash
podman exec -it zms-web bash -lc "cd zmsapi && vendor/bin/migrate --update --phase=contract"
```

Locally you usually run `--update` without `--phase` to apply everything at once. The phase flags are mainly for testing the same order used in deployment.

## Expand and contract

Breaking schema changes (renames, drops) are split into two steps so old and new application code can run at the same time during rollout:

1. **Expand** — add the new schema (new column, view, backfill, sync triggers). Old code keeps working.
2. **Deploy code** — release that uses the new names.
3. **Contract** — remove the old schema (drop column, drop view, drop triggers). Only safe once nothing uses the old names.

Additive changes (`ADD COLUMN`, new tables) only need an expand step — no contract file.

For the full deployment pipeline and MariaDB patterns, see [Zero-downtime deployments and database migrations](../on-the-future/database-refactor/zero-downtime-deployments-and-migrations.md).

## Filename prefixes

All migration files go in `zmsdb/migrations/`. The filename tells the runner which phase applies:

| Change type                                             | Filename pattern | `--phase`  | Example                                        |
| ------------------------------------------------------- | ---------------- | ---------- | ---------------------------------------------- |
| Additive only (`ADD COLUMN`, new table, indexes, seeds) | no prefix        | `expand`   | `20260702-add-standort-custom-text-field3.sql` |
| Expand step of a rename or dual-column transition       | `*-expand-*`     | `expand`   | `20260702-expand-standort-add-scope-id.sql`    |
| Contract step (drop column, drop view, drop trigger)    | `*-contract-*`   | `contract` | `20260702-contract-drop-standort-view.sql`     |

Quick rules:

- **`ADD COLUMN`** → unprefixed (or `*-expand-*` if paired with a later contract). Never `*-contract-*`.
- **`DROP COLUMN` / `DROP TABLE` / `DROP VIEW`** → `*-contract-*` only. Never unprefixed.
- **`RENAME COLUMN` / `RENAME TABLE`** → never unprefixed. Use an `*-expand-*` + `*-contract-*` pair in the same release.

Without `--phase`, `bin/migrate` runs every pending file (legacy behaviour). With `--phase=expand`, it skips `*-contract-*` files. With `--phase=contract`, it runs only `*-contract-*` files.
