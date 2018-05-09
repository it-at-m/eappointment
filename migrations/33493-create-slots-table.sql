
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

ALTER TABLE `standort`
    ADD COLUMN `updateTimestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ADD INDEX (`updateTimestamp`);

UPDATE `standort` SET updateTimestamp = NOW();

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
    `slotID` INT(5) UNSIGNED NOT NULL, 
    `processID` INT(5) UNSIGNED NOT NULL, 
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
    `slotsequence` INT(5) UNSIGNED PRIMARY KEY 
) 
COMMENT="This table is just a helper for some queries"
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

INSERT INTO slot_sequence VALUES (1), (2), (3), (4), (5), (6), (7), (8), (9), (10), (11), (12), (13), (14), (15), (16), (17), (18), (19), (20), (21), (22), (23), (24), (25), (26), (27), (28), (29), (30), (31), (32), (33), (34), (35), (36), (37), (38), (39), (40), (41), (42), (43), (44), (45), (46), (47), (48), (49), (50);

DROP TABLE IF EXISTS `process_sequence`;
CREATE TABLE `process_sequence` ( 
    `processId` INT(5) UNSIGNED PRIMARY KEY 
) 
COMMENT="This table is just a helper for some queries"
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

INSERT INTO
`process_sequence`
(
    `processId`
)
SELECT
Zahlen.Ziffer
FROM
(
    SELECT
    (HUNDREDTHOUSANDS.Ziffer + TENTHOUSANDS.Ziffer + THOUSANDS.Ziffer + HUNDREDS.Ziffer + TENS.Ziffer + ONES.Ziffer) Ziffer
    FROM
    (
        SELECT 0  Ziffer
        UNION ALL
        SELECT 1 Ziffer
        UNION ALL
        SELECT 2 Ziffer
        UNION ALL
        SELECT 3 Ziffer
        UNION ALL
        SELECT 4 Ziffer
        UNION ALL
        SELECT 5 Ziffer
        UNION ALL
        SELECT 6 Ziffer
        UNION ALL
        SELECT 7 Ziffer
        UNION ALL
        SELECT 8 Ziffer
        UNION ALL
        SELECT 9 Ziffer
    ) ONES
    CROSS JOIN
    (
        SELECT 0 Ziffer
        UNION ALL
        SELECT 10 Ziffer
        UNION ALL
        SELECT 20 Ziffer
        UNION ALL
        SELECT 30 Ziffer
        UNION ALL
        SELECT 40 Ziffer
        UNION ALL
        SELECT 50 Ziffer
        UNION ALL
        SELECT 60 Ziffer
        UNION ALL
        SELECT 70 Ziffer
        UNION ALL
        SELECT 80 Ziffer
        UNION ALL
        SELECT 90 Ziffer
    ) TENS
    CROSS JOIN
    (
        SELECT 0 Ziffer
        UNION ALL
        SELECT 100 Ziffer
        UNION ALL
        SELECT 200 Ziffer
        UNION ALL
        SELECT 300 Ziffer
        UNION ALL
        SELECT 400 Ziffer
        UNION ALL
        SELECT 500 Ziffer
        UNION ALL
        SELECT 600 Ziffer
        UNION ALL
        SELECT 700 Ziffer
        UNION ALL
        SELECT 800 Ziffer
        UNION ALL
        SELECT 900 Ziffer
    ) HUNDREDS
    CROSS JOIN
    (
        SELECT 0 Ziffer
        UNION ALL
        SELECT 1000 Ziffer
        UNION ALL
        SELECT 2000 Ziffer
        UNION ALL
        SELECT 3000 Ziffer
        UNION ALL
        SELECT 4000 Ziffer
        UNION ALL
        SELECT 5000 Ziffer
        UNION ALL
        SELECT 6000 Ziffer
        UNION ALL
        SELECT 7000 Ziffer
        UNION ALL
        SELECT 8000 Ziffer
        UNION ALL
        SELECT 9000 Ziffer
    ) THOUSANDS
    CROSS JOIN
    (
        SELECT 0 Ziffer
        UNION ALL
        SELECT 10000 Ziffer
        UNION ALL
        SELECT 20000 Ziffer
        UNION ALL
        SELECT 30000 Ziffer
        UNION ALL
        SELECT 40000 Ziffer
        UNION ALL
        SELECT 50000 Ziffer
        UNION ALL
        SELECT 60000 Ziffer
        UNION ALL
        SELECT 70000 Ziffer
        UNION ALL
        SELECT 80000 Ziffer
        UNION ALL
        SELECT 90000 Ziffer
    ) TENTHOUSANDS
    CROSS JOIN
    (
        SELECT 0 Ziffer
        UNION ALL
        SELECT 100000 Ziffer
        UNION ALL
        SELECT 200000 Ziffer
        UNION ALL
        SELECT 300000 Ziffer
        UNION ALL
        SELECT 400000 Ziffer
        UNION ALL
        SELECT 500000 Ziffer
        UNION ALL
        SELECT 600000 Ziffer
        UNION ALL
        SELECT 700000 Ziffer
        UNION ALL
        SELECT 800000 Ziffer
        UNION ALL
        SELECT 900000 Ziffer
    ) HUNDREDTHOUSANDS
) Zahlen;

DELETE FROM `process_sequence` WHERE `processId` < 100000;

