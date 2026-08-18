-- Additive: opening-hours change history for tech admins (ZMSKVR-1249).
-- Snapshot uses the same English column names/types as oeffnungszeit (except PK
-- OeffnungszeitID → availability_id, and updated_at → changed_at audit stamp).
-- No FKs: availability_id must survive deletes; scope_id is logical only.
-- weekday uses the same bit matrix as availability.weekday.

CREATE TABLE IF NOT EXISTS `availability_history` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `scope_id` INT(5) UNSIGNED NOT NULL,
    `availability_id` INT(5) UNSIGNED DEFAULT NULL,
    `action` ENUM('created', 'updated', 'deleted', 'dldb_slot_update') NOT NULL,
    `start_date` DATE NOT NULL DEFAULT '0000-00-00',
    `end_date` DATE NOT NULL DEFAULT '0000-00-00',
    `every_x_weeks` INT(5) UNSIGNED NOT NULL DEFAULT 0,
    `every_other_week` INT(5) UNSIGNED NOT NULL DEFAULT 0,
    `weekday` INT(5) UNSIGNED NOT NULL DEFAULT 0,
    `start_time` TIME NOT NULL DEFAULT '00:00:00',
    `appointment_start_time` TIME NOT NULL DEFAULT '00:00:00',
    `end_time` TIME NOT NULL DEFAULT '00:00:00',
    `appointment_end_time` TIME NOT NULL DEFAULT '00:00:00',
    `time_slot` TIME NOT NULL DEFAULT '00:00:00',
    `workstation_count` INT(5) NOT NULL DEFAULT 0,
    `appointment_workstation_count` INT(5) NOT NULL DEFAULT 0,
    `comment` VARCHAR(200) NULL DEFAULT NULL,
    `internet_reduction` INT(2) NULL DEFAULT 0,
    `multiple_slots_allowed` TINYINT(1) NOT NULL DEFAULT 0,
    `open_from_days` INT(11) NOT NULL DEFAULT 0,
    `open_until_days` INT(11) NOT NULL DEFAULT 0,
    `version` INT(5) NOT NULL DEFAULT 1,
    `changed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `changed_by` VARCHAR(100) NOT NULL DEFAULT '',

    PRIMARY KEY (`id`),
    KEY `idx_availability_history_scope_changed` (`scope_id`, `changed_at`),
    KEY `idx_availability_history_availability` (`availability_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
