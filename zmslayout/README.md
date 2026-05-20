# zmslayout

Vendored staff UI layout assets (ZMSKVR-1321), shared by `zmsadmin` and `zmsstatistic`.

- `js/` — UI behavior scripts (`bo-zms-layout-js`)
- `scss/` — SCSS structure (`bo-zms-layout-scss`)

Consumed via `file:` dependencies in each module’s `package.json`.

`swiper` was removed from the JS package; swiper SCSS remains disabled (`$use_swiper: false`).
