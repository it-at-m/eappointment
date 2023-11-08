DROP TABLE IF EXISTS `source`;
CREATE TABLE `source` (
    `source` VARCHAR(50) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `label` VARCHAR(100) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `editable` TINYINT(1) NOT NULL DEFAULT 0,
    `contact__name` VARCHAR(50) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `contact__email` VARCHAR(50) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `lastChange` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX (`source`, `lastChange`)
)
ENGINE = InnoDB
CHARACTER SET utf8
COLLATE utf8_unicode_ci;

INSERT INTO `source` SET `source` = "dldb", `label` = "Dienstleistungsdatenbank", `editable` = 0, `contact__name` = "Landesredaktion", `contact__email` = "dienstleistungsdatenbank@skzl.berlin.de";
