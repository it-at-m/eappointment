CREATE INDEX scopeID_year_month_day_status_index ON slot(scopeID, year, month, day, status);
CREATE INDEX ancestorID_ancestorLevel_index ON slot_hiera(ancestorID, ancestorLevel);
CREATE INDEX scopeID_status_slotID_index ON slot(scopeID, status, slotID);
CREATE INDEX slotID_index ON slot_process(slotID);