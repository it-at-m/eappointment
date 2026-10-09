# Personal agent rules (local only)

Put **your** preferences here. They are gitignored and never leave your machine.

## Where to put files

```text
.agents/local/rules/*.md
```

Examples: editor taste, shortcuts you like, machine-specific paths. Do **not** put secrets, tokens, or personal data about other people.

## What agents do

Root `AGENTS.md` tells every harness to read markdown files in `.agents/local/rules/` when they exist. Personal rules must **not contradict** the constitution or shared modes under `.agents/modes/`.

Tool homes that also stay off-repo (optional, in addition to this folder):

| Tool | Personal file |
|------|----------------|
| OpenCode | `~/.config/opencode/AGENTS.md` |
| Codex | `~/.codex/AGENTS.md` |
| Claude | `~/.claude/CLAUDE.md` |
| Gemini | `~/.gemini/GEMINI.md` |
| Cursor | Cursor Settings → User Rules |

## Restored Cursor rules

If you had project `.cursor/rules/*.mdc` before the shared adapters landed, a one-time copy may exist under `rules/backup-from-cursor-*.md` on the machine that migrated (gitignored). Prefer promoting anything still needed into shared `.agents/modes/` via PR, or keep it as personal taste here.
