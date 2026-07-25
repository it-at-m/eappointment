---
outline: deep
---

# Podman and Dev Containers on macOS (Podman 6.x)

::: info
**Guide for Podman 6.x** (Homebrew, Apple Silicon, krunkit 1.2.x+).  
Legacy guide for Podman 5.8: [Podman and Dev Containers (Legacy)](./podman-and-dev-containers-legacy.md)
:::

These steps extend [DDEV and Devcontainer — Devcontainer (Podman)](https://it-at-m.github.io/eappointment/en/setup-and-development/getting-started/ddev-and-devcontainer.html#devcontainer-podman) when you work on **macOS** (Apple Silicon) with Podman and Dev Containers.

Podman 6.x (Homebrew) can use **libkrun** via **krunkit**. You need krunkit **1.2.x+** from the `libkrun/krun` tap, and you must set `CONTAINERS_MACHINE_PROVIDER=libkrun` (Homebrew’s default provider is still `applehv`). The older `slp/krunkit` tap (krunkit 1.1.x) is deprecated and causes `Error: krunkit exited unexpectedly with exit code 2` with Podman 6.0. Homebrew may still redirect `slp/krun` → `libkrun/krun`; use `libkrun/krun` directly to avoid duplicate-tap conflicts.

## All-in-one (copy-paste)

Run from the **repository root** in **zsh**. You will be prompted for your macOS password (`sudo` for `podman-mac-helper`). Adjust `--memory` if needed.

```bash
setopt NULL_GLOB && (podman machine stop 2>/dev/null || true) && (podman machine rm -f 2>/dev/null || true) && rm -rf ~/.config/containers ~/.local/share/containers ~/.cache/containers ~/Library/Containers/io.podman* ~/Library/Application\ Support/Podman* ~/Library/Preferences/io.podman* && (brew uninstall -f podman podman-desktop 2>/dev/null || true) && brew cleanup && (brew list --full-name 2>/dev/null | grep "^slp/krunkit/" | xargs brew uninstall 2>/dev/null || true) && (brew untap slp/krunkit 2>/dev/null || true) && (brew untap slp/krun 2>/dev/null || true) && brew tap libkrun/krun && brew trust libkrun/krun && brew install libkrun/krun/krunkit && brew install podman && brew install --cask podman-desktop && export CONTAINERS_MACHINE_PROVIDER=libkrun && (grep -q 'CONTAINERS_MACHINE_PROVIDER=libkrun' ~/.zshrc || echo 'export CONTAINERS_MACHINE_PROVIDER=libkrun' >> ~/.zshrc) && (grep -q 'CONTAINERS_MACHINE_PROVIDER=libkrun' ~/.bashrc 2>/dev/null || echo 'export CONTAINERS_MACHINE_PROVIDER=libkrun' >> ~/.bashrc) && podman machine init --cpus 4 --memory 4096 --disk-size 100 && sudo "$(brew --prefix podman)/bin/podman-mac-helper" install && (podman machine stop 2>/dev/null || true) && podman machine start && (grep -q 'DOCKER_HOST=unix:///var/run/docker.sock' ~/.zshrc || echo 'export DOCKER_HOST=unix:///var/run/docker.sock' >> ~/.zshrc) && (grep -q 'DOCKER_HOST=unix:///var/run/docker.sock' ~/.bashrc 2>/dev/null || echo 'export DOCKER_HOST=unix:///var/run/docker.sock' >> ~/.bashrc) && export DOCKER_HOST=unix:///var/run/docker.sock && podman machine list && podman ps && (command -v devcontainer >/dev/null || npm install -g @devcontainers/cli) && devcontainer up --workspace-folder .
```

Step-by-step breakdown of the same flow:

## 1. Stop and remove any Podman machines

```bash
podman machine stop 2>/dev/null && podman machine rm -f 2>/dev/null
```

## 2. Delete all Podman configuration and VM data

```bash
# NULL_GLOB: zsh otherwise errors with "no matches found" when paths are absent
setopt NULL_GLOB
rm -rf ~/.config/containers ~/.local/share/containers ~/.cache/containers
rm -rf ~/Library/Containers/io.podman*
rm -rf ~/Library/Application\ Support/Podman*
rm -rf ~/Library/Preferences/io.podman*
```

## 3. Uninstall Homebrew Podman and Podman Desktop

```bash
brew uninstall -f podman podman-desktop 2>/dev/null && brew cleanup
```

## 4. Install krunkit (Podman 6.x)

Podman 6.x expects krunkit **1.2.x+** with `--timesync` support. Install from the `libkrun/krun` tap (not the deprecated `slp/krunkit` tap). Do **not** also tap `slp/krun` — Homebrew redirects it to `libkrun/krun`, which creates duplicate formulae (`gvproxy`, etc.) and breaks the install.

```bash
# Remove old / duplicate taps if present
brew list --full-name 2>/dev/null | grep "^slp/krunkit/" | xargs brew uninstall 2>/dev/null
brew untap slp/krunkit 2>/dev/null
brew untap slp/krun 2>/dev/null

brew tap libkrun/krun
brew trust libkrun/krun
brew install libkrun/krun/krunkit
krunkit --version   # should show 1.2.x or newer
```

If Homebrew rejects the tap as untrusted, run `brew trust libkrun/krun` and retry.

## 5. Install Podman CLI and Podman Desktop

```bash
brew install podman && brew install --cask podman-desktop
```

## 6. Initialize a new Podman machine (example: 4 GB RAM)

On Apple Silicon, use the **libkrun** provider (not the default `applehv`/`vfkit`). Set the provider before `init`, and keep it in your shell profile so later `start`/`stop` use the same backend:

```bash
export CONTAINERS_MACHINE_PROVIDER=libkrun
echo 'export CONTAINERS_MACHINE_PROVIDER=libkrun' >> ~/.zshrc
echo 'export CONTAINERS_MACHINE_PROVIDER=libkrun' >> ~/.bashrc

# Adjust --memory to your available RAM (4096 MB is a good starting point on a 16 GB host)
podman machine init --cpus 4 --memory 4096 --disk-size 100
```

`podman machine list` should show `VM TYPE` as `libkrun`. If you previously created an `applehv` machine, remove it first (`podman machine rm -f`) and init again with the provider set.

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

| Symptom                                           | Cause                                     | Fix                                                                                 |
| ------------------------------------------------- | ----------------------------------------- | ----------------------------------------------------------------------------------- |
| `krunkit exited unexpectedly with exit code 2`    | krunkit 1.1.x with Podman 6.x             | Upgrade krunkit via `libkrun/krun` to 1.2.x+ (step 4)                               |
| `Formulae found in multiple taps` (`gvproxy`)     | Both `slp/krun` and `libkrun/krun` tapped | `brew untap slp/krun`, then install from `libkrun/krun`                             |
| `Refusing to load formula ... from untrusted tap` | Tap trust missing                         | `brew trust libkrun/krun`                                                           |
| `Cannot connect to Podman` / `connection refused` | VM not running or wrong socket            | `podman machine start`; check `DOCKER_HOST` (step 7)                                |
| Ignition emergency mode / `applehv` VM stuck      | Default `applehv` provider, or stale VM   | `podman machine rm -f`, set `CONTAINERS_MACHINE_PROVIDER=libkrun`, re-init (step 6) |
| `devcontainer: command not found`                 | CLI not installed                         | Step 9                                                                              |
