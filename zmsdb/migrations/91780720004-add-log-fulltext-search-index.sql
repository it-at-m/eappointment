-- Full-text index for word search on indexed log columns (terms with 4+ characters).
-- Build time on large tables can take several minutes; run during deploy maintenance window.
-- Short (1-3 char) queries use prefix/substring paths in Log::getBySearchParams instead.

CREATE FULLTEXT INDEX IF NOT EXISTS idx_log_search_fulltext
    ON log (client_name, services, scope_name, client_email);

CREATE INDEX IF NOT EXISTS idx_log_type_client_ts
    ON log (type, client_name(64), ts);
