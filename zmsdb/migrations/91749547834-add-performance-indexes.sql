-- Add indexes for ProcessStatusFreeUnique query performance
ALTER TABLE slot ADD INDEX idx_slot_status_scope (status, scopeID);
ALTER TABLE slot ADD INDEX idx_slot_date (year, month, day);
ALTER TABLE slot_hiera ADD INDEX idx_slot_hiera_ancestor (ancestorID, ancestorLevel);
ALTER TABLE slot_process ADD INDEX idx_slot_process_slot (slotID);
ALTER TABLE calendarscope ADD INDEX idx_calendarscope_scope (scopeID);
ALTER TABLE oeffnungszeit ADD INDEX idx_oeffnungszeit_id (OeffnungszeitID);
ALTER TABLE closures ADD INDEX idx_closures_scope_date (StandortID, year, month, day);

-- Add composite index for the GROUP BY clause
ALTER TABLE slot ADD INDEX idx_slot_scope_date (scopeID, year, month, day, time); 