---
outline: deep
---

# Zero-Downtime-Deployments und Datenbank-Migrationen

> **Status:** Entwurf  
> **Verwandt:** [standardize-database-table-and-field-naming.md](./standardize-database-table-and-field-naming.md) (EN)

Dieses Dokument beschreibt, **wie wir Datenbank-Migrationen** im Rahmen des [Schema-Standardisierungsplans](./standardize-database-table-and-field-naming.md) ausführen: Reihenfolge in der Pipeline, Benennung der SQL-Dateien und MariaDB-Muster für additive Änderungen, Spalten- und Tabellenumbenennungen.

Anwendungs-Releases und Schema-Änderungen können in **einer Version** gebündelt werden; die Pipeline führt Migrationen in der richtigen Reihenfolge aus — **Expand (und additive SQL) vor neuem Code**, **Contract (Aufräumen) nach neuem Code** — damit alte und neue Instanzen beim Rollout parallel laufen können, ohne fehlerhafte Abfragen oder unnötige Ausfallzeit.

---

## Industriestandard: Expand–Contract (Parallel Change)

Referenz: [Martin Fowler — Parallel Change](https://martinfowler.com/bliki/ParallelChange.html)

Niemals eine Änderung ausrollen, bei der Code und Schema im selben Moment umschalten müssen. Beim Rollout laufen alte und neue Instanzen parallel (Rolling Update). Das Schema muss alle Kombinationen abdecken:

| Alter Code                    | Neuer Code                   |
| ----------------------------- | ---------------------------- |
| altes Schema                  | neues Schema                 |
| altes Schema ✓                | neues Schema ✓ (nach Expand) |
| altes Schema ✓ (bis Contract) | neues Schema ✓               |

Ein breaking `RENAME COLUMN` verletzt das. Aufteilen in:

1. **Expand-Migration** — additiv, abwärtskompatibel (Spalte hinzufügen, Backfill, Sync)
2. **Code deployen** — Release mit neuen Namen
3. **Contract-Migration** — destruktive Bereinigung (alte Spalte droppen), erst wenn neuer Code überall läuft

---

## Eine eAppointment-Version, ein Deployment

**Ja.** Ein getaggtes Release (z. B. `2.26.00`) kann bündeln:

- `*-expand-*.sql`
- Anwendungscode (Query-Klassen usw.)
- `*-contract-*.sql`

Ein Merge → ein Container-Image-Build → ein Deployment-Tag.

**Was nicht sicher ist**

- Expand + Contract in einer SQL-Datei, die nach dem Code läuft
- Bloßes `RENAME COLUMN` ohne Dual-Column- / View-Schicht
- Contract ausführen, bevor alle Instanzen (und Cronjobs) auf dem neuen Tag sind

### Ziel-Pipeline (ein Tag)

```text
Tag Images
  → Migrate Expand     (vor Deploy; Expand + additive SQL)
  → Provision            (Helm Rolling Update)
  → Migrate Contract     (nach Deploy; Contract-SQL; manuelles Gate in Produktion)
  → Tools
```

**Hinweis:** Contract in derselben Pipeline ist nur sicher, wenn keine alten Instanzen oder Cronjobs noch alte Spaltennamen nutzen. In **Produktion** **Migrate Contract** manuell freigeben (gleicher Tag — Job kann später nachgeholt werden).

### Lücke in der aktuellen Pipeline

Migrate läuft heute in der **laufenden** `zmsapi`-Instanz:

```bash
bin/migrate --update   # im laufenden zmsapi-Container
```

Zum Expand-Zeitpunkt ist das noch das **alte Image** — ohne die neuen Migrationsdateien.

**Erforderliche Änderung:** **Migrate Expand** muss vom **neuen Image** **vor** Provision laufen, z. B. als einmaliger Migrations-Job:

```bash
bin/migrate --update --phase=pre
# im Container des neuen zmsapi-Images, vor Helm-Upgrade
```

Contract analog nach Provision oder im aktualisierten Deployment.

---

## Migrations-Dateikonvention

Ein Ordner: `zmsdb/migrations/`. Unterscheidung per **Dateinamen-Präfix** (oder `--phase` in `bin/migrate`).

### Welcher Pipeline-Job für welche Migration?

**Empfehlung:** den einzelnen post-deploy-`Migrate`-Job durch **zwei Jobs** ersetzen. Keinen dritten „normalen Migrate“-Job nach Provision.

| Migrationstyp                                             | Dateiname      | Pipeline-Job         | Wann                                |
| --------------------------------------------------------- | -------------- | -------------------- | ----------------------------------- |
| **Expand** (Umbenennung, Dual Columns, Backfill, Trigger) | `*-expand-*`   | **Migrate Expand**   | Vor Deploy (neues Image)            |
| **Normal / additiv** (`ADD COLUMN`, neue Tabelle, …)      | ohne Präfix    | **Migrate Expand**   | Vor Deploy                          |
| **Contract** (`DROP`, Trigger/View entfernen)             | `*-contract-*` | **Migrate Contract** | Nach Deploy (manuell in Produktion) |

**Warum additive Migrationen zu Expand gehören**

- Abwärtskompatibel: alter Code ignoriert neue Spalten; neuer Code braucht sie ggf. sofort.
- Nach Provision würde sich der Vorfall wiederholen.

**`bin/migrate` (Vorschlag)**

```bash
bin/migrate --update --phase=pre   # *-expand-* und unprefixed (nur additiv)
bin/migrate --update --phase=post  # nur *-contract-*
```

Unprefixed-Dateien: CI-Lint **nur additiv** (kein `DROP`, `RENAME`, …). Breaking Changes: `-expand-` / `-contract-`-Paar.

### Beispiel: nur additiv — `custom_text_field3` (kein Contract)

Wie [`91744880189-add-standort-custom-text-field2.sql`](../../../../zmsdb/migrations/91744880189-add-standort-custom-text-field2.sql). Unprefixed, nur **Migrate Expand**, kein Contract.

### Beispiel: Code und Spalten löschen (nur Contract)

**Drops sind nie unprefixed.** Provision (Code weg) → **Migrate Contract** (`DROP COLUMN`).

### Kurzübersicht Benennung

|                       | Unprefixed | `*-expand-*` | `*-contract-*`   |
| --------------------- | ---------- | ------------ | ---------------- |
| `ADD COLUMN`          | ✓          | optional     | ✗                |
| `DROP COLUMN`         | ✗          | ✗            | ✓                |
| Dual Column + Trigger | ✗          | ✓            | Drop im Contract |

---

## MariaDB: zwei Spalten während des Rollouts synchron halten

Bei **Spaltenumbenennung:** zwei physische Spalten + Trigger.

- Alte Instanzen: `StandortID`
- Neue Instanzen: `scope_id`
- Trigger halten Werte gleich

Kein Datenverlust bei korrektem Backfill/Sync. Risiko: **Drift** oder **Contract zu früh**.

---

## Tabellenumbenennung (`standort` → `scope`, …)

Kompatibilitätsschicht: **VIEW** auf den alten Namen.

Nach Expand gibt es nur **eine** physische Tabelle (`scope`). `standort` ist eine View — keine zweite Datenkopie. Alter Code schreibt/liest über die View in `scope`; neuer Code nutzt `scope` direkt. Beide sehen dieselben Zeilen. CRUD während des Rollouts möglich, wenn Schreibzugriffe über die View funktionieren — auf Staging testen.

### Muster

| Phase        | SQL                                                                                | Nutzung                  |
| ------------ | ---------------------------------------------------------------------------------- | ------------------------ |
| **Expand**   | `RENAME TABLE standort TO scope;` + `CREATE VIEW standort AS SELECT * FROM scope;` | Alt: View. Neu: Tabelle. |
| **Code**     | `const TABLE = 'scope'`                                                            | Nur neuer Code.          |
| **Contract** | `DROP VIEW standort;`                                                              | Nur `scope` bleibt.      |

**Empfehlung:** Tabellen- und Spaltenumbenennung in getrennte Releases splitten.

Tabellenumbenennung ist **nie unprefixed**.

---

## Beispiel: `standort.StandortID` → `scope_id`

1. **Expand:** `scope_id` hinzufügen, Backfill, Trigger
2. **Code:** Mapping in [`Scope.php`](../../../../zmsdb/src/Zmsdb/Query/Scope.php) auf `scope_id`
3. **Contract:** Trigger droppen, `StandortID` droppen

---

## Post-Mortem: fehlgeschlagene Rename-Migration

[`91775568666`](../../../../zmsdb/migrations/91775568666-rename-waiting-way-processing-columns.sql) hätte Expand → Code → Contract sein müssen, nicht bloßes `RENAME COLUMN`.
