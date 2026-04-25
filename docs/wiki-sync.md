# Wiki Sync Policy

## Source of Truth

Documentation source of truth is this repository (`docs/`) on the `main` branch.

## Automation Rules

- Wiki sync workflow runs on:
  - push to `main` when `docs/**` changes
  - manual `workflow_dispatch`
- It does not run on pull requests or feature branches.
- `docs/index.md` is mapped to wiki `Home.md`.
- Concurrent runs use the same concurrency group and **queue** (`cancel-in-progress: false`) so an in-flight push is not aborted mid-sync.

## Required secret: `WIKI_PUSH_TOKEN`

Pushing to `https://github.com/<org>/<repo>.wiki.git` is **not** supported by the default `GITHUB_TOKEN` for wiki remotes in typical setups.

- Configure a repository secret named **`WIKI_PUSH_TOKEN`** with a PAT that can push to the wiki (classic PAT with `repo` scope, or a fine-grained token with wiki write as allowed by your org policy).
- The workflow **fails fast** if `WIKI_PUSH_TOKEN` is missing, so misconfiguration is visible in the Actions log instead of failing only at `git push`.

## Manual Wiki Reset (one-time baseline)

Use these steps once to align existing wiki history with repository docs:

1. Clone wiki repository:
   - `https://github.com/it-at-m/eappointment.wiki.git`
2. Replace wiki markdown with the baseline generated from `docs/`.
3. Commit and push baseline.
4. After this baseline, use only repository-driven updates.

## Editing Guidance

- Do not manually maintain long-term content directly in the wiki.
- Create documentation changes in repository PRs and merge to `main`.
- Use manual dispatch only for controlled backfills or re-sync.
