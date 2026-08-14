CREATE TABLE IF NOT EXISTS `process_search_history`
(
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `history_key` CHAR(64)
        CHARACTER SET ascii
        COLLATE ascii_bin
        NOT NULL,

    `process_id` INT UNSIGNED NOT NULL,
    `scope_id` INT UNSIGNED NOT NULL,
    `display_number` VARCHAR(8) DEFAULT NULL,

    `appointment_at` DATETIME NOT NULL,
    `booked_at` DATETIME DEFAULT NULL,
    `called_at` DATETIME DEFAULT NULL,
    `finalized_at` DATETIME NOT NULL,

    `status` VARCHAR(20) NOT NULL,

    `citizen_name` VARCHAR(200) NOT NULL DEFAULT '',
    `telephone` VARCHAR(50) NOT NULL DEFAULT '',
    `citizen_email` VARCHAR(200) NOT NULL DEFAULT '',
    `amendment` TEXT DEFAULT NULL,

    `location_name` VARCHAR(255) NOT NULL DEFAULT '',
    `provider_name` VARCHAR(255) NOT NULL DEFAULT '',
    `services` TEXT DEFAULT NULL,

    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),

    UNIQUE KEY `uniq_process_search_history_key` (`history_key`),

    KEY `idx_psh_process_id` (`process_id`),
    KEY `idx_psh_appointment_at` (`appointment_at`),
    KEY `idx_psh_scope_appointment` (`scope_id`, `appointment_at`),
    KEY `idx_psh_status` (`status`),
    KEY `idx_psh_citizen_name` (`citizen_name`),
    KEY `idx_psh_display_number` (`display_number`)
)
ENGINE = InnoDB
DEFAULT CHARSET = utf8mb4
COLLATE = utf8mb4_unicode_ci;
