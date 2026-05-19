# Internal layout includes (ZMSKVR-1321)

Vendored copies of the former external GitLab dependencies:

- `layout-admin-js` — admin UI behavior scripts (from `layout-admin-js` tag `2.24.14`)
- `layout-admin-scss` — admin SCSS structure (from `layout-admin-scss` branch `muc-main`)

Consumed by `zmsadmin` and `zmsstatistic` via `file:` references in `package.json`.

`swiper` was removed from `layout-admin-js` dependencies; swiper SCSS remains disabled (`$use_swiper: false`).
