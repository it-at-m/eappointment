-- ZMSKVR-1418: past appointments in customer search, scope 169 (department 40).
-- ZMSKVR-1545 checks the 90-day window and the descending appointment time.
-- ZMSKVR-1590 checks the Terminstatus column. The planned row is booked by the scenario.
-- The table is created here because this Flyway run is before the backend migration.

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

INSERT INTO `process_search_history` (
    `history_key`,
    `process_id`,
    `scope_id`,
    `display_number`,
    `appointment_at`,
    `booked_at`,
    `called_at`,
    `finalized_at`,
    `status`,
    `citizen_name`,
    `telephone`,
    `citizen_email`,
    `amendment`,
    `location_name`,
    `provider_name`,
    `services`
) VALUES (
    SHA2('zmskvr-1418-within', 256),
    99014181,
    169,
    'H4181',
    TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 80 DAY), '10:00:00'),
    TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 81 DAY), '09:00:00'),
    NULL,
    TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 80 DAY), '11:00:00'),
    'completed',
    'Zmskvr1418Within',
    '',
    '',
    NULL,
    'Bürgerbüro Forstenrieder Allee',
    'Bürgerbüro Forstenrieder Allee',
    NULL
), (
    SHA2('zmskvr-1418-missed', 256),
    99014182,
    169,
    'H4182',
    TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 10 DAY), '15:00:00'),
    TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 11 DAY), '09:00:00'),
    TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 10 DAY), '15:30:00'),
    TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 10 DAY), '16:00:00'),
    'missed',
    'Zmskvr1418Missed',
    '',
    '',
    NULL,
    'Bürgerbüro Forstenrieder Allee',
    'Bürgerbüro Forstenrieder Allee',
    NULL
), (
    SHA2('zmskvr-1418-old', 256),
    99014183,
    169,
    'H4183',
    TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 100 DAY), '10:00:00'),
    TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 101 DAY), '09:00:00'),
    NULL,
    TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 100 DAY), '11:00:00'),
    'completed',
    'Zmskvr1418Old',
    '',
    '',
    NULL,
    'Bürgerbüro Forstenrieder Allee',
    'Bürgerbüro Forstenrieder Allee',
    NULL
);
