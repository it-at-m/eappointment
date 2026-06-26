-- Normalize request_variant IDs to the canonical order (idempotent).
-- Preserves request.variant_id by remapping through variant names.

SET FOREIGN_KEY_CHECKS = 0;

CREATE TEMPORARY TABLE `tmp_request_variant` AS
SELECT `id`, `name` FROM `request_variant`;

DROP TABLE IF EXISTS `request_variant`;

CREATE TABLE `request_variant`
(
    `id`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(50) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_request_variant_name` (`name`)
) ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

INSERT INTO `request_variant` (`id`, `name`) VALUES
(1, 'Präsenz'),
(2, 'Telefon'),
(3, 'Videoberatung'),
(4, 'Einzelperson'),
(5, 'Familie'),
(6, 'Kleinkunde'),
(7, 'Großkunde');

ALTER TABLE `request_variant` AUTO_INCREMENT = 8;

UPDATE `request` r
INNER JOIN `tmp_request_variant` old ON r.`variant_id` = old.`id`
INNER JOIN `request_variant` new ON new.`name` = old.`name`
SET r.`variant_id` = new.`id`
WHERE r.`variant_id` IS NOT NULL;

DROP TEMPORARY TABLE `tmp_request_variant`;

SET FOREIGN_KEY_CHECKS = 1;
