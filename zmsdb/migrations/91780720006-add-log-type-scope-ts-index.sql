-- Composite index for scoped log search: type + scope_id + ts (ORDER BY ts DESC).
CREATE INDEX IF NOT EXISTS idx_log_type_scope_ts ON log (type, scope_id, ts);
