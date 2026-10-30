# Mode: bugfix (Builder)

Use for a ticketed product bug. Announce: `mode: bugfix`.

Same borders as feature mode (`AGENTS.md`). Differences:

## Branch

- From `origin/next`: `bugfix-<project>-<ticket>-short-slug`.
- Hotfix that must land on production: branch from `main` as `hotfix-…` only when the team treats it as a hotfix.

## Commits

- Subject type is `fix(...)`, e.g. `fix(ZMSKVR-12345): why`.
- Prefer the smallest fix that removes the bug. No drive-by refactors (those are Plumber + ticket).

## Stack

Start at the layer that owns the bug. Still follow bottom-up when the fix needs lower layers — see `docs/en/setup-and-development/getting-started/implement-a-user-story.md` and `.agents/modes/feature.md`.

## Verify, push, PR

Same as feature mode: filtered tests, browser when UI-visible, PII check, PR to `next` (hotfix PRs follow the hotfix process to `main`).
