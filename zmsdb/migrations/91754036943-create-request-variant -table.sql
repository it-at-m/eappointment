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

INSERT INTO `request_variant` (`name`) VALUES ('Präsenz');
INSERT INTO `request_variant` (`name`) VALUES ('Telefon');
INSERT INTO `request_variant` (`name`) VALUES ('Videoberatung');
INSERT INTO `request_variant` (`name`) VALUES ('Großkunde');
INSERT INTO `request_variant` (`name`) VALUES ('Kleinkunde');
INSERT INTO `request_variant` (`name`) VALUES ('Familie');
INSERT INTO `request_variant` (`name`) VALUES ('Einzelperson');
