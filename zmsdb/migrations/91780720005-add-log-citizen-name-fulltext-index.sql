-- FULLTEXT on citizen_name only for fast quoted name search (word/phrase match).
-- Runs after 91780720002, before 91780720006 (backfill). Built on empty columns for speed.
CREATE FULLTEXT INDEX IF NOT EXISTS idx_log_citizen_name_fulltext ON log (citizen_name);
