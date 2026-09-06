# Quick reset of the local environment

These commands wipe **all** containers, volumes, and images managed by Podman on your machine (not only this repository). Use when you want a clean slate, then bring the stack back up.

```bash
podman rm -af && \
podman volume rm -af && \
podman rmi -af
```

```bash
devcontainer up --workspace-folder .
```

If there is nothing to remove, a step may print a harmless error; you can ignore that and continue.

For the normal startup flow and endpoints, see
[Devcontainer and Podman](/setup-and-development/getting-started/devcontainer).
