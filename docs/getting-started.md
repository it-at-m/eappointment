# Getting Started

## Using DDEV

```bash
ddev start
ddev exec ./cli modules loop composer install
ddev exec ./cli modules loop npm install
ddev exec ./cli modules loop npm build
```

## Using Podman and Devcontainer

```bash
devcontainer up --workspace-folder .
podman exec -it zms-web bash -lc "./cli modules loop composer install"
podman exec -it zms-web bash -lc "./cli modules loop npm install"
podman exec -it zms-web bash -lc "./cli modules loop npm build"
```

## Keycloak Host Mapping

Add `keycloak` to your hosts file:

```bash
echo "127.0.0.1 keycloak" | sudo tee -a /etc/hosts
```

Then restart containers:

```bash
podman restart zms-keycloak
# or
ddev restart
```

## Database Setup

For a complete local setup:

```bash
# DDEV
ddev exec ./cli db full-setup

# Podman
podman exec -it zms-web bash -lc "./cli db full-setup"
```

Use this together with the install/build commands shown above.
