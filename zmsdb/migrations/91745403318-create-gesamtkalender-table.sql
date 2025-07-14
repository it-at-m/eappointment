DROP TABLE IF EXISTS `gesamtkalender`;
CREATE TABLE `gesamtkalender` (
    `id`         INT          UNSIGNED NOT NULL AUTO_INCREMENT,
    `scope_id`   INT          UNSIGNED NOT NULL,
    `availability_id`   INT          UNSIGNED NULL,
    `time`       DATETIME              NOT NULL,
    `seat`       TINYINT      UNSIGNED NOT NULL DEFAULT 1,
    `process_id` INT          UNSIGNED NULL,
    `slots`      INT          UNSIGNED NULL,
    `status`     VARCHAR(10)           NOT NULL DEFAULT 'free',
    `updated_at` TIMESTAMP             NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    FOREIGN KEY (`scope_id`) REFERENCES `standort` (`StandortID`) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY `uk_scope_time` (`scope_id`, `time`, `seat`),

    KEY `idx_status`    (`status`),
    KEY `idx_process`   (`process_id`),
    KEY `idx_updated`   (`updated_at`)
) ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;
