# Datenbank-Migrationen

SQL-Migrationen liegen in `zmsdb/migrations/`. Der Migrations-Runner ist `bin/migrate` (im Modul `zmsdb`). Über `zmsapi` nutzt du den Composer-Wrapper:

```bash
zmsapi/vendor/bin/migrate
```

Für ein vollständiges lokales DB-Reset (Basisschema, Testdaten, Migrationen, Cronjobs) siehe [Lokale Datenbank- und Cache-Operationen](./local-database-and-cache-operations.md).

## Migrationen lokal ausführen

### DDEV

Ausstehende Migrationen anzeigen (Dry Run):

```bash
ddev exec zmsapi/vendor/bin/migrate
```

Alle ausstehenden Migrationen anwenden:

```bash
ddev exec zmsapi/vendor/bin/migrate --update
```

Nur Expand-Phase (siehe unten):

```bash
ddev exec zmsapi/vendor/bin/migrate --update --phase=expand
```

Nur Contract-Phase:

```bash
ddev exec zmsapi/vendor/bin/migrate --update --phase=contract
```

### Podman

Ausstehende Migrationen anzeigen (Dry Run):

```bash
podman exec -it zms-web bash -lc "cd zmsapi && vendor/bin/migrate"
```

Alle ausstehenden Migrationen anwenden:

```bash
podman exec -it zms-web bash -lc "cd zmsapi && vendor/bin/migrate --update"
```

Nur Expand-Phase:

```bash
podman exec -it zms-web bash -lc "cd zmsapi && vendor/bin/migrate --update --phase=expand"
```

Nur Contract-Phase:

```bash
podman exec -it zms-web bash -lc "cd zmsapi && vendor/bin/migrate --update --phase=contract"
```

Lokal reicht meist `--update` ohne `--phase`, um alles auf einmal anzuwenden. Die Phase-Flags dienen vor allem zum Testen derselben Reihenfolge wie im Deployment.

## Expand und Contract

Breaking Schema-Änderungen (Umbenennungen, Drops) werden in zwei Schritte geteilt, damit alter und neuer Anwendungscode während eines Rollouts parallel laufen können:

1. **Expand** — neues Schema hinzufügen (neue Spalte, View, Backfill, Sync-Trigger). Alter Code funktioniert weiter.
2. **Code deployen** — Release, das die neuen Namen nutzt.
3. **Contract** — altes Schema entfernen (Spalte/View/Trigger droppen). Erst sicher, wenn nichts mehr die alten Namen nutzt.

Additive Änderungen (`ADD COLUMN`, neue Tabellen) brauchen nur Expand — keine Contract-Datei.

Zum vollständigen Deployment-Pipeline und MariaDB-Mustern siehe [Zero-Downtime-Deployments und Migrationen](../on-the-future/database-refactor/zero-downtime-deployments-and-migrations.md).

## Dateinamen-Präfixe

Alle Migrationsdateien liegen in `zmsdb/migrations/`. Der Dateiname bestimmt, welche Phase der Runner ausführt:

| Änderungstyp                                                  | Dateinamen-Muster | `--phase`  | Beispiel                                       |
| ------------------------------------------------------------- | ----------------- | ---------- | ---------------------------------------------- |
| Nur additiv (`ADD COLUMN`, neue Tabelle, Indizes, Seeds)      | kein Präfix       | `expand`   | `20260702-add-standort-custom-text-field3.sql` |
| Expand-Schritt einer Umbenennung oder Dual-Spalten-Transition | `*-expand-*`      | `expand`   | `20260702-expand-standort-add-scope-id.sql`    |
| Contract-Schritt (Spalte/View/Trigger droppen)                | `*-contract-*`    | `contract` | `20260702-contract-drop-standort-view.sql`     |

Kurzregeln:

- **`ADD COLUMN`** → ohne Präfix (oder `*-expand-*`, wenn später ein Contract folgt). Nie `*-contract-*`.
- **`DROP COLUMN` / `DROP TABLE` / `DROP VIEW`** → nur `*-contract-*`. Nie ohne Präfix.
- **`RENAME COLUMN` / `RENAME TABLE`** → nie ohne Präfix. Im selben Release ein `*-expand-*`- + `*-contract-*`-Paar verwenden.

Ohne `--phase` führt `bin/migrate` alle ausstehenden Dateien aus (Legacy-Verhalten). Mit `--phase=expand` werden `*-contract-*`-Dateien übersprungen. Mit `--phase=contract` laufen nur `*-contract-*`-Dateien.
