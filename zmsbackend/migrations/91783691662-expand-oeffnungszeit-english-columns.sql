-- Expand phase: dual English columns on oeffnungszeit for rename toward `availability`.
-- Deploy before code that reads/writes the English column names.
-- Keep German columns + indexes; do not touch slot / standort.
--
-- Mapping (docs/en/.../standardize-database-table-and-field-naming.md):
--   StandortID → scope_id, Startdatum → start_date, Endedatum → end_date,
--   allexWochen → every_x_weeks, jedexteWoche → every_other_week, Wochentag → weekday,
--   Anfangszeit → start_time, Terminanfangszeit → appointment_start_time,
--   Endzeit → end_time, Terminendzeit → appointment_end_time, Timeslot → time_slot,
--   Anzahlarbeitsplaetze → workstation_count,
--   Anzahlterminarbeitsplaetze → appointment_workstation_count,
--   kommentar → comment, reduktionTermineImInternet → internet_reduction,
--   erlaubemehrfachslots → multiple_slots_allowed,
--   Offen_ab → open_from_days, Offen_bis → open_until_days,
--   updateTimestamp → updated_at
-- PK OeffnungszeitID and table name stay until a later rename step.
-- version is already English snake_case.

ALTER TABLE `oeffnungszeit`
    ADD COLUMN IF NOT EXISTS `scope_id` INT(5) UNSIGNED NOT NULL DEFAULT 0 AFTER `OeffnungszeitID`,
    ADD COLUMN IF NOT EXISTS `start_date` DATE NOT NULL DEFAULT '0000-00-00' AFTER `scope_id`,
    ADD COLUMN IF NOT EXISTS `end_date` DATE NOT NULL DEFAULT '0000-00-00' AFTER `start_date`,
    ADD COLUMN IF NOT EXISTS `every_x_weeks` INT(5) UNSIGNED NOT NULL DEFAULT 0 AFTER `end_date`,
    ADD COLUMN IF NOT EXISTS `every_other_week` INT(5) UNSIGNED NOT NULL DEFAULT 0 AFTER `every_x_weeks`,
    ADD COLUMN IF NOT EXISTS `weekday` INT(5) UNSIGNED NOT NULL DEFAULT 0 AFTER `every_other_week`,
    ADD COLUMN IF NOT EXISTS `start_time` TIME NOT NULL DEFAULT '00:00:00' AFTER `weekday`,
    ADD COLUMN IF NOT EXISTS `appointment_start_time` TIME NOT NULL DEFAULT '00:00:00' AFTER `start_time`,
    ADD COLUMN IF NOT EXISTS `end_time` TIME NOT NULL DEFAULT '00:00:00' AFTER `appointment_start_time`,
    ADD COLUMN IF NOT EXISTS `appointment_end_time` TIME NOT NULL DEFAULT '00:00:00' AFTER `end_time`,
    ADD COLUMN IF NOT EXISTS `time_slot` TIME NOT NULL DEFAULT '00:00:00' AFTER `appointment_end_time`,
    ADD COLUMN IF NOT EXISTS `workstation_count` INT(5) NOT NULL DEFAULT 0 AFTER `time_slot`,
    ADD COLUMN IF NOT EXISTS `appointment_workstation_count` INT(5) NOT NULL DEFAULT 0 AFTER `workstation_count`,
    ADD COLUMN IF NOT EXISTS `comment` VARCHAR(200) NULL DEFAULT NULL AFTER `appointment_workstation_count`,
    ADD COLUMN IF NOT EXISTS `internet_reduction` INT(2) NULL DEFAULT 0 AFTER `comment`,
    ADD COLUMN IF NOT EXISTS `multiple_slots_allowed` TINYINT(1) NOT NULL DEFAULT 0 AFTER `internet_reduction`,
    ADD COLUMN IF NOT EXISTS `open_from_days` INT(11) NOT NULL DEFAULT 0 AFTER `multiple_slots_allowed`,
    ADD COLUMN IF NOT EXISTS `open_until_days` INT(11) NOT NULL DEFAULT 0 AFTER `open_from_days`,
    ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `open_until_days`;

