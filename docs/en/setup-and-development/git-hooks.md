# Git Hooks (Husky)

This repository uses [Husky](https://github.com/typicode/husky) to run Git hooks from the root `.husky/` directory. Hooks run automatically at specific points in the Git workflow to keep code style and commit messages consistent across the monorepo.

Hook scripts live in [`.husky/`](https://github.com/it-at-m/eappointment/tree/main/.husky) (`pre-commit`, `commit-msg`).

## Setup

Hooks are configured when you run at the **repository root**:

```bash
npm install
```

The `prepare` script runs automatically and points Git at `.husky/`.

> [!NOTE]
> Husky lives in the **root** `package.json` because hooks apply to the **entire monorepo**. Vue lint still uses `zmscitizenview`; docs formatting uses `docs/`.

After cloning, run `npm install` once at the repo root (also in the [root README](https://github.com/it-at-m/eappointment/blob/main/README.md)). For doc changes, install docs dependencies once: `cd docs && npm install`.

## Hooks

### `pre-commit`

No-op placeholder. Git always runs `pre-commit` before `commit-msg`; the real checks are in `commit-msg` so the **commit subject is validated before** Vue/docs/PHP.

### `commit-msg`

All checks run in this hook, in **fail-fast** order:

1. **Commit message** — subject line from the message file Git passes to this hook
2. **Vue code style** — Prettier check in `zmscitizenview` (`npm run lint`)
3. **Docs formatting** — Prettier check in `docs/` (`npm run format:check`)
4. **PHP code style** — PHP CodeSniffer (PSR-12) across PHP modules via the `zms-web` container

**Container detection**

The PHP check detects your runtime automatically:

- **Podman** — if a container named `zms-web` is running
- **Docker** — fallback when Podman is unavailable
- **Skip** — if no container is running (warning only, non-blocking)

**Behavior**

- Commit message, Vue, and docs checks **block** the commit on failure
- PHP checks run only when `zms-web` is up; otherwise they are skipped with a warning

See also [Code formatting](./code-formatting.md) for manual PHPCS/Prettier commands.

**Commit message format** (step 1):

```txt
type(PROJECT-123): commit message
type(PROJECT): commit message
```

The ticket number is **optional** — use `PROJECT-123` or just `PROJECT` (uppercase).

**Merge commits**

Git’s default merge subjects (for example `Merge branch 'main' into feature-branch`) are **allowed automatically** so `git merge` can finish without renaming the message. You can still use a conventional subject if you prefer, for example `chore(ZMS): merge main into feature-branch`.

Full rules, types, projects, and examples: [Commit message convention](./development-rules/commit-message-convention.md).

## Troubleshooting

### Vue lint fails

```bash
cd zmscitizenview
npm run format
```

Then commit again.

### Docs formatting fails

If Prettier reports issues under `docs/`:

```bash
cd docs
npm run format
```

Install dependencies first if needed: `cd docs && npm install`.

### PHP CodeSniffer fails

The hook prints a fix command for your container engine:

```bash
# Podman
podman exec -it zms-web bash -lc "./cli modules loop 'vendor/bin/phpcbf --standard=psr12 src/'"

# Docker
docker exec -it zms-web bash -lc "./cli modules loop 'vendor/bin/phpcbf --standard=psr12 src/'"
```

### Container not detected

If you see a warning that `zms-web` is not running:

```bash
podman ps | grep zms-web
docker ps | grep zms-web
```

Start the dev environment if needed ([DDEV and Devcontainer](./getting-started/ddev-and-devcontainer.md), [Podman and Dev Containers](./getting-started/macos-local-configuration/podman-and-dev-containers.md)). PHP checks are skipped when the container is down; the commit is not blocked for PHP alone.

### Invalid commit message format

Common mistakes:

- Missing project: `feat: add feature`
- Lowercase project: `feat(zms-123): add feature`
- Missing colon: `feat(ZMS-123) add feature`

Correct examples:

- `feat(ZMS-123): add feature`
- `chore(ZMSKVR): clean up`

### Merge blocked by commit-msg

If `git merge` fails with “Invalid commit message format” and a subject like `Merge branch 'main' into …`, update `.husky/commit-msg` from the latest `main` or feature branch (merge subjects should be accepted). As a workaround, finish the merge with a conventional message:

```bash
git commit -m "chore(ZMS): merge main into your-branch"
```

## Bypassing hooks

In emergencies, use Git’s `--no-verify` flag:

```bash
git commit --no-verify -m "feat(ZMS-123): urgent fix"
```

> [!CAUTION]
> Use `--no-verify` only when necessary. Bypassing hooks can hurt code quality and affect other contributors.

## Related documentation

- [Commit message convention](./development-rules/commit-message-convention.md)
- [Code formatting](./code-formatting.md)
- [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/)
- [Husky documentation](https://github.com/typicode/husky)
