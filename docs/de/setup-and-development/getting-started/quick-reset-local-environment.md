# Schnelles Zurücksetzen der lokalen Umgebung

Diese Befehle entfernen **alle** Container, Volumes und Images, die Podman auf deinem Rechner verwaltet (nicht nur die dieses Repositories). Verwende sie, wenn du einen sauberen Stand willst und den Stack anschließend neu hochfährst.

```bash
podman rm -af && \
podman volume rm -af && \
podman rmi -af
```

```bash
devcontainer up --workspace-folder .
```

Wenn nichts zu entfernen ist, kann ein Schritt eine harmlose Fehlermeldung ausgeben; das kannst du ignorieren und fortfahren.

Den normalen Startprozess und die Endpunkte beschreibt
[Devcontainer und Podman](/de/setup-and-development/getting-started/devcontainer).
