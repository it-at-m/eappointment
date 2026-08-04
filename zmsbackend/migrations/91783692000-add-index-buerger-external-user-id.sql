-- ZMSKVR-1204: speed up my-appointments lookup by external_user_id
CREATE INDEX idx_buerger_external_user_id ON buerger (external_user_id);
