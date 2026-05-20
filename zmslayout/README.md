# zmslayout

Vendored layout assets (ZMSKVR-1321), shared by `zmsadmin` and `zmsstatistic`.

- `js/` — UI behavior scripts (`bo-zms-layout-js`)
- `scss/` — SCSS structure (`bo-zms-layout-scss`)

Consumed via `file:` dependencies in each module’s `package.json`.

`make live` in `zmsadmin` / `zmsstatistic` copies these packages into `node_modules` (no symlinks) so GHCR images and `zmsautomation` injection work. CI also mounts `zmslayout` at `/var/www/html/zmslayout` when injecting prebuilt modules.

`swiper` was removed from the JS package; swiper SCSS remains disabled (`$use_swiper: false`).
