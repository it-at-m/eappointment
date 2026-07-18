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

### Podman

```bash
podman machine stop && \
podman machine start && \
devcontainer up --workspace-folder .
```

### DDEV

```bash
ddev restart
```

Verify:

```bash
ping keycloak
```

## Citizen login (zmscitizenview)

Local Vite host pages (`appointment-view.html`, etc.) use the public Keycloak client `dbs-fragments` in realm `zms` (migrations `09_add-citizen-client.yml`, `10_add-citizen-token-mappers.yml`). Defaults live in `zmscitizenview/.env.development`.

The external `dbs-login` loader CDN is often unreachable on local networks. With `VITE_USE_LOCAL_CITIZEN_LOGIN=true`, host pages load `src/local-dbs-login.ts` instead: it listens for `authorization-request`, runs OIDC authorization-code + PKCE against local Keycloak, then emits `authorization-event`.

1. Apply migrations (restart the stack so `init-keycloak` runs, or recreate that service).
2. Restart the Vite / citizenview process so env and the new login scripts load.
3. Open the host page index [http://localhost:8082/webcomponents.html](http://localhost:8082/webcomponents.html) and pick a page (e.g. appointment-view), or go directly to [http://localhost:8082/appointment-view.html](http://localhost:8082/appointment-view.html). On the customer step with login enabled, click **Login**.
4. Sign in on the Keycloak page, then you should return logged in.

After login, API calls use `/buergeransicht/authenticated/api/citizen/…`. The Vite dev proxy and local gateway both need that path (see `zmscitizenview/vite.config.ts` and `.devcontainer` / `.ddev` `local-gateway-application.yml`). Restart `refarch-gateway` and the Vite / citizenview process after pulling these changes.

The local login shim stores the access token in `sessionStorage` so [appointment-overview](http://localhost:8082/appointment-overview.html), [appointment-detail](http://localhost:8082/appointment-detail.html), and [appointment-slider](http://localhost:8082/appointment-slider.html) stay logged in on the same browser tab/origin. Tokens include claim `lhmExtID` (Keycloak username) so `my-appointments` can resolve the user. Re-login (and re-book if needed) after applying migration `13_add-citizen-lhmextid-claim.yml`.

| Field    | Value      |
| -------- | ---------- |
| Username | `citizen`  |
| Password | `vorschau` |

Keycloak URL used by the host pages: `http://localhost:8080/auth` (matches the realm issuer in the browser). The `keycloak` hosts entry is still useful for admin/statistic and in-container DNS.

The local API gateway often runs with security disabled, so authenticated citizen API calls may succeed without JWT checks. Turning on gateway JWT validation can reuse `SSO_URL` / `SSO_REALM` / `SSO_CLIENTID` from the ddev / devcontainer `.env.template` files.

## Podman (Linux) note

Podman may merge host `/etc/hosts` into containers, which can break in-container `keycloak` resolution. Add this to `~/.config/containers/containers.conf`:

```ini
[containers]
base_hosts_file="none"
```
