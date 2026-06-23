CREATE INDEX IF NOT EXISTS idx_log_type_ts ON log (type, ts);
CREATE INDEX IF NOT EXISTS idx_log_scope_ts ON log (scope_id, ts);
