-- Expand phase: add citizen_amendment column and backfill from legacy JSON `data`.
-- Deploy before code that stops writing `data` and before the contract migration.

ALTER TABLE `log`
    ADD COLUMN IF NOT EXISTS `citizen_amendment` VARCHAR(512) DEFAULT NULL;

UPDATE `log`
SET `citizen_amendment` = NULLIF(TRIM(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.Anmerkung'))), '')
WHERE `type` = 'buerger'
  AND `data` IS NOT NULL
  AND `data` != ''
  AND `citizen_amendment` IS NULL
  AND JSON_EXTRACT(`data`, '$.Anmerkung') IS NOT NULL
  AND JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.Anmerkung')) != '';
