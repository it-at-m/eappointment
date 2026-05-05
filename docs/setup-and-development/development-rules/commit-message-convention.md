# Commit Message Convention

This page defines commit message conventions used in this repository.

## Commit Message Format

Please provide your project and ticket number in the commit message subject.
For baseline commit style semantics, refer to [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/).

Use this structure:

`<type>(<PROJECT>-<ticket>): <short summary>`

Example:

`fix(ZMSKVR-1347): handle unpublished relation filtering`

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

3. **ticket number**: Digits only, matching the project ticket/issue ID.

4. **summary**: A concise imperative statement describing the change intent.

## Examples

- `feat(ZMS-123): add scope hint support for office mapping`
- `fix(ZMSKVR-123): prevent stale provider visibility cache reads`
- `clean(ZMS-123): simplify munich transformer duration merge logic`
- `chore(ZMSKVR-123): update vitepress dependencies`
- `docs(ZMS-123): document sadb visibility decision flow`

## Regular Expression

The subject line should match this pattern:

`^(feat|fix|clean|chore|docs)\((ZMS|ZMSKVR|MPDZBS|MUXDBS|GH)-[0-9]+\): .+$`

## Additional Guidelines

- Keep the subject line focused on the "why"/intent, not a full changelog.
- Use a body when context is needed (for example migration notes, behavior tradeoffs, or rollout implications).
- Prefer one logical change per commit to keep review and cherry-picking clean.
- For documentation-only updates, use `docs(...)` type.
