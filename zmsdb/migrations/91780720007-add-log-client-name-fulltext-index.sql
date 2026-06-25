-- FULLTEXT on client_name only for fast quoted name search (word/phrase match).
CREATE FULLTEXT INDEX IF NOT EXISTS idx_log_client_name_fulltext ON log (client_name);
