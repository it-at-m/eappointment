
DROP TABLE IF EXISTS `preferences`;
CREATE TABLE `preferences` ( 
    `entity` ENUM("owner", "organisation", "department", "scope", "process", "availability"),
    `id` INT(5) UNSIGNED, 
    `groupName` VARCHAR(50) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `name` VARCHAR(100) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `value` TEXT,
    `updateTimestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`entity`, `id`, `groupName`, `name`),
    INDEX (`updateTimestamp`)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

INSERT INTO `preferences` SELECT 'scope', StandortID, 'appointment', 'startInDaysDefault', Termine_ab, NULL FROM standort;
INSERT INTO `preferences` SELECT 'scope', StandortID, 'appointment', 'endInDaysDefault', Termine_bis, NULL FROM standort;
