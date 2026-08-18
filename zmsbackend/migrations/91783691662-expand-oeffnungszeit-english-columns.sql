-- Expand phase: dual English columns on oeffnungszeit for rename toward `availability`.
-- Deploy before code that reads/writes the English column names.
-- Keep German columns + indexes; do not touch slot / standort.
--
-- Do not CREATE/DROP TRIGGER here. The migrate user on managed MySQL has no SUPER
-- privilege and binary logging is on, so those statements fail with SQLSTATE 1419
-- (log_bin_trust_function_creators). New code writes English column names; German
-- twins remain until contract. Mixed old/new writers can drift until Helm finishes.
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
