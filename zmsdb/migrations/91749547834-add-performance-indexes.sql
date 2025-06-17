-- Add remaining indexes for ProcessStatusFree query performance
ALTER TABLE oeffnungszeit ADD INDEX idx_oeffnungszeit_id (OeffnungszeitID);
ALTER TABLE closures ADD INDEX idx_closures_scope_date (StandortID, year, month, day); 