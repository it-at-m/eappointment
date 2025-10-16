CREATE INDEX idx_buerger_AbholortID_StandortID_status
    ON buerger(`AbholortID`, `StandortID`, `status`);