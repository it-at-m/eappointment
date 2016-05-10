CREATE TABLE IF NOT EXISTS `zmsbo`.`config` ( 
	`name` VARCHAR(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL , 
	`value` VARCHAR(250) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL , 
	`changeTimestamp` BIGINT(20) NOT NULL DEFAULT '0' , 
PRIMARY KEY (`name`)) 
ENGINE = MyISAM 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;