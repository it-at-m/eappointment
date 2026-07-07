-- Full-text index for log search on indexed columns (see Log::buildUnquotedSearchParts).
-- Runs after 91780720002, before 91780720006 (backfill). Built on empty columns for speed.

CREATE FULLTEXT INDEX IF NOT EXISTS idx_log_search_fulltext
    ON log (citizen_name, services, scope_name, citizen_email);

CREATE INDEX IF NOT EXISTS idx_log_type_citizen_ts
    ON log (type, citizen_name(64), ts);
