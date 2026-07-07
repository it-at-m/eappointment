---
outline: deep
---

# Podman und Dev Containers unter macOS (Legacy, Podman 5.8)

::: warning Veraltet
Diese Anleitung galt für **Podman 5.8.x** mit dem Tap `slp/krunkit` (krunkit 1.1.x). Mit **Podman 6.x** (Homebrew) führt sie zu `Error: krunkit exited unexpectedly with exit code 2`.

**Aktuelle Anleitung:** [Podman und Dev Containers (Podman 6.x)](./podman-and-dev-containers.md)
:::

Diese Schritte ergänzen [DDEV und Devcontainer — Devcontainer (Podman)](https://it-at-m.github.io/eappointment/de/setup-and-development/getting-started/ddev-and-devcontainer.html#devcontainer-podman), wenn du auf **macOS** mit Podman und Dev Containers arbeitest.

Möglicherweise musst du das fehlende Paket `krunkit` vor der Podman-Installation installieren und in deiner `~/.zshrc` oder `~/.bashrc` `export DOCKER_HOST=unix:///var/run/docker.sock` setzen (oder in der Shell exportieren), bevor du `devcontainer`-Befehle nutzt.

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

## 4. krunkit installieren (Podman 5.8.0)

Podman 5.8.0 kann [krunkit](https://github.com/containers/podman/issues/27056#issuecomment-3434700252) erforderlich machen.

```bash
brew tap slp/krunkit && brew install krunkit && krunkit --version
```

## 5. Podman-CLI und Podman Desktop installieren

```bash
brew install podman && brew install --cask podman-desktop
```

## 6. Eine neue QEMU-VM für Podman initialisieren (Beispiel: 8 GB RAM)

```bash
podman machine init --cpus 4 --memory 8192 --disk-size 100
```

## 7. Docker-kompatiblen Socket für Dev Containers exportieren

```bash
echo 'export DOCKER_HOST=unix:///var/run/docker.sock' >> ~/.zshrc && echo 'export DOCKER_HOST=unix:///var/run/docker.sock' >> ~/.bashrc
source ~/.zshrc && source ~/.bashrc
```

## 8. VM starten und Maschinen auflisten

```bash
podman machine stop && podman machine start && podman machine list
```

## 9. Projekt starten

Vom Repository-Wurzelverzeichnis:

```bash
devcontainer up --workspace-folder .
```
