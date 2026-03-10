-- Drop legacy Berechtigung column and its index from nutzer
-- Preconditions:
--   - All application code no longer depends on numeric Berechtigung levels
--   - Roles and permissions (user_role / role_permission) are the single
--     source of truth for access control

ALTER TABLE `nutzer`
    DROP COLUMN `Berechtigung`,
    DROP INDEX `idx_nutzer_berechtigung`;

