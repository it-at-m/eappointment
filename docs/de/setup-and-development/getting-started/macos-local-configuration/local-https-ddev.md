---
outline: deep
---

# Lokales HTTPS-SSL für DDEV (macOS)

Mit dieser Anleitung öffnest du deine lokale DDEV-Site unter `https://zms.ddev.site` **ohne SSL-Warnungen im Browser** auf macOS.

## Voraussetzungen

- macOS mit installiertem [Homebrew](https://brew.sh)
- [DDEV](https://ddev.readthedocs.io/en/stable/)
- [mkcert](https://github.com/FiloSottile/mkcert) für lokal vertrauenswürdige Zertifikate

## 1. mkcert und Abhängigkeiten installieren

```bash
brew install mkcert
brew install nss # optional, für Firefox-Unterstützung
mkcert -install
```

## 2. SSL-Verzeichnis anlegen (falls nicht vorhanden)

```bash
mkdir -p ~/.ddev/global_config/ssl
```

## 3. Zertifikat und Schlüssel für die Projekt-Domain erzeugen

```bash
mkcert -cert-file ~/.ddev/global_config/ssl/zms.ddev.site.crt \
       -key-file ~/.ddev/global_config/ssl/zms.ddev.site.key \
       zms.ddev.site
```

## 4. Der mkcert-Root-CA vertrauen (macOS)

```bash
sudo security add-trusted-cert -d -r trustRoot \
      -k /Library/Keychains/System.keychain \
      "$(mkcert -CAROOT)/rootCA.pem"
```

So vertraut macOS Zertifikaten, die deine lokale mkcert-CA ausstellt.

## 5. DDEV neu starten

```bash
ddev restart
```

## 6. Site über HTTPS öffnen

- Projekt-URL: `https://zms.ddev.site`
- Beispiel-API-URL: `https://zms.ddev.site/terminvereinbarung/api/citizen/offices-and-services/`

Nach diesen Schritten solltest du in Safari oder Chromium-basierten Browsern keine Zertifikatsfehler mehr sehen.

## Firefox

Damit Firefox denselben Roots vertraut:

1. `about:config` öffnen
2. Nach `security.enterprise_roots.enabled` suchen
3. Wert auf `true` setzen
4. Firefox neu starten

## Optional: mit curl prüfen

```bash
curl -I https://zms.ddev.site
```

Du solltest eine `200`-Antwort oder eine Weiterleitung ohne TLS-Verifikationsfehler erhalten.
