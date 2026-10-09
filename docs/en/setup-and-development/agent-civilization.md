# Agent civilization

This page is the human onboarding guide for shared coding-agent practice in eAppointment. The always-on agent law is the repository root [`AGENTS.md`](https://github.com/it-at-m/eappointment/blob/next/AGENTS.md). The team guide and modes live under [`.agents/`](https://github.com/it-at-m/eappointment/tree/next/.agents).

Tracking issue: [it-at-m/eappointment#3668](https://github.com/it-at-m/eappointment/issues/3668).

## Why

When everyone on a small team uses agents, coding volume rises faster than review capacity. Shared borders keep pull requests review-sized and ticketed so more agent activity still becomes merged software.

## Roles

| Role         | Job                                                      |
| ------------ | -------------------------------------------------------- |
| **Builder**  | Ticketed product feature or bugfix                       |
| **Reviewer** | PR filter: merge / request changes / split; comment only |
| **Plumber**  | Debt notes and one-theme cleanup after a ticket          |
| **Sentinel** | Behavioral tests (for example zmsautomation)             |

## Model policy

**Free only** with OpenCode: local models and/or free cloud quotas. The optional GitHub Actions Reviewer (`.github/workflows/opencode-review.yml`) stays off until the repository variable `OPENCODE_REVIEW_ENABLED=true` and a free-capable API secret are set. Otherwise run Reviewer locally.

## How to work

1. Open the repo in OpenCode (or another harness that reads `AGENTS.md`).
2. Announce one mode and open `.agents/modes/<mode>.md`.
3. Without a ticket, write an inbox note under `.agents/inbox/` instead of opening a product PR.
4. Keep product, plumber, and sentinel work on separate themes.

Branching, commits, and stack order remain in the existing handbook pages under [Setup and development](/setup-and-development/getting-started/getting-started-with-docs).

Team process details (meetings, ownership rotation, metrics) stay outside this handbook for now. See [`.agents/README.md`](https://github.com/it-at-m/eappointment/blob/next/.agents/README.md) for roles, modes, and the free-only model policy.
