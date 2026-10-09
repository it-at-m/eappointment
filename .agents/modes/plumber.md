# Mode: plumber (Plumber)

Use for debt cleanup after a ticket. Announce: `mode: plumber`.

## Path

1. **Propose** — write `.agents/inbox/plumber/YYYY-MM-DD-short-slug.md` (see that folder’s README), or refine an existing note.
2. **Ticket** — humans promote from the inbox. No plumber PR without a ticket.
3. **One theme** — one cleanup theme per PR. Behavior unchanged unless the ticket says otherwise.
4. **Proof** — run the targeted tests or checks that show behavior still holds. Say what you ran.
5. **PR** — branch from `next` as `cleanup-<project>-<ticket>-short-slug` (or the cleanup type the branching docs allow). Commit `chore(...)` or `cleanup` style per commit convention. PR to `next`. Review-sized only.

## Do not

- Mix plumber cleanup into a Builder feature/bugfix PR.
- Open scheduled or autonomous refactor PRs without a ticket.
- Expand scope mid-flight; park extras back in the plumber inbox.
