# Git-Hooks (Husky)

Dieses Repository nutzt [Husky](https://github.com/typicode/husky), um Git-Hooks aus dem Root-Verzeichnis `.husky/` auszuführen. Die Hooks laufen automatisch an festen Punkten im Git-Workflow und halten Code-Stil und Commit-Messages im Monorepo konsistent.

Die Skripte liegen in [`.husky/`](https://github.com/it-at-m/eappointment/tree/main/.husky) (`pre-commit`, `commit-msg`).

## Einrichtung

Die Hooks werden im **Repository-Root** eingerichtet:

```bash
npm install
```

Das `prepare`-Skript läuft automatisch und zeigt Git auf `.husky/`.

> [!NOTE]
> Husky liegt im **Root-**`package.json`, weil die Hooks für den **gesamten Monorepo** gelten. Vue-Lint nutzt weiter `zmscitizenview`; Docs-Formatierung nutzt `docs/`.

Nach dem Klonen einmal `npm install` im Repo-Root ausführen (steht auch im [Root-README](https://github.com/it-at-m/eappointment/blob/main/README.md)). Für Doc-Änderungen einmal Docs-Abhängigkeiten installieren: `cd docs && npm install`.

## Hooks

### `pre-commit`

Läuft vor jedem Commit und prüft die Codequalität.

**Prüfungen:**

1. **Vue-Code-Stil** — Prettier-Check in `zmscitizenview` (`npm run lint`)
2. **Docs-Formatierung** — Prettier-Check in `docs/` (`npm run format:check`)
3. **PHP-Code-Stil** — PHP CodeSniffer (PSR-12) über alle PHP-Module im Container `zms-web`

**Container-Erkennung**

Die PHP-Prüfung erkennt die Laufzeit automatisch:

- **Podman** — wenn ein Container `zms-web` läuft
- **Docker** — Fallback, wenn Podman nicht verfügbar ist
- **Überspringen** — wenn kein Container läuft (nur Warnung, nicht blockierend)

**Verhalten**

- Vue- und Docs-Prüfungen laufen immer und **blockieren** den Commit bei Fehlern
- PHP-Prüfungen nur bei laufendem `zms-web`; sonst Warnung und Überspringen

Siehe auch [Code-Formatierung](./code-formatting.md) für manuelle PHPCS-/Prettier-Befehle.

### `commit-msg`

Validiert nur die **erste Zeile** der Commit-Message (Subject), damit mehrzeilige Bodies die Regeln nicht umgehen können.

**Format**

```txt
type(PROJECT-123): commit message
type(PROJECT): commit message
```

Die Ticketnummer ist **optional** — `PROJECT-123` oder nur `PROJECT` (Großbuchstaben).

**Merge-Commits**

Gits Standard-Merge-Subjects (z. B. `Merge branch 'main' into feature-branch`) werden **automatisch akzeptiert**, damit `git merge` ohne Umbenennung der Message abschließen kann. Optional geht auch eine konventionelle Message, z. B. `chore(ZMS): merge main into feature-branch`.

Vollständige Regeln, Typen, Projekte und Beispiele: [Commit-Message-Konvention](./development-rules/commit-message-convention.md).

## Fehlerbehebung

### Vue-Lint schlägt fehl

```bash
cd zmscitizenview
npm run format
```

Danach erneut committen.

### Docs-Formatierung schlägt fehl

Wenn Prettier unter `docs/` Fehler meldet:

```bash
cd docs
npm run format
```

Bei Bedarf zuerst Abhängigkeiten installieren: `cd docs && npm install`.

### PHP CodeSniffer schlägt fehl

Der Hook gibt den Fix-Befehl für Ihre Container-Engine aus:

```bash
# Podman
podman exec -it zms-web bash -lc "./cli modules loop 'vendor/bin/phpcbf --standard=psr12 src/'"

# Docker
docker exec -it zms-web bash -lc "./cli modules loop 'vendor/bin/phpcbf --standard=psr12 src/'"
```

### Container nicht erkannt

Wenn eine Warnung erscheint, dass `zms-web` nicht läuft:

```bash
podman ps | grep zms-web
docker ps | grep zms-web
```

Dev-Umgebung bei Bedarf starten ([DDEV und Devcontainer](./getting-started/ddev-and-devcontainer.md), [Podman und Dev Containers](./getting-started/macos-local-configuration/podman-and-dev-containers.md)). Ohne Container werden PHP-Prüfungen übersprungen; der Commit wird allein deshalb nicht wegen PHP blockiert.

### Ungültiges Commit-Message-Format

Typische Fehler:

- Projekt fehlt: `feat: add feature`
- Projekt kleingeschrieben: `feat(zms-123): add feature`
- Doppelpunkt fehlt: `feat(ZMS-123) add feature`

Korrekte Beispiele:

- `feat(ZMS-123): add feature`
- `chore(ZMSKVR): clean up`

### Merge durch commit-msg blockiert

Wenn `git merge` mit „Invalid commit message format“ und einem Subject wie `Merge branch 'main' into …` scheitert, `.husky/commit-msg` vom aktuellen `main` oder Feature-Branch holen (Merge-Subjects sollten erlaubt sein). Workaround mit konventioneller Message:

```bash
git commit -m "chore(ZMS): merge main into your-branch"
```

## Hooks umgehen

In Notfällen Git-Flag `--no-verify` verwenden:

```bash
git commit --no-verify -m "feat(ZMS-123): urgent fix"
```

> [!CAUTION]
> `--no-verify` nur im Notfall nutzen. Umgangene Hooks können die Codequalität verschlechtern und andere Entwickler betreffen.

## Verwandte Dokumentation

- [Commit-Message-Konvention](./development-rules/commit-message-convention.md)
- [Code-Formatierung](./code-formatting.md)
- [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/)
- [Husky-Dokumentation](https://github.com/typicode/husky)
