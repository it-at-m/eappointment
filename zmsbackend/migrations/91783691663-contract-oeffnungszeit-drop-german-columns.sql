-- Contract phase: drop German twin columns on oeffnungszeit.
-- Run only after expand migration 91783691662 and deploy of code that
-- reads/writes English column names only (PK OeffnungszeitID and table name unchanged).
--
-- No DROP TRIGGER: expand never creates triggers (SQLSTATE 1419 on managed MySQL
-- with binary logging and no SUPER). DROP TRIGGER IF EXISTS fails with the same error.
--
-- Table rename oeffnungszeit → availability and PK OeffnungszeitID → availability_id
-- are intentionally deferred to a follow-up expand/contract pair.

ALTER TABLE `oeffnungszeit`
    DROP COLUMN IF EXISTS `StandortID`,
    DROP COLUMN IF EXISTS `Startdatum`,
    DROP COLUMN IF EXISTS `Endedatum`,
    DROP COLUMN IF EXISTS `allexWochen`,
    DROP COLUMN IF EXISTS `jedexteWoche`,
    DROP COLUMN IF EXISTS `Wochentag`,
    DROP COLUMN IF EXISTS `Anfangszeit`,
    DROP COLUMN IF EXISTS `Terminanfangszeit`,
    DROP COLUMN IF EXISTS `Endzeit`,
    DROP COLUMN IF EXISTS `Terminendzeit`,
    DROP COLUMN IF EXISTS `Timeslot`,
    DROP COLUMN IF EXISTS `Anzahlarbeitsplaetze`,
    DROP COLUMN IF EXISTS `Anzahlterminarbeitsplaetze`,
    DROP COLUMN IF EXISTS `kommentar`,
    DROP COLUMN IF EXISTS `reduktionTermineImInternet`,
    DROP COLUMN IF EXISTS `erlaubemehrfachslots`,
    DROP COLUMN IF EXISTS `Offen_ab`,
    DROP COLUMN IF EXISTS `Offen_bis`,
    DROP COLUMN IF EXISTS `updateTimestamp`;

-- Redundant duplicate of PRIMARY KEY; safe to drop after expand indexes exist.
ALTER TABLE `oeffnungszeit`
    DROP INDEX IF EXISTS `idx_oeffnungszeit_id`;

-- Ensure updated_at keeps ON UPDATE semantics formerly on updateTimestamp.
ALTER TABLE `oeffnungszeit`
    MODIFY COLUMN `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
