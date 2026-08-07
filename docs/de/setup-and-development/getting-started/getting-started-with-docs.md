# Erste Schritte mit der Dokumentation

Die Dokumentations-Site dieses Repositories liegt im Ordner `docs` und wird mit [VitePress](https://vitepress.dev/) gebaut.

## Branching und GitHub Pages

Das Handbuch auf **[GitHub Pages](https://it-at-m.github.io/eappointment/)** wird aus dem Branch **`next`** veröffentlicht (über `combined-workflow-with-docs` bei Push auf `next`).

**Reine Doc-Änderungen** (Handbuch-Updates ohne Produktcode in derselben Änderung) sollen nach **`next`**, damit die Site aktualisiert wird:

- **Von `next` abzweigen**, Pull Request öffnen und in **`next` mergen**.
- Wenn die Änderung auch auf `main` braucht (z. B. vor einem Release), `next` über den üblichen Weg in `main` mergen oder entsprechend spiegeln.

Wenn deine Arbeit ein **Feature oder Bugfix** ist, das auch `docs/` betrifft, folge dem **normalen Prozess** dafür (von `next` abzweigen, PR nach `next`). Die Doc-Änderungen gehören in denselben Feature-/Bugfix-Branch.

Details und Diagramme stehen in [Branching-Strategie und -Konvention](/de/setup-and-development/development-rules/branching-strategy-and-convention).

## Voraussetzungen

- Node.js (LTS empfohlen), gleiche Major-Version wie sonst im Repo
- npm

## Lokal installieren und starten

Vom Repository-Wurzelverzeichnis:

```bash
cd docs
npm install
npm run docs:dev
```

VitePress gibt eine lokale URL aus (typischerweise `http://localhost:5173`). Öffne sie im Browser, um die Site mit Hot Reload zu durchstöbern, während du Markdown unter `docs/` bearbeitest.

## Weitere Befehle

- **`npm run format`** — formatiert Markdown, Vue, JS und CSS unter `docs/` mit Prettier (gleiches `@muenchen/prettier-codeformat`-Preset wie `zmscitizenview`)
- **`npm run format:check`** — prüft die Formatierung, ohne Dateien zu schreiben (nützlich in CI)
- **`npm run docs:build`** — Produktions-Build; Ausgabe nach `docs/.vitepress/dist`
- **`npm run docs:preview`** — serviert die gebaute Site lokal, um den Build zu prüfen
- **`npm run docs:log-inventory`** — erzeugt `docs/.vitepress/data/log-inventory.json` neu (läuft auch automatisch bei `docs:dev` / `docs:build`)

## Automatisch erzeugte Dokumentation

Einige Seiten werden beim Start oder Build von VitePress generiert:

- **Cucumber-Feature-Liste** — aus `zmsautomation/src/test/resources/features`
- **Monolog-Log-Inventar** — scannt `App::$log`-Aufrufe in ZMS-Modul-PHP; siehe [Monolog-Logging](/de/operations/monolog-logging)

`log-inventory.json` wird lokal und in CI erzeugt und **nicht** committed (siehe `docs/.gitignore`).

## Konfiguration und Theme

- Site-Konfiguration: `docs/.vitepress/config.mjs`
- Eigene Theme-Bausteine: `docs/.vitepress/theme/`

Die veröffentlichte Site nutzt `base: /eappointment/` in der Konfiguration. Der lokale `docs:dev`-Modus serviert hingegen vom Dev-Server-Root; falls Asset-Pfade auffällig sind, vergleiche das Verhalten mit `docs:preview` nach einem `docs:build`.

## In GitHub Codespaces

Wenn dein Codespace die Node-Tools enthält, nutze dieselben Befehle aus dem Repo-Workspace, nachdem du den `docs`-Ordner geöffnet hast. Stelle sicher, dass die Port-Weiterleitung für den Dev-Server-Port aktiv ist, den VitePress meldet, damit du ihn im Browser öffnen kannst.
