-- Contract phase: drop legacy JSON blob after indexed columns and process_amendment are in use.
-- Run only after expand migration 91783691659 and deploy of code that no longer reads/writes `data`.

ALTER TABLE `log`
    DROP COLUMN IF EXISTS `data`;
