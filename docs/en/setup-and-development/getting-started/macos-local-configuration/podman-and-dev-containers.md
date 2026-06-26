---
outline: deep
---

# Podman and Dev Containers on macOS (Podman 6.x)

::: info
**Guide for Podman 6.x** (Homebrew, Apple Silicon, krunkit 1.2.x).  
Legacy guide for Podman 5.8: [Podman and Dev Containers (Legacy)](./podman-and-dev-containers-legacy.md)
:::

These steps extend [DDEV and Devcontainer — Devcontainer (Podman)](https://it-at-m.github.io/eappointment/en/setup-and-development/getting-started/ddev-and-devcontainer.html#devcontainer-podman) when you work on **macOS** (Apple Silicon) with Podman and Dev Containers.

Podman 6.x (Homebrew) uses **libkrun** via **krunkit** by default. You need krunkit **1.2.x** from the `slp/krun` tap. The older `slp/krunkit` tap (krunkit 1.1.x) is deprecated and causes `Error: krunkit exited unexpectedly with exit code 2` with Podman 6.0.

## 1. Stop and remove any Podman machines

```bash
podman machine stop 2>/dev/null && podman machine rm -f 2>/dev/null
```

## 2. Delete all Podman configuration and VM data

```bash
rm -rf ~/.config/containers ~/.local/share/containers ~/.cache/containers && rm -rf ~/Library/Containers/io.podman* 2>/dev/null || true && rm -rf ~/Library/Application\ Support/Podman* 2>/dev/null || true && rm -rf ~/Library/Preferences/io.podman* 2>/dev/null || true
```

## 3. Uninstall Homebrew Podman and Podman Desktop

```bash
brew uninstall -f podman podman-desktop 2>/dev/null && brew cleanup
```

## 4. Install krunkit (Podman 6.x)

Podman 6.x expects krunkit **1.2.x** with `--timesync` support. Install from the current `slp/krun` tap (not `slp/krunkit`):

```bash
# Remove old tap if present
brew list --full-name 2>/dev/null | grep "^slp/krunkit/" | xargs brew uninstall 2>/dev/null
brew untap slp/krunkit 2>/dev/null

brew tap slp/krun
brew trust slp/krun
brew install slp/krun/krunkit
krunkit --version   # should show 1.2.x
```

If Homebrew rejects the tap as untrusted, run `brew trust slp/krun` and retry.

## 5. Install Podman CLI and Podman Desktop

```bash
brew install podman && brew install --cask podman-desktop
```

## 6. Initialize a new Podman machine (example: 4 GB RAM)

On Apple Silicon, libkrun is used (not QEMU). Adjust `--memory` to your available RAM (4096 MB is a good starting point on a 16 GB host):

```bash
podman machine init --cpus 4 --memory 4096 --disk-size 100
```

## 7. Set up a Docker-compatible socket for Dev Containers

`export DOCKER_HOST=unix:///var/run/docker.sock` only works if Podman provides that socket. Install the macOS helper first:

```bash
sudo $(brew --prefix podman)/bin/podman-mac-helper install
podman machine stop && podman machine start
```

Then add to `~/.zshrc` and `~/.bashrc` (or export in the current shell only):

```bash
echo 'export DOCKER_HOST=unix:///var/run/docker.sock' >> ~/.zshrc
echo 'export DOCKER_HOST=unix:///var/run/docker.sock' >> ~/.bashrc
source ~/.zshrc
```

**Alternative without the helper:** Point at Podman's socket via `TMPDIR` (works with the separate `docker` CLI that `devcontainer` invokes):

```bash
echo 'export DOCKER_HOST="unix://${TMPDIR}podman/podman-machine-default-api.sock"' >> ~/.zshrc
echo 'export DOCKER_HOST="unix://${TMPDIR}podman/podman-machine-default-api.sock"' >> ~/.bashrc
source ~/.zshrc
docker ps   # should succeed
```

## 8. Start the VM and verify

```bash
podman machine start && podman machine list && podman ps
```

## 9. Install the Dev Container CLI

If `devcontainer` is not available yet:

```bash
npm install -g @devcontainers/cli
```

## 10. Start the project

From the repository root:

```bash
devcontainer up --workspace-folder .
```

## Troubleshooting

| Symptom                                           | Cause                          | Fix                                                  |
| ------------------------------------------------- | ------------------------------ | ---------------------------------------------------- |
| `krunkit exited unexpectedly with exit code 2`    | krunkit 1.1.x with Podman 6.x  | Upgrade krunkit via `slp/krun` to 1.2.x (step 4)     |
| `Refusing to load formula ... from untrusted tap` | Tap trust missing              | `brew trust slp/krun`                                |
| `Cannot connect to Podman` / `connection refused` | VM not running or wrong socket | `podman machine start`; check `DOCKER_HOST` (step 7) |
| `devcontainer: command not found`                 | CLI not installed              | Step 9                                               |
