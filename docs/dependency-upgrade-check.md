# Dependency Upgrade Check

Pass the PHP version that you would want to upgrade to and recieve information about dependency changes patch, minor, or major for each module.
e.g.

```bash
# DDEV
ddev exec ./cli modules check-upgrade 8.4
```

```bash
# Podman
podman exec -it zms-web bash -lc "./cli modules check-upgrade 8.4"
```

Adjust the version as needed (for example `8.4`) to review patch/minor/major dependency impact per module.
