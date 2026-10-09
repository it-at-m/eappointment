# Mode: feature (Builder)

Use for a ticketed product feature or enhancement. Announce: `mode: feature`.

## Branch

1. Fetch `origin/next` and branch from that commit (not from `main` or another feature branch).
2. Name from `docs/en/setup-and-development/development-rules/branching-strategy-and-convention.md`, e.g. `feature-zmskvr-12345-short-slug`.
3. Project prefix from the ticket (`ZMSKVR` → `zmskvr`). No ticket → stop; use `.agents/inbox/ideas/` instead.

## Plan, then commits

Before editing, list commits for the layers this story touches.

- One module, no migration: one commit. Say so, then implement.
- Two or more layers: write each commit subject in stack order, then implement in that order. One layer per commit.

Subjects: `docs/en/setup-and-development/development-rules/commit-message-convention.md` — e.g. `feat(ZMSKVR-12345): why`. Unit tests travel with the behavior they cover. Handbook updates are a separate `docs(...)` commit. Do not skip hooks.

## Stack

Follow `docs/en/setup-and-development/getting-started/implement-a-user-story.md`. Bottom-up; skip layers the story does not need.

1. `zmsbackend` migration when schema or stored data changes
2. Repository conditions, then the service that composes them
3. `zmsentities` schema/model when the API response shape changes
4. API controller; `zmsbackend/routing.php` when the controller is new
5. Legacy UI and/or `zmscitizenapi`, then `zmscitizenview`

`zmscitizenapi` has no direct DB access. New citizen fields start in `zmsentities/schema/citizenapi/`. UI-only work starts at that UI. When a handbook page already describes the behavior, update `docs/en` and `docs/de`.

## Verify, push, PR

- Run filtered unit tests for touched modules inside `zms-web` with Xdebug off (`zmscitizenview`: `npm test`). Fix failures before commit.
- UI-visible change: verify the flow in the browser before commit.
- Diff check for personal/internal data (root `AGENTS.md`). Stop if found.
- Fetch/merge `origin/next` when behind. Push. Open PR against `next`.
- Title matches commit subject. Body starts from `.github/PULL_REQUEST_TEMPLATE.md`. Check boxes only when done. Leave code review and fachliche Tests unchecked. Leave zmsautomation unchecked unless this PR also adds those tests (then use Sentinel / `test.md`).
