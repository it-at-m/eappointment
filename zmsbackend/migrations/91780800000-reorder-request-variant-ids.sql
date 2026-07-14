UPDATE `request` r
INNER JOIN `request_variant` rv ON r.`variant_id` = rv.`id`
SET r.`variant_id` = CASE rv.`name`
    WHEN 'Präsenz' THEN 1
    WHEN 'Telefon' THEN 2
    WHEN 'Videoberatung' THEN 3
    WHEN 'Einzelperson' THEN 4
    WHEN 'Familie' THEN 5
    WHEN 'Kleinkunde' THEN 6
    WHEN 'Großkunde' THEN 7
    ELSE r.`variant_id`
END
WHERE r.`variant_id` IS NOT NULL;

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

INSERT INTO `request_variant` (`id`, `name`) VALUES (1, 'Präsenz');
INSERT INTO `request_variant` (`id`, `name`) VALUES (2, 'Telefon');
INSERT INTO `request_variant` (`id`, `name`) VALUES (3, 'Videoberatung');
INSERT INTO `request_variant` (`id`, `name`) VALUES (4, 'Einzelperson');
INSERT INTO `request_variant` (`id`, `name`) VALUES (5, 'Familie');
INSERT INTO `request_variant` (`id`, `name`) VALUES (6, 'Kleinkunde');
INSERT INTO `request_variant` (`id`, `name`) VALUES (7, 'Großkunde');
