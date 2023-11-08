DROP TABLE IF EXISTS `eventlog`;
CREATE TABLE `eventlog` (
    `eventId` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `eventName` CHAR(80) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
    `origin` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
    `referenceType` CHAR(80) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
    `reference` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL ,
    `sessionid` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL ,
    `contextjson` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin CHECK (JSON_VALID(`contextjson`)),
    `creationDateTime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ,
    `expirationDateTime` DATETIME NOT NULL DEFAULT '9999-12-23 00:00:00',
    INDEX (`reference`)
)
ENGINE = InnoDB
CHARACTER SET utf8
COLLATE utf8_unicode_ci;