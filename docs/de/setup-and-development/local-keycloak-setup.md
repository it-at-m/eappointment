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

## Hinweis zu Podman (Linux)

Podman fügt unter Umständen die Host-`/etc/hosts` in Container ein, was die Auflösung von `keycloak` im Container brechen kann. Ergänze in `~/.config/containers/containers.conf`:

```ini
[containers]
base_hosts_file="none"
```
