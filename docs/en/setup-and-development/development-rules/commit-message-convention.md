# Commit Message Convention

This page defines commit message conventions used in this repository.

## Commit Message Format

Please provide your project and optionally the ticket number in the commit message subject.
For baseline commit style semantics, refer to [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/).

Use this structure:

`<type>(<PROJECT>-<ticket>): <short summary>`

or without a ticket number:

`<type>(<PROJECT>): <short summary>`

Example:

`fix(ZMSKVR-1347): handle unpublished relation filtering`

`chore(ZMS): update dependencies`

The `commit-msg` Git hook validates only the subject line. Merge commits use Git’s default `Merge branch …` subject automatically. See [Git hooks (Husky)](../git-hooks.md) for setup and troubleshooting.

## Required Components

1. **type**: The type of change. Common values in this repository:
   - `feat`: new feature or capability
   - `fix`: bug fix
   - `clean`: refactoring/cleanup without behavior change
   - `docs`: documentation-only change
   - `chore`: maintenance/dependency/build tooling changes

2. **project**: The project identifier. This should be:
   - `ZMS` for the ZMS project
   - `ZMSKVR` for the ZMSKVR project
   - `MPDZBS` for the MPDZBS project
   - `MUXDBS` for the MUXDBS project
   - `GH` for GitHub-only issue tracking

3. **ticket number**: Optional digits matching the project ticket/issue ID (for example `ZMSKVR-1347`). You may omit the number and use only the project scope (for example `chore(ZMS): …`).

4. **summary**: A concise imperative statement describing the change intent.

## Examples

- `feat(ZMS-123): add scope hint support for office mapping`
- `fix(ZMSKVR-123): prevent stale provider visibility cache reads`
- `clean(ZMS-123): simplify munich transformer duration merge logic`
- `chore(ZMSKVR-123): update vitepress dependencies`
- `docs(ZMS-123): document sadb visibility decision flow`
- `chore(ZMS): merge main into feature branch`
- `clean(GH): remove obsolete workflow`

## Regular Expression

The subject line validated by the `commit-msg` hook matches:

`^(feat|fix|clean|chore|docs)\((ZMS|ZMSKVR|MPDZBS|MUXDBS|GH)(-[0-9]+)?\): .+$`

Merge commits with a subject starting with `Merge ` are exempt (see [Git hooks (Husky)](../git-hooks.md)).

## Additional Guidelines

- Keep the subject line focused on the "why"/intent, not a full changelog.
- Use a body when context is needed (for example migration notes, behavior tradeoffs, or rollout implications).
- Prefer one logical change per commit to keep review and cherry-picking clean.
- For documentation-only updates, use `docs(...)` type.
