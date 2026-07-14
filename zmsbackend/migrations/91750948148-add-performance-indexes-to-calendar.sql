CREATE INDEX idx_slot_scope_year_month_status ON slot(scopeID, year, month, status);
CREATE INDEX idx_slot_hiera_ancestorID_slotID_level ON slot_hiera(ancestorID, slotID, ancestorLevel);
CREATE INDEX idx_closures_StandortID_year_month_day ON closures(StandortID, year, month, day);