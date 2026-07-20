---
outline: deep
---

# Podman und Dev Containers unter macOS (Podman 6.x)

::: info
**Anleitung für Podman 6.x** (Homebrew, Apple Silicon, krunkit 1.2.x).  
Legacy-Anleitung für Podman 5.8: [Podman und Dev Containers (Legacy)](./podman-and-dev-containers-legacy.md)
:::

Diese Schritte ergänzen [DDEV und Devcontainer — Devcontainer (Podman)](https://it-at-m.github.io/eappointment/de/setup-and-development/getting-started/ddev-and-devcontainer.html#devcontainer-podman), wenn du auf **macOS** (Apple Silicon) mit Podman und Dev Containers arbeitest.

Podman 6.x (Homebrew) nutzt standardmäßig **libkrun** über **krunkit**. Dafür brauchst du krunkit **1.2.x** aus dem Tap `slp/krun`. Der ältere Tap `slp/krunkit` (krunkit 1.1.x) ist veraltet und führt bei Podman 6.0 zu `Error: krunkit exited unexpectedly with exit code 2`.

## 1. Vorhandene Podman-Maschinen stoppen und entfernen

```bash
podman machine stop 2>/dev/null && podman machine rm -f 2>/dev/null
```

## 2. Podman-Konfiguration und VM-Daten vollständig löschen

```bash
rm -rf ~/.config/containers ~/.local/share/containers ~/.cache/containers && rm -rf ~/Library/Containers/io.podman* 2>/dev/null || true && rm -rf ~/Library/Application\ Support/Podman* 2>/dev/null || true && rm -rf ~/Library/Preferences/io.podman* 2>/dev/null || true
```

## 3. Homebrew-Podman und Podman Desktop deinstallieren

```bash
brew uninstall -f podman podman-desktop 2>/dev/null && brew cleanup
```

## 4. krunkit installieren (Podman 6.x)

Podman 6.x erwartet krunkit **1.2.x** mit Unterstützung für `--timesync`. Installiere krunkit aus dem aktuellen Tap `slp/krun` (nicht `slp/krunkit`):

```bash
# Alten Tap entfernen, falls vorhanden
brew list --full-name 2>/dev/null | grep "^slp/krunkit/" | xargs brew uninstall 2>/dev/null
brew untap slp/krunkit 2>/dev/null

brew tap slp/krun
brew trust slp/krun
brew install slp/krun/krunkit
krunkit --version   # sollte 1.2.x anzeigen
```

Falls Homebrew den Tap als „untrusted“ ablehnt: `brew trust slp/krun` ausführen und den Befehl wiederholen.

## 5. Podman-CLI und Podman Desktop installieren

```bash
brew install podman && brew install --cask podman-desktop
```

## 6. Eine neue Podman-Maschine initialisieren (Beispiel: 4 GB RAM)

Auf Apple Silicon wird libkrun (nicht QEMU) verwendet. Passe `--memory` an den verfügbaren RAM an (bei 16 GB Host-RAM sind 4096 MB ein guter Startwert):

```bash
podman machine init --cpus 4 --memory 4096 --disk-size 100
```

## 7. Docker-kompatiblen Socket für Dev Containers einrichten

`export DOCKER_HOST=unix:///var/run/docker.sock` funktioniert **nur**, wenn Podman diesen Socket bereitstellt. Dafür zuerst den macOS-Helfer installieren:

```bash
sudo $(brew --prefix podman)/bin/podman-mac-helper install
podman machine stop && podman machine start
```

Danach in `~/.zshrc` und `~/.bashrc` (oder nur in der aktuellen Shell):

```bash
echo 'export DOCKER_HOST=unix:///var/run/docker.sock' >> ~/.zshrc && echo 'export DOCKER_HOST=unix:///var/run/docker.sock' >> ~/.bashrc && source ~/.zshrc
```

**Alternative ohne Helfer:** Podman-Socket über `TMPDIR` setzen (funktioniert mit dem separaten `docker`-CLI, das `devcontainer` aufruft):

```bash
echo 'export DOCKER_HOST="unix://${TMPDIR}podman/podman-machine-default-api.sock"' >> ~/.zshrc
echo 'export DOCKER_HOST="unix://${TMPDIR}podman/podman-machine-default-api.sock"' >> ~/.bashrc
source ~/.zshrc
docker ps   # sollte ohne Fehler laufen
```

## 8. VM starten und prüfen

```bash
podman machine start && podman machine list && podman ps
```

## 9. Dev-Container-CLI installieren

Falls `devcontainer` noch nicht verfügbar ist:

```bash
npm install -g @devcontainers/cli
```

## 10. Projekt starten

Vom Repository-Wurzelverzeichnis:

```bash
devcontainer up --workspace-folder .
```

## Fehlerbehebung

| Symptom                                           | Ursache                             | Lösung                                                      |
| ------------------------------------------------- | ----------------------------------- | ----------------------------------------------------------- |
| `krunkit exited unexpectedly with exit code 2`    | krunkit 1.1.x mit Podman 6.x        | krunkit über `slp/krun` auf 1.2.x aktualisieren (Schritt 4) |
| `Refusing to load formula ... from untrusted tap` | Tap-Vertrauen fehlt                 | `brew trust slp/krun`                                       |
| `Cannot connect to Podman` / `connection refused` | VM läuft nicht oder falscher Socket | `podman machine start`; `DOCKER_HOST` prüfen (Schritt 7)    |
| `devcontainer: command not found`                 | CLI fehlt                           | Schritt 9                                                   |
