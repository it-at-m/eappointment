---
outline: deep
---

# Podman and Dev Containers on macOS

These steps extend [Using Podman (Devcontainer)](https://github.com/it-at-m/eappointment/blob/main/README.md#using-podman-devcontainer) in the repository `README` when you work on **macOS** with Podman and Dev Containers.

You may need to install the missing `krunkit` package before installing Podman, and set `export DOCKER_HOST=unix:///var/run/docker.sock` in your `~/.zshrc` or `~/.bashrc` (or export it in the terminal) before using `devcontainer` commands.

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

## 4. Install krunkit (Podman 5.8.0)

Podman 5.8.0 can require [krunkit](https://github.com/containers/podman/issues/27056#issuecomment-3434700252).

```bash
brew tap slp/krunkit && brew install krunkit && krunkit --version
```

## 5. Install Podman CLI and Podman Desktop

```bash
brew install podman && brew install --cask podman-desktop
```

## 6. Initialize a new QEMU VM for Podman (example: 8 GB RAM)

```bash
podman machine init --cpus 4 --memory 8192 --disk-size 100
```

## 7. Export a Docker-compatible socket for Dev Containers

```bash
echo 'export DOCKER_HOST=unix:///var/run/docker.sock' >> ~/.zshrc && echo 'export DOCKER_HOST=unix:///var/run/docker.sock' >> ~/.bashrc
source ~/.zshrc && source ~/.bashrc
```

## 8. Start the VM and list machines

```bash
podman machine stop && podman machine start && podman machine list
```

## 9. Start the project

From the repository root:

```bash
devcontainer up --workspace-folder .
```