-- Backfill English columns from German without destroying original timestamps.
-- Any row UPDATE would bump updateTimestamp via ON UPDATE CURRENT_TIMESTAMP and
-- make availability.lastChange look newer than slot rows → perpetual rebuilds /
-- empty free lists in unit tests (importTestData runs this then CalculateSlots).
ALTER TABLE `oeffnungszeit`
    MODIFY COLUMN `updateTimestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

UPDATE `oeffnungszeit`
SET
    `scope_id` = `StandortID`,
    `start_date` = `Startdatum`,
    `end_date` = `Endedatum`,
    `every_x_weeks` = `allexWochen`,
    `every_other_week` = `jedexteWoche`,
    `weekday` = `Wochentag`,
    `start_time` = `Anfangszeit`,
    `appointment_start_time` = `Terminanfangszeit`,
    `end_time` = `Endzeit`,
    `appointment_end_time` = `Terminendzeit`,
    `time_slot` = `Timeslot`,
    `workstation_count` = `Anzahlarbeitsplaetze`,
    `appointment_workstation_count` = `Anzahlterminarbeitsplaetze`,
    `comment` = `kommentar`,
    `internet_reduction` = `reduktionTermineImInternet`,
    `multiple_slots_allowed` = `erlaubemehrfachslots`,
    `open_from_days` = `Offen_ab`,
    `open_until_days` = `Offen_bis`,
    `updated_at` = `updateTimestamp`;

ALTER TABLE `oeffnungszeit`
    MODIFY COLUMN `updateTimestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Indexes on English columns so new code keeps query plans after code switch.
-- Old indexes on German columns remain until contract.
ALTER TABLE `oeffnungszeit`
    ADD INDEX IF NOT EXISTS `idx_availability_scope_appt_start` (`scope_id`, `appointment_start_time`),
    ADD INDEX IF NOT EXISTS `idx_availability_start_end_date` (`start_date`, `end_date`),
    ADD INDEX IF NOT EXISTS `idx_availability_updated_at` (`updated_at`);

DELIMITER $$

DROP TRIGGER IF EXISTS `oeffnungszeit_english_bi`$$
DROP TRIGGER IF EXISTS `oeffnungszeit_english_bu`$$

