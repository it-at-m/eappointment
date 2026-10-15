# Mode: test (Sentinel)

Use for zmsautomation (and similar) behavioral tests. Announce: `mode: test`. You are the **Sentinel**: prove claimed behavior holds. Do not change product code to make a past window save.

## Tickets and naming

- Test ticket = Jira issue linked as "wird getestet von". The other key is the story or bug.
- Put both keys in Cucumber tags and in the feature filename.
- New Flyway testdata only when existing migrations cannot cover the scenario; both keys in filename and header comment.
- Branch from `next`: `test-zmskvr-<story>-zmskvr-<test>-<slug>` (adjust project prefix to the tickets).
- Commit and PR title: `test(<story> <test>): ...`. PR targets `next`.

## Dynamic time (Berlin)

Resolve dates and times from Berlin now so the suite can run any hour. A fixed clock range such as `05:00`–`06:00` is invalid when the date can be today and that hour is already over. Do not keep fixed times and only shift the date. The booked or saved window must stay in the future: next full hour that still fits today, else next valid day with a future hour (e.g. `08:00`–`09:00`). Reuse the same resolved times in later assertions.

## Flyway opening hours (`oeffnungszeit`)

New bookable opening-hours test data must use the same **dynamic** pattern as `V10__opening_hours_availability_test_data.sql` and `V25__ZMSKVR-1571_opening_hours_for_scheidplatz.sql`:

- Comment that `CURDATE()` / `CURTIME()` follow zms-db `TZ=Europe/Berlin`.
- Round start to the next 5-minute slot from `CURTIME()`.
- End at `23:55:00` the same day while at least three hours remain.
- If fewer than three hours remain (or start would be at/after midnight), open the **next** calendar day for the whole bookable day: `00:05:00`–`23:55:00`. That midnight gap is an application limit, not something to paper over with a fixed `08:00`–`20:00` window.
- Span several days ahead (`CURDATE()` … `+7` / next-day … `+8`) with `Wochentag = 127` unless the scenario needs a narrower range.
- Prefer enough `Anzahlterminarbeitsplaetze` for parallel shards when the scope is hit by many scenarios (captcha / short-hold scopes).
- Do **not** plant fixed wall-clock bands such as `08:00`–`20:00` for “always bookable” scopes. Copy the `@slot_seconds` / `@latest_end` / `@use_next_day` block from V10 or V25 instead of inventing a new shape.

## Workflow

- PII/internal-data check before commit, push, and PR (root `AGENTS.md`). Stop if found.
- Commit and push, then open the PR immediately. Do not wait for a local green run.
- After every push of a test, start GitHub `zmsautomation-workflow.yaml` with `gh workflow run`, filtered to the test-ticket tag, on the branch just pushed. `test_layer`: `ui`, `api`, or `both`. Chrome and Firefox. Do not wait for the run. Print the command and run URL in the summary.
- Do not run the zmsautomation suite locally as a gate. Optionally print Podman Chrome/Firefox commands filtered to the tag; if started, run in the background.
- Exception — ticket tags only (tags/filename/header, no step or helper changes): skip workflow kickoff and Podman commands; commit/push is enough.
- Finish or delete every process or appointment the scenario creates.
- Add REST and UI features when the ticket can be exercised on both; otherwise only the layer it covers.

## Tags and actors

- `@web` starts the browser (not a screen size). For zmscitizenview phone layouts, add `@mobile` with `@web` (`390×844`). Desktop omits `@mobile`.
- Reuse actor tags: `@clerk`, `@technical-admin`, `@controlling`, `@citizen-login`, `@system`.
- Keycloak logins and pools: follow existing suite conventions (`agent_basic`, `agent_queue`, pool accounts `ataf_*`, password `vorschau`). Roles are not workstations.
