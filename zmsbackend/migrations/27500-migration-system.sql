DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` ( 
	`filename` VARCHAR(250) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL , 
	`changeTimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
PRIMARY KEY (`filename`)) 
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

INSERT INTO `migrations` SET filename="27500-migration-system.sql";
