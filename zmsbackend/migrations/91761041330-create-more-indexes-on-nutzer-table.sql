CREATE INDEX IF NOT EXISTS idx_nutzer_sessionid_expiry
    ON nutzer (SessionID, SessionExpiry);
