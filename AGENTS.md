# eAppointment — agent constitution

This repository is the ZMS / eAppointment monorepo. Human process lives in `docs/en/setup-and-development/`. Agents follow this file and the active mode under `.agents/modes/`. Do not invent process that contradicts the docs.

## Borders

1. **Review capacity is scarce.** Prefer review-sized changes. If the work would not fit one careful review sitting, stop and split.
2. **No ticket → no product PR.** Without a Jira or GitHub issue, write an inbox note under `.agents/inbox/ideas/` (or `.agents/inbox/plumber/` for debt). Do not open a product branch or PR.
3. **One theme per PR.** Do not mix product work with drive-by refactors or unrelated cleanup. Plumber work is its own PR after a ticket.
4. **Smallest diff.** No speculative abstractions or drive-by refactors in a product PR.
5. **One mode per session.** Announce the mode. Read `.agents/modes/<mode>.md` when that file exists.
6. **Docs win.** Branching, commits, and stack order: `docs/en/setup-and-development/`.
7. **Git safety.** No force-push to `main` or `next`. Do not skip hooks. Commit, push, and open a PR only when the human asked (or a documented workflow explicitly allows it).
8. **No personal or internal data** in commits, pushes, or PRs: real names, emails, phones, postal addresses, tokens, keys, passwords (except shared test password `vorschau`), or personal/internal URLs and hosts. Fake `Muster*` names, role logins (`agent_basic`, `agent_queue`, `ataf`, `citizen`), `@mailinator.com` from those names, and `+491234567890` are fine. If found, stop and report file and line.

## Modes

| Mode | File | Role |
|------|------|------|
| review | `.agents/modes/review.md` | Reviewer — checklist only; comment, do not rewrite |
| feature | `.agents/modes/feature.md` | Builder — ticketed feature |
| bugfix | `.agents/modes/bugfix.md` | Builder — ticketed bug |
| test | `.agents/modes/test.md` | Sentinel — zmsautomation / behavioral tests |
| plumber | `.agents/modes/plumber.md` | Plumber — debt after a ticket |

Personal taste: if `.agents/local/rules/` contains `*.md` files, read them after this constitution. They must not contradict shared law. That folder is gitignored (see `.agents/local/README.md`). Optional tool homes: `~/.config/opencode/AGENTS.md`, Cursor User Rules, `~/.codex/AGENTS.md`, `~/.claude/CLAUDE.md`, `~/.gemini/GEMINI.md`. Never auto-load `.agents/museum/`. Model policy: **free only** (see `.agents/README.md`).

Team guide: `.agents/README.md`. Tracking issue: https://github.com/it-at-m/eappointment/issues/3668
