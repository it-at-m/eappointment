# Devcontainer and Podman Commands

## Getting Started

- `devcontainer up --workspace-folder .`
- `podman exec -it zms-web bash -lc "./cli modules loop composer install"`
- `podman exec -it zms-web bash -lc "./cli modules loop npm install"`
- `podman exec -it zms-web bash -lc "./cli modules loop npm build"`

## Import Database

- `podman exec -i zms-db mysql -u root -proot db < .resources/zms.sql`
- `podman exec -it zms-web bash -lc "cd zmsapi && vendor/bin/migrate --update"`

## Stop Containers

- `podman stop $(podman ps -aq)`

> [!NOTE]
> The [dev container CLI](https://github.com/devcontainers/cli) is currently in active development. The `devcontainer stop` & `devcontainer down` commands haven't been implemented yet, that's why we use `podman stop $(podman ps -aq)` for the time being.

## Code Quality Checks

### PHPCS

Run all checks at once:

```bash
podman exec -it zms-web bash -lc "./cli modules loop 'vendor/bin/phpcs --standard=psr12 src/'" && podman exec -it zms-web bash -lc "./cli modules loop 'vendor/bin/phpcbf --standard=psr12 src'"
```

Run checks for one specific module:

```bash
podman exec -it zms-web bash

# Go to the desired module directory:
cd zmsadmin

# Run PHPCS
vendor/bin/phpcs --standard=psr12 src/

# Automatically fix formatting issues by running the following:
vendor/bin/phpcbf --standard=psr12 src/
# or run:
phpcs --standard=psr12 --fix src/

# Run PHPMD
vendor/bin/phpmd src/ text ../phpmd.rules.xml
```

## CORS on refarch-gateway (no browser Network tab)

Browsers enforce CORS; **curl without an `Origin` header does not.** So curl can succeed while **Selenium `fetch()` fails** if the gateway never returns CORS headers for that origin.

**1) Check headers from a container (same as curl in zms-web / citizenview):**

```bash
podman exec -it zms-citizenview bash -lc 'bash /workspace/.devcontainer/scripts/check-gateway-cors.sh'
# optional: custom origin (must match your app URL, e.g. Selenium opens http://citizenview:8082)
podman exec -it zms-citizenview bash -lc 'bash /workspace/.devcontainer/scripts/check-gateway-cors.sh "http://refarch-gateway:8080/buergeransicht/api/citizen/offices-and-services/" "http://citizenview:8082"'
```

You should see **`Access-Control-Allow-Origin`** (reflecting your origin or `*`) on the **OPTIONS** reply and ideally on the **GET** reply. Config lives in `local-gateway-application.yml` (`globalcors` → `allowedOriginPatterns`).

**2) “Fetch” without opening DevTools — only in a real browser**

There is **no shell command that equals browser `fetch()`** for CORS (Node’s `fetch` ignores CORS). To see the same failure as the app, run **`fetch` inside Selenium** (same origin as the app), e.g.:

```js
// In Selenium, execute async script from a page on http://citizenview:8082
return await fetch('http://refarch-gateway:8080/buergeransicht/api/citizen/offices-and-services/', { mode: 'cors' })
  .then(r => ({ ok: r.ok, status: r.status }))
  .catch(e => ({ error: String(e) }));
```

If the curl+CORS check shows no `Access-Control-Allow-Origin`, fix the gateway first; if curl shows CORS OK but Selenium still fails, compare the **exact** page origin (scheme/host/port) to `allowedOriginPatterns`.

## Unit Testing

- `podman exec -it zms-web bash`
- `cd {zmsadmin, zmscalldisplay, zmsdldb, zmsentities, zmsmessaging, zmsslim, zmsstatistic, zmsticketprinter}`
- `./vendor/bin/phpunit`
