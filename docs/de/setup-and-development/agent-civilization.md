# Agent-Zivilisation

Diese Seite ist die menschliche Einstiegsdokumentation für gemeinsame Coding-Agent-Praxis in eAppointment. Das immer geltende Agent-Recht liegt in [`AGENTS.md`](https://github.com/it-at-m/eappointment/blob/next/AGENTS.md) im Repository-Root. Teamleitfaden und Modi liegen unter [`.agents/`](https://github.com/it-at-m/eappointment/tree/next/.agents).

Tracking-Issue: [it-at-m/eappointment#3668](https://github.com/it-at-m/eappointment/issues/3668).

## Warum

Wenn alle in einem kleinen Team Agenten nutzen, steigt das Codevolumen schneller als die Review-Kapazität. Gemeinsame Grenzen halten Pull Requests review-groß und ticketgebunden, damit aus Agent-Aktivität weiterhin gemergte Software wird.

## Rollen

| Rolle        | Aufgabe                                                      |
| ------------ | ------------------------------------------------------------ |
| **Builder**  | Produkt-Feature oder Bugfix mit Ticket                       |
| **Reviewer** | PR-Filter: merge / request_changes / split; nur kommentieren |
| **Plumber**  | Schuldennotizen und ein Thema Cleanup nach Ticket            |
| **Sentinel** | Verhaltens-Tests (z. B. zmsautomation)                       |

## Modellpolitik

**Nur kostenlos** mit OpenCode: lokale Modelle und/oder kostenlose Cloud-Kontingente. Der optionale GitHub-Actions-Reviewer (`.github/workflows/opencode-review.yml`) bleibt aus, bis die Repository-Variable `OPENCODE_REVIEW_ENABLED=true` und ein für Free-Modelle geeignetes API-Secret gesetzt sind. Sonst Reviewer lokal ausführen.

## So arbeiten

1. Repository in OpenCode öffnen (oder einem anderen Harness, der `AGENTS.md` liest).
2. Einen Modus ansagen und `.agents/modes/<mode>.md` öffnen.
3. Ohne Ticket eine Inbox-Notiz unter `.agents/inbox/` schreiben statt eines Produkt-PRs.
4. Produkt-, Plumber- und Sentinel-Arbeit thematisch getrennt halten.

Branching, Commits und Stack-Reihenfolge bleiben in den bestehenden Handbuchseiten unter [Setup und Entwicklung](/de/setup-and-development/getting-started/getting-started-with-docs).

## Rituale und Metriken

Siehe [`.agents/README.md`](https://github.com/it-at-m/eappointment/blob/next/.agents/README.md) zu Triage-Rhythmus, rotierendem Agent Mayor und monatlicher Retro (PRs geöffnet vs. gemerged, Review-Wartezeit, Split/Refactor-Rückläufer, Inbox-Promotion, PII-Near-Misses).
