# Agent civilization (team guide)

OpenCode-first shared law for a small team where everyone may run agents. The goal is **mergeable work**, not maximum agent activity. Code review is the scarce resource.

Tracking: [issue #3668](https://github.com/it-at-m/eappointment/issues/3668).

## Layout

| Path | Purpose |
|------|---------|
| [`../AGENTS.md`](../AGENTS.md) | Always-on constitution (keep short) |
| [`modes/`](modes/) | One mode per session; open on demand |
| [`inbox/ideas/`](inbox/ideas/) | Unticketed ideas — agents may append; humans promote to a ticket, defer, or delete |
| [`inbox/plumber/`](inbox/plumber/) | Debt / refactor notes — same promotion rules |
| [`museum/`](museum/) | Examples and dead ideas — **never** auto-loaded |

Human depth stays in `docs/en/setup-and-development/`. Do not copy the handbook into always-on agent context.

## Roles

Every developer can wear any role.

| Role | Job |
|------|-----|
| **Builder** | Ticketed product feature or bugfix |
| **Reviewer** | PR filter: merge / request changes / split; no silent rewrites |
| **Plumber** | Debt notes and one-theme cleanup PRs after a ticket |
| **Sentinel** | Behavioral verification (e.g. zmsautomation); tests that claimed behavior holds |

**Sentinel** is the tester role. It is not the same as **Reviewer** (code/PR shape).

## Model policy — free only

Assume **free** OpenCode use only: local models (e.g. Ollama) and/or free cloud quotas. No paid API tier is required for this system. CI Reviewer jobs run only if they can use free quota or a self-hosted free model; otherwise run Reviewer locally with `opencode run`.

## Bureaucracy budget

- Always-on constitution under ~80 lines.
- One concern per mode file; link to `docs/` for depth.
- A new shared rule needs a PR, an owner, and a failure mode it prevents.
- If a rule does not reduce review cost, safety incidents, or wrong-branch mistakes — delete it.
- Personal rules stay off-repo and must not contradict the constitution.

## Harness adapters

| File | Tool |
|------|------|
| [`../AGENTS.md`](../AGENTS.md) | OpenCode, Codex, Cursor, and anything else that reads `AGENTS.md` |
| [`../CLAUDE.md`](../CLAUDE.md) | Claude Code (`@AGENTS.md`) |
| [`../GEMINI.md`](../GEMINI.md) | Gemini Code Assist / Gemini CLI (`@AGENTS.md`) |
| [`.cursor/rules/`](../.cursor/rules/) | Cursor thin pointers into `.agents/modes/` |
| [`../opencode.json`](../opencode.json) | OpenCode extra `instructions` globs |

## Optional CI Reviewer

Workflow: `.github/workflows/opencode-review.yml`. Off by default. Set repository variable `OPENCODE_REVIEW_ENABLED=true` and secret `OPENROUTER_API_KEY` (or equivalent free-capable key) only when free quota can cover PR traffic. The job comments only; it never pushes or approves.
