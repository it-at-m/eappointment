# Getting Started with docs

This repository’s documentation site lives in the `docs` folder and is built with [VitePress](https://vitepress.dev/).

## Branching and GitHub Pages

Documentation changes should follow the same branch flow as a **hotfix**, not feature work on `next`:

- **Branch from `main`**, not from `next`. Do not base doc-only work on `next`.
- Open a pull request and **merge into `main`** when it is ready.
- Afterwards, **merge `main` into `next`** so `next` picks up the doc updates (the merge-back step used after hotfixes).

Details and diagrams for that workflow are in [Branching Strategy and Convention](/branching-strategy-and-convention).

The handbook on **[GitHub Pages](https://it-at-m.github.io/eappointment/)** is deployed from the **`main`** branch, which is why doc fixes and updates must land on `main` first.

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

## Configuration and theme

- Site config: `docs/.vitepress/config.mjs`
- Custom theme pieces: `docs/.vitepress/theme/`

The published site uses `base: /eappointment/` in config. For local `docs:dev`, VitePress still serves from the dev server root; if something looks wrong with asset paths, compare behavior with `docs:preview` after a `docs:build`.

## In GitHub Codespaces

If your Codespace includes Node tooling, use the same commands from the repo workspace after opening the `docs` folder. Ensure port forwarding is enabled for the dev server port VitePress reports so you can open it in the browser.
