CREATE TABLE IF NOT EXISTS `overview_calendar` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `scope_id`    INT    UNSIGNED NOT NULL,
    `process_id`  INT    UNSIGNED NOT NULL,
    `status`      ENUM('confirmed','cancelled') NOT NULL DEFAULT 'confirmed',
    `starts_at`   DATETIME NOT NULL,
    `ends_at`     DATETIME NOT NULL,
    `updated_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),

    KEY `idx_scope_starts`  (`scope_id`, `starts_at`),
    KEY `idx_scope_ends`    (`scope_id`, `ends_at`),
    KEY `idx_updated`       (`updated_at`),
    KEY `idx_status`        (`status`),
    KEY `idx_process`       (process_id),

    CONSTRAINT `fk_ocb_scope`
    FOREIGN KEY (`scope_id`)
    REFERENCES `standort`(`StandortID`)
    ON DELETE CASCADE ON UPDATE CASCADE
    )
    ENGINE=InnoDB
    DEFAULT CHARSET=utf8mb4
    COLLATE=utf8mb4_unicode_ci;
