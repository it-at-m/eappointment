# Erste Schritte mit der Dokumentation

Die Dokumentations-Site dieses Repositories liegt im Ordner `docs` und wird mit [VitePress](https://vitepress.dev/) gebaut.

## Branching und GitHub Pages

**Reine Doc-Änderungen** (Handbuch-Updates ohne Produktcode in derselben Änderung) sollen demselben Branch-Flow wie ein **Hotfix** folgen, nicht dem Feature-Flow auf `next`:

- **Vom `main`-Branch abzweigen**, nicht von `next`. Reine Doc-Arbeit nicht auf `next` aufsetzen.
- Pull Request öffnen und nach Fertigstellung in **`main` mergen**.
- Anschließend **`main` in `next` mergen**, damit `next` die Doc-Updates erhält (Merge-back-Schritt nach Hotfixes).

Wenn deine Arbeit ein **Feature oder Bugfix** ist, das auch `docs/` betrifft, folge dem **normalen Prozess** dafür (z. B. von `next` abzweigen und dort den üblichen PR öffnen). Die Doc-Änderungen kommen in denselben Feature-/Bugfix-Branch; ein separater Doc-Only-Branch von `main` ist dafür nicht nötig.

Details und Diagramme zu beiden Flows stehen in [Branching-Strategie und -Konvention](/de/setup-and-development/development-rules/branching-strategy-and-convention).

Das Handbuch auf **[GitHub Pages](https://it-at-m.github.io/eappointment/)** wird aus dem Branch **`main`** veröffentlicht. Reine Doc-Fixes sollten zuerst nach `main`, damit die Site schnell aktualisiert wird; Dokumentation, die zu einem Feature oder Bugfix gehört, erreicht `main` mit dem üblichen Release-Pfad dieser Änderung.

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

## Konfiguration und Theme

- Site-Konfiguration: `docs/.vitepress/config.mjs`
- Eigene Theme-Bausteine: `docs/.vitepress/theme/`

Die veröffentlichte Site nutzt `base: /eappointment/` in der Konfiguration. Der lokale `docs:dev`-Modus serviert hingegen vom Dev-Server-Root; falls Asset-Pfade auffällig sind, vergleiche das Verhalten mit `docs:preview` nach einem `docs:build`.

## In GitHub Codespaces

Wenn dein Codespace die Node-Tools enthält, nutze dieselben Befehle aus dem Repo-Workspace, nachdem du den `docs`-Ordner geöffnet hast. Stelle sicher, dass die Port-Weiterleitung für den Dev-Server-Port aktiv ist, den VitePress meldet, damit du ihn im Browser öffnen kannst.
