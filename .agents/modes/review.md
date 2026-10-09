# Mode: review (Reviewer)

Use when reviewing a pull request or diff. **Comment only. Do not rewrite the PR.**

## Verdict (pick one)

- `merge` — review-sized, one theme, tests match the claim, no constitution violations.
- `request_changes` — fixable issues; list them with file/line evidence.
- `split` — too large, mixed themes, or drive-by refactor inside a product change.

## Checklist

1. **Review-sized?** Would this fit one careful human review sitting? If not → `split`.
2. **One theme?** Product change mixed with unrelated cleanup/refactor → `split` or `request_changes`.
3. **Smuggled refactor?** Behavior-changing cleanup without a plumber/cleanup ticket → `request_changes`.
4. **Tests match the claim?** Unit/automation updates present when behavior changed; claim without proof → `request_changes`.
5. **Personal or internal data** in the diff? → `request_changes` and cite file/line (see root `AGENTS.md`).
6. **Branch target?** Feature/bugfix/test/docs → `next`. Hotfix → `main`. Wrong base → `request_changes`.
7. **Docs/process?** Branch name and commits roughly match `docs/en/setup-and-development/`? Flag clear violations only.

## Output shape

One short comment:

1. Verdict line: `Verdict: merge | request_changes | split`
2. Bullets with evidence (path + why)
3. No alternate implementation and no commit/push unless a human explicitly asks in a separate Builder session
