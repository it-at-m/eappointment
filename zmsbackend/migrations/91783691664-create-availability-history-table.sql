-- Additive: opening-hours change history for tech admins (ZMSKVR-1249).
-- No FKs: availability_id must survive deletes; scope_id is logical only.
-- Columns mirror the admin day-table snapshot (not a free-text summary).

CREATE TABLE IF NOT EXISTS `availability_history` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `scope_id` INT UNSIGNED NOT NULL,
    `availability_id` INT UNSIGNED DEFAULT NULL,
    `action` ENUM('created', 'updated', 'deleted', 'dldb_slot_update') NOT NULL,
    `weekdays` VARCHAR(255) NOT NULL DEFAULT '',
    `series` VARCHAR(100) NOT NULL DEFAULT '',
    `valid_from` VARCHAR(10) NOT NULL DEFAULT '',
    `valid_to` VARCHAR(10) NOT NULL DEFAULT '',
    `time_range` VARCHAR(32) NOT NULL DEFAULT '',
    `type` VARCHAR(50) NOT NULL DEFAULT '',
    `slot_time` VARCHAR(20) NOT NULL DEFAULT '',
    `workstations` VARCHAR(20) NOT NULL DEFAULT '',
    `bookable` VARCHAR(32) NOT NULL DEFAULT '',
    `description` VARCHAR(512) NOT NULL DEFAULT '',
    `changed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `changed_by` VARCHAR(100) NOT NULL DEFAULT '',

    PRIMARY KEY (`id`),
    KEY `idx_availability_history_scope_changed` (`scope_id`, `changed_at`),
    KEY `idx_availability_history_availability` (`availability_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
