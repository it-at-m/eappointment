# Schnelles Zurücksetzen der lokalen Umgebung

Diese Befehle entfernen **alle** Container, Volumes und Images, die von der jeweiligen Engine auf deinem Rechner verwaltet werden (nicht nur die dieses Repositories). Verwende sie, wenn du einen sauberen Stand willst und den Stack anschließend neu hochfährst.

## Podman (Devcontainer-Workflow)

```bash
podman rm -af && \
podman volume rm -af && \
podman rmi -af
```

```bash
devcontainer up --workspace-folder .
```

## DDEV (Docker-Engine)

DDEV ersetzt nicht die Image-/Volume-APIs von Docker. Dieselbe Klasse von Reset wird über die Docker-CLI durchgeführt; danach wird das Projekt neu gestartet:

```bash
docker ps -aq | xargs docker rm -f && \
docker volume ls -q | xargs docker volume rm && \
docker images -aq | xargs docker rmi -f && \
```

```bash
ddev start
```

Wenn nichts zu entfernen ist, kann ein Schritt eine harmlose Fehlermeldung ausgeben (z. B. `docker rm` ohne Container-IDs); das kannst du ignorieren und fortfahren.

Den normalen Startprozess und die Endpunkte beschreibt
[DDEV und Devcontainer](/de/setup-and-development/getting-started/ddev-and-devcontainer).
