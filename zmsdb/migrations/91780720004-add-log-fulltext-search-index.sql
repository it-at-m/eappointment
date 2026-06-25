-- Full-text index for log search on indexed columns (see Log::buildUnquotedSearchParts).
-- Build time on large tables can take several minutes; run during deploy maintenance window.

CREATE FULLTEXT INDEX IF NOT EXISTS idx_log_search_fulltext
    ON log (client_name, services, scope_name, client_email);

CREATE INDEX IF NOT EXISTS idx_log_type_client_ts
    ON log (type, client_name(64), ts);
