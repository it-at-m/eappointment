# Agent civilization (team guide)

OpenCode-first shared law for a small team where everyone may run agents. The goal is **mergeable work**, not maximum agent activity. Code review is the scarce resource.

Tracking: [issue #3668](https://github.com/it-at-m/eappointment/issues/3668).

## Layout

| Path | Purpose |
|------|---------|
| [`../AGENTS.md`](../AGENTS.md) | Always-on constitution (keep short) |
| [`modes/`](modes/) | One mode per session; open on demand |
| [`inbox/ideas/`](inbox/ideas/) | Unticketed ideas — agents may append; humans triage |
| [`inbox/plumber/`](inbox/plumber/) | Debt / refactor notes — same triage rules |
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

## Rituals

- **Daily:** announce mode; open only review-sized PRs.
- **Twice weekly (~15 min):** triage `.agents/inbox/` — promote to a ticket, defer, or delete.
- **Weekly:** one Plumber scan (manual until automation exists).
- **Monthly:** retro on PRs opened vs merged, review wait, and send-backs for “too large” or “mixed refactor”.

## Bureaucracy budget

- Always-on constitution under ~80 lines.
- One concern per mode file; link to `docs/` for depth.
- A new shared rule needs a PR, an owner, and a failure mode it prevents.
- If a rule does not reduce review cost, safety incidents, or wrong-branch mistakes — delete it.
- Personal rules stay off-repo and must not contradict the constitution.

## Ownership

Rotate an **agent mayor** every four weeks: owns `AGENTS.md` drift and inbox triage (or delegates triage weekly).
