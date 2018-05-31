DROP TABLE IF EXISTS `apikey`;
CREATE TABLE `apikey` (
    `key` varchar(100) NOT NULL,
    `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
PRIMARY KEY (`key`))
ENGINE = InnoDB
CHARACTER SET utf8
COLLATE utf8_unicode_ci;

DROP TABLE IF EXISTS `apiquota`;
CREATE TABLE `apiquota` (
    `key` varchar(100) NOT NULL,
    `route` varchar(100) NOT NULL,
    `period` enum(
        "minute",
        "hour",
        "day",
        "week",
        "month"
    ) NOT NULL,
    `requests` INT(3) NOT NULL,
PRIMARY KEY (`key`))
ENGINE = InnoDB
CHARACTER SET utf8
COLLATE utf8_unicode_ci;
