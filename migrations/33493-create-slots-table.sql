
ALTER TABLE `oeffnungszeit`
    ADD COLUMN `updateTimestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ADD INDEX (`updateTimestamp`);

UPDATE `oeffnungszeit` SET updateTimestamp = NOW();

ALTER TABLE `buerger`
    ADD COLUMN `updateTimestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ADD INDEX (`updateTimestamp`);

UPDATE `buerger` SET updateTimestamp = NOW();

ALTER TABLE `feiertage`
    ADD COLUMN `updateTimestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ADD INDEX (`updateTimestamp`);

UPDATE `feiertage` SET updateTimestamp = NOW();

DROP TABLE IF EXISTS `slot`;
CREATE TABLE `slot` ( 

    `slotID` INT(5) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
    `scopeID` INT(5) UNSIGNED DEFAULT NULL, 
    `year` SMALLINT(5) UNSIGNED DEFAULT NULL, 
    `month` TINYINT(5) UNSIGNED DEFAULT NULL, 
    `day` TINYINT(5) UNSIGNED DEFAULT NULL, 
    `time` TIME DEFAULT NULL, 
    `availabilityID` INT(5) UNSIGNED DEFAULT NULL, 
    `public` TINYINT(5) UNSIGNED DEFAULT NULL, 
    `callcenter` TINYINT(5) UNSIGNED DEFAULT NULL, 
    `intern` TINYINT(5) UNSIGNED DEFAULT NULL, 
    `status` ENUM("free", "cancelled") DEFAULT "free",
    `slotTimeInMinutes` TINYINT(5) UNSIGNED DEFAULT NULL, 
    `createTimestamp` BIGINT(20),
    `updateTimestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX (`scopeID`, `year`, `month`, `day`, `time`, `status`),
    INDEX (`year`, `month`, `day`, `time`),
    INDEX (`availabilityID`),
    INDEX (`updateTimestamp`)
) 
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

DROP TABLE IF EXISTS `slot_hiera`;
CREATE TABLE `slot_hiera` ( 
    `slothieraID` BIGINT(5) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
    `slotID` INT(5) UNSIGNED DEFAULT NULL, 
    `ancestorID` INT(5) UNSIGNED DEFAULT NULL, 
    `ancestorLevel` TINYINT(5) UNSIGNED DEFAULT NULL, 

    INDEX (`slotID`, `ancestorID`),
    INDEX (`ancestorID`, `ancestorLevel`, `slotID`)
) 
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

DROP TABLE IF EXISTS `slot_process`;
CREATE TABLE `slot_process` ( 
    `slotID` INT(5) UNSIGNED DEFAULT NULL, 
    `processID` INT(5) UNSIGNED DEFAULT NULL, 
    `updateTimestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`slotID`, `processID`),
    INDEX (`processID`),
    INDEX (`updateTimestamp`)
) 
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

