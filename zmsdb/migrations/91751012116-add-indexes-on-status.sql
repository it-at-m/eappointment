ALTER TABLE buerger
    ADD INDEX idx_status (`status`);

ALTER TABLE buerger
    ADD INDEX idx_status_standort_abholort (`status`, `StandortID`, `AbholortID`);