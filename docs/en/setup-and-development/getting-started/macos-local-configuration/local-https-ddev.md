---
outline: deep
---

# Local HTTPS SSL for DDEV (macOS)

Use this guide to open your local DDEV site at `https://zms.ddev.site` **without browser SSL warnings** on macOS.

## Requirements

- macOS with [Homebrew](https://brew.sh) installed
- [DDEV](https://ddev.readthedocs.io/en/stable/)
- [mkcert](https://github.com/FiloSottile/mkcert) for locally trusted certificates

## 1. Install mkcert and dependencies

```bash
brew install mkcert
brew install nss # optional, for Firefox support
mkcert -install
```

## 2. Create the SSL directory (if it does not exist)

```bash
mkdir -p ~/.ddev/global_config/ssl
```

## 3. Generate certificate and key for the project domain

```bash
mkcert -cert-file ~/.ddev/global_config/ssl/zms.ddev.site.crt \
       -key-file ~/.ddev/global_config/ssl/zms.ddev.site.key \
       zms.ddev.site
```

## 4. Trust the mkcert root CA (macOS)

```bash
sudo security add-trusted-cert -d -r trustRoot \
      -k /Library/Keychains/System.keychain \
      "$(mkcert -CAROOT)/rootCA.pem"
```

This lets macOS trust certificates issued by your local mkcert CA.

## 5. Restart DDEV

```bash
ddev restart
```

## 6. Open the site over HTTPS

- Project URL: `https://zms.ddev.site`
- Example API URL: `https://zms.ddev.site/terminvereinbarung/api/citizen/offices-and-services/`

You should not see certificate errors in Safari or Chromium-based browsers after the steps above.

## Firefox

To trust the same roots in Firefox:

1. Open `about:config`
2. Search for `security.enterprise_roots.enabled`
3. Set the value to `true`
4. Restart Firefox

## Optional: verify with curl

```bash
curl -I https://zms.ddev.site
```

You should get a `200` or redirect response without TLS verification errors.
