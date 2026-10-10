CREATE TABLE IF NOT EXISTS `statistic_availability_check`
(
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `checked_date` DATE NOT NULL,
    `checked_at` DATETIME NOT NULL,
    `status` ENUM('ok', 'error', 'unknown') NOT NULL DEFAULT 'unknown',
    `statistics_checked` INT UNSIGNED NOT NULL DEFAULT 0,
    `statistics_missing` INT UNSIGNED NOT NULL DEFAULT 0,
    `missing` JSON NOT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_statistic_availability_checked_date` (`checked_date`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;