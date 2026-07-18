# Lokale Keycloak-Einrichtung

Für die lokale Entwicklung ist Keycloak so konfiguriert, dass es – wie in der RefArch-Einrichtung – den Hostnamen `keycloak` statt `localhost` verwendet.

Das ist nötig, weil:

- Browser-Weiterleitungen auf dem Host nach `127.0.0.1` aufgelöst werden müssen.
- PHP-Code in Containern über das Container-Netzwerk-DNS aufgelöst werden muss.
- Innerhalb von Containern verweist `localhost` auf den Container selbst.

## `keycloak` zu hosts unter macOS/Linux hinzufügen

```bash
echo "127.0.0.1 keycloak" | sudo tee -a /etc/hosts
```

## `keycloak` zu hosts unter Windows hinzufügen

1. Notepad als Administrator öffnen (Rechtsklick → Als Administrator ausführen).
2. `C:\Windows\System32\drivers\etc\hosts` öffnen.
3. Diese Zeile am Ende hinzufügen:

   ```text
   127.0.0.1 keycloak
   ```

4. Datei speichern.

## Lokale Umgebung neu starten und prüfen

Nach dem Eintrag den Keycloak-/Container-Stack neu starten:

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

Prüfen:

```bash
ping keycloak
```

## Bürger-Login (zmscitizenview)

Die lokalen Vite-Host-Seiten (`appointment-view.html` usw.) nutzen den öffentlichen Keycloak-Client `dbs-fragments` im Realm `zms` (Migrationen `09_add-citizen-client.yml`, `10_add-citizen-token-mappers.yml`). Defaults stehen in `zmscitizenview/.env.development`.

Der externe `dbs-login`-Loader ist lokal oft nicht erreichbar. Mit `VITE_USE_LOCAL_CITIZEN_LOGIN=true` laden die Host-Seiten stattdessen `src/local-dbs-login.ts`: lauscht auf `authorization-request`, führt OIDC Authorization-Code + PKCE gegen lokales Keycloak aus und sendet `authorization-event`.

1. Migrationen anwenden (Stack neu starten, damit `init-keycloak` läuft, oder den Service neu erzeugen).
2. Vite-/citizenview-Prozess neu starten, damit Env und Login-Skripte geladen werden.
3. Die Host-Seiten-Übersicht [http://localhost:8082/webcomponents.html](http://localhost:8082/webcomponents.html) öffnen und eine Seite wählen (z. B. appointment-view), oder direkt [http://localhost:8082/appointment-view.html](http://localhost:8082/appointment-view.html). Am Kundenschritt mit Login **Anmelden** klicken.
4. Am Keycloak-Login anmelden; danach solltest du eingeloggt zurückkommen.

Nach dem Login laufen API-Aufrufe über `/buergeransicht/authenticated/api/citizen/…`. Vite-Dev-Proxy und lokales Gateway brauchen diesen Pfad (siehe `zmscitizenview/vite.config.ts` sowie `.devcontainer` / `.ddev` `local-gateway-application.yml`). Nach dem Pull `refarch-gateway` und den Vite-/citizenview-Prozess neu starten.

| Feld         | Wert       |
| ------------ | ---------- |
| Benutzername | `citizen`  |
| Passwort     | `vorschau` |

Keycloak-URL der Host-Seiten: `http://localhost:8080/auth` (passt zum Realm-Issuer im Browser). Der Hosts-Eintrag `keycloak` bleibt für Admin/Statistik und Container-DNS sinnvoll.

Das lokale API-Gateway läuft oft ohne Security-Profil; authentifizierte Citizen-API-Aufrufe werden dann ggf. ohne JWT-Prüfung durchgelassen. Für JWT-Validierung können `SSO_URL` / `SSO_REALM` / `SSO_CLIENTID` aus den ddev-/devcontainer-`.env.template`-Dateien genutzt werden.

## Hinweis zu Podman (Linux)

Podman fügt unter Umständen die Host-`/etc/hosts` in Container ein, was die Auflösung von `keycloak` im Container brechen kann. Ergänze in `~/.config/containers/containers.conf`:

```ini
[containers]
base_hosts_file="none"
```
