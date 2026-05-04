# Quick reset of the local environment

These commands wipe **all** containers, volumes, and images managed by that engine on your machine (not only this repository). Use when you want a clean slate, then bring the stack back up.

## Podman (devcontainer workflow)

```bash
podman rm -af && \ 
podman volume rm -af && \ 
podman rmi -af
```

```bash
devcontainer up --workspace-folder .
```

## DDEV (Docker engine)

DDEV does not replace Docker’s image/volume APIs. The same class of reset is done with the Docker CLI, then the project is started again:

```bash
docker ps -aq | xargs docker rm -f && \
docker volume ls -q | xargs docker volume rm && \
docker images -aq | xargs docker rmi -f && \
```

```bash
ddev start
```

If there is nothing to remove, a step may print a harmless error (for example `docker rm` with no container IDs); you can ignore that and continue.

For the normal startup flow and endpoints, see [Getting Started](/getting-started).
