# ZMS base (`zmsbase`)

PHP Docker base images for the [eappointment](https://github.com/it-at-m/eappointment) monorepo. Every PHP module image is built `FROM ghcr.io/it-at-m/eappointment/zmsbase:<tag>` (see [`.resources/Containerfile`](../.resources/Containerfile) and module `Dockerfile`s).

## Layout

| Path | Images |
|------|--------|
| `php83/` | `8.3-base`, `8.3-dev` |
| `php84/` | `8.4-base`, `8.4-dev` |
| `php83-local/` | `8.3-local-amd64`, `8.3-local-arm64` (local dev / zmsautomation) |
| `php84-local/` | `8.4-local-amd64`, `8.4-local-arm64` (PHP 8.4 local dev / automation) |
| `scripts/` | Helpers copied into image builds |

## Build and publish

CI workflow: [`.github/workflows/zmsbase-build-images.yaml`](../.github/workflows/zmsbase-build-images.yaml) (🐳 Build ZMS base images).

Registry: `ghcr.io/it-at-m/eappointment/zmsbase`

Handbook: [PHP base images (EN)](../docs/en/setup-and-development/php-base-images.md) · [PHP-Basis-Images (DE)](../docs/de/setup-and-development/php-base-images.md)

## History

- Former standalone repo: [it-at-m/eappointment-php-base](https://github.com/it-at-m/eappointment-php-base)
- Original Berlin sources: [gitlab.com/eappointment/php-base](https://gitlab.com/eappointment/php-base)

## License

MIT — see [LICENSE](LICENSE).
