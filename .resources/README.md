# Local debugging dumps

- **`zms_complete.sql`** — Full DB snapshot after all Flyway migrations + Munich (etc.) imports, for spinning up a local zmscitizenview / API test environment without re-running the whole pipeline. Regenerate when migrations or seed data change materially.