CREATE TRIGGER `oeffnungszeit_english_bi`
BEFORE INSERT ON `oeffnungszeit`
FOR EACH ROW
BEGIN
    IF NEW.`StandortID` != 0 AND NEW.`scope_id` = 0 THEN
        SET NEW.`scope_id` = NEW.`StandortID`;
    ELSEIF NEW.`scope_id` != 0 AND NEW.`StandortID` = 0 THEN
        SET NEW.`StandortID` = NEW.`scope_id`;
    ELSEIF NEW.`scope_id` != NEW.`StandortID` THEN
        SET NEW.`StandortID` = NEW.`scope_id`;
    END IF;

    IF NEW.`Startdatum` != '0000-00-00' AND NEW.`start_date` = '0000-00-00' THEN
        SET NEW.`start_date` = NEW.`Startdatum`;
    ELSEIF NEW.`start_date` != '0000-00-00' AND NEW.`Startdatum` = '0000-00-00' THEN
        SET NEW.`Startdatum` = NEW.`start_date`;
    ELSEIF NEW.`start_date` != NEW.`Startdatum` THEN
        SET NEW.`Startdatum` = NEW.`start_date`;
    END IF;

    IF NEW.`Endedatum` != '0000-00-00' AND NEW.`end_date` = '0000-00-00' THEN
        SET NEW.`end_date` = NEW.`Endedatum`;
    ELSEIF NEW.`end_date` != '0000-00-00' AND NEW.`Endedatum` = '0000-00-00' THEN
        SET NEW.`Endedatum` = NEW.`end_date`;
    ELSEIF NEW.`end_date` != NEW.`Endedatum` THEN
        SET NEW.`Endedatum` = NEW.`end_date`;
    END IF;

    IF NEW.`allexWochen` != 0 AND NEW.`every_x_weeks` = 0 THEN
        SET NEW.`every_x_weeks` = NEW.`allexWochen`;
    ELSEIF NEW.`every_x_weeks` != 0 AND NEW.`allexWochen` = 0 THEN
        SET NEW.`allexWochen` = NEW.`every_x_weeks`;
    ELSEIF NEW.`every_x_weeks` != NEW.`allexWochen` THEN
        SET NEW.`allexWochen` = NEW.`every_x_weeks`;
    END IF;

    IF NEW.`jedexteWoche` != 0 AND NEW.`every_other_week` = 0 THEN
        SET NEW.`every_other_week` = NEW.`jedexteWoche`;
    ELSEIF NEW.`every_other_week` != 0 AND NEW.`jedexteWoche` = 0 THEN
        SET NEW.`jedexteWoche` = NEW.`every_other_week`;
    ELSEIF NEW.`every_other_week` != NEW.`jedexteWoche` THEN
        SET NEW.`jedexteWoche` = NEW.`every_other_week`;
    END IF;

    IF NEW.`Wochentag` != 0 AND NEW.`weekday` = 0 THEN
        SET NEW.`weekday` = NEW.`Wochentag`;
    ELSEIF NEW.`weekday` != 0 AND NEW.`Wochentag` = 0 THEN
        SET NEW.`Wochentag` = NEW.`weekday`;
    ELSEIF NEW.`weekday` != NEW.`Wochentag` THEN
        SET NEW.`Wochentag` = NEW.`weekday`;
    END IF;

    IF NEW.`Anfangszeit` != '00:00:00' AND NEW.`start_time` = '00:00:00' THEN
        SET NEW.`start_time` = NEW.`Anfangszeit`;
    ELSEIF NEW.`start_time` != '00:00:00' AND NEW.`Anfangszeit` = '00:00:00' THEN
        SET NEW.`Anfangszeit` = NEW.`start_time`;
    ELSEIF NEW.`start_time` != NEW.`Anfangszeit` THEN
        SET NEW.`Anfangszeit` = NEW.`start_time`;
    END IF;

    IF NEW.`Terminanfangszeit` != '00:00:00' AND NEW.`appointment_start_time` = '00:00:00' THEN
        SET NEW.`appointment_start_time` = NEW.`Terminanfangszeit`;
    ELSEIF NEW.`appointment_start_time` != '00:00:00' AND NEW.`Terminanfangszeit` = '00:00:00' THEN
        SET NEW.`Terminanfangszeit` = NEW.`appointment_start_time`;
    ELSEIF NEW.`appointment_start_time` != NEW.`Terminanfangszeit` THEN
        SET NEW.`Terminanfangszeit` = NEW.`appointment_start_time`;
    END IF;

    IF NEW.`Endzeit` != '00:00:00' AND NEW.`end_time` = '00:00:00' THEN
        SET NEW.`end_time` = NEW.`Endzeit`;
    ELSEIF NEW.`end_time` != '00:00:00' AND NEW.`Endzeit` = '00:00:00' THEN
        SET NEW.`Endzeit` = NEW.`end_time`;
    ELSEIF NEW.`end_time` != NEW.`Endzeit` THEN
        SET NEW.`Endzeit` = NEW.`end_time`;
    END IF;

    IF NEW.`Terminendzeit` != '00:00:00' AND NEW.`appointment_end_time` = '00:00:00' THEN
        SET NEW.`appointment_end_time` = NEW.`Terminendzeit`;
    ELSEIF NEW.`appointment_end_time` != '00:00:00' AND NEW.`Terminendzeit` = '00:00:00' THEN
        SET NEW.`Terminendzeit` = NEW.`appointment_end_time`;
    ELSEIF NEW.`appointment_end_time` != NEW.`Terminendzeit` THEN
        SET NEW.`Terminendzeit` = NEW.`appointment_end_time`;
    END IF;

    IF NEW.`Timeslot` != '00:00:00' AND NEW.`time_slot` = '00:00:00' THEN
        SET NEW.`time_slot` = NEW.`Timeslot`;
    ELSEIF NEW.`time_slot` != '00:00:00' AND NEW.`Timeslot` = '00:00:00' THEN
        SET NEW.`Timeslot` = NEW.`time_slot`;
    ELSEIF NEW.`time_slot` != NEW.`Timeslot` THEN
        SET NEW.`Timeslot` = NEW.`time_slot`;
    END IF;

    IF NEW.`Anzahlarbeitsplaetze` != 0 AND NEW.`workstation_count` = 0 THEN
        SET NEW.`workstation_count` = NEW.`Anzahlarbeitsplaetze`;
    ELSEIF NEW.`workstation_count` != 0 AND NEW.`Anzahlarbeitsplaetze` = 0 THEN
        SET NEW.`Anzahlarbeitsplaetze` = NEW.`workstation_count`;
    ELSEIF NEW.`workstation_count` != NEW.`Anzahlarbeitsplaetze` THEN
        SET NEW.`Anzahlarbeitsplaetze` = NEW.`workstation_count`;
    END IF;

    IF NEW.`Anzahlterminarbeitsplaetze` != 0 AND NEW.`appointment_workstation_count` = 0 THEN
        SET NEW.`appointment_workstation_count` = NEW.`Anzahlterminarbeitsplaetze`;
    ELSEIF NEW.`appointment_workstation_count` != 0 AND NEW.`Anzahlterminarbeitsplaetze` = 0 THEN
        SET NEW.`Anzahlterminarbeitsplaetze` = NEW.`appointment_workstation_count`;
    ELSEIF NEW.`appointment_workstation_count` != NEW.`Anzahlterminarbeitsplaetze` THEN
        SET NEW.`Anzahlterminarbeitsplaetze` = NEW.`appointment_workstation_count`;
    END IF;

    IF NEW.`kommentar` IS NOT NULL AND NEW.`comment` IS NULL THEN
        SET NEW.`comment` = NEW.`kommentar`;
    ELSEIF NEW.`comment` IS NOT NULL AND NEW.`kommentar` IS NULL THEN
        SET NEW.`kommentar` = NEW.`comment`;
    ELSEIF NEW.`comment` IS NOT NULL AND NEW.`kommentar` IS NOT NULL AND NEW.`comment` != NEW.`kommentar` THEN
        SET NEW.`kommentar` = NEW.`comment`;
    END IF;

    IF (NEW.`reduktionTermineImInternet` IS NOT NULL AND NEW.`reduktionTermineImInternet` != 0)
       AND (NEW.`internet_reduction` IS NULL OR NEW.`internet_reduction` = 0) THEN
        SET NEW.`internet_reduction` = NEW.`reduktionTermineImInternet`;
    ELSEIF (NEW.`internet_reduction` IS NOT NULL AND NEW.`internet_reduction` != 0)
       AND (NEW.`reduktionTermineImInternet` IS NULL OR NEW.`reduktionTermineImInternet` = 0) THEN
        SET NEW.`reduktionTermineImInternet` = NEW.`internet_reduction`;
    ELSEIF NEW.`internet_reduction` IS NOT NULL
       AND NEW.`internet_reduction` != NEW.`reduktionTermineImInternet` THEN
        SET NEW.`reduktionTermineImInternet` = NEW.`internet_reduction`;
    END IF;

    IF NEW.`erlaubemehrfachslots` != 0 AND NEW.`multiple_slots_allowed` = 0 THEN
        SET NEW.`multiple_slots_allowed` = NEW.`erlaubemehrfachslots`;
    ELSEIF NEW.`multiple_slots_allowed` != 0 AND NEW.`erlaubemehrfachslots` = 0 THEN
        SET NEW.`erlaubemehrfachslots` = NEW.`multiple_slots_allowed`;
    ELSEIF NEW.`multiple_slots_allowed` != NEW.`erlaubemehrfachslots` THEN
        SET NEW.`erlaubemehrfachslots` = NEW.`multiple_slots_allowed`;
    END IF;

    IF NEW.`Offen_ab` != 0 AND NEW.`open_from_days` = 0 THEN
        SET NEW.`open_from_days` = NEW.`Offen_ab`;
    ELSEIF NEW.`open_from_days` != 0 AND NEW.`Offen_ab` = 0 THEN
        SET NEW.`Offen_ab` = NEW.`open_from_days`;
    ELSEIF NEW.`open_from_days` != NEW.`Offen_ab` THEN
        SET NEW.`Offen_ab` = NEW.`open_from_days`;
    END IF;

    IF NEW.`Offen_bis` != 0 AND NEW.`open_until_days` = 0 THEN
        SET NEW.`open_until_days` = NEW.`Offen_bis`;
    ELSEIF NEW.`open_until_days` != 0 AND NEW.`Offen_bis` = 0 THEN
        SET NEW.`Offen_bis` = NEW.`open_until_days`;
    ELSEIF NEW.`open_until_days` != NEW.`Offen_bis` THEN
        SET NEW.`Offen_bis` = NEW.`open_until_days`;
    END IF;

    -- Prefer an explicitly supplied updated_at over the auto default on updateTimestamp.
    IF NEW.`updated_at` != NEW.`updateTimestamp` THEN
        SET NEW.`updateTimestamp` = NEW.`updated_at`;
    ELSE
        SET NEW.`updated_at` = NEW.`updateTimestamp`;
    END IF;
