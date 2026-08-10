# Getting Started with docs

This repository’s documentation site lives in the `docs` folder and is built with [VitePress](https://vitepress.dev/).

## Branching and GitHub Pages

The handbook on **[GitHub Pages](https://it-at-m.github.io/eappointment/)** is deployed from the **`next`** branch (via `combined-workflow-with-docs` on push to `next`).

**Doc-only changes** (handbook updates with no product code in the same change) should land on **`next`** so the site updates:

- **Branch from `next`**, open a pull request, and **merge into `next`**.
- If the change also needs to be on `main` (for example before a release), merge `next` into `main` through your usual path, or cherry-pick / mirror as needed.

If your work is a **feature or bugfix** that also touches `docs/`, follow the **normal process** for that work (branch from `next`, PR into `next`). Edit the docs in the same feature or bugfix branch.

Details and diagrams are in [Branching Strategy and Convention](/setup-and-development/development-rules/branching-strategy-and-convention).

## Prerequisites

- Node.js (LTS recommended), same major version you use elsewhere in this repo
- npm

## Install and run locally

From the repository root:

```bash
cd docs
npm install
npm run docs:dev
```

VitePress prints a local URL (typically `http://localhost:5173`). Open it in a browser to browse the site with hot reload while you edit Markdown under `docs/`.

## Other commands

- **`npm run format`** — format Markdown, Vue, JS, and CSS under `docs/` with Prettier (same `@muenchen/prettier-codeformat` preset as `zmscitizenview`)
- **`npm run format:check`** — verify formatting without writing files (useful in CI)
- **`npm run docs:build`** — production build; output is written to `docs/.vitepress/dist`
- **`npm run docs:preview`** — serve the built site locally to verify the build
- **`npm run docs:log-inventory`** — regenerate `docs/.vitepress/data/log-inventory.json` (also runs automatically on `docs:dev` / `docs:build`)

## Auto-generated documentation

Some pages are generated when VitePress starts or builds:

- **Cucumber feature list** — from `zmsautomation/src/test/resources/features`
- **Monolog log inventory** — scans `App::$log` calls in ZMS module PHP sources; see [Monolog logging](/operations/monolog-logging)

`log-inventory.json` is generated locally and in CI; it is **not** committed (see `docs/.gitignore`).

## Configuration and theme

- Site config: `docs/.vitepress/config.mjs`
- Custom theme pieces: `docs/.vitepress/theme/`

The published site uses `base: /eappointment/` in config. For local `docs:dev`, VitePress still serves from the dev server root; if something looks wrong with asset paths, compare behavior with `docs:preview` after a `docs:build`.

## In GitHub Codespaces

If your Codespace includes Node tooling, use the same commands from the repo workspace after opening the `docs` folder. Ensure port forwarding is enabled for the dev server port VitePress reports so you can open it in the browser.
