
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
    `status` ENUM("free", "full", "cancelled") DEFAULT "free",
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

DROP TABLE IF EXISTS `slot_sequence`;
CREATE TABLE `slot_sequence` ( 
    `slotsequence` INT(5) UNSIGNED, 

    PRIMARY KEY (`slotsequence`)
) 
COMMENT="This table is just a helper for some queries"
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

INSERT INTO slot_sequence VALUES (1), (2), (3), (4), (5), (6), (7), (8), (9), (10), (11), (12), (13), (14), (15), (16), (17), (18), (19), (20), (21), (22), (23), (24), (25), (26), (27), (28), (29), (30), (31), (32), (33), (34), (35), (36), (37), (38), (39), (40), (41), (42), (43), (44), (45), (46), (47), (48), (49), (50);

