-- Additive: opening-hours change history for tech admins (ZMSKVR-1249).
-- No FKs: availability_id must survive deletes; scope_id is logical only.
-- Write hooks and API land in follow-up commits.

CREATE TABLE IF NOT EXISTS `availability_history` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `scope_id` INT UNSIGNED NOT NULL,
    `availability_id` INT UNSIGNED DEFAULT NULL,
    `action` ENUM('created', 'updated', 'deleted', 'dldb_slot_update') NOT NULL,
    `summary` VARCHAR(512) NOT NULL,
    `changed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `changed_by` VARCHAR(100) NOT NULL DEFAULT '',

    PRIMARY KEY (`id`),
    KEY `idx_availability_history_scope_changed` (`scope_id`, `changed_at`),
    KEY `idx_availability_history_availability` (`availability_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