END$$

CREATE TRIGGER `oeffnungszeit_english_bu`
BEFORE UPDATE ON `oeffnungszeit`
FOR EACH ROW
BEGIN
    IF NEW.`StandortID` != OLD.`StandortID` THEN
        SET NEW.`scope_id` = NEW.`StandortID`;
    ELSEIF NEW.`scope_id` != OLD.`scope_id` THEN
        SET NEW.`StandortID` = NEW.`scope_id`;
    END IF;

    IF NEW.`Startdatum` != OLD.`Startdatum` THEN
        SET NEW.`start_date` = NEW.`Startdatum`;
    ELSEIF NEW.`start_date` != OLD.`start_date` THEN
        SET NEW.`Startdatum` = NEW.`start_date`;
    END IF;

    IF NEW.`Endedatum` != OLD.`Endedatum` THEN
        SET NEW.`end_date` = NEW.`Endedatum`;
    ELSEIF NEW.`end_date` != OLD.`end_date` THEN
        SET NEW.`Endedatum` = NEW.`end_date`;
    END IF;

    IF NEW.`allexWochen` != OLD.`allexWochen` THEN
        SET NEW.`every_x_weeks` = NEW.`allexWochen`;
    ELSEIF NEW.`every_x_weeks` != OLD.`every_x_weeks` THEN
        SET NEW.`allexWochen` = NEW.`every_x_weeks`;
    END IF;

    IF NEW.`jedexteWoche` != OLD.`jedexteWoche` THEN
        SET NEW.`every_other_week` = NEW.`jedexteWoche`;
    ELSEIF NEW.`every_other_week` != OLD.`every_other_week` THEN
        SET NEW.`jedexteWoche` = NEW.`every_other_week`;
    END IF;

    IF NEW.`Wochentag` != OLD.`Wochentag` THEN
        SET NEW.`weekday` = NEW.`Wochentag`;
    ELSEIF NEW.`weekday` != OLD.`weekday` THEN
        SET NEW.`Wochentag` = NEW.`weekday`;
    END IF;

    IF NEW.`Anfangszeit` != OLD.`Anfangszeit` THEN
        SET NEW.`start_time` = NEW.`Anfangszeit`;
    ELSEIF NEW.`start_time` != OLD.`start_time` THEN
        SET NEW.`Anfangszeit` = NEW.`start_time`;
    END IF;

    IF NEW.`Terminanfangszeit` != OLD.`Terminanfangszeit` THEN
        SET NEW.`appointment_start_time` = NEW.`Terminanfangszeit`;
    ELSEIF NEW.`appointment_start_time` != OLD.`appointment_start_time` THEN
        SET NEW.`Terminanfangszeit` = NEW.`appointment_start_time`;
    END IF;

    IF NEW.`Endzeit` != OLD.`Endzeit` THEN
        SET NEW.`end_time` = NEW.`Endzeit`;
    ELSEIF NEW.`end_time` != OLD.`end_time` THEN
        SET NEW.`Endzeit` = NEW.`end_time`;
    END IF;

    IF NEW.`Terminendzeit` != OLD.`Terminendzeit` THEN
        SET NEW.`appointment_end_time` = NEW.`Terminendzeit`;
    ELSEIF NEW.`appointment_end_time` != OLD.`appointment_end_time` THEN
        SET NEW.`Terminendzeit` = NEW.`appointment_end_time`;
    END IF;

    IF NEW.`Timeslot` != OLD.`Timeslot` THEN
        SET NEW.`time_slot` = NEW.`Timeslot`;
    ELSEIF NEW.`time_slot` != OLD.`time_slot` THEN
        SET NEW.`Timeslot` = NEW.`time_slot`;
    END IF;

    IF NEW.`Anzahlarbeitsplaetze` != OLD.`Anzahlarbeitsplaetze` THEN
        SET NEW.`workstation_count` = NEW.`Anzahlarbeitsplaetze`;
    ELSEIF NEW.`workstation_count` != OLD.`workstation_count` THEN
        SET NEW.`Anzahlarbeitsplaetze` = NEW.`workstation_count`;
    END IF;

    IF NEW.`Anzahlterminarbeitsplaetze` != OLD.`Anzahlterminarbeitsplaetze` THEN
        SET NEW.`appointment_workstation_count` = NEW.`Anzahlterminarbeitsplaetze`;
    ELSEIF NEW.`appointment_workstation_count` != OLD.`appointment_workstation_count` THEN
        SET NEW.`Anzahlterminarbeitsplaetze` = NEW.`appointment_workstation_count`;
    END IF;

    IF NOT (NEW.`kommentar` <=> OLD.`kommentar`) THEN
        SET NEW.`comment` = NEW.`kommentar`;
    ELSEIF NOT (NEW.`comment` <=> OLD.`comment`) THEN
        SET NEW.`kommentar` = NEW.`comment`;
    END IF;

    IF NOT (NEW.`reduktionTermineImInternet` <=> OLD.`reduktionTermineImInternet`) THEN
        SET NEW.`internet_reduction` = NEW.`reduktionTermineImInternet`;
    ELSEIF NOT (NEW.`internet_reduction` <=> OLD.`internet_reduction`) THEN
        SET NEW.`reduktionTermineImInternet` = NEW.`internet_reduction`;
    END IF;

    IF NEW.`erlaubemehrfachslots` != OLD.`erlaubemehrfachslots` THEN
        SET NEW.`multiple_slots_allowed` = NEW.`erlaubemehrfachslots`;
    ELSEIF NEW.`multiple_slots_allowed` != OLD.`multiple_slots_allowed` THEN
        SET NEW.`erlaubemehrfachslots` = NEW.`multiple_slots_allowed`;
    END IF;

    IF NEW.`Offen_ab` != OLD.`Offen_ab` THEN
        SET NEW.`open_from_days` = NEW.`Offen_ab`;
    ELSEIF NEW.`open_from_days` != OLD.`open_from_days` THEN
        SET NEW.`Offen_ab` = NEW.`open_from_days`;
    END IF;

    IF NEW.`Offen_bis` != OLD.`Offen_bis` THEN
        SET NEW.`open_until_days` = NEW.`Offen_bis`;
    ELSEIF NEW.`open_until_days` != OLD.`open_until_days` THEN
        SET NEW.`Offen_bis` = NEW.`open_until_days`;
    END IF;

    -- Prefer an explicit updated_at write over ON UPDATE CURRENT_TIMESTAMP on updateTimestamp.
    IF NEW.`updated_at` != OLD.`updated_at` THEN
        SET NEW.`updateTimestamp` = NEW.`updated_at`;
    ELSEIF NEW.`updateTimestamp` != OLD.`updateTimestamp` THEN
        SET NEW.`updated_at` = NEW.`updateTimestamp`;
    ELSE
        SET NEW.`updated_at` = NEW.`updateTimestamp`;
    END IF;
END$$

DELIMITER ;
