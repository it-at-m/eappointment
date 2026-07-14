DROP TABLE IF EXISTS `apikey`;
CREATE TABLE `apikey` (
    `key` varchar(100) NOT NULL,
    `createIP` VARCHAR(40) NOT NULL , 
    `ts` bigint(20) NOT NULL,
PRIMARY KEY (`key`))
ENGINE = InnoDB
CHARACTER SET utf8
COLLATE utf8_unicode_ci;

DROP TABLE IF EXISTS `apiquota`;
CREATE TABLE `apiquota` (
    `quotaid` INT(5) UNSIGNED NOT NULL AUTO_INCREMENT ,
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
    `ts` bigint(20) NOT NULL,
PRIMARY KEY (`quotaid`))
ENGINE = InnoDB
CHARACTER SET utf8
COLLATE utf8_unicode_ci;
