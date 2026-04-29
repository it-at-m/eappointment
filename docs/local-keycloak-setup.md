# Local Keycloak Setup

For local development, Keycloak is configured to use the hostname `keycloak` like in the RefArch setup instead of `localhost`.

This is required because:

- Browser redirects on the host must resolve to `127.0.0.1`.
- PHP code running in containers must resolve via container network DNS.
- Inside containers, `localhost` points to the container itself.

## Add `keycloak` to hosts macOS/Linux

```bash
echo "127.0.0.1 keycloak" | sudo tee -a /etc/hosts
```

## Add `keycloak` to hosts Windows

1. Open Notepad as Administrator (right-click -> Run as administrator).
2. Open `C:\Windows\System32\drivers\etc\hosts`.
3. Add this line at the end:

   ```text
   127.0.0.1 keycloak
   ```

4. Save the file.

## Restart the Local Environment and Verify:

After adding the entry, restart Keycloak/container stack:

```bash
# Podman
podman machine stop && \
podman machine start && \
devcontainer up --workspace-folder .
```

```bash
# DDEV
ddev restart
```
Verify:
```bash
ping keycloak
```


## Podman (Linux) note

Podman may merge host `/etc/hosts` into containers, which can break in-container `keycloak` resolution. Add this to `~/.config/containers/containers.conf`:

```ini
[containers]
base_hosts_file="none"
```
