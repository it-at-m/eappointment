# Commit-Message-Konvention

Diese Seite definiert die Commit-Message-Konventionen, die in diesem Repository verwendet werden.

## Commit-Message-Format

Bitte gib in der Commit-Message-Zeile dein Projekt und optional die Ticketnummer an.
Für die grundlegende Commit-Stilsemantik siehe [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/).

Verwende dieses Format:

`<type>(<PROJECT>-<ticket>): <kurze Zusammenfassung>`

oder ohne Ticketnummer:

`<type>(<PROJECT>): <kurze Zusammenfassung>`

Beispiel:

`fix(ZMSKVR-1347): handle unpublished relation filtering`

`chore(ZMS): Abhängigkeiten aktualisieren`

Der Git-Hook `commit-msg` prüft nur die Subject-Zeile. Merge-Commits nutzen automatisch Gits Standard-Subject `Merge branch …`. Siehe [Git-Hooks (Husky)](../git-hooks.md) für Einrichtung und Fehlerbehebung.

## Pflichtbestandteile

1. **type**: Die Art der Änderung. In diesem Repository übliche Werte:
   - `feat`: neue Funktion oder Fähigkeit
   - `fix`: Fehlerbehebung
   - `clean`: Refactoring/Aufräumen ohne Verhaltensänderung
   - `docs`: ausschließlich Dokumentationsänderung
   - `chore`: Wartung/Abhängigkeiten/Build-Tooling-Änderungen

2. **project**: Der Projektkürzel. Erlaubt sind:
   - `ZMS` für das ZMS-Projekt
   - `ZMSKVR` für das ZMSKVR-Projekt
   - `MPDZBS` für das MPDZBS-Projekt
   - `MUXDBS` für das MUXDBS-Projekt
   - `GH` ausschließliche Issue-Nachverfolgung in GitHub

3. **ticket number**: Optionale Ziffern passend zur Ticket-/Issue-ID (z. B. `ZMSKVR-1347`). Die Nummer kann entfallen; dann nur der Projektscope (z. B. `chore(ZMS): …`).

4. **summary**: Eine knappe, in der Befehlsform formulierte Aussage zur Absicht der Änderung.

## Beispiele

- `feat(ZMS-123): add scope hint support for office mapping`
- `fix(ZMSKVR-123): prevent stale provider visibility cache reads`
- `clean(ZMS-123): simplify munich transformer duration merge logic`
- `chore(ZMSKVR-123): update vitepress dependencies`
- `docs(ZMS-123): document sadb visibility decision flow`
- `chore(ZMS): main in Feature-Branch mergen`
- `clean(GH): veralteten Workflow entfernen`

## Regulärer Ausdruck

Die vom Hook `commit-msg` geprüfte Subject-Zeile entspricht:

`^(feat|fix|clean|chore|docs)\((ZMS|ZMSKVR|MPDZBS|MUXDBS|GH)(-[0-9]+)?\): .+$`

Merge-Commits mit Subject `Merge …` sind ausgenommen (siehe [Git-Hooks (Husky)](../git-hooks.md)).

## Weitere Hinweise

- Halte die Subject-Zeile auf das „Warum“ bzw. die Absicht fokussiert, nicht auf einen vollständigen Changelog.
- Nutze einen Body, wenn Kontext nötig ist (z. B. Migrationsnotizen, Verhaltensabwägungen oder Rollout-Implikationen).
- Bevorzuge eine logische Änderung pro Commit, damit Reviews und Cherry-Picks sauber bleiben.
- Für reine Dokumentations-Updates verwende den Typ `docs(...)`.
