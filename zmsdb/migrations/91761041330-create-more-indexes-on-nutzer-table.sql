CREATE INDEX idx_nutzer_sessionid_expiry
    ON nutzer (SessionID, SessionExpiry);
