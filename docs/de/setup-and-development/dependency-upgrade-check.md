# Abhängigkeits-Aktualisierungsprüfung

Übergib die PHP-Version, auf die du aktualisieren möchtest, und erhalte je Modul Informationen zu Patch-, Minor- oder Major-Änderungen der Abhängigkeiten.
Beispiel:

### DDEV

```bash
ddev exec ./cli modules check-upgrade 8.4
```

### Podman

```bash
podman exec -it zms-web bash -lc "./cli modules check-upgrade 8.4"
```

Passe die Version nach Bedarf an (zum Beispiel `8.4`), um die Auswirkungen auf Patch-/Minor-/Major-Abhängigkeiten je Modul zu prüfen.
