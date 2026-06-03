# Codequalitäts-Prüfungen

Diese Seite fasst die Formatierungs- und Codequalitäts-Prüfungen zusammen, die in den eappointment-Modulen verwendet werden.
Da das Repository PHP-, JavaScript-/TypeScript- und Java-Komponenten enthält, nutzt jeder Modulbereich eigene Werkzeuge und Befehle.
Git-Hooks ([Git-Hooks (Husky)](./git-hooks.md)) führen viele dieser Prüfungen vor jedem Commit automatisch aus, wenn Husky eingerichtet ist.

## PHP-Formatierung

Wir nutzen PHPCS (gemäß PSR-12) und PHPMD, um die Codequalität zu sichern und potenzielle Probleme früh zu erkennen. Diese Prüfungen laufen automatisch in unserer GitHub-Actions-Pipeline, können aber auch lokal ausgeführt werden.

### Mit DDEV

```bash
ddev exec "./cli modules loop 'vendor/bin/phpcs --standard=psr12 src/'" && \
ddev exec "./cli modules loop 'vendor/bin/phpcbf --standard=psr12 src'"
```

### Mit Podman

```bash
podman exec -it zms-web bash -lc "./cli modules loop 'vendor/bin/phpcs --standard=psr12 src/'" && \
podman exec -it zms-web bash -lc "./cli modules loop 'vendor/bin/phpcbf --standard=psr12 src'"
```

## JS-Formatierung von zmscitizenview

Wir nutzen `prettier-codeformat` zum Prüfen und Formatieren des Codestils in zmscitizenview. Die format-Funktion behebt Codestil-/Lint-Probleme:

1. Wechsle in `zmscitizenview`:

```bash
cd zmscitizenview
```

2. Führe aus:

```bash
npm run format
```

## Maven-Formatierung von zmsautomation

`zmsautomation` nutzt das Maven-Spotless-Plugin für die Java-Formatierung.

In das Modul wechseln:

```bash
cd zmsautomation
```

Formatierung prüfen:

```bash
mvn spotless:check
```

Formatierung anwenden:

```bash
mvn spotless:apply
```
