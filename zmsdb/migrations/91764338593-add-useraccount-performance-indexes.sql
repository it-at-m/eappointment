-- Add indexes to improve useraccount list query performance
-- These indexes optimize department-based and role-based user lookups

-- Index on nutzerzuordnung.behoerdenid for department-based queries
-- This is critical for addConditionDepartmentIds() and department list endpoints
ALTER TABLE nutzerzuordnung 
    ADD INDEX IF NOT EXISTS idx_nutzerzuordnung_behoerdenid (behoerdenid);

-- Index on nutzer.Berechtigung for role-based queries
-- This optimizes readListRole() and role-based list endpoints
ALTER TABLE nutzer 
    ADD INDEX IF NOT EXISTS idx_nutzer_berechtigung (Berechtigung);

